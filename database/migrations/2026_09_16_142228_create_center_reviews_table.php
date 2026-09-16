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
        Schema::create('center_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('center_id');

            $table->unsignedTinyInteger('rating');     // 1..5
            $table->text('comment')->nullable();

            $table->timestamps();

            // One review per user per center
            $table->unique(['user_id', 'center_id']);

            // Fast lookups for "list reviews of center X"
            $table->index('center_id');
            $table->index('user_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('center_id')->references('id')->on('centers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('center_reviews');
    }
};
