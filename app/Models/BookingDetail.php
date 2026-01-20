<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingDetail extends Model
{
    use CreatedAtTrait, UpdatedAtTrait;

    protected $table = 'bookings_details';
    protected $fillable = [
        '_date',
        'from_time',
        'to_time',
        'commission',
        'commission_type',
        'is_free',
        'tip',
        'price',
        'user_id',
        'booking_id',
        'worker_id',
        'service_id',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }
}
