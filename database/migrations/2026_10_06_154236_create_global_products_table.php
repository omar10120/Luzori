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
        Schema::create('global_products', function (Blueprint $table) {
            $table->id();
            
            // Reference to the center in the main database
            $table->unsignedBigInteger('center_id');
            
            // The ID of the product from the center (tenant) database
            $table->unsignedBigInteger('product_id');
            
            // Cached fields for fast API search and display
            $table->string('name')->nullable();
            
            // Admin controls
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();

            // Foreign Key Constraints
            $table->foreign('center_id')
                  ->references('id')
                  ->on('centers')
                  ->onDelete('cascade');


            // Unique Constraint to prevent adding the same product from the same center twice
            $table->unique(['center_id', 'product_id'], 'global_products_center_product_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_products');
    }
};