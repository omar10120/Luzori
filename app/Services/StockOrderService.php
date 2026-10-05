<?php

namespace App\Services;

use App\Models\ProductSupplier;
use App\Models\StockOrder;
use Illuminate\Support\Facades\DB;
use Exception;

class StockOrderService
{
    public function create(array $data, ?int $createdBy = null): StockOrder
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $sourceType = $data['source_type'] ?? 'supplier';
            $supplier = $sourceType === 'supplier'
                ? ProductSupplier::findOrFail($data['product_supplier_id'])
                : null;
            $branchIds = array_values(array_unique(array_map('intval', $data['branch_ids'] ?? [])));

            if ($sourceType === 'main_stock' && empty($branchIds)) {
                throw new Exception('At least one destination branch is required');
            }

            $totalCost = 0;
            $lineItems = [];

            foreach ($data['items'] as $item) {
                $orderQty = (float) $item['order_qty'];
                $unitCost = $sourceType === 'supplier' ? (float) $item['unit_cost'] : 0;
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
                'product_supplier_id' => $supplier?->id,
                'source_type' => $sourceType,
                'deliver_from' => $supplier?->name ?? 'Main Stock',
                'expected_at' => $data['expected_at'] ?? null,
                'total_cost' => round($totalCost, 2),
                'status' => 'ordered',
                'created_by' => $createdBy,
            ]);

            $order->branches()->sync($branchIds);

            foreach ($lineItems as $line) {
                $order->items()->create($line);
            }

            if ($sourceType === 'main_stock') {
                app(CreateTransferOrder::class)->reserve($order);
            }

            return $order->load(['items.product.translation', 'branches.translation', 'supplier']);
        });
    }

    public function receive(StockOrder $order, array $itemsData): StockOrder
    {
        return DB::transaction(function () use ($order, $itemsData) {
            $order = StockOrder::query()->lockForUpdate()->findOrFail($order->id);
            if ($order->status !== 'ordered') {
                throw new Exception('Order already received');
            }

            $totalCost = 0;
            $itemsById = collect($itemsData)->keyBy('id');
            $order->load(['items.product.primarySku', 'branches']);

            if ($itemsById->count() !== $order->items->count()) {
                throw new Exception('Receive quantities are required for every order item');
            }

            foreach ($itemsById as $itemId => $payload) {
                if (!$order->items->contains('id', (int) $itemId)) {
                    throw new Exception('Receive item does not belong to this order');
                }
            }

            if ($order->source_type === 'main_stock') {
                app(ReceiveTransferOrder::class)->receive($order, $itemsById);
                $order->update([
                    'status' => 'received',
                    'received_at' => now(),
                    'total_cost' => 0,
                ]);

                return $order->fresh(['items.product.translation', 'branches.translation', 'supplier']);
            }

            $purchaseReceiver = app(ReceivePurchaseOrder::class);

            foreach ($order->items as $item) {
                if (!$itemsById->has($item->id)) {
                    continue;
                }

                $payload = $itemsById->get($item->id);
                $receivedQty = (float) $payload['received_qty'];
                $unitCost = (float) $payload['unit_cost'];
                if ($receivedQty > (float) $item->order_qty) {
                    throw new Exception('Received quantity cannot exceed the ordered quantity');
                }
                $lineTotal = round($receivedQty * $unitCost, 2);
                $totalCost += $lineTotal;

                $item->update([
                    'received_qty' => $receivedQty,
                    'unit_cost' => $unitCost,
                    'line_total' => $lineTotal,
                ]);

                $purchaseReceiver->receive($order, $item, (int) $receivedQty);
            }

            $order->update([
                'status' => 'received',
                'received_at' => now(),
                'total_cost' => round($totalCost, 2),
            ]);

            return $order->fresh(['items.product.translation', 'branches.translation', 'supplier']);
        });
    }
}
