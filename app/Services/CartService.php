<?php

namespace App\Services;

use App\Models\AppUser;
use App\Models\CartItem;
use App\Models\Center;
use App\Models\Product;
use App\Models\ProductBranch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CartService
{
    public function listForUser(AppUser $user, ?int $centerId = null, ?string $itemType = null): Collection
    {
        $query = CartItem::query()
            ->with(['center:id,name,domain'])
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at');

        if ($centerId) {
            $query->where('center_id', $centerId);
        }

        if ($itemType) {
            $query->where('item_type', $itemType);
        }

        return $query->get();
    }

    /**
     * Add or bump a product line. branch_id is required for products.
     */
    public function addProduct(AppUser $user, array $data): CartItem
    {
        $center = $this->resolveCenter((int) $data['center_id']);
        $branchId = (int) $data['branch_id'];
        $productId = (int) $data['product_id'];
        $qty = (int) $data['qty'];

        if ($qty < 1) {
            throw new RuntimeException('Quantity must be at least 1');
        }

        $snapshot = $this->withTenantDatabase($center, function () use ($productId, $branchId, $qty, $data) {
            $product = Product::on('tenant')
                ->with(['translation', 'primarySku', 'media'])
                ->find($productId);

            if (!$product) {
                throw new RuntimeException('Product not found for this center');
            }

            // Existing cart qty lives on central; passed in for stock check of new total
            $existingQty = (int) ($data['_existing_qty'] ?? 0);
            $neededQty = $existingQty + $qty;

            $this->validateProductStock($product, $neededQty, $branchId);

            $unitPrice = ($product->retail_price && $product->retail_price > 0)
                ? (float) $product->retail_price
                : (float) ($product->supply_price ?? 0);

            return [
                'name' => (string) ($product->name ?? ''),
                'sku' => $product->primarySku?->sku,
                'image_url' => $product->getFirstMediaUrl('Product') ?: null,
                'unit_price' => $unitPrice,
            ];
        });

        $dedupeKey = CartItem::productDedupeKey($user->id, $center->id, $branchId, $productId);

        return DB::connection('central')->transaction(function () use ($user, $center, $branchId, $productId, $qty, $snapshot, $dedupeKey) {
            $item = CartItem::query()
                ->where('dedupe_key', $dedupeKey)
                ->lockForUpdate()
                ->first();

            if ($item) {
                $item->qty = (int) $item->qty + $qty;
                $item->name = $snapshot['name'];
                $item->sku = $snapshot['sku'];
                $item->image_url = $snapshot['image_url'];
                $item->unit_price = $snapshot['unit_price'];
                $item->total_price = round($item->qty * (float) $snapshot['unit_price'], 2);
                $item->request_payload = [
                    'id' => $productId,
                    'qty' => $item->qty,
                ];
                $item->save();

                return $item->fresh('center');
            }

            return CartItem::create([
                'user_id' => $user->id,
                'center_id' => $center->id,
                'branch_id' => $branchId,
                'item_type' => CartItem::TYPE_PRODUCT,
                'item_id' => $productId,
                'qty' => $qty,
                'name' => $snapshot['name'],
                'sku' => $snapshot['sku'],
                'image_url' => $snapshot['image_url'],
                'unit_price' => $snapshot['unit_price'],
                'total_price' => round($qty * (float) $snapshot['unit_price'], 2),
                'request_payload' => [
                    'id' => $productId,
                    'qty' => $qty,
                ],
                'dedupe_key' => $dedupeKey,
            ])->load('center');
        });
    }

    public function updateProductQty(AppUser $user, int $cartItemId, int $qty): CartItem
    {
        if ($qty < 1) {
            throw new RuntimeException('Quantity must be at least 1');
        }

        $item = $this->findOwnedProductItem($user, $cartItemId);
        $center = $this->resolveCenter((int) $item->center_id);

        $this->withTenantDatabase($center, function () use ($item, $qty) {
            $product = Product::on('tenant')->find($item->item_id);
            if (!$product) {
                throw new RuntimeException('Product not found for this center');
            }
            $this->validateProductStock($product, $qty, (int) $item->branch_id);
        });

        $item->qty = $qty;
        $item->total_price = round($qty * (float) $item->unit_price, 2);
        $item->request_payload = [
            'id' => (int) $item->item_id,
            'qty' => $qty,
        ];
        $item->save();

        return $item->fresh('center');
    }

    public function remove(AppUser $user, int $cartItemId): bool
    {
        $item = CartItem::query()
            ->where('user_id', $user->id)
            ->where('id', $cartItemId)
            ->first();

        if (!$item) {
            return false;
        }

        return (bool) $item->delete();
    }

    public function clear(AppUser $user, ?int $centerId = null, ?int $branchId = null, ?string $itemType = null): int
    {
        $query = CartItem::query()->where('user_id', $user->id);

        if ($centerId) {
            $query->where('center_id', $centerId);
        }
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        if ($itemType) {
            $query->where('item_type', $itemType);
        }

        return $query->delete();
    }

    /**
     * Payload shape for app_api/buyproducts/add (products only).
     */
    public function checkoutPayloadForBuyProducts(AppUser $user, int $centerId, int $branchId): array
    {
        $items = CartItem::query()
            ->where('user_id', $user->id)
            ->where('center_id', $centerId)
            ->where('branch_id', $branchId)
            ->where('item_type', CartItem::TYPE_PRODUCT)
            ->get();

        return [
            'center_id' => $centerId,
            'branch_id' => $branchId,
            'products' => $items->map(fn (CartItem $item) => [
                'id' => (int) $item->item_id,
                'qty' => (int) $item->qty,
            ])->values()->all(),
            'cart_item_ids' => $items->pluck('id')->values()->all(),
            'subtotal' => round((float) $items->sum('total_price'), 2),
        ];
    }

    public function existingProductQty(AppUser $user, int $centerId, int $branchId, int $productId): int
    {
        $item = CartItem::query()
            ->where('dedupe_key', CartItem::productDedupeKey($user->id, $centerId, $branchId, $productId))
            ->first();

        return (int) ($item?->qty ?? 0);
    }

    private function findOwnedProductItem(AppUser $user, int $cartItemId): CartItem
    {
        $item = CartItem::query()
            ->where('user_id', $user->id)
            ->where('id', $cartItemId)
            ->first();

        if (!$item) {
            throw new RuntimeException('Cart item not found');
        }

        if (!$item->isProduct()) {
            throw new RuntimeException('Only product cart items can update quantity');
        }

        if (!$item->branch_id) {
            throw new RuntimeException('Product cart item is missing branch_id');
        }

        return $item;
    }

    private function resolveCenter(int $centerId): Center
    {
        $center = Center::find($centerId);
        if (!$center || !$center->database) {
            throw new RuntimeException('Center not found or database missing');
        }
        if ($center->expire_date && now()->gt($center->expire_date)) {
            throw new RuntimeException('انتهت فترة صلاحية هذا المركز. يرجى التواصل مع الدعم الفني.');
        }
        if ($center->status !== 'approve') {
            throw new RuntimeException('Center is not available');
        }

        return $center;
    }

    private function validateProductStock(Product $product, int $quantity, int $branchId): void
    {
        $productBranch = ProductBranch::on('tenant')
            ->where('product_id', $product->id)
            ->where('branch_id', $branchId)
            ->first();

        if (!$product->track_stock && !$productBranch) {
            return;
        }

        if (!$productBranch) {
            throw new RuntimeException('Product not available in this branch');
        }

        if ($productBranch->stock_quantity < $quantity) {
            throw new RuntimeException(
                "Insufficient stock. Available: {$productBranch->stock_quantity}, Requested: {$quantity}"
            );
        }
    }

    private function withTenantDatabase(Center $center, callable $callback): mixed
    {
        $previousDatabase = Config::get('database.connections.tenant.database');
        Config::set('database.connections.tenant.database', $center->database);
        DB::purge('tenant');

        try {
            return $callback();
        } finally {
            DB::purge('tenant');
            Config::set('database.connections.tenant.database', $previousDatabase);
        }
    }
}
