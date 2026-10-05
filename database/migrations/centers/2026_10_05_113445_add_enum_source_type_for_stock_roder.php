<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_orders', function (Blueprint $table) {
            // 'supplier'   => external purchase, fills main_stocks
            // 'main_stock' => internal transfer, drains main_stocks into branches
            $table->enum('source_type', ['supplier', 'main_stock'])
                  ->default('supplier')
                  ->after('product_supplier_id');
        });

        // Make product_supplier_id nullable AFTER adding the column
        // (needed because ->change() requires doctrine/dbal on older Laravel versions)
        Schema::table('stock_orders', function (Blueprint $table) {
            $table->foreignId('product_supplier_id')
                  ->nullable()
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('stock_orders', function (Blueprint $table) {
            // Restore NOT NULL constraint (WARNING: will fail if null rows exist)
            $table->foreignId('product_supplier_id')
                  ->nullable(false)
                  ->change();

            $table->dropColumn('source_type');
        });
    }
};