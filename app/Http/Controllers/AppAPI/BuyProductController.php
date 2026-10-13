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
use App\Models\User as TenantUser;
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

    public function all(Request $request)
    {
        $request->validate([
            'center_id' => 'required|integer|exists:central.centers,id',
        ]);

        $switched = $this->switchToCenter((int) $request->center_id);
        if ($switched instanceof \Illuminate\Http\JsonResponse) {
            return $switched;
        }

        try {
            $relations = ['worker', 'sales_worker', 'details' => function ($q) {
                $q->with(['product' => function ($q1) {
                    $q1->with(['translation']);
                }]);
            }];

            $items = $this->crudService->paginate($this->model, $relations, 0);
            if ($items) {
                $paginationData = PaginateDateResource::make($items);
                $items = BuyProductResource::collection($items);
                return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items, $paginationData);
            }

            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
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
        ]);

        $branchId = (int) $validated['branch_id'];
        $isPaidFromWallet = ($validated['payment_type'] === 'wallet');
        $totalPrice = 0.0;
        $walletDeducted = false;

        try {
            // Validate stock + build priced lines before creating anything
            $pricedLines = [];
            foreach ($validated['products'] as $line) {
                $productId = (int) $line['id'];
                $qty = (int) $line['qty'];
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
                $pricedLines[] = [
                    'product' => $product,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                ];
            }

            // Deduct from main DB users.wallet (central) — same as Booking/Package AppAPI
            if ($isPaidFromWallet) {
                $centralUser = DB::connection('central')->table('users')->where('id', $appUser->id)->first();

                if (!$centralUser || (float) $centralUser->wallet < $totalPrice) {
                    return MyHelper::responseJSON(
                        __('api.insufficient_balance') ?? 'Insufficient wallet balance',
                        Response::HTTP_BAD_REQUEST
                    );
                }

                DB::connection('central')->table('users')->where('id', $appUser->id)->decrement('wallet', $totalPrice);
                $walletDeducted = true;
            }

            $tenantUser = $this->resolveTenantUser($appUser);

            $item = DB::transaction(function () use ($validated, $tenantUser, $branchId, $pricedLines) {
                $buyProduct = BuyProduct::create([
                    'payment_type' => $validated['payment_type'],
                    'commission' => $validated['commission'] ?? null,
                    'discount' => $validated['discount'] ?? null,
                    'sales_worker_id' => $validated['sales_worker_id'],
                    'worker_id' => $validated['worker_id'] ?? null,
                    'created_by' => $tenantUser->id,
                ]);

                foreach ($pricedLines as $line) {
                    $product = $line['product'];
                    $qty = $line['qty'];
                    // Store pre-discount retail/supply unit on details (same as CRUDService);
                    // bill amount for wallet already applied discount above.
                    $detailPrice = ($product->retail_price && $product->retail_price > 0)
                        ? $product->retail_price
                        : ($product->supply_price ?? 0);

                    for ($i = 0; $i < $qty; $i++) {
                        BuyProductDetail::create([
                            'buy_product_id' => $buyProduct->id,
                            'product_id' => $product->id,
                            'price' => $detailPrice,
                        ]);
                    }

                    $this->decrementProductStock($product, $qty, $branchId, $buyProduct);
                }

                return $buyProduct;
            });

            $item->load(['worker', 'sales_worker', 'details.product.translation']);

            return MyHelper::responseJSON(
                __('api.addSuccessfully'),
                Response::HTTP_CREATED,
                BuyProductResource::make($item)
            );
        } catch (\Exception $e) {
            if ($walletDeducted) {
                DB::connection('central')->table('users')->where('id', $appUser->id)->increment('wallet', $totalPrice);
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
