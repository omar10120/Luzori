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
        Schema::create('products_inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('sku_id')->nullable()->constrained('product_skus')->nullOnDelete();
            
            $table->integer('quantity')->comment('Positive = IN, Negative = OUT');
            $table->enum('movement_type', [
                'purchase', 
                'stock_order', 
                'sale', 
                'sale_deleted',  
                'adjustment', 
                'transfer_out', 
                'transfer_in', 
                'initial'
            ])->comment('Type of stock operation');
            
            $table->unsignedBigInteger('reference_id')->nullable()->comment('Source record ID');
            $table->string('reference_type', 100)->nullable()->comment('Source model class');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            
            $table->timestamps();

        
            $table->index(['product_id', 'branch_id', 'movement_type'], 'idx_product_branch_movement');
            $table->index(['branch_id', 'movement_type', 'created_at'], 'idx_branch_movement_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products_inventory_movements');
    }
};
