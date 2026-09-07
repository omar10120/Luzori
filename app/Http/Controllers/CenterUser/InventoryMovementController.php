<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\InventoryMovementDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class InventoryMovementController extends Controller
{
    private $plural = 'inventorymovements';
    private $indexRoute;

    public function __construct()
    {
        $this->indexRoute = 'center_user.' . $this->plural . '.index';
    }

    public function index(InventoryMovementDataTable $dataTable)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $title = __('locale.' . $this->plural);
        return $dataTable->render('CenterUser.SubViews.core-table', compact('title'));
    }

    public function snapshot(Request $request)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $branchId = $request->integer('branch_id') ?: null;

        $products = Product::query()
            ->with('translation')
            ->withSum([
                'productBranches as current_stock' => function ($q) use ($branchId) {
                    if ($branchId) {
                        $q->where('branch_id', $branchId);
                    }
                },
            ], 'stock_quantity')
            ->withSum([
                'inventoryMovements as sold_out' => function ($q) use ($branchId) {
                    $q->where('movement_type', InventoryMovement::TYPE_SALE);
                    if ($branchId) {
                        $q->where('branch_id', $branchId);
                    }
                },
            ], 'quantity')
            ->withSum([
                'inventoryMovements as sold_restored' => function ($q) use ($branchId) {
                    $q->where('movement_type', InventoryMovement::TYPE_SALE_DELETED);
                    if ($branchId) {
                        $q->where('branch_id', $branchId);
                    }
                },
            ], 'quantity')
            ->withSum([
                'inventoryMovements as total_ordered' => function ($q) use ($branchId) {
                    $q->whereIn('movement_type', [
                        InventoryMovement::TYPE_STOCK_ORDER,
                        InventoryMovement::TYPE_PURCHASE,
                        InventoryMovement::TYPE_INITIAL,
                    ])->where('quantity', '>', 0);
                    if ($branchId) {
                        $q->where('branch_id', $branchId);
                    }
                },
            ], 'quantity')
            ->orderBy('id')
            ->get()
            ->map(function ($product) {
                $soldOut = abs((int) ($product->sold_out ?? 0));
                $restored = (int) ($product->sold_restored ?? 0);

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'barcode' => $product->barcode,
                    'current_stock' => (int) ($product->current_stock ?? 0),
                    'net_sold' => max(0, $soldOut - $restored),
                    'total_ordered' => (int) ($product->total_ordered ?? 0),
                ];
            });

        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $products);
    }

    public function show(Request $request, $productId)
    {
        $can = 'SHOW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $product = Product::with('translation')->findOrFail($productId);
        $branchId = $request->integer('branch_id') ?: null;
        $branches = Branch::with('translation')->get();

        $movements = InventoryMovement::query()
            ->with(['branch.translation'])
            ->where('product_id', $product->id)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);
        $title = __('locale.inventorymovement') . ': ' . ($product->name ?? ('#' . $product->id));

        return view('CenterUser.SubViews.InventoryMovement.show', compact(
            'product',
            'movements',
            'branches',
            'branchId',
            'title',
            'menu',
            'menu_link'
        ));
    }

    public function export(Request $request, string $format)
    {
        $can = 'SHOW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        abort_unless(in_array($format, ['csv', 'pdf'], true), 404);

        $productIds = collect($request->input('product_ids', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $branchId = $request->integer('branch_id') ?: null;

        $movements = InventoryMovement::query()
            ->with(['product.translation', 'branch.translation'])
            ->when($productIds->isNotEmpty(), fn ($query) => $query->whereIn('product_id', $productIds))
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->orderByDesc('id')
            ->get();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('CenterUser.SubViews.InventoryMovement.export', [
                'movements' => $movements,
                'title' => __('locale.inventorymovement'),
            ], [], ['orientation' => 'landscape']);

            return $pdf->download('inventory-movements-' . now()->format('YmdHis') . '.pdf');
        }

        return response()->streamDownload(function () use ($movements) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                '#', __('field.product_name'), __('field.barcode'), __('field.created_at'),
                __('field.branch'), __('field.movement_type'), __('field.quantity'),
                __('field.reference'), __('field.notes'),
            ]);

            foreach ($movements as $movement) {
                fputcsv($handle, [
                    $movement->id,
                    $movement->product?->name ?? '-',
                    $movement->product?->barcode ?? '-',
                    optional($movement->created_at)->format('Y-m-d H:i'),
                    $movement->branch?->name ?? '-',
                    __('field.movement_' . $movement->movement_type),
                    $movement->quantity,
                    $movement->reference_type && $movement->reference_id
                        ? $movement->reference_type . ' #' . $movement->reference_id
                        : '-',
                    $movement->notes ?: '-',
                ]);
            }

            fclose($handle);
        }, 'inventory-movements-' . now()->format('YmdHis') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
