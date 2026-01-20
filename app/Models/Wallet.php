<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    protected $table = 'wallets';
    protected $fillable = [
        'code',
        'amount',
        'invoiced_amount',
        'used',
        'start_at',
        'end_at',
        'created_by'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(UserWallet::class);
    }
}
