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
        Schema::connection('central')->table('centers', function (Blueprint $table) {
            $table->timestamp('expire_date')->nullable()->after('admin_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->table('centers', function (Blueprint $table) {
            $table->dropColumn(['expire_date']);
        });
    }
};
