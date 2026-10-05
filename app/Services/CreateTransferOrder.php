<?php

namespace App\Services;

use App\Models\MainStock;
use App\Models\Product;
use App\Models\StockOrder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateTransferOrder
{
    public function reserve(StockOrder $order): void
    {
        DB::transaction(function () use ($order) {
            $order->loadMissing(['items', 'branches']);
            $branchCount = $order->branches->count();

            if ($branchCount === 0) {
                throw new RuntimeException('Select at least one destination branch.');
            }

            foreach ($order->items as $item) {
                $orderQuantity = (float) $item->order_qty;
                if ($orderQuantity < 1 || floor($orderQuantity) !== $orderQuantity) {
                    throw new RuntimeException('Main stock transfer quantities must be whole numbers.');
                }

                $product = Product::query()
                    ->with('primarySku')
                    ->lockForUpdate()
                    ->findOrFail($item->product_id);
                $skuId = $product->primarySku?->id;
                $stockQuery = MainStock::query()->where('product_id', $product->id);
                $skuId === null ? $stockQuery->whereNull('sku_id') : $stockQuery->where('sku_id', $skuId);
                $stock = $stockQuery->lockForUpdate()->first();
                $requiredQuantity = (int) $orderQuantity * $branchCount;

                if (!$stock || $stock->available_quantity < $requiredQuantity) {
                    throw new RuntimeException(
                        'Insufficient main stock for ' . $product->name . '. Required: ' . $requiredQuantity
                    );
                }

                $stock->increment('reserved_quantity', $requiredQuantity);
            }
        });
    }
}
