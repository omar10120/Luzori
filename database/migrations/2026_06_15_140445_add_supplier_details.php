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
        Schema::table('centers', function (Blueprint $table) {
            $table->bigInteger('supplier_code')->nullable()->after('id');
            $table->string('supplier_email')->nullable()->after('supplier_code');
            $table->dateTime('supplier_date')->nullable()->after('supplier_email');
            $table->boolean('is_supplier')->default(false)->after('supplier_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('centers', function (Blueprint $table) {
            $table->dropColumn('supplier_code');
            $table->dropColumn('supplier_email');
            $table->dropColumn('supplier_date');
            $table->dropColumn('is_supplier');
        });
    }
};
