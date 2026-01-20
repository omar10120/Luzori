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
        Schema::create('buy_products', function (Blueprint $table) {
            $table->id();
            $table->string('payment_type')->nullable();
            $table->float('commission')->nullable();
            $table->float('discount')->nullable();
            $table->foreignId('sales_worker_id')->nullable()->constrained('workers')->onDelete('cascade');
            $table->foreignId('worker_id')->nullable()->constrained('workers')->onDelete('cascade');
            $table->integer('created_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buy_products');
    }
};
