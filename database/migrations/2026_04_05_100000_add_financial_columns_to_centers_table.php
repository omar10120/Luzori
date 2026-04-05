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
            $table->decimal('wallet', 15, 2)->default(0)->after('is_setup');
            $table->string('bank_name', 21)->nullable()->after('wallet');
            $table->decimal('admin_discount', 5, 2)->default(0)->after('bank_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->table('centers', function (Blueprint $table) {
            $table->dropColumn(['wallet', 'bank_name', 'admin_discount']);
        });
    }
};
