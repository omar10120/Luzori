<?php

namespace Database\Seeders;

use App\Models\BranchTranslation;
use Illuminate\Database\Seeder;

class BranchTranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BranchTranslation::create([
            'branch_id' => 1,
            'name' => 'Main Branch',
            'city' => 'Main Branch',
            'address' => 'Address 1',
            'locale' => 'en',
        ]);

        BranchTranslation::create([
            'branch_id' => 1,
            'name' => 'الفرع الرئيسي',
            'city' => 'الفرع الرئيسي',
            'address' => 'Address 1',
            'locale' => 'ar',
        ]);
    }
}
