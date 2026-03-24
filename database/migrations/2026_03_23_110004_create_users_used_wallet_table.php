<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users_used_wallet', function (Blueprint $table) {
            $table->id();
            $table->float('amount');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('wallet_id')->nullable()->constrained('wallets')->onDelete('cascade');
            $table->unsignedBigInteger('booking_id')->nullable(); // Reference to tenant booking id
            $table->unsignedBigInteger('center_id')->nullable();  // Reference to center where booking happened
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users_used_wallet');
    }
};
