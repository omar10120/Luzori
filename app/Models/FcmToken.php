<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;

class FcmToken extends Model
{
    use CreatedAtTrait, UpdatedAtTrait;

    protected $table = 'fcm_tokens';
    protected $fillable = ['token'];

    public function tokenable()
    {
        return $this->morphTo();
    }
}
