<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppUserUsedWallet extends Model
{
    use CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    protected $connection = 'central';
    protected $table = 'users_used_wallet';

    protected $fillable = [
        'amount',
        'user_id',
        'wallet_id',
        'booking_id', // Note: Booking lies in the tenant DB, but we record ID here
        'center_id',  // Note: To identify which center the booking happened in
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
