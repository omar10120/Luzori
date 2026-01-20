<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check and drop foreign key for supplier_id if it exists
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'products' 
            AND COLUMN_NAME = 'supplier_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        if (!empty($foreignKeys)) {
            $constraintName = $foreignKeys[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE `products` DROP FOREIGN KEY `{$constraintName}`");
        }
        
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'supplier_id')) {
                $table->dropColumn('supplier_id');
            }
            if (Schema::hasColumn('products', 'sku')) {
                $table->dropColumn('sku');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku', 100)->unique()->nullable()->after('allow_retail_sales');
            }
            if (!Schema::hasColumn('products', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null')->after('sku');
            }
        });
    }
};

