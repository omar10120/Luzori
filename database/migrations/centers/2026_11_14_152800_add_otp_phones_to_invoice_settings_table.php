<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_settings', 'otp_phones')) {
                $table->json('otp_phones')->nullable()->after('phone_number_3');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoice_settings', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_settings', 'otp_phones')) {
                $table->dropColumn('otp_phones');
            }
        });
    }
};
