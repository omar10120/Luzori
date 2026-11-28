<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('central')->table('centers', function (Blueprint $table) {
            if (!Schema::connection('central')->hasColumn('centers', 'center_device_limit')) {
                $table->unsignedInteger('center_device_limit')->nullable()->after('sms_request_package');
            }
            if (!Schema::connection('central')->hasColumn('centers', 'device_otp_extra_emails')) {
                $table->text('device_otp_extra_emails')->nullable()->after('center_device_limit');
            }
        });

        if (!Schema::connection('central')->hasTable('trusted_devices')) {
            Schema::connection('central')->create('trusted_devices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('center_id');
                $table->unsignedBigInteger('user_id');
                $table->string('device_uuid', 255);
                $table->string('device_name', 150)->nullable();
                $table->enum('device_type', ['desktop', 'tablet', 'mobile'])->default('desktop');
                $table->string('platform', 50)->nullable();
                $table->string('browser', 50)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->boolean('is_blocked')->default(false);
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();

                $table->unique(['center_id', 'device_uuid']);
                $table->index(['center_id', 'user_id']);
            });
        }

        if (!Schema::connection('central')->hasTable('device_otp_codes')) {
            Schema::connection('central')->create('device_otp_codes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('center_id');
                $table->unsignedBigInteger('user_id');
                $table->string('device_uuid', 255);
                $table->string('code_hash', 255);
                $table->timestamp('expires_at');
                $table->timestamp('used_at')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index(['center_id', 'user_id', 'device_uuid']);
            });
        }

        if (!Schema::connection('central')->hasTable('device_sessions')) {
            Schema::connection('central')->create('device_sessions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('center_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('device_id');
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->boolean('is_active')->default(true);

                $table->index(['center_id', 'is_active']);
                $table->index(['device_id']);
            });
        }

        if (!Schema::connection('central')->hasTable('auth_logs')) {
            Schema::connection('central')->create('auth_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('center_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('device_uuid', 255)->nullable();
                $table->enum('action', [
                    'login_success',
                    'login_failed',
                    'otp_sent',
                    'otp_verified',
                    'otp_failed',
                    'device_trusted',
                    'device_removed',
                    'device_blocked',
                    'logout',
                ]);
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index(['center_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('auth_logs');
        Schema::connection('central')->dropIfExists('device_sessions');
        Schema::connection('central')->dropIfExists('device_otp_codes');
        Schema::connection('central')->dropIfExists('trusted_devices');

        Schema::connection('central')->table('centers', function (Blueprint $table) {
            if (Schema::connection('central')->hasColumn('centers', 'device_otp_extra_emails')) {
                $table->dropColumn('device_otp_extra_emails');
            }
            if (Schema::connection('central')->hasColumn('centers', 'center_device_limit')) {
                $table->dropColumn('center_device_limit');
            }
        });
    }
};
