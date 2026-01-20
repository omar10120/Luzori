<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserUsedDiscount extends Model
{
    use CreatedAtTrait, UpdatedAtTrait;

    protected $table = 'users_used_discount';
    protected $fillable = [
        'code',
        'amount',
        'type',
        'user_id',
        'discountcode_id',
        'booking_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function discount_code(): BelongsTo
    {
        return $this->belongsTo(Discount::class, 'discountcode_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
