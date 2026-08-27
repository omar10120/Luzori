<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('central')->create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->enum('target_type', ['users', 'centers'])->default('users');
            $table->boolean('status')->default(true);
            $table->unsignedInteger('sent_count')->default(0);
            $table->string('type')->nullable()->comment('admin, booking_confirmed, booking_rejected, system');
            $table->timestamps();
        });

        Schema::connection('central')->create('notification_translations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('text');
            $table->string('locale')->index();
            $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
            $table->unique(['notification_id', 'locale']);
            $table->timestamps();
        });

        Schema::connection('central')->create('notifiables', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_read')->default(false);
            $table->nullableMorphs('notifiable');
            $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::connection('central')->create('fcm_tokens', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('tokenable');
            $table->string('token');
            $table->timestamps();
        });

        Schema::connection('central')->create('firebase_settings', function (Blueprint $table) {
            $table->id();
            $table->longText('service_account_json')->nullable();
            $table->string('api_key')->nullable();
            $table->string('auth_domain')->nullable();
            $table->string('project_id')->nullable();
            $table->string('storage_bucket')->nullable();
            $table->string('messaging_sender_id')->nullable();
            $table->string('app_id')->nullable();
            $table->string('measurement_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('firebase_settings');
        Schema::connection('central')->dropIfExists('fcm_tokens');
        Schema::connection('central')->dropIfExists('notifiables');
        Schema::connection('central')->dropIfExists('notification_translations');
        Schema::connection('central')->dropIfExists('notifications');
    }
};
