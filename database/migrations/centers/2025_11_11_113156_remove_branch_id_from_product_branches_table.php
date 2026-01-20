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
        $connection = DB::connection();
        $database = $connection->getDatabaseName();
        
        // Check if column exists using raw SQL
        $columnExists = $connection->selectOne("
            SELECT COUNT(*) as count
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = 'product_branches' 
            AND COLUMN_NAME = 'branch_id'
        ", [$database]);
        
        if ($columnExists && $columnExists->count > 0) {
            try {
                // Get the actual foreign key constraint name
                $fkResult = $connection->select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = ? 
                    AND TABLE_NAME = 'product_branches' 
                    AND COLUMN_NAME = 'branch_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                    LIMIT 1
                ", [$database]);
                
                // Drop foreign key if it exists
                if (!empty($fkResult) && isset($fkResult[0]->CONSTRAINT_NAME)) {
                    try {
                        $fkName = $fkResult[0]->CONSTRAINT_NAME;
                        DB::statement("ALTER TABLE `product_branches` DROP FOREIGN KEY `{$fkName}`");
                    } catch (\Exception $e) {
                        // Foreign key might not exist, continue
                    }
                }
                
                // Drop the column
                DB::statement("ALTER TABLE `product_branches` DROP COLUMN `branch_id`");
            } catch (\Exception $e) {
                // Column might already be dropped, that's okay
                // Just log it but don't fail the migration
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_branches', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
        });
    }
};
