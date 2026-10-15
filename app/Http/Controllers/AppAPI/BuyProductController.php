<?php

namespace App\Http\Controllers\AppAPI;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BuyProductResource;
use App\Http\Resources\PaginateDateResource;
use App\Http\Resources\PrintBuyProductResource;
use App\Models\BuyProduct;
use App\Models\BuyProductDetail;
use App\Models\Center;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User as TenantUser;
use App\Models\CartItem;
use App\Services\CartService;
use App\Services\CRUDService;
use App\Services\InventoryMovementService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class BuyProductController extends Controller
{
    private CRUDService $crudService;
    private string $model = 'BuyProduct';

    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
    }

    use Illuminate\Pagination\LengthAwarePaginator;

public function all(Request $request)
{
    $request->validate([
        'center_id' => 'nullable|integer|exists:central.centers,id',
        'per_page'  => 'nullable|integer|min:1|max:100',
        'page'      => 'nullable|integer|min:1',
    ]);

    $appUser = auth('sanctum')->user();
    if (!$appUser) {
        return MyHelper::responseJSON('Unauthorized', Response::HTTP_UNAUTHORIZED);
    }

    $relations = ['worker', 'sales_worker', 'details' => function ($q) {
        $q->with(['product' => function ($q1) {
            $q1->with(['translation']);
        }]);
    }];

    try {
        // ─────────────────────────────────────────────────────────────
        // Case A: center_id provided → single-center, admin-style list
        // ─────────────────────────────────────────────────────────────
        if ($request->filled('center_id')) {
            $switched = $this->switchToCenter((int) $request->center_id);
            if ($switched instanceof \Illuminate\Http\JsonResponse) {
                return $switched;
            }

            $items = $this->crudService->paginate($this->model, $relations, 0);
            if ($items) {
                $paginationData = PaginateDateResource::make($items);
                $items = BuyProductResource::collection($items);
                return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items, $paginationData);
            }

            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // ─────────────────────────────────────────────────────────────
        // Case B: no center_id → aggregate the user's orders across all centers
        // ─────────────────────────────────────────────────────────────
        $perPage = (int) $request->input('per_page', 15);
        $page    = (int) $request->input('page', 1);

        // Remember the original database so we can restore at the end
        $originalDatabase = Config::get('database.connections.mysql.database');

        $centers = Center::query()
            ->where('status', 'approve')
            ->whereNotNull('database')
            ->get(['id', 'database', 'expire_date']);

        $allItems = collect();

        foreach ($centers as $center) {
            if ($center->expire_date && now()->gt($center->expire_date)) {
                continue;
            }

            try {
                Config::set('database.connections.mysql.database', $center->database);
                DB::purge('mysql');
                DB::reconnect('mysql');

                $rows = BuyProduct::with($relations)
                    ->where('global_user_id', $appUser->id)
                    ->orderByDesc('created_at')
                    ->get()
                    ->map(function (BuyProduct $row) use ($center) {
                        // Attach the source center so the resource can render it
                        $row->setAttribute('center_id', $center->id);
                        return $row;
                    });

                $allItems = $allItems->merge($rows);
            } catch (\Throwable $e) {
                // One bad center shouldn't break the whole list
                Log::warning('BuyProduct all: failed to query center', [
                    'center_id' => $center->id,
                    'error'     => $e->getMessage(),
                ]);
                continue;
            }
        }

        // ── In-memory sort + pagination across all centers ──
        $sorted   = $allItems->sortByDesc('created_at')->values();
        $total    = $sorted->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page     = min($page, $lastPage);
        $items    = $sorted->slice(($page - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Restore the connection so downstream code doesn't see the last center's DB
        Config::set('database.connections.mysql.database', $originalDatabase);
        DB::purge('mysql');
        DB::reconnect('mysql');

        return MyHelper::responseJSON(
            __('api.doneSuccessfully'),
            Response::HTTP_OK,
            BuyProductResource::collection($paginator),
            PaginateDateResource::make($paginator)
        );
    } catch (\Throwable $e) {
        Log::error('AppAPI BuyProduct all: ' . $e->getMessage());
        return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

    public function add(Request $request)
    {   
        $request->validate([
            'center_id' => 'required|integer|exists:central.centers,id',
        ]);
    
        $appUser = auth('sanctum')->user();
        if (!$appUser) {
            return MyHelper::responseJSON('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }
    
        $switched = $this->switchToCenter((int) $request->center_id);
        if ($switched instanceof \Illuminate\Http\JsonResponse) {
            return $switched;
        }
    
        $allowedPayments = array_unique(array_merge(get_payment_method_names(), ['wallet']));
    
        $validated = $request->validate([
            'branch_id' => 'required|integer|exists:branches,id,deleted_at,NULL',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|integer|exists:products,id,deleted_at,NULL',
            'products.*.qty' => 'required|integer|min:1',
            'payment_type' => 'required|string|in:' . implode(',', $allowedPayments),
            'commission' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'sales_worker_id' => 'required|exists:workers,id,deleted_at,NULL',
            'worker_id' => 'nullable|exists:workers,id,deleted_at,NULL',
            'clear_cart' => 'nullable|boolean',
            'cart_item_ids' => 'nullable|array',
            'cart_item_ids.*' => 'integer',
        ]);
    
        $branchId         = (int) $validated['branch_id'];
        $centerId         = (int) $request->center_id;
        $isPaidFromWallet = ($validated['payment_type'] === 'wallet');
        $totalPrice       = 0.0;
        $totalQty         = 0;
        $walletDeducted   = false;
    
        try {
            // ── 1. Validate stock + build priced lines before creating anything ──
            $pricedLines = [];
            foreach ($validated['products'] as $line) {
                $productId = (int) $line['id'];
                $qty       = (int) $line['qty'];
    
                $this->validateProductStock($productId, $qty, $branchId);
    
                $product = Product::select(['id', 'retail_price', 'supply_price'])
                    ->with('primarySku')
                    ->findOrFail($productId);
    
                $unitPrice = ($product->retail_price && $product->retail_price > 0)
                    ? (float) $product->retail_price
                    : (float) ($product->supply_price ?? 0);
    
                if (!empty($validated['discount'])) {
                    $unitPrice -= ($unitPrice * (float) $validated['discount']) / 100;
                }
    
                $totalPrice += $unitPrice * $qty;
                $totalQty   += $qty;
    
                $pricedLines[] = [
                    'product'    => $product,
                    'qty'        => $qty,
                    'unit_price' => $unitPrice,
                ];
            }
    
            // ── 2. Deduct wallet (central DB) before the tenant transaction ──
            if ($isPaidFromWallet) {
                $centralUser = DB::connection('central')->table('users')->where('id', $appUser->id)->first();
    
                if (!$centralUser || (float) $centralUser->wallet < $totalPrice) {
                    return MyHelper::responseJSON(
                        __('api.insufficient_balance') ?? 'Insufficient wallet balance',
                        Response::HTTP_BAD_REQUEST
                    );
                }
    
                DB::connection('central')->table('users')
                    ->where('id', $appUser->id)
                    ->decrement('wallet', $totalPrice);
    
                $walletDeducted = true;
            }
    
            $tenantUser = $this->resolveTenantUser($appUser);
    
            // ── 3. Sale + BuyProduct + BuyProductDetail + SaleItem, all atomic ──
            $item = DB::transaction(function () use (
                $appUser,
                $validated,
                $tenantUser,
                $branchId,
                $pricedLines,
                $totalPrice,
                $totalQty,
                
            ) {
                // 3a. Sale record (mirrors SalesService::processSale)
                $sale = Sale::create([
                    'subtotal'   => $totalPrice,
                    'tax'        => 0,
                    'tip'        => 0,
                    'total'      => $totalPrice,
                    'worker_id'  => $validated['worker_id'] ?? $validated['sales_worker_id'],
                    'client_id'  => $tenantUser->id,
                    'branch_id'  => $branchId,
                    'created_by' => $tenantUser->id,
                ]);
    
                // 3b. BuyProduct linked to the sale
                $buyProduct = BuyProduct::create([
                    'payment_type'    => $validated['payment_type'],
                    'commission'      => $validated['commission'] ?? null,
                    'discount'        => $validated['discount'] ?? null,
                    'sales_worker_id' => $validated['sales_worker_id'],
                    // 'worker_id'       => $validated['worker_id'] ?? null,
                    'worker_id'       => 1,
                    'global_user_id'  => $appUser->id,
                    'created_by'      => $tenantUser->id,
                    'sale_id'         => $sale->id, // ← link it back
                ]);
    
                // 3c. BuyProductDetail rows + stock decrement
                foreach ($pricedLines as $line) {
                    $product = $line['product'];
                    $qty     = $line['qty'];
    
                    $detailPrice = ($product->retail_price && $product->retail_price > 0)
                        ? $product->retail_price
                        : ($product->supply_price ?? 0);
    
                    for ($i = 0; $i < $qty; $i++) {
                        BuyProductDetail::create([
                            'buy_product_id' => $buyProduct->id,
                            'product_id'     => $product->id,
                            'price'          => $detailPrice,
                        ]);
                    }
    
                    $this->decrementProductStock($product, $qty, $branchId, $buyProduct);
                }
    
                // 3d. SaleItem (mirrors SalesService::processSale)
                SaleItem::create([
                    'sale_id'       => $sale->id,
                    'item_type'     => 'buy_product',
                    'itemable_id'   => $buyProduct->id,
                    'itemable_type' => 'App\Models\BuyProduct',
                    'quantity'      => $totalQty,
                    'price'         => $totalQty > 0 ? $totalPrice / $totalQty : 0,
                    'subtotal'      => $totalPrice,
                ]);
    
                return $buyProduct;
            });
    
            $item->load(['worker', 'sales_worker', 'details.product.translation']);
    
            // ── 4. Clear matching central cart product lines after success ──
            if (!empty($validated['clear_cart'])) {
                if (!empty($validated['cart_item_ids'])) {
                    \App\Models\CartItem::query()
                        ->where('user_id', $appUser->id)
                        ->where('item_type', \App\Models\CartItem::TYPE_PRODUCT)
                        ->whereIn('id', $validated['cart_item_ids'])
                        ->delete();
                } else {
                    app(CartService::class)->clear(
                        $appUser,
                        $centerId,
                        $branchId,
                        \App\Models\CartItem::TYPE_PRODUCT
                    );
                }
            }
    
            return MyHelper::responseJSON(
                __('api.addSuccessfully'),
                Response::HTTP_CREATED,
                BuyProductResource::make($item)
            );
        } catch (\Exception $e) {
            // Roll back the wallet charge if anything blew up after we took the money
            if ($walletDeducted) {
                DB::connection('central')->table('users')
                    ->where('id', $appUser->id)
                    ->increment('wallet', $totalPrice);
            }
    
            Log::error('AppAPI BuyProduct add: ' . $e->getMessage());
            $message = strtolower($e->getMessage());
            $status = str_contains($message, 'stock')
                || str_contains($message, 'available')
                || str_contains($message, 'wallet')
                || str_contains($message, 'balance')
                ? Response::HTTP_UNPROCESSABLE_ENTITY
                : Response::HTTP_INTERNAL_SERVER_ERROR;
    
            return MyHelper::responseJSON($e->getMessage(), $status);
        }
    }

    public function delete(Request $request)
    {
        $request->validate([
            'center_id' => 'required|integer|exists:central.centers,id',
            'id' => 'required|integer',
        ]);

        $switched = $this->switchToCenter((int) $request->center_id);
        if ($switched instanceof \Illuminate\Http\JsonResponse) {
            return $switched;
        }

        $request->validate([
            'id' => 'required|exists:buy_products,id,deleted_at,NULL',
        ]);

        try {
            $item = $this->crudService->delete($this->model, $request->id);
            if ($item) {
                return MyHelper::responseJSON(__('api.deleteSuccessfully'), Response::HTTP_OK);
            }

            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            Log::error('AppAPI BuyProduct delete: ' . $e->getMessage());
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function printInvoice(Request $request)
    {
        $request->validate([
            'center_id' => 'required|integer|exists:central.centers,id',
            'id' => 'required|integer',
        ]);

        $switched = $this->switchToCenter((int) $request->center_id);
        if ($switched instanceof \Illuminate\Http\JsonResponse) {
            return $switched;
        }

        $request->validate([
            'id' => 'required|exists:buy_products,id,deleted_at,NULL',
        ]);

        try {
            $buyProduct = BuyProduct::with(['created_by_user', 'details' => function ($q) {
                $q->with(['product' => function ($q) {
                    $q->with(['translation']);
                }]);
            }])->findOrFail($request->id);

            return MyHelper::responseJSON(
                __('api.doneSuccessfully'),
                Response::HTTP_OK,
                PrintBuyProductResource::make($buyProduct)
            );
        } catch (\Exception $e) {
            Log::error('AppAPI BuyProduct printInvoice: ' . $e->getMessage());
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Same rules as SalesService::validateProductStock
     */
    private function validateProductStock(int $productId, int $quantity, int $branchId): void
    {
        $product = Product::find($productId);
        if (!$product) {
            throw new \Exception('Product not found');
        }

        $productBranch = ProductBranch::where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->first();

        if (!$product->track_stock && !$productBranch) {
            return;
        }

        if (!$productBranch) {
            throw new \Exception('Product not available in this branch');
        }

        if ($productBranch->stock_quantity < $quantity) {
            throw new \Exception(
                "Insufficient stock. Available: {$productBranch->stock_quantity}, Requested: {$quantity}"
            );
        }
    }

    /**
     * Same flow as SalesService::updateProductStock
     */
    private function decrementProductStock(Product $product, int $quantity, int $branchId, BuyProduct $buyProduct): void
    {
        $productBranch = ProductBranch::withTrashed()
            ->where('product_id', $product->id)
            ->where('branch_id', $branchId)
            ->first();

        if (!$productBranch) {
            return;
        }

        if ($productBranch->trashed()) {
            $productBranch->restore();
        }

        $productBranch->decrement('stock_quantity', $quantity);

        app(InventoryMovementService::class)->record(
            (int) $product->id,
            $branchId,
            -$quantity,
            InventoryMovement::TYPE_SALE,
            $buyProduct,
            $product->primarySku?->id,
            'App buy product sale'
        );
    }

    private function switchToCenter(int $centerId)
    {
        $center = Center::find($centerId);
        if (!$center || !$center->database) {
            return MyHelper::responseJSON('Center not found or database missing', Response::HTTP_NOT_FOUND);
        }
        if ($center->expire_date && now()->gt($center->expire_date)) {
            return MyHelper::responseJSON(
                'انتهت فترة صلاحية هذا المركز. يرجى التواصل مع الدعم الفني.',
                Response::HTTP_BAD_REQUEST
            );
        }

        Config::set('database.connections.mysql.database', $center->database);
        DB::purge('mysql');
        DB::reconnect('mysql');

        return $center;
    }

    private function resolveTenantUser($appUser): TenantUser
    {
        $tenantUser = TenantUser::where('email', $appUser->email)
            ->orWhere('phone', $appUser->phone)
            ->first();

        if ($tenantUser) {
            return $tenantUser;
        }

        return TenantUser::create([
            'first_name' => $appUser->first_name,
            'last_name' => $appUser->last_name,
            'email' => $appUser->email,
            'phone' => $appUser->phone ?? '-',
            'country_code' => $appUser->country_code ?? '+',
            'wallet' => 0,
        ]);
    }
}
