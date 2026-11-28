<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceSession extends Model
{
    public $timestamps = false;

    protected $connection = 'central';

    protected $fillable = [
        'center_id',
        'user_id',
        'device_id',
        'ip_address',
        'started_at',
        'ended_at',
        'is_active',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(TrustedDevice::class, 'device_id');
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }
}
