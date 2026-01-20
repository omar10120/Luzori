<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Payment methods that can be used for both booking and products
        PaymentMethod::create([
            'name' => 'service_cash',
            'types' => [PaymentMethod::TYPE_BOOKING, PaymentMethod::TYPE_PRODUCT]
        ]);
        
        PaymentMethod::create([
            'name' => 'service_visa',
            'types' => [PaymentMethod::TYPE_BOOKING, PaymentMethod::TYPE_PRODUCT]
        ]);
        
        PaymentMethod::create([
            'name' => 'sales_cash',
            'types' => [PaymentMethod::TYPE_BOOKING, PaymentMethod::TYPE_PRODUCT]
        ]);
        
        PaymentMethod::create([
            'name' => 'sales_visa',
            'types' => [PaymentMethod::TYPE_BOOKING, PaymentMethod::TYPE_PRODUCT]
        ]);
        
        PaymentMethod::create([
            'name' => 'sales_cash_cp',
            'types' => [PaymentMethod::TYPE_BOOKING, PaymentMethod::TYPE_PRODUCT]
        ]);
        
        PaymentMethod::create([
            'name' => 'sales_visa_cp',
            'types' => [PaymentMethod::TYPE_BOOKING, PaymentMethod::TYPE_PRODUCT]
        ]);
        
        // Booking-only payment methods
        PaymentMethod::create([
            'name' => 'transfer_bank',
            'types' => [PaymentMethod::TYPE_BOOKING]
        ]);
        
        PaymentMethod::create([
            'name' => 'free',
            'types' => [PaymentMethod::TYPE_BOOKING]
        ]);
        
        PaymentMethod::create([
            'name' => 'tips_visa',
            'types' => [PaymentMethod::TYPE_BOOKING]
        ]);
        PaymentMethod::create([
            'name' => 'tips',
            'types' => [PaymentMethod::TYPE_TIPS]
        ]);
        
        
        // Wallet payment methods
        PaymentMethod::create([
            'name' => 'wallet',
            'types' => [PaymentMethod::TYPE_WALLET]
        ]);
        
        // General payment method (can be used anywhere)
        PaymentMethod::create([
            'name' => 'general_cash',
            'types' => [PaymentMethod::TYPE_GENERAL]
        ]);
    }
}

