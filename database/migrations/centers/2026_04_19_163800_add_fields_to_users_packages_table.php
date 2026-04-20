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
        Schema::table('users_packages', function (Blueprint $table) {
            $table->string('package_type')->nullable()->after('status');
            $table->integer('created_by')->nullable()->after('package_type');
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_packages', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['package_type', 'created_by']);
        });
    }
};
