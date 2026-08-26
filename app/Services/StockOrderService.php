<?php

namespace App\Services;

use App\Models\ProductBranch;
use App\Models\ProductSupplier;
use App\Models\StockOrder;
use Illuminate\Support\Facades\DB;
use Exception;

class StockOrderService
{
    public function create(array $data, ?int $createdBy = null): StockOrder
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $supplier = ProductSupplier::findOrFail($data['product_supplier_id']);

            $totalCost = 0;
            $lineItems = [];

            foreach ($data['items'] as $item) {
                $orderQty = (float) $item['order_qty'];
                $unitCost = (float) $item['unit_cost'];
                $lineTotal = round($orderQty * $unitCost, 2);
                $totalCost += $lineTotal;

                $lineItems[] = [
                    'product_id' => (int) $item['product_id'],
                    'order_qty' => $orderQty,
                    'unit_cost' => $unitCost,
                    'line_total' => $lineTotal,
                ];
            }

            $order = StockOrder::create([
                'order_number' => StockOrder::nextOrderNumber(),
                'branch_id' => (int) $data['branch_id'],
                'product_supplier_id' => (int) $data['product_supplier_id'],
                'deliver_from' => $supplier->name,
                'expected_at' => $data['expected_at'] ?? null,
                'total_cost' => round($totalCost, 2),
                'status' => 'ordered',
                'created_by' => $createdBy,
            ]);

            foreach ($lineItems as $line) {
                $order->items()->create($line);
            }

            return $order->load(['items.product.translation', 'branch.translation', 'supplier']);
        });
    }

    public function receive(StockOrder $order, array $itemsData): StockOrder
    {
        if ($order->status !== 'ordered') {
            throw new Exception('Order already received');
        }

        return DB::transaction(function () use ($order, $itemsData) {
            $totalCost = 0;
            $itemsById = collect($itemsData)->keyBy('id');

            foreach ($order->items as $item) {
                if (!$itemsById->has($item->id)) {
                    continue;
                }

                $payload = $itemsById->get($item->id);
                $receivedQty = (float) $payload['received_qty'];
                $unitCost = (float) $payload['unit_cost'];
                $lineTotal = round($receivedQty * $unitCost, 2);
                $totalCost += $lineTotal;

                $item->update([
                    'received_qty' => $receivedQty,
                    'unit_cost' => $unitCost,
                    'line_total' => $lineTotal,
                ]);

                if ($receivedQty > 0) {
                    $productBranch = ProductBranch::withTrashed()->firstOrCreate(
                        [
                            'product_id' => $item->product_id,
                            'branch_id' => $order->branch_id,
                        ],
                        ['stock_quantity' => 0]
                    );

                    if ($productBranch->trashed()) {
                        $productBranch->restore();
                    }

                    $productBranch->increment('stock_quantity', $receivedQty);
                }
            }

            $order->update([
                'status' => 'received',
                'received_at' => now(),
                'total_cost' => round($totalCost, 2),
            ]);

            return $order->fresh(['items.product.translation', 'branch.translation', 'supplier']);
        });
    }
}
