<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $arabic_faker = Faker::create('ar_JO');
        return [
            'price' => $this->faker->numberBetween(111,999),
            'ar' => [
                'name' => $arabic_faker->name(),
                'text' => $arabic_faker->text()
            ],
            'en' => [
                'name' => $this->faker->name(),
                'text' => $this->faker->text()
            ]
        ];
    }
}
