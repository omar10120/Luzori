<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_order_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_order_id')
                  ->constrained('stock_orders')
                  ->cascadeOnDelete();
            $table->foreignId('branch_id')
                  ->constrained('branches')
                  ->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['stock_order_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_order_branches');
    }
};