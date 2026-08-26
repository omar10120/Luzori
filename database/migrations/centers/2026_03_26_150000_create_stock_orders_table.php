<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('product_supplier_id')->constrained('product_suppliers')->cascadeOnDelete();
            $table->string('deliver_from');
            $table->date('expected_at')->nullable();
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->enum('status', ['ordered', 'received'])->default('ordered');
            $table->foreignId('created_by')->nullable()->constrained('center_users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_orders');
    }
};
