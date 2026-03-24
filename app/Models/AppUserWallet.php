<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppUserWallet extends Model
{
    use CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    protected $connection = 'central';
    protected $table = 'users_wallets';

    protected $fillable = [
        'wallet_type',
        'amount',
        'invoiced_amount',
        'commission',
        'wallet_id',
        'user_id',
        'created_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'user_id');
    }

    public function app_wallet(): BelongsTo
    {
        return $this->belongsTo(AppWallet::class, 'wallet_id');
    }
}
