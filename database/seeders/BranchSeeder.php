<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Branch::create([
            'longitude'  => 46.6753,  
            'latitude'   => 24.7136,
            'open_time'  => '09:00:00',
            'close_time' => '22:00:00',
        ]);

        // Branch::factory()->count(15)->create();
    }
}
