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
        Schema::table('products', function (Blueprint $table) {
            // Basic product fields
            $table->string('barcode')->nullable()->after('id');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null')->after('barcode');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null')->after('brand_id');
            $table->string('measure_unit', 50)->nullable()->after('category_id');
            $table->decimal('measure_amount', 10, 2)->nullable()->after('measure_unit');
            $table->string('short_description', 100)->nullable()->after('measure_amount');
            
            // Pricing fields
            $table->decimal('supply_price', 10, 2)->nullable()->after('short_description');
            $table->decimal('retail_price', 10, 2)->nullable()->after('supply_price');
            $table->decimal('markup', 5, 2)->nullable()->after('retail_price');
            $table->boolean('allow_retail_sales')->default(true)->after('markup');
            
            // Inventory fields
            $table->boolean('track_stock')->default(false)->after('allow_retail_sales');
            $table->integer('current_stock')->nullable()->after('track_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['category_id']);
            $table->dropForeign(['supplier_id']);
            $table->dropColumn([
                'barcode',
                'brand_id',
                'category_id',
                'measure_unit',
                'measure_amount',
                'short_description',
                'supply_price',
                'retail_price',
                'markup',
                'allow_retail_sales',
                'sku',
                'supplier_id',
                'track_stock',
                'current_stock'
            ]);
        });
    }
};

