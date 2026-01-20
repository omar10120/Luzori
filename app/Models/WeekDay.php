<?php

namespace App\Models;

use App\Enums\DayEnum;
use App\Enums\DayStatusEnum;
use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;

class WeekDay extends Model
{
    use CreatedAtTrait, UpdatedAtTrait;

    protected $table = 'weeks_days';
    protected $fillable = [
        'day',
        'status',
    ];

    protected $casts = [
        'day' => DayEnum::class,
        'status' => DayStatusEnum::class
    ];
}
