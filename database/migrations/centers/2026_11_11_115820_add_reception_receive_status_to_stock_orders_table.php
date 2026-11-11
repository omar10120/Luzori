<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE stock_orders
            MODIFY COLUMN status ENUM('ordered', 'received', 'reception-receive')
            NOT NULL DEFAULT 'ordered'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE stock_orders
            MODIFY COLUMN status ENUM('ordered', 'received')
            NOT NULL DEFAULT 'ordered'
        ");
    }
};
