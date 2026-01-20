<?php

namespace Database\Seeders;

use App\Enums\DayEnum;
use App\Enums\DayStatusEnum;
use App\Models\WeekDay;
use Illuminate\Database\Seeder;

class WeekDaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WeekDay::create([
            'day' => DayEnum::Sunday->value,
            'status' => DayStatusEnum::OPEN->value,
        ]);
        WeekDay::create([
            'day' => DayEnum::Monday->value,
            'status' => DayStatusEnum::OPEN->value,
        ]);
        WeekDay::create([
            'day' => DayEnum::Tuesday->value,
            'status' => DayStatusEnum::OPEN->value,
        ]);
        WeekDay::create([
            'day' => DayEnum::Wednesday->value,
            'status' => DayStatusEnum::OPEN->value,
        ]);
        WeekDay::create([
            'day' => DayEnum::Thursday->value,
            'status' => DayStatusEnum::OPEN->value,
        ]);
        WeekDay::create([
            'day' => DayEnum::Friday->value,
            'status' => DayStatusEnum::CLOSED->value,
        ]);
        WeekDay::create([
            'day' => DayEnum::Saturday->value,
            'status' => DayStatusEnum::CLOSED->value,
        ]);
    }
}
