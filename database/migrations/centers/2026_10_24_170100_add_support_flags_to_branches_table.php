<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'support_chat_enabled')) {
                $table->boolean('support_chat_enabled')->default(true);
            }
            if (!Schema::hasColumn('branches', 'support_tickets_enabled')) {
                $table->boolean('support_tickets_enabled')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('branches', 'support_chat_enabled')) {
                $cols[] = 'support_chat_enabled';
            }
            if (Schema::hasColumn('branches', 'support_tickets_enabled')) {
                $cols[] = 'support_tickets_enabled';
            }
            if ($cols) {
                $table->dropColumn($cols);
            }
        });
    }
};
