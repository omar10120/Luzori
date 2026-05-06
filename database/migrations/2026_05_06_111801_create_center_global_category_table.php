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
        Schema::create('center_global_category', function (Blueprint $table) {
            $table->id();

            $table->foreignId('center_id')->constrained()->cascadeOnDelete();
            $table->foreignId('global_category_id')->constrained()->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['center_id', 'global_category_id']); // prevent duplicates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('center_global_category');
    }
};
