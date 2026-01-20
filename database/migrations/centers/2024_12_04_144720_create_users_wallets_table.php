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
        Schema::create('users_wallets', function (Blueprint $table) {
            $table->id();
            $table->string('wallet_type')->nullable();
            $table->double('amount')->nullable();
            $table->double('invoiced_amount')->nullable();
            $table->float('commission')->nullable();
            $table->foreignId('wallet_id')->nullable()->constrained('wallets')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('worker_id')->nullable()->constrained('workers')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
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
        Schema::dropIfExists('users_wallets');
    }
};
