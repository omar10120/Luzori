<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('bookings_details', 'status')) {
            return;
        }

        Schema::table('bookings_details', function (Blueprint $table) {
            $column = $table->enum('status', ['pending', 'confirmed', 'rejected'])->default('confirmed');
            if (Schema::hasColumn('bookings_details', 'booking_source')) {
                $column->after('booking_source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings_details', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
