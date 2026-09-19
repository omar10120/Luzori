<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\HasMediaTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
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
        'is_default',
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

    public function fcmTokens(): MorphMany
    {
        return $this->morphMany(CentralFcmToken::class, 'tokenable');
    }

    public function appNotifications(): MorphToMany
    {
        return $this->morphToMany(AppNotification::class, 'notifiable', 'notifiables', 'notifiable_id', 'notification_id')
            ->withPivot('is_read', 'id')
            ->withTimestamps();
    }

    public function favoriteCenters()
    {
        return $this->belongsToMany(Center::class, 'favorite_centers', 'user_id', 'center_id')
            ->withTimestamps();
    }

    public function favorites()
    {
        return $this->hasMany(FavoriteCenter::class, 'user_id');
    }

    public function centerReviews()
    {
        return $this->hasMany(CenterReview::class, 'user_id');
    }
}
