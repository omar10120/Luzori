<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use App\Enums\SettingEnum;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::create([
            'key' => SettingEnum::purchase_code->value,
            'value' => '2023'
        ]);
        Setting::create([
            'key' => SettingEnum::language->value,
            'value' => 'en'
        ]);
        Setting::create([
            'key' => SettingEnum::tips->value,
            'value' => '100'
        ]);
        Setting::create([
            'key' => SettingEnum::invoice_info->value,
            'value' => 'text'
        ]);
    }
}
