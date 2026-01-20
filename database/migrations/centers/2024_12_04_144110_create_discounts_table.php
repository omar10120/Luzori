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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('type')->nullable();
            $table->double('amount')->nullable();
            $table->string('start_at')->nullable();
            $table->string('end_at')->nullable();
            $table->string('status')->nullable();
            $table->string('using_type')->nullable();
            $table->integer('benefit_numbers')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
