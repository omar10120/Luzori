<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('central')->create('support_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained('centers')->cascadeOnDelete();
            $table->text('last_message')->nullable();
            $table->string('last_sender_name')->nullable();
            $table->string('last_sender_type', 32)->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->unsignedInteger('unread_admin')->default(0);
            $table->unsignedInteger('unread_center')->default(0);
            $table->timestamps();

            $table->unique('center_id');
            $table->index('last_message_at');
        });

        Schema::connection('central')->create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('support_conversations')->cascadeOnDelete();
            $table->foreignId('center_id')->constrained('centers')->cascadeOnDelete();
            $table->string('sender_type', 32); // admin | center_user
            $table->unsignedBigInteger('sender_id');
            $table->string('sender_name');
            $table->string('sender_role')->nullable();
            $table->text('body');
            $table->string('firebase_key')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'id']);
            $table->index(['center_id', 'id']);
        });

        Schema::connection('central')->create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained('centers')->cascadeOnDelete();
            $table->unsignedBigInteger('opened_by_id')->nullable();
            $table->string('opened_by_name')->nullable();
            $table->string('subject');
            $table->text('body');
            $table->string('status', 32)->default('open'); // open | in_progress | resolved | closed
            $table->string('priority', 16)->default('normal'); // low | normal | high | urgent
            $table->timestamp('last_reply_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'updated_at']);
            $table->index(['center_id', 'status']);
        });

        Schema::connection('central')->create('support_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->string('sender_type', 32); // admin | center_user
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('sender_name');
            $table->text('body');
            $table->timestamps();

            $table->index(['ticket_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('support_ticket_replies');
        Schema::connection('central')->dropIfExists('support_tickets');
        Schema::connection('central')->dropIfExists('support_messages');
        Schema::connection('central')->dropIfExists('support_conversations');
    }
};
