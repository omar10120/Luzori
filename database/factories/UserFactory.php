<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'first_name' => fake()->word(),
            'last_name' => fake()->word(),
            'email' => fake()->safeEmail(),
            'country_code' => $this->faker->countryCode(),
            'phone' => $this->faker->phoneNumber(),
            'password' => bcrypt('p@$$word'),
        ];
    }
}
