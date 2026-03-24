<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppPaymentMethod extends Model
{
    use CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    protected $connection = 'central';
    protected $table = 'payment_methods';

    protected $fillable = [
        'name',
        'types', // Array of types
    ];

    protected $casts = [
        'types' => 'array',
    ];
}
