<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\HasMediaTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Permission\Traits\HasRoles;

class Center extends Authenticatable implements HasMedia
{
    use HasApiTokens,
        HasFactory,
        Notifiable,
        HasRoles,
        CreatedAtTrait,
        UpdatedAtTrait,
        HasMediaTrait,
        SoftDeletes;

    protected $table = 'centers';
    protected $connection = 'mysql'; // Always use main database for centers table
    protected $fillable = [
        'name',
        'domain',
        'database',
        'email',
        'country_code',
        'phone',
        'password',
        'currency'
    ];
    protected $hidden = ['password'];

    public function fcmTokens()
    {
        return $this->morphMany(FcmToken::class, 'tokenable');
    }

    public function notifications()
    {
        return $this->morphToMany(Notification::class, 'notifiable')->withPivot('is_read');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
}
