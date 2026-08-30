<?php

namespace App\Services;

use App\Models\InventoryMovement;
use Illuminate\Database\Eloquent\Model;

class InventoryMovementService
{
    /**
     * Record a stock movement. Positive qty = IN, negative = OUT.
     */
    public function record(
        int $productId,
        int $branchId,
        int $quantity,
        string $type,
        ?Model $reference = null,
        ?int $skuId = null,
        ?string $notes = null
    ): ?InventoryMovement {
        if ($quantity === 0) {
            return null;
        }

        $centerUserId = auth('center_user')->id();
        $actorNote = $centerUserId ? "center_user:{$centerUserId}" : null;
        $mergedNotes = trim(implode(' | ', array_filter([$notes, $actorNote])));

        return InventoryMovement::create([
            'product_id' => $productId,
            'branch_id' => $branchId,
            'sku_id' => $skuId,
            'quantity' => $quantity,
            'movement_type' => $type,
            'reference_id' => $reference?->getKey(),
            'reference_type' => $reference ? class_basename($reference) : null,
            'user_id' => null, // FK points to users; actor is center_user (stored in notes)
            'notes' => $mergedNotes !== '' ? $mergedNotes : null,
        ]);
    }
}
