<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('central')->table('trusted_devices', function (Blueprint $table) {
            if (!Schema::connection('central')->hasColumn('trusted_devices', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('ip_address');
            }
            if (!Schema::connection('central')->hasColumn('trusted_devices', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });

        Schema::connection('central')->table('auth_logs', function (Blueprint $table) {
            if (!Schema::connection('central')->hasColumn('auth_logs', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('ip_address');
            }
            if (!Schema::connection('central')->hasColumn('auth_logs', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('central')->table('trusted_devices', function (Blueprint $table) {
            if (Schema::connection('central')->hasColumn('trusted_devices', 'longitude')) {
                $table->dropColumn('longitude');
            }
            if (Schema::connection('central')->hasColumn('trusted_devices', 'latitude')) {
                $table->dropColumn('latitude');
            }
        });

        Schema::connection('central')->table('auth_logs', function (Blueprint $table) {
            if (Schema::connection('central')->hasColumn('auth_logs', 'longitude')) {
                $table->dropColumn('longitude');
            }
            if (Schema::connection('central')->hasColumn('auth_logs', 'latitude')) {
                $table->dropColumn('latitude');
            }
        });
    }
};
