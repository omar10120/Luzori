<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\HasMediaTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements HasMedia
{
    use HasFactory,
        CreatedAtTrait,
        UpdatedAtTrait,
        HasMediaTrait,
        SoftDeletes;

    protected $appends = ['name', 'full_phone', 'image'];

    protected $table = 'users';
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'country_code',
        'phone',
        'wallet',
        'branch_id',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(ServiceUser::class);
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(UserWallet::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    protected function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    protected function getFullPhoneAttribute()
    {
        return $this->country_code . $this->phone;
    }
}
