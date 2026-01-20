<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\GenderEnum;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'first_name' => 'user',
            'last_name' => 'user',
            'email' => 'user@user.com',
            'country_code' => '+963',
            'phone' => '09935',
            'password' => 'p@$$word',
        ]);

        // for ($i = 1; $i <= 5000; $i++) {
        //     $data[] = [
        //         'first_name' => 'user',
        //         'last_name' => 'user',
        //         'email' => 'user' . $i . '@user.com',
        //         'country_code' => '+963',
        //         'phone' => '099357' . $i,
        //         'password' => bcrypt('p@$$word'),
        //     ];
        // }

        // $chunks = array_chunk($data, 500);
        // foreach ($chunks as $chunk) {
        //     User::insert($chunk);
        // }

        // User::factory()->count(3)->create();
    }
}
