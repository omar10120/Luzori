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
        'user_agent',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }
}
