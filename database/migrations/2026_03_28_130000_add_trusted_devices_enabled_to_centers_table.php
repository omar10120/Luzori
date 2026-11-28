<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('central')->table('centers', function (Blueprint $table) {
            if (!Schema::connection('central')->hasColumn('centers', 'trusted_devices_enabled')) {
                $table->boolean('trusted_devices_enabled')->default(false)->after('center_device_limit');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('central')->table('centers', function (Blueprint $table) {
            if (Schema::connection('central')->hasColumn('centers', 'trusted_devices_enabled')) {
                $table->dropColumn('trusted_devices_enabled');
            }
        });
    }
};
