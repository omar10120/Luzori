<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\ProductDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CenterUser\ProductRequest;
use App\Services\CRUDService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Product';
    private $plural = 'products';
    private $indexRoute;
    private $updateOrCreateRoute;

    /**
     * @param CRUDService $crudService
     */
    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
        $this->indexRoute = 'center_user.' . $this->plural . '.index';
        $this->updateOrCreateRoute = 'center_user.' . $this->plural . '.updateOrCreate';
    }

    public function index(ProductDataTable $dataTable)
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
            $relations = ['media', 'translation', 'brand', 'category', 'productSuppliers', 'skus', 'productBranches.branch.translation'];
            $item = $this->crudService->find($this->model, $request->id, $relations);
        }

        // Get brands, categories, product suppliers, and branches for dropdowns
        $brands = \App\Models\Brand::all();
        $categories = \App\Models\Category::all();
        $productSuppliers = \App\Models\ProductSupplier::all();
        $branches = \App\Models\Branch::with('translation')->get();

        if (is_null($item)) {
            $title = __('general.add');
            $requestUrl = route($this->updateOrCreateRoute);
        } else {
            $title = __('general.edit');
            $requestUrl = route($this->updateOrCreateRoute, ['id' => $request->id]);
        }

        $view = 'CenterUser.SubViews.' . $this->model . '.index';
        return view($view, compact('item', 'requestUrl', 'title', 'menu', 'menu_link', 'brands', 'categories', 'productSuppliers', 'branches'));
    }

    public function updateOrCreate(ProductRequest $request)
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
            'image'
        );

        // Calculate markup if supply_price and retail_price are provided and markup is not manually set
        if (isset($newRequest['supply_price']) && isset($newRequest['retail_price']) && $newRequest['supply_price'] > 0) {
            if (!isset($newRequest['markup']) || empty($newRequest['markup'])) {
                $markupValue = (($newRequest['retail_price'] - $newRequest['supply_price']) / $newRequest['supply_price']) * 100;
                $newRequest['markup'] = round($markupValue, 2);
            }
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

        // Handle Product Branches
        if ($item && $request->has('product_branches')) {
            $productBranches = $request->input('product_branches', []);
            $syncData = [];
            
            foreach ($productBranches as $branchData) {
                // Only process if branch_id is set and not empty
                if (!empty($branchData['branch_id']) && isset($branchData['stock_quantity'])) {
                    $stockQuantity = (int)($branchData['stock_quantity'] ?? 0);
                    $syncData[$branchData['branch_id']] = [
                        'stock_quantity' => $stockQuantity
                    ];
                }
            }
            
            // Sync branches with stock quantities (this will update existing or create new)
            // If no branches selected, detach all
            if (empty($syncData)) {
                $item->branches()->detach();
            } else {
                $item->branches()->sync($syncData);
            }
        }

        // Handle Multiple Images
        if ($item) {
            // Handle deleted images
            if ($request->has('deleted_images') && !empty($request->deleted_images)) {
                $deletedIds = json_decode($request->deleted_images, true);
                if (is_array($deletedIds)) {
                    foreach ($deletedIds as $mediaId) {
                        $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId);
                        if ($media && $media->model_id == $item->id && $media->model_type == 'App\Models\Product') {
                            $media->delete();
                        }
                    }
                }
            }

            // Handle new image uploads
            $newlyUploadedMedia = [];
            if ($request->hasFile('image')) {
                $files = $request->file('image');
                foreach ($files as $file) {
                    $media = $item->addMedia($file)->toMediaCollection('Product');
                    $newlyUploadedMedia[] = $media;
                }
            }

            // Handle image ordering
            if ($request->has('image_order') && !empty($request->image_order)) {
                $orderArray = json_decode($request->image_order, true);
                if (is_array($orderArray) && !empty($orderArray)) {
                    $tempIdIndex = 0; // Track which newly uploaded file to use
                    
                    foreach ($orderArray as $index => $mediaId) {
                        $media = null;
                        
                        // Check if it's a new temp ID (starts with 'temp-')
                        if (strpos($mediaId, 'temp-') === 0) {
                            // Use the corresponding newly uploaded media
                            if (isset($newlyUploadedMedia[$tempIdIndex])) {
                                $media = $newlyUploadedMedia[$tempIdIndex];
                                $tempIdIndex++;
                            }
                        } else {
                            // Existing media - find by ID
                            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId);
                            // Verify it belongs to this product
                            if ($media && ($media->model_id != $item->id || $media->model_type != 'App\Models\Product')) {
                                $media = null;
                            }
                        }
                        
                        if ($media) {
                            // Secondary images start from order_column = 1
                            $media->order_column = $index + 1;
                            $media->save();
                        }
                    }
                }
            } else {
                // If no order provided, set order for newly uploaded files
                $existingCount = $item->getMedia('Product')->where('order_column', '>', 0)->count();
                foreach ($newlyUploadedMedia as $index => $media) {
                    $media->order_column = $existingCount + $index + 1;
                    $media->save();
                }
            }

            // Handle main image (order_column = 0)
            if ($request->has('main_image_id') && !empty($request->main_image_id)) {
                $mainImageId = $request->main_image_id;
                $mainMedia = null;
                
                // If it's a temp ID, use the first newly uploaded media
                if (strpos($mainImageId, 'temp-') === 0) {
                    if (!empty($newlyUploadedMedia)) {
                        $mainMedia = $newlyUploadedMedia[0];
                    }
                } else {
                    // Existing media
                    $mainMedia = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($mainImageId);
                    // Verify it belongs to this product
                    if ($mainMedia && ($mainMedia->model_id != $item->id || $mainMedia->model_type != 'App\Models\Product')) {
                        $mainMedia = null;
                    }
                }
                
                if ($mainMedia) {
                    // Remove old main image (set to secondary)
                    $oldMain = $item->getMedia('Product')->where('order_column', 0)->first();
                    if ($oldMain && $oldMain->id != $mainMedia->id) {
                        // Find the highest order and add 1
                        $maxOrder = $item->getMedia('Product')->where('order_column', '>', 0)->max('order_column') ?? 0;
                        $oldMain->order_column = $maxOrder + 1;
                        $oldMain->save();
                    }
                    
                    // Set new main image (order_column = 0)
                    $mainMedia->order_column = 0;
                    $mainMedia->save();
                }
            } elseif (!empty($newlyUploadedMedia) && empty($item->getMedia('Product')->where('order_column', 0)->first())) {
                // If no main image is set and we have new uploads, set the first one as main
                $firstMedia = $newlyUploadedMedia[0];
                $firstMedia->order_column = 0;
                $firstMedia->save();
            }
        }

        if ($item) {
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('center_user.products.index'));
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function addBrand(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name'
        ]);

        try {
            $brand = \App\Models\Brand::create([
                'name' => $request->name
            ]);

            return MyHelper::responseJSON(__('admin.operation_done_successfully'), Response::HTTP_CREATED, $brand);
        } catch (\Exception $e) {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function addCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name'
        ]);

        try {
            $category = \App\Models\Category::create([
                'name' => $request->name
            ]);

            return MyHelper::responseJSON(__('admin.operation_done_successfully'), Response::HTTP_CREATED, $category);
        } catch (\Exception $e) {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function addSupplier(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:product_suppliers,name'
        ]);

        try {
            $supplier = \App\Models\ProductSupplier::create([
                'name' => $request->name
            ]);

            return MyHelper::responseJSON(__('admin.operation_done_successfully'), Response::HTTP_CREATED, $supplier);
        } catch (\Exception $e) {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
