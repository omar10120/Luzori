<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = DB::connection();
        $database = $connection->getDatabaseName();
        
        // Check if column exists
        $columnExists = $connection->selectOne("
            SELECT COUNT(*) as count
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = 'product_branches' 
            AND COLUMN_NAME = 'branch_id'
        ", [$database]);
        
        if ($columnExists && $columnExists->count > 0) {
            // Get all foreign keys on this column
            $fks = $connection->select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = ? 
                AND TABLE_NAME = 'product_branches' 
                AND COLUMN_NAME = 'branch_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$database]);
            
            // Drop all foreign keys
            foreach ($fks as $fk) {
                try {
                    DB::statement("ALTER TABLE `product_branches` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                } catch (\Exception $e) {
                    // Continue if FK doesn't exist
                }
            }
            
            // Drop the column
            try {
                DB::statement("ALTER TABLE `product_branches` DROP COLUMN `branch_id`");
            } catch (\Exception $e) {
                // Column might already be dropped
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not reversible - we don't want to add branch_id back
    }
};

