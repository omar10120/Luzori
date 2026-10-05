<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       
        Schema::create('main_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('sku_id')->nullable()->constrained('product_skus')->nullOnDelete();

            $table->integer('quantity')->default(0);          // physical on-hand
            $table->integer('reserved_quantity')->default(0); // qty committed to pending stock orders
            $table->integer('reorder_level')->default(0);     // low-stock threshold

            $table->timestamps();

            $table->unique(['product_id', 'sku_id']); // one row per product (or product+sku)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('main_stocks');
    }
};
