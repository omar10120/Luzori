<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoice_settings', function (Blueprint $table) {
            $table->string('otp_channel', 10)->default('sms')->after('otp_phones');
        });
    }
    public function down(): void
    {
        Schema::table('invoice_settings', function (Blueprint $table) {
            $table->dropColumn('otp_channel');
        });
    }
};