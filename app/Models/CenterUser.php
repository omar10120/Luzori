<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\HasMediaTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Permission\Traits\HasRoles;

class CenterUser extends Authenticatable implements HasMedia
{
    use HasApiTokens,
        HasFactory,
        Notifiable,
        HasRoles,
        CreatedAtTrait,
        UpdatedAtTrait,
        HasMediaTrait,
        SoftDeletes;

    protected $table = 'center_users';
    protected $fillable = [
        'name',
        'email',
        'country_code',
        'phone',
        'password',
        'currency',
        'statusWeb',
        'language_id',
        'branch_id',
    ];
    protected $hidden = ['password'];

    public function fcmTokens(): MorphMany
    {
        return $this->morphMany(FcmToken::class, 'tokenable');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function notifications(): MorphToMany
    {
        return $this->morphToMany(Notification::class, 'notifiable')->withPivot('is_read');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
}
