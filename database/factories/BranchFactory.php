<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

class BranchFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Branch::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $arabic_faker = Faker::create('ar_JO');
        return [
            'longitude' => $this->faker->longitude(),
            'latitude' => $this->faker->latitude(),
            'ar' => [
                'name' => $arabic_faker->name(),
                'city' => $arabic_faker->city(),
                'address' => $arabic_faker->address()
            ],
            'en' => [
                'name' => $this->faker->name(),
                'city' => $this->faker->city(),
                'address' => $this->faker->address()
            ]
        ];
    }
}
