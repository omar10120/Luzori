<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * App users live on the `central` connection (see AppUser, add_firebase_columns migration).
     * Columns are optional (nullable). Safe to re-run: skips if columns already exist.
     */
    public function up(): void
    {
        $connection = 'central';

        if (!Schema::connection($connection)->hasTable('users')) {
            return;
        }

        Schema::connection($connection)->table('users', function (Blueprint $table) use ($connection) {
            if (!Schema::connection($connection)->hasColumn('users', 'address')) {
                $table->text('address')->nullable();
            }
            if (!Schema::connection($connection)->hasColumn('users', 'birth')) {
                $table->date('birth')->nullable();
            }
            if (!Schema::connection($connection)->hasColumn('users', 'gender')) {
                $table->string('gender', 32)->nullable();
            }
        });
    }

    public function down(): void
    {
        $connection = 'central';

        if (!Schema::connection($connection)->hasTable('users')) {
            return;
        }

        Schema::connection($connection)->table('users', function (Blueprint $table) use ($connection) {
            $drop = [];
            foreach (['address', 'birth', 'gender'] as $column) {
                if (Schema::connection($connection)->hasColumn('users', $column)) {
                    $drop[] = $column;
                }
            }
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
