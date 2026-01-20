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
        Schema::create('stocktake_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stocktake_id')->constrained('stocktakes')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->integer('expected_qty')->default(0);
            $table->integer('counted_qty')->nullable();
            $table->integer('difference')->default(0);
            $table->decimal('cost', 10, 2)->default(0);
            $table->foreignId('counted_by')->nullable()->constrained('center_users')->onDelete('set null');
            $table->timestamps();
            
            $table->unique(['stocktake_id', 'product_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocktake_products');
    }
};
