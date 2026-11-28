<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrustedDevice extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'center_id',
        'user_id',
        'device_uuid',
        'device_name',
        'device_type',
        'platform',
        'browser',
        'ip_address',
        'is_blocked',
        'last_used_at',
    ];

    protected $casts = [
        'is_blocked' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(DeviceSession::class, 'device_id');
    }
}
