<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserUsedCard extends Model
{
    use CreatedAtTrait, UpdatedAtTrait;

    protected $table = 'users_used_cards';
    protected $fillable = [
        'code',
        'amount',
        'user_id',
        'membershipcards_id',
        'booking_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function membership_card(): BelongsTo
    {
        return $this->belongsTo(Membership::class, 'membershipcards_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
