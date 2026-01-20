<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique()->nullable();
            $table->string('country_code', 20)->nullable();
            $table->string('phone')->unique();
            $table->string('password')->nullable();
            $table->float('wallet')->default(0);
            $table->boolean('is_active')->default(0);
            $table->integer('role_id')->nullable();
            $table->text('image')->nullable();
            $table->string('remember_token')->nullable();
            $table->string('created_by')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
