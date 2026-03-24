<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppWallet extends Model
{
    use CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    protected $connection = 'central';
    protected $table = 'wallets';

    protected $fillable = [
        'code',
        'amount',
        'invoiced_amount',
        'used',
        'status',
        'start_at',
        'end_at',
        'created_by',
    ];
}
