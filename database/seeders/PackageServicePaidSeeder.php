<?php

namespace Database\Seeders;

use App\Models\PackageServicePaid;
use Illuminate\Database\Seeder;

class PackageServicePaidSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PackageServicePaid::factory()->count(10)->create();
    }
}
