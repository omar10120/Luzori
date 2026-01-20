<?php

namespace Database\Factories;

use App\Helpers\MyHelper;
use App\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Discount::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => MyHelper::generateCode(10),
            'type' => $this->faker->numberBetween(1, 2),
            'amount' => $this->faker->numberBetween(1, 100),
            'start_at' => $this->faker->date(),
            'end_at' => $this->faker->date(),
            'using_type' => $this->faker->numberBetween(1, 2),
        ];
    }
}
