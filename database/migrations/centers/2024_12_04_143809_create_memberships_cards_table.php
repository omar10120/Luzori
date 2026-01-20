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
        Schema::create('memberships_cards', function (Blueprint $table) {
            $table->id();
            $table->string('membership_no')->nullable();
            $table->double('percent')->nullable();
            $table->string('start_at')->nullable();
            $table->string('end_at')->nullable();
            $table->string('status')->nullable();
            $table->integer('created_by')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships_cards');
    }
};
