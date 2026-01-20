<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Services\CRUDService;
use App\Helpers\MyHelper;
use App\Http\Requests\CenterAPI\Product\ProductRequest;
use App\Http\Requests\CenterAPI\Product\CheckProductIdRequest;
use App\Http\Resources\PaginateDateResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductDetailsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Product';

    /**
     * @param CRUDService $crudService
     */
    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
    }

    public function all(Request $request)
    {
        if ($request->page == 0) {
            $items = $this->crudService->all($this->model, ['translation', 'media', 'brand', 'category', 'productSuppliers', 'skus'], 0);
        } else {
            $items = $this->crudService->paginate($this->model, ['translation', 'media', 'brand', 'category', 'productSuppliers', 'skus'], 0);
        }
        if ($items) {
            if ($request->page == 0) {
                $items = ProductResource::collection($items);
                return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items);
            } else {
                $paginationData = PaginateDateResource::make($items);
                $items = ProductResource::collection($items);
                return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items, $paginationData);
            }
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function find(CheckProductIdRequest $request)
    {
        $item = $this->crudService->find($this->model, $request->id, ['translation', 'media', 'brand', 'category', 'productSuppliers', 'skus'], 0);
        if ($item) {
            $item = ProductDetailsResource::make($item);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function add(ProductRequest $request)
    {
        $newRequest = $request->only(
            'ar',
            'en',
            'barcode',
            'brand_id',
            'category_id',
            'measure_unit',
            'measure_amount',
            'short_description',
            'supply_price',
            'retail_price',
            'markup',
            'allow_retail_sales',
            'track_stock',
            'current_stock',
            'image',
        );

        // Calculate markup if supply_price and retail_price are provided and markup is not manually set
        if (isset($newRequest['supply_price']) && isset($newRequest['retail_price']) && $newRequest['supply_price'] > 0) {
            if (!isset($newRequest['markup']) || empty($newRequest['markup'])) {
                $markupValue = (($newRequest['retail_price'] - $newRequest['supply_price']) / $newRequest['supply_price']) * 100;
                $newRequest['markup'] = round($markupValue, 2);
            }
        }

        // If track_stock is false, set current_stock to null
        if (isset($newRequest['track_stock']) && !$newRequest['track_stock']) {
            $newRequest['current_stock'] = null;
        }

        // Set default values
        if (!isset($newRequest['allow_retail_sales'])) {
            $newRequest['allow_retail_sales'] = true;
        }
        if (!isset($newRequest['track_stock'])) {
            $newRequest['track_stock'] = false;
        }

        $item = $this->crudService->updateOrCreate($this->model, $newRequest, true);

        // Handle SKUs
        if ($item && $request->has('skus')) {
            // Delete existing SKUs
            $item->skus()->delete();
            
            // Create new SKUs - only first one is primary, rest are secondary
            $skus = $request->input('skus', []);
            if (!empty($skus)) {
                foreach ($skus as $index => $skuData) {
                    if (!empty($skuData['sku'])) {
                        $item->skus()->create([
                            'sku' => $skuData['sku'],
                            'type' => $index === 0 ? 'primary' : 'secondary',
                            'order' => $index,
                        ]);
                    }
                }
            }
        }

        // Handle Product Suppliers
        if ($item && $request->has('product_supplier_ids')) {
            $item->productSuppliers()->sync($request->input('product_supplier_ids', []));
        } elseif ($item && !$request->has('product_supplier_ids')) {
            // If no suppliers provided, detach all
            $item->productSuppliers()->detach();
        }
        if ($item) {
            $item = ProductResource::make($item);
            return MyHelper::responseJSON(__('api.addSuccessfully'), Response::HTTP_CREATED, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function edit(ProductRequest $request)
    {
        $newRequest = $request->only(
            'id',
            'ar',
            'en',
            'barcode',
            'brand_id',
            'category_id',
            'measure_unit',
            'measure_amount',
            'short_description',
            'supply_price',
            'retail_price',
            'markup',
            'allow_retail_sales',
            'track_stock',
            'current_stock',
            'image',
        );

        // Calculate markup if supply_price and retail_price are provided and markup is not manually set
        if (isset($newRequest['supply_price']) && isset($newRequest['retail_price']) && $newRequest['supply_price'] > 0) {
            if (!isset($newRequest['markup']) || empty($newRequest['markup'])) {
                $markupValue = (($newRequest['retail_price'] - $newRequest['supply_price']) / $newRequest['supply_price']) * 100;
                $newRequest['markup'] = round($markupValue, 2);
            }
        }

        // If track_stock is false, set current_stock to null
        if (isset($newRequest['track_stock']) && !$newRequest['track_stock']) {
            $newRequest['current_stock'] = null;
        }

        // Set default values
        if (!isset($newRequest['allow_retail_sales'])) {
            $newRequest['allow_retail_sales'] = true;
        }
        if (!isset($newRequest['track_stock'])) {
            $newRequest['track_stock'] = false;
        }

        $item = $this->crudService->updateOrCreate($this->model, $newRequest, true);

        // Handle SKUs
        if ($item && $request->has('skus')) {
            // Delete existing SKUs
            $item->skus()->delete();
            
            // Create new SKUs - only first one is primary, rest are secondary
            $skus = $request->input('skus', []);
            if (!empty($skus)) {
                foreach ($skus as $index => $skuData) {
                    if (!empty($skuData['sku'])) {
                        $item->skus()->create([
                            'sku' => $skuData['sku'],
                            'type' => $index === 0 ? 'primary' : 'secondary',
                            'order' => $index,
                        ]);
                    }
                }
            }
        }

        // Handle Product Suppliers
        if ($item && $request->has('product_supplier_ids')) {
            $item->productSuppliers()->sync($request->input('product_supplier_ids', []));
        } elseif ($item && !$request->has('product_supplier_ids')) {
            // If no suppliers provided, detach all
            $item->productSuppliers()->detach();
        }
        if ($item) {
            $item = ProductResource::make($item);
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('center_user.products.index'));
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(CheckProductIdRequest $request)
    {
        $item = $this->crudService->delete($this->model, $request->id);
        if ($item) {
            return MyHelper::responseJSON(__('api.deleteSuccessfully'), Response::HTTP_OK);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
