<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\PackageServicePaid;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageServicePaidFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PackageServicePaid::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'package_id' => Package::inRandomOrder()->first()->id,
            'service_id' => Service::inRandomOrder()->first()->id,
        ];
    }
}
