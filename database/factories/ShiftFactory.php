<?php

namespace Database\Factories;

use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Shift::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'start_time' => $this->faker->time(),
            'end_time' => $this->faker->time(),
            'break_start' => $this->faker->time(),
            'break_end' => $this->faker->time(),
        ];
    }
}
