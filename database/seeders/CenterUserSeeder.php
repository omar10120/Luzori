<?php

namespace Database\Seeders;

use App\Models\CenterUser;
use Illuminate\Database\Seeder;

class CenterUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CenterUser::create([
            'name' => 'Super Admin',
            'email' => 'super_admin@admin.com',
            'country_code' => '+971',
            'phone' => '0504310232',
            'password' => 'admin123'
        ])->assignRole('center_api');
    }
}
