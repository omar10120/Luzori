<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('placement_type', [
            'home',
            'mobile',
            'web',
            'sidebar',
            'checkout',
            'category_page',
            'service_page',
        ])->default('home');
            $table->enum('link_type', ['external', 'category', 'service', 'center'])
                ->default('external');
            $table->string('external_url')->nullable();
            $table->foreignId('center_id')->nullable()->constrained('centers')->nullOnDelete();
            $table->foreignId('global_category_id')->nullable()->constrained('global_categories')->nullOnDelete();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['link_type', 'is_active', 'sort_order']);
            $table->index(['center_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
