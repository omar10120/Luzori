<?php

namespace App\Services;

use App\Models\InventoryMovement;
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

            foreach ($order->items as $item) {
                if (!$itemsById->has($item->id)) {
                    continue;
                }

                $payload = $itemsById->get($item->id);
                $receivedQty = (float) $payload['received_qty'];
                $unitCost = $order->source_type === 'supplier' ? (float) $payload['unit_cost'] : 0;
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
            }

            $order->update([
                'status' => StockOrder::STATUS_RECEIVED,
                'received_at' => now(),
                'total_cost' => $order->source_type === 'main_stock' ? 0 : round($totalCost, 2),
            ]);

            return $order->fresh(['items.product.translation', 'branches.translation', 'supplier']);
        });
    }

    public function receptionReceive(StockOrder $order): StockOrder
    {
        return DB::transaction(function () use ($order) {
            $order = StockOrder::query()->lockForUpdate()->findOrFail($order->id);
            if ($order->status !== StockOrder::STATUS_RECEIVED) {
                throw new Exception('Admin must receive this order first');
            }

            $order->load(['items.product.primarySku', 'branches']);
            $alreadyApplied = InventoryMovement::query()
                ->where('reference_type', class_basename($order))
                ->where('reference_id', $order->id)
                ->exists();

            $itemsById = $order->items->mapWithKeys(function ($item) {
                $qty = $item->received_qty !== null
                    ? (float) $item->received_qty
                    : (float) $item->order_qty;

                return [$item->id => [
                    'id' => $item->id,
                    'received_qty' => $qty,
                    'unit_cost' => (float) $item->unit_cost,
                ]];
            });

            if (!$alreadyApplied) {
                if ($order->source_type === 'main_stock') {
                    app(ReceiveTransferOrder::class)->receive($order, $itemsById);
                } else {
                    $purchaseReceiver = app(ReceivePurchaseOrder::class);
                    foreach ($order->items as $item) {
                        $qty = (int) ($itemsById[$item->id]['received_qty'] ?? 0);
                        $purchaseReceiver->receive($order, $item, $qty);
                    }
                }
            }

            $order->load(['items.product']);
            $totalCost = 0;
            foreach ($order->items as $item) {
                $qty = $item->received_qty !== null
                    ? (float) $item->received_qty
                    : (float) ($itemsById[$item->id]['received_qty'] ?? $item->order_qty);
                $unitCost = (float) $item->unit_cost;
                if ($unitCost <= 0) {
                    $unitCost = (float) ($item->product->supply_price ?? 0);
                }
                $lineTotal = round($qty * $unitCost, 2);
                $totalCost += $lineTotal;
                $item->update([
                    'received_qty' => $qty,
                    'unit_cost' => $unitCost,
                    'line_total' => $lineTotal,
                ]);
            }

            $order->update([
                'status' => StockOrder::STATUS_RECEPTION_RECEIVE,
                'total_cost' => round($totalCost, 2),
            ]);

            return $order->fresh(['items.product.translation', 'branches.translation', 'supplier']);
        });
    }
}
