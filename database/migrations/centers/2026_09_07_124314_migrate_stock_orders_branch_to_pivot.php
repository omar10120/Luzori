<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Copy existing branch_id to the pivot table
        $orders = DB::table('stock_orders')
                    ->whereNotNull('branch_id')
                    ->get(['id', 'branch_id']);

        foreach ($orders as $order) {
            DB::table('stock_order_branches')->insert([
                'stock_order_id' => $order->id,
                'branch_id'      => $order->branch_id,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        // 2. Drop the foreign key constraint (if defined)
        Schema::table('stock_orders', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }

    public function down(): void
    {
        // Rollback: re-add the column (data won't be restored automatically)
        Schema::table('stock_orders', function (Blueprint $table) {
            $table->foreignId('branch_id')
                  ->nullable()
                  ->constrained('branches')
                  ->cascadeOnDelete();
        });
    }
};