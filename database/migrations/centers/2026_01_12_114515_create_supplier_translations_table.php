<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('supplier_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->string('locale')->index();
            $table->text('description')->nullable();

            $table->unique(['supplier_id', 'locale']);
            $table->timestamps();
        });

        // Copy existing descriptions to the translation table (using default locale 'ar' or 'en')
        $suppliers = DB::table('suppliers')->get();
        foreach ($suppliers as $supplier) {
            if ($supplier->description) {
                DB::table('supplier_translations')->insert([
                    'supplier_id' => $supplier->id,
                    'locale' => 'ar', // Assuming existing data is Arabic as per current UI
                    'description' => $supplier->description,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Remove description from suppliers table
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add description back to suppliers table
        Schema::table('suppliers', function (Blueprint $table) {
            $table->text('description')->nullable();
        });

        // Copy back from translations if needed (back to Arabic description)
        $translations = DB::table('supplier_translations')->where('locale', 'ar')->get();
        foreach ($translations as $translation) {
            DB::table('suppliers')->where('id', $translation->supplier_id)->update([
                'description' => $translation->description
            ]);
        }

        Schema::dropIfExists('supplier_translations');
    }
};
