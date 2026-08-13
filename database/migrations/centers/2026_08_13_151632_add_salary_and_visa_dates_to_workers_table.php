<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->decimal('salary', 10, 2)->nullable()->after('phone');
            $table->date('visa_start_date')->nullable()->after('salary'); 
            $table->date('visa_end_date')->nullable()->after('visa_start_date');
        });
    }

    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn(['salary', 'visa_start_date', 'visa_end_date']);
        });
    }
};