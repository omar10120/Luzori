<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\MainStock;
use App\Models\Product;
use App\Models\StockOrder;
use App\Models\StockOrderItem;
use Illuminate\Support\Facades\DB;

class ReceivePurchaseOrder
{
    public function receive(StockOrder $order, StockOrderItem $item, int $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        DB::transaction(function () use ($order, $item, $quantity) {
            $product = Product::query()
                ->with('primarySku')
                ->lockForUpdate()
                ->findOrFail($item->product_id);
            $skuId = $product->primarySku?->id;

            $stockQuery = MainStock::query()->where('product_id', $product->id);
            $skuId === null ? $stockQuery->whereNull('sku_id') : $stockQuery->where('sku_id', $skuId);
            $stock = $stockQuery->lockForUpdate()->first();

            if (!$stock) {
                $stock = MainStock::create([
                    'product_id' => $product->id,
                    'sku_id' => $skuId,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'reorder_level' => 0,
                ]);
            }

            $stock->increment('quantity', $quantity);

            app(InventoryMovementService::class)->record(
                (int) $product->id,
                null,
                $quantity,
                InventoryMovement::TYPE_PURCHASE,
                $order,
                $skuId ? (int) $skuId : null,
                'Supplier stock order receive ' . $order->order_number,
                true
            );
        });
    }
}
