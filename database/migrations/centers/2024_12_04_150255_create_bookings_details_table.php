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
        Schema::create('bookings_details', function (Blueprint $table) {
            $table->id();
            $table->string('_date')->nullable();
            $table->string('from_time')->nullable();
            $table->string('to_time')->nullable();
            $table->float('commission')->nullable();
            $table->boolean('is_free')->nullable();
            $table->integer('tip')->nullable();
            $table->double('price')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('cascade');
            $table->foreignId('worker_id')->nullable()->constrained('workers')->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings_details');
    }
};
