<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\HasMediaTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;

class AppUser extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, CreatedAtTrait, UpdatedAtTrait, HasMediaTrait, SoftDeletes;

    protected $connection = 'central';
    protected $table = 'users';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'firebase_uid',
        'provider',
        'country_code',
        'phone',
        'password',
        'wallet',
        'is_active',
        'image',
        'address',
        'birth',
        'gender',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['name', 'full_phone'];

    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getFullPhoneAttribute()
    {
        return $this->country_code . '-' . $this->phone;
    }

    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    public function app_wallets()
    {
        return $this->hasMany(AppUserWallet::class, 'user_id');
    }

    public function used_wallets()
    {
        return $this->hasMany(AppUserUsedWallet::class, 'user_id');
    }
}
