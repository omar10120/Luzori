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
        Schema::table('bookings_details', function (Blueprint $table) {
            $table->enum('commission_type', ['percentage', 'fixed'])->nullable()->after('commission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings_details', function (Blueprint $table) {
            $table->dropColumn('commission_type');
        });
    }
};
