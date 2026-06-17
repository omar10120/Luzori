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
        Schema::table('centers', function (Blueprint $table) {
            $table->string('iban', 100)->nullable();
            $table->string('BankAccountHolderName', 100)->nullable();
            $table->string('BusinessName', 100)->nullable();
            $table->string('BankAccount', 100)->nullable();
        });
    }   

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('centers', function (Blueprint $table) {
            
        });
    }
};
