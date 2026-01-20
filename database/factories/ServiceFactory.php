<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

class ServiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $arabic_faker = Faker::create('ar_JO');
        return [
            'rooms_no' => $this->faker->numberBetween(1, 10),
            'free_book' => $this->faker->numberBetween(10, 100),
            'max_time' => $this->faker->time(),
            'extra_time' => $this->faker->time(),
            'price' => $this->faker->numberBetween(111,999),
            'sort_order' => $this->faker->numberBetween(1, 10),
            'is_top' => $this->faker->boolean(),
            'has_commission' => $this->faker->boolean(),
            'ar' => [
                'name' => $arabic_faker->name()
            ],
            'en' => [
                'name' => $this->faker->name()
            ]
        ];
    }
}
