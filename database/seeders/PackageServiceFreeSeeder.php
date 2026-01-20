<?php

namespace Database\Seeders;

use App\Models\PackageServiceFree;
use Illuminate\Database\Seeder;

class PackageServiceFreeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PackageServiceFree::factory()->count(10)->create();
    }
}
