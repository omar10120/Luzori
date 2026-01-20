<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('product_branches')) {
            // Drop existing columns if they exist
            Schema::table('product_branches', function (Blueprint $table) {
                if (Schema::hasColumn('product_branches', 'longitude')) {
                    $table->dropColumn('longitude');
                }
                if (Schema::hasColumn('product_branches', 'latitude')) {
                    $table->dropColumn('latitude');
                }
            });

            // Add branch_id if it doesn't exist
            if (!Schema::hasColumn('product_branches', 'branch_id')) {
                Schema::table('product_branches', function (Blueprint $table) {
                    $table->foreignId('branch_id')->after('product_id')->constrained('branches')->onDelete('cascade');
                });
            }

            // Add unique constraint for product_id + branch_id
            $connection = DB::connection();
            $database = $connection->getDatabaseName();
            
            $indexExists = $connection->selectOne("
                SELECT COUNT(*) as count
                FROM information_schema.STATISTICS 
                WHERE TABLE_SCHEMA = ? 
                AND TABLE_NAME = 'product_branches' 
                AND INDEX_NAME = 'product_branches_product_id_branch_id_unique'
            ", [$database]);
            
            if (!$indexExists || $indexExists->count == 0) {
                Schema::table('product_branches', function (Blueprint $table) {
                    $table->unique(['product_id', 'branch_id']);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('product_branches')) {
            Schema::table('product_branches', function (Blueprint $table) {
                // Drop unique constraint
                $table->dropUnique(['product_id', 'branch_id']);
                
                // Drop branch_id
                if (Schema::hasColumn('product_branches', 'branch_id')) {
                    $table->dropForeign(['branch_id']);
                    $table->dropColumn('branch_id');
                }
                
                // Re-add longitude and latitude
                if (!Schema::hasColumn('product_branches', 'longitude')) {
                    $table->string('longitude')->after('stock_quantity');
                }
                if (!Schema::hasColumn('product_branches', 'latitude')) {
                    $table->string('latitude')->after('longitude');
                }
            });
        }
    }
};
