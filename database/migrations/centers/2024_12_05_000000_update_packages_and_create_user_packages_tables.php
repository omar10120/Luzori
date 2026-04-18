<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add price to packages
        if (!Schema::hasColumn('packages', 'price')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->double('price')->nullable()->after('created_by');
            });
        }

        // Create users_packages
        if (!Schema::hasTable('users_packages')) {
            Schema::create('users_packages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
                $table->double('price')->default(0);
                $table->string('status')->default('active'); // active, consumed
                $table->timestamps();
            });
        }

        // Create users_used_packages
        if (!Schema::hasTable('users_used_packages')) {
            Schema::create('users_used_packages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('user_package_id')->constrained('users_packages')->onDelete('cascade');
                $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
                $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
                $table->boolean('is_free')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_used_packages');
        Schema::dropIfExists('users_packages');
        
        if (Schema::hasColumn('packages', 'price')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->dropColumn('price');
            });
        }
    }
};
