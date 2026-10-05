<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\MainStock;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\StockOrder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReceiveTransferOrder
{
    public function receive(StockOrder $order, Collection $itemsById): void
    {
        DB::transaction(function () use ($order, $itemsById) {
            $order->loadMissing(['items', 'branches']);
            $branchIds = $order->branches->pluck('id')->map(fn ($id) => (int) $id)->all();
            $branchCount = count($branchIds);

            if ($branchCount === 0) {
                throw new RuntimeException('Stock transfer order has no destination branches.');
            }

            foreach ($order->items as $item) {
                $payload = $itemsById->get($item->id);
                $orderQuantity = (int) $item->order_qty;
                $receivedQuantity = (int) $payload['received_qty'];

                if ($receivedQuantity > $orderQuantity) {
                    throw new RuntimeException('Received quantity cannot exceed the ordered quantity.');
                }

                $product = Product::query()
                ->with('primarySku')
                ->lockForUpdate()
                ->findOrFail($item->product_id);
                $skuId = $product->primarySku?->id;
                $stockQuery = MainStock::query()->where('product_id', $product->id);
                $skuId === null ? $stockQuery->whereNull('sku_id') : $stockQuery->where('sku_id', $skuId);
                $stock = $stockQuery->lockForUpdate()->first();
                $reservedQuantity = $orderQuantity * $branchCount;
                $receivedTotal = $receivedQuantity * $branchCount;

                if (!$stock || $stock->reserved_quantity < $reservedQuantity || $stock->quantity < $receivedTotal) {
                throw new RuntimeException('Main stock reservation is no longer available for ' . $product->name . '.');
                }

                $stock->decrement('reserved_quantity', $reservedQuantity);
                if ($receivedTotal > 0) {
                $stock->decrement('quantity', $receivedTotal);

                app(InventoryMovementService::class)->record(
                    (int) $product->id,
                    null,
                    -$receivedTotal,
                    InventoryMovement::TYPE_TRANSFER_OUT,
                    $order,
                    $skuId ? (int) $skuId : null,
                    'Main stock transfer ' . $order->order_number,
                    true
                );
                }

                if ($receivedQuantity > 0) {
                foreach ($branchIds as $branchId) {
                    $productBranch = ProductBranch::withTrashed()->firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'branch_id' => $branchId,
                        ],
                        ['stock_quantity' => 0]
                    );

                    if ($productBranch->trashed()) {
                        $productBranch->restore();
                    }

                    $productBranch->increment('stock_quantity', $receivedQuantity);

                    app(InventoryMovementService::class)->record(
                        (int) $product->id,
                        $branchId,
                        $receivedQuantity,
                        InventoryMovement::TYPE_TRANSFER_IN,
                        $order,
                        $skuId ? (int) $skuId : null,
                        'Main stock transfer ' . $order->order_number
                    );
                }
                }

                $item->update([
                'received_qty' => $receivedQuantity,
                'unit_cost' => 0,
                'line_total' => 0,
                ]);
            }
        });
    }
}
