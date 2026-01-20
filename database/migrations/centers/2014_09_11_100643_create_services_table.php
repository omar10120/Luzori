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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->integer('rooms_no');
            $table->integer('free_book');
            $table->string('max_time')->nullable();
            $table->string('extra_time')->nullable();
            $table->double('price');
            $table->integer('sort_order')->nullable();
            $table->boolean('is_top')->default(0);
            $table->boolean('has_commission')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
