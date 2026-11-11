<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('buy_products', 'commission_type')) {
            Schema::table('buy_products', function (Blueprint $table) {
                $table->enum('commission_type', ['percentage', 'fixed'])->nullable()->after('commission');
            });
        }

        if (!Schema::hasColumn('users_wallets', 'commission_type')) {
            Schema::table('users_wallets', function (Blueprint $table) {
                $table->enum('commission_type', ['percentage', 'fixed'])->nullable()->after('commission');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('buy_products', 'commission_type')) {
            Schema::table('buy_products', function (Blueprint $table) {
                $table->dropColumn('commission_type');
            });
        }

        if (Schema::hasColumn('users_wallets', 'commission_type')) {
            Schema::table('users_wallets', function (Blueprint $table) {
                $table->dropColumn('commission_type');
            });
        }
    }
};
