<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use HasFactory, CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    protected $table = 'discounts';
    protected $fillable = [
        'code',
        'type',
        'amount',
        'start_at',
        'end_at',
        'using_type',
        'benefit_numbers',
    ];
}
