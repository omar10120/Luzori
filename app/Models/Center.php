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
    protected $connection = 'central'; // Always use main database for centers table
    protected $fillable = [
        'name',
        'domain',
        'database',
        'email',
        'country_code',
        'phone',
        'password',
        'currency',
        'status',
        'reject_reason',
        'rate',
        'is_setup',
        'wallet',
        'bank_name',
        'admin_discount',
        'expire_date'
    ];
    protected $hidden = ['password'];

    protected $casts = [
        'wallet' => 'decimal:2',
        'admin_discount' => 'decimal:2',
        'is_setup' => 'boolean',
        'expire_date' => 'datetime'
    ];

    /**
     * Increment center wallet balance
     */
    public function incrementWallet(float $amount)
    {
        if ($amount <= 0) {
            return;
        }

        $this->update([
            'wallet' => ($this->wallet ?? 0) + $amount
        ]);
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(CenterWithdrawalRequest::class, 'center_id');
    }

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
        if (!empty($value)) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    public function globalCategories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(GlobalCategory::class, 'center_global_category');
    }
}
