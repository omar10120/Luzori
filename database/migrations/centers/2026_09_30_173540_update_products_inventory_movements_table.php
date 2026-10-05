<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products_inventory_movements', function (Blueprint $table) {
            // Allow the same table to record warehouse-side movements
            $table->unsignedBigInteger('branch_id')->nullable()->change();

            // true  => this movement happened at the central warehouse
            // false => this movement happened at a branch (branch_id is set)
            $table->boolean('is_main_stock')->default(false)->after('branch_id');
        });
    }

    public function down(): void
    {
        Schema::table('products_inventory_movements', function (Blueprint $table) {
            $table->dropColumn('is_main_stock');
            $table->unsignedBigInteger('branch_id')->nullable(false)->change();
        });
    }
};