<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('country_code', 20)->nullable();
            $table->string('phone')->unique();
            $table->string('password')->nullable();
            $table->float('wallet')->default(0);
            $table->boolean('is_active')->default(1);
            $table->text('image')->nullable();
            $table->text('address')->nullable();
            $table->date('birth')->nullable();
            $table->string('gender', 32)->nullable();
            $table->string('remember_token')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
