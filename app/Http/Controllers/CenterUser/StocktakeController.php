<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\StocktakeDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CenterUser\StocktakeRequest;
use App\Models\Stocktake;
use App\Models\StocktakeProduct;
use App\Services\CRUDService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class StocktakeController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Stocktake';
    private $plural = 'stocktakes';
    private $indexRoute;
    private $updateOrCreateRoute;

    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
        $this->indexRoute = 'center_user.' . $this->plural . '.index';
        $this->updateOrCreateRoute = 'center_user.' . $this->plural . '.updateOrCreate';
    }

    public function index(StocktakeDataTable $dataTable)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        $title = __('locale.' . $this->plural);
        return $dataTable->render("CenterUser.SubViews.core-table", compact('title'));
    }

    public function create(Request $request)
    {
        if (isset($request->id)) {
            $can = 'UPDATE_' . Str::upper($this->plural);
        } else {
            $can = 'CREATE_' . Str::upper($this->plural);
        }
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $item = null;
        if ($request->id) {
            $relations = ['branches.translation', 'startedBy', 'reviewedBy'];
            $item = $this->crudService->find($this->model, $request->id, $relations);
        }

        $branches = \App\Models\Branch::with('translation')->get();

        if (is_null($item)) {
            $title = __('general.add');
            $requestUrl = route($this->updateOrCreateRoute);
        } else {
            $title = __('general.edit');
            $requestUrl = route($this->updateOrCreateRoute, ['id' => $request->id]);
        }

        $view = 'CenterUser.SubViews.' . $this->model . '.create';
        return view($view, compact('item', 'requestUrl', 'title', 'menu', 'menu_link', 'branches'));
    }

    public function updateOrCreate(StocktakeRequest $request)
    {
        $responseCode = Response::HTTP_OK;
        if (isset($request->id)) {
            $can = 'UPDATE_' . Str::upper($this->plural);
        } else {
            $responseCode = Response::HTTP_CREATED;
            $can = 'CREATE_' . Str::upper($this->plural);
        }
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        $newRequest = $request->only(
            'id',
            'name',
            'description'
        );

        // Set default status if creating new
        if (!isset($newRequest['id'])) {
            $newRequest['status'] = 'draft';
        }

        $item = $this->crudService->updateOrCreate($this->model, $newRequest, true);

        // Handle branches
        if ($item && $request->has('branch_ids')) {
            $item->branches()->sync($request->input('branch_ids', []));
        }

        if ($item) {
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('center_user.stocktakes.start', ['id' => $item->id]));
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function start(Request $request, $id)
    {
        $can = 'UPDATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        $stocktake = Stocktake::with(['branches', 'stocktakeProducts'])->findOrFail($id);
        
        if ($stocktake->status == 'draft') {
            $stocktake->status = 'in_progress';
            $stocktake->started_at = now();
            $stocktake->started_by = auth('center_user')->id();
            $stocktake->save();

            // Initialize products for all branches
            foreach ($stocktake->branches as $branch) {
                $products = \App\Models\Product::whereHas('productBranches', function($q) use ($branch) {
                    $q->where('branch_id', $branch->id);
                })->get();

                foreach ($products as $product) {
                    $productBranch = $product->productBranches()->where('branch_id', $branch->id)->first();
                    $expectedQty = $productBranch ? $productBranch->stock_quantity : 0;

                    // Only create if it doesn't exist
                    if (!StocktakeProduct::where('stocktake_id', $stocktake->id)
                        ->where('product_id', $product->id)
                        ->where('branch_id', $branch->id)
                        ->exists()) {
                        StocktakeProduct::create([
                            'stocktake_id' => $stocktake->id,
                            'product_id' => $product->id,
                            'branch_id' => $branch->id,
                            'expected_qty' => $expectedQty,
                            'counted_qty' => null,
                            'difference' => 0,
                            'cost' => 0,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('center_user.stocktakes.count', ['id' => $id]);
    }

    public function count(Request $request, $id)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        $stocktake = Stocktake::with([
            'branches.translation', 
            'stocktakeProducts.product.translation', 
            'stocktakeProducts.product.primarySku',
            'stocktakeProducts.branch.translation',
            'stocktakeProducts.countedBy'
        ])->findOrFail($id);
        
        if ($stocktake->status != 'in_progress') {
            return redirect()->route('center_user.stocktakes.index')->with('error', 'Stocktake is not in progress');
        }

        $title = 'Count Products';
        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $view = 'CenterUser.SubViews.' . $this->model . '.count';
        return view($view, compact('stocktake', 'title', 'menu', 'menu_link'));
    }

    public function updateCount(Request $request, $id)
    {
        $can = 'UPDATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        $request->validate([
            'products' => 'required|array',
            'products.*.stocktake_product_id' => 'required|exists:stocktake_products,id',
            'products.*.counted_qty' => 'nullable|integer|min:0',
        ]);

        $stocktake = Stocktake::findOrFail($id);
        
        foreach ($request->input('products', []) as $productData) {
            $stocktakeProduct = StocktakeProduct::find($productData['stocktake_product_id']);
            if ($stocktakeProduct && $stocktakeProduct->stocktake_id == $stocktake->id) {
                $countedQty = isset($productData['counted_qty']) && $productData['counted_qty'] !== '' ? (int)$productData['counted_qty'] : null;
                $stocktakeProduct->counted_qty = $countedQty;
                $stocktakeProduct->difference = $countedQty !== null ? $countedQty - $stocktakeProduct->expected_qty : 0;
                
                // Calculate cost using supply_price
                if ($countedQty !== null && $stocktakeProduct->product) {
                    $supplyPrice = $stocktakeProduct->product->supply_price ?? 0;
                    $stocktakeProduct->cost = $stocktakeProduct->difference * $supplyPrice;
                } else {
                    $stocktakeProduct->cost = 0;
                }
                
                $stocktakeProduct->counted_by = auth('center_user')->id();
                $stocktakeProduct->save();
            }
        }

        return MyHelper::responseJSON(__('admin.operation_done_successfully'), Response::HTTP_OK);
    }

    public function complete($id)
    {
        $can = 'UPDATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        $stocktake = Stocktake::findOrFail($id);
        
        if ($stocktake->status == 'in_progress') {
            $stocktake->status = 'completed';
            $stocktake->completed_at = now();
            $stocktake->save();
        }

        return redirect()->route('center_user.stocktakes.details', ['id' => $id])->with('success', 'Stocktake completed successfully');
    }

    public function details(Request $request, $id)
    {
        $can = 'SHOW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        $stocktake = Stocktake::with([
            'branches.translation',
            'stocktakeProducts.product.translation',
            'stocktakeProducts.product.primarySku',
            'stocktakeProducts.branch.translation',
            'stocktakeProducts.countedBy',
            'startedBy',
            'reviewedBy'
        ])->findOrFail($id);

        $title = 'Stocktake Details';
        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $view = 'CenterUser.SubViews.' . $this->model . '.details';
        return view($view, compact('stocktake', 'title', 'menu', 'menu_link'));
    }

    public function review(Request $request, $id)
    {
        $can = 'UPDATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $stocktake = Stocktake::with('stocktakeProducts')->findOrFail($id);
        
        if ($stocktake->status == 'completed') {
            // Update stock quantities
            foreach ($stocktake->stocktakeProducts as $stocktakeProduct) {
                if ($stocktakeProduct->counted_qty !== null) {
                    $productBranch = \App\Models\ProductBranch::where('product_id', $stocktakeProduct->product_id)
                        ->where('branch_id', $stocktakeProduct->branch_id)
                        ->first();
                    
                    if ($productBranch) {
                        $productBranch->stock_quantity = $stocktakeProduct->counted_qty;
                        $productBranch->save();
                    }
                }
            }

            $stocktake->status = 'reviewed';
            $stocktake->reviewed_by = auth('center_user')->id();
            $stocktake->notes = $request->input('notes');
            $stocktake->save();
        }

        return redirect()->route('center_user.stocktakes.details', ['id' => $id])->with('success', 'Stocktake reviewed and stock updated successfully');
    }
}
