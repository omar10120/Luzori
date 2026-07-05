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
            'name' => 'Branch 1',
            'city' => 'City 1',
            'address' => 'Address 2',
            'locale' => 'en',
        ]);
        BranchTranslation::create([
            'branch_id' => 1,
            'name' => 'Branch 1',
            'city' => 'Branch 1',
            'address' => 'Address 2',
            'locale' => 'ar',   
        ]);
    }
}
