<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();

            // Central user (main DB)
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Tenant references — no FKs (these live in the center DB)
            $table->unsignedBigInteger('center_id')->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();

            // 'product' | 'booking'  (validate in FormRequest, not DB enum)
            $table->string('item_type', 20)->index();

            // product_id  OR  service_id (tenant-side id)
            $table->unsignedBigInteger('item_id')->index();

            // ---------- product-only ----------
            $table->unsignedInteger('qty')->nullable();

            // ---------- booking-only ----------
            // booking_number stays NULL until a real booking is created at checkout
            $table->string('booking_number', 50)->nullable()->index();
            $table->unsignedBigInteger('worker_id')->nullable();
            $table->date('booking_date')->nullable();
            $table->time('from_time')->nullable();
            $table->time('to_time')->nullable();
            $table->json('user_package_ids')->nullable();

            // ---------- denormalized snapshot for fast cart listing ----------
            $table->string('name')->nullable();
            $table->string('sku', 100)->nullable();
            $table->string('image_url')->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);

            // ---------- exact client request for replay at checkout ----------
            $table->json('request_payload')->nullable();

            // ---------- dedupe (products only) ----------
            // md5("{user_id}:{center_id}:{branch_id}:product:{item_id}")
            // NULL for bookings → unique index allows many NULLs
            $table->string('dedupe_key', 64)->nullable()->unique();

            $table->timestamps();
            // NOTE: no softDeletes — cart rows are hard-deleted on remove/checkout.

            $table->index(['user_id', 'center_id', 'item_type']);
            $table->index(['user_id', 'item_type', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};