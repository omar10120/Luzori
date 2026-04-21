<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserPackage extends Model
{
    use HasFactory, CreatedAtTrait, UpdatedAtTrait;

    protected $table = 'users_packages';
    
    protected $fillable = [
        'user_id',
        'package_id',
        'price',
        'status',
        'package_type',
        'created_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
    

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function translation(): HasMany
    {
        return $this->hasMany(PackageTranslation::class, 'package_id');
    }
}
