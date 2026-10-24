<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('central')->table('centers', function (Blueprint $table) {
            if (!Schema::connection('central')->hasColumn('centers', 'support_chat_enabled')) {
                $table->boolean('support_chat_enabled')->default(true);
            }
            if (!Schema::connection('central')->hasColumn('centers', 'support_tickets_enabled')) {
                $table->boolean('support_tickets_enabled')->default(true);
            }
        });

        Schema::connection('central')->table('support_messages', function (Blueprint $table) {
            if (!Schema::connection('central')->hasColumn('support_messages', 'attachment_type')) {
                $table->string('attachment_type', 16)->nullable(); // image | video
            }
            if (!Schema::connection('central')->hasColumn('support_messages', 'attachment_path')) {
                $table->string('attachment_path')->nullable();
            }
            if (!Schema::connection('central')->hasColumn('support_messages', 'attachment_name')) {
                $table->string('attachment_name')->nullable();
            }
            if (!Schema::connection('central')->hasColumn('support_messages', 'attachment_mime')) {
                $table->string('attachment_mime', 120)->nullable();
            }
            if (!Schema::connection('central')->hasColumn('support_messages', 'attachment_size')) {
                $table->unsignedBigInteger('attachment_size')->nullable();
            }
            if (!Schema::connection('central')->hasColumn('support_messages', 'seen_at')) {
                $table->timestamp('seen_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::connection('central')->table('centers', function (Blueprint $table) {
            $cols = [];
            if (Schema::connection('central')->hasColumn('centers', 'support_chat_enabled')) {
                $cols[] = 'support_chat_enabled';
            }
            if (Schema::connection('central')->hasColumn('centers', 'support_tickets_enabled')) {
                $cols[] = 'support_tickets_enabled';
            }
            if ($cols) {
                $table->dropColumn($cols);
            }
        });

        Schema::connection('central')->table('support_messages', function (Blueprint $table) {
            $cols = [];
            foreach (['attachment_type', 'attachment_path', 'attachment_name', 'attachment_mime', 'attachment_size', 'seen_at'] as $c) {
                if (Schema::connection('central')->hasColumn('support_messages', $c)) {
                    $cols[] = $c;
                }
            }
            if ($cols) {
                $table->dropColumn($cols);
            }
        });
    }
};
