<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthLog extends Model
{
    public $timestamps = false;

    protected $connection = 'central';

    protected $fillable = [
        'center_id',
        'user_id',
        'device_uuid',
        'action',
        'ip_address',
        'latitude',
        'longitude',
        'user_agent',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'created_at' => 'datetime',
    ];

    public function hasLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }
}
