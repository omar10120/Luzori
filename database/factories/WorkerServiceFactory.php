<?php

namespace Database\Factories;

use App\Models\WorkerService;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkerServiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WorkerService::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'service_id' => Service::inRandomOrder()->first()->id,
        ];
    }
}
