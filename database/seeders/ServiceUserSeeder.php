<?php

namespace Database\Seeders;

use App\Models\ServiceUser;
use Illuminate\Database\Seeder;

class ServiceUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ServiceUser::factory()->count(20)->create();
    }
}
