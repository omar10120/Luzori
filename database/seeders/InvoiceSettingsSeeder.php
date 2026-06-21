<?php

namespace Database\Seeders;

use App\Models\Invoice_Settings;
use Illuminate\Database\Seeder;

class InvoiceSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Invoice_Settings::create([
            'phone_number_1' => '959658778',
            'phone_number_2' => '959658778',
            'phone_number_3' => '959658778',
            'emirate' => 'Dubai',
            'tax_number' => '1234567890',
        ]);
    }
}
