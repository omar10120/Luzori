<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;

class CentralFcmToken extends Model
{
    use CreatedAtTrait, UpdatedAtTrait;

    protected $connection = 'central';
    protected $table = 'fcm_tokens';
    protected $fillable = ['token'];

    public function tokenable()
    {
        return $this->morphTo();
    }
}
