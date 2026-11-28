<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceOtpCode extends Model
{
    public $timestamps = false;

    protected $connection = 'central';

    protected $fillable = [
        'center_id',
        'user_id',
        'device_uuid',
        'code_hash',
        'expires_at',
        'used_at',
        'created_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}
