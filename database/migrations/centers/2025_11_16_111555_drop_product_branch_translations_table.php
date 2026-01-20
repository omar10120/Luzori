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
        Schema::dropIfExists('product_branch_translations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('product_branch_translations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city');
            $table->text('address');
            $table->string('locale')->index();
            
            $table->unique(['product_branch_id', 'locale']);
            $table->foreignId('product_branch_id')->nullable()->constrained('product_branches')->onDelete('cascade');
            $table->timestamps();
        });
    }
};
