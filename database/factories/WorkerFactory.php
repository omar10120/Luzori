<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Worker;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Worker::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'country_code' => $this->faker->countryCode(),
            'phone' => $this->faker->phoneNumber(),
            'has_commission' => $this->faker->boolean(),
            'branch_id' => Branch::inRandomOrder()->first()->id,
            'shift_id' => Shift::inRandomOrder()->first()->id,
        ];
    }
}
