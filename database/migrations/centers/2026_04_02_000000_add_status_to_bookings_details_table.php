<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings_details', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed', 'rejected'])->default('confirmed')->after('booking_source');
        });
    }

    public function down(): void
    {
        Schema::table('bookings_details', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
