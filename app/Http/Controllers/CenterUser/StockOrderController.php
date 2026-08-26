<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\StockOrderDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CenterUser\StockOrderReceiveRequest;
use App\Http\Requests\CenterUser\StockOrderRequest;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductSupplier;
use App\Models\StockOrder;
use App\Services\StockOrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class StockOrderController extends Controller
{
    private $model = 'StockOrder';
    private $plural = 'stockorders';
    private $indexRoute;
    private StockOrderService $stockOrderService;

    public function __construct(StockOrderService $stockOrderService)
    {
        $this->stockOrderService = $stockOrderService;
        $this->indexRoute = 'center_user.' . $this->plural . '.index';
    }

    public function index(StockOrderDataTable $dataTable)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $title = __('locale.' . $this->plural);
        return $dataTable->render('CenterUser.SubViews.core-table', compact('title'));
    }

    public function create()
    {
        $can = 'CREATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);
        $title = __('general.add') . ' ' . __('locale.stockorder');
        $requestUrl = route('center_user.stockorders.updateOrCreate');
        $branches = Branch::with('translation')->get();

        return view('CenterUser.SubViews.StockOrder.create', compact(
            'title',
            'menu',
            'menu_link',
            'requestUrl',
            'branches'
        ));
    }

    public function updateOrCreate(StockOrderRequest $request)
    {
        $can = 'CREATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        try {
            $order = $this->stockOrderService->create(
                $request->validated(),
                auth('center_user')->id()
            );

            return MyHelper::responseJSON(
                __('admin.operation_done_successfully'),
                Response::HTTP_CREATED,
                ['id' => $order->id, 'redirect_url' => route($this->indexRoute)]
            );
        } catch (\Exception $e) {
            return MyHelper::responseJSON($e->getMessage() ?: __('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function suppliers(Request $request)
    {
        $can = 'CREATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $branchId = (int) $request->get('branch_id');
        if (!$branchId) {
            return response()->json(['data' => []]);
        }

        $suppliers = ProductSupplier::query()
            ->whereIn('id', function ($sub) use ($branchId) {
                $sub->select('product_product_supplier.product_supplier_id')
                    ->from('product_product_supplier')
                    ->join('product_branches', 'product_branches.product_id', '=', 'product_product_supplier.product_id')
                    ->where('product_branches.branch_id', $branchId)
                    ->whereNull('product_branches.deleted_at');
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'data' => $suppliers->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
            ]),
        ]);
    }

    public function products(Request $request)
    {
        $can = 'CREATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $branchId = (int) $request->get('branch_id');
        $supplierId = (int) $request->get('supplier_id');
        $search = trim((string) $request->get('q', ''));

        if (!$branchId || !$supplierId) {
            return response()->json(['data' => []]);
        }

        $query = Product::with([
            'translation',
            'category',
            'primarySku',
            'productBranches' => fn ($q) => $q->where('branch_id', $branchId),
        ])
            ->whereHas('productBranches', fn ($q) => $q->where('branch_id', $branchId))
            ->whereHas('productSuppliers', fn ($q) => $q->where('product_suppliers.id', $supplierId));

        if ($search !== '') {
            $like = '%' . mb_strtolower($search) . '%';
            $query->where(function ($q) use ($like, $search) {
                $q->where('barcode', 'like', '%' . $search . '%')
                    ->orWhereHas('translation', function ($tq) use ($like) {
                        $tq->whereRaw('LOWER(name) LIKE ?', [$like]);
                    })
                    ->orWhereHas('skus', function ($sq) use ($search) {
                        $sq->where('sku', 'like', '%' . $search . '%');
                    });
            });
        }

        $products = $query->orderBy('id')->limit(100)->get();

        $data = $products->map(function ($product) {
            $branchStock = $product->productBranches->first();

            return [
                'id' => $product->id,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'sku' => $product->primarySku->sku ?? null,
                'category' => $product->category->name ?? '-',
                'stock_quantity' => (float) ($branchStock->stock_quantity ?? 0),
                'supply_price' => (float) ($product->supply_price ?? 0),
                'image' => method_exists($product, 'getFirstMediaUrl') ? ($product->getFirstMediaUrl('image') ?: null) : null,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function receive($id)
    {
        $can = 'UPDATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $order = StockOrder::with([
            'items.product.translation',
            'items.product.primarySku',
            'branch.translation',
            'supplier',
        ])->findOrFail($id);

        if ($order->status !== 'ordered') {
            return redirect()->route('center_user.stockorders.show', ['id' => $order->id]);
        }

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);
        $title = __('general.receive') . ' ' . $order->order_number;
        $requestUrl = route('center_user.stockorders.receive.store', ['id' => $order->id]);

        return view('CenterUser.SubViews.StockOrder.receive', compact(
            'order',
            'title',
            'menu',
            'menu_link',
            'requestUrl'
        ));
    }

    public function receiveStore(StockOrderReceiveRequest $request, $id)
    {
        $can = 'UPDATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $order = StockOrder::with('items')->findOrFail($id);

        try {
            $order = $this->stockOrderService->receive($order, $request->validated()['items']);

            return MyHelper::responseJSON(
                __('admin.operation_done_successfully'),
                Response::HTTP_OK,
                ['id' => $order->id, 'redirect_url' => route('center_user.stockorders.show', ['id' => $order->id])]
            );
        } catch (\Exception $e) {
            return MyHelper::responseJSON($e->getMessage() ?: __('admin.an_error_occurred'), Response::HTTP_BAD_REQUEST);
        }
    }

    public function show($id)
    {
        $can = 'SHOW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $order = StockOrder::with([
            'items.product.translation',
            'items.product.primarySku',
            'branch.translation',
            'supplier',
            'createdBy',
        ])->withTrashed()->findOrFail($id);

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);
        $title = __('locale.stockorder') . ' ' . $order->order_number;

        return view('CenterUser.SubViews.StockOrder.show', compact(
            'order',
            'title',
            'menu',
            'menu_link'
        ));
    }
}
