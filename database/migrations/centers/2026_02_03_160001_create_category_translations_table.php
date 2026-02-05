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
        /* Schema::create('category_service_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_service_id')->constrained('categories_services')->onDelete('cascade');
            $table->string('locale')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('keywords')->nullable();
            $table->timestamps();

            $table->unique(['category_id', 'locale']);
        }); */
        Schema::create('category_service_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_service_id')->constrained('categories_services')->onDelete('cascade');
            $table->string('locale')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('keywords')->nullable();
            $table->timestamps();
        });
        $categories = DB::table('categories_services')->get();
        foreach ($categories as $category) {
            DB::table('category_service_translations')->insert([
                'category_service_id' => $category->id,
                'locale' => 'ar',
                'name' => $category->name,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_service_translations');
    }
};
