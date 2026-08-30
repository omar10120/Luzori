<?php

namespace Database\Seeders;

use App\Models\InventoryMovement;
use App\Models\ProductBranch;
use Illuminate\Database\Seeder;

/**
 * One-time backfill: seed products_inventory_movements from current product_branches stock.
 * Run on the tenant (center) database after switching connection, e.g.:
 *   php artisan db:seed --class=InventoryMovementBackfillSeeder
 */
class InventoryMovementBackfillSeeder extends Seeder
{
    public function run(): void
    {
        $rows = ProductBranch::query()
            ->whereNull('deleted_at')
            ->where('stock_quantity', '!=', 0)
            ->get(['id', 'product_id', 'branch_id', 'stock_quantity']);

        $created = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $exists = InventoryMovement::query()
                ->where('product_id', $row->product_id)
                ->where('branch_id', $row->branch_id)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            InventoryMovement::create([
                'product_id' => $row->product_id,
                'branch_id' => $row->branch_id,
                'sku_id' => null,
                'quantity' => (int) $row->stock_quantity,
                'movement_type' => InventoryMovement::TYPE_INITIAL,
                'reference_id' => $row->id,
                'reference_type' => 'ProductBranch',
                'user_id' => null,
                'notes' => 'Backfill from product_branches',
            ]);

            $created++;
        }

        $this->command?->info("Inventory backfill done. Created: {$created}, skipped (already had movements): {$skipped}");
    }
}
