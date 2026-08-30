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
}
