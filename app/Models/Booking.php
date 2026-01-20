<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    protected $table = 'bookings';
    protected $fillable = [
        'booking_date',
        'full_name',
        'mobile',
        'notes',
        'payment_type',
        'created_by',
        'wallet_id',
        'branch_id',
        'user_id',
        'sale_id'
    ];

    public function details(): HasMany
    {
        return $this->hasMany(BookingDetail::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function created_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
