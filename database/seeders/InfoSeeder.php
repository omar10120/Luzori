<?php

namespace Database\Seeders;

use App\Models\Info;
use Illuminate\Database\Seeder;

class InfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Info::create([
            'email' => 'example@example.com',
            'phone' => '959658778',
            'facebook' => 'https://www.facebook.com',
            'linkedin' => 'https://www.linkedin.com',
            'instagram' => 'https://www.instagram.com',
            'twitter' => 'https://www.twitter.com',
            'whatsapp' => 'https://www.whatsapp.com',
            'youtube' => 'https://www.youtube.com',
        ]);
    }
}
