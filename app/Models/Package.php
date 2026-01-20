<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use Translatable, HasFactory, CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    protected $table = 'packages';
    public $translatedAttributes = [
        'name'
    ];
    protected $hidden = ['translations'];
    protected $fillable = [
        'created_by'
    ];

    public function packageServicePaid(): HasMany
    {
        return $this->hasMany(PackageServicePaid::class);
    }

    public function packageServiceFree(): HasMany
    {
        return $this->hasMany(PackageServiceFree::class);
    }
}
