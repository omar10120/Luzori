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
        'created_by',
        'price',
    ];

    public function packageServicePaid(): HasMany
    {
        return $this->hasMany(PackageServicePaid::class);
    }

    public function packageServiceFree(): HasMany
    {
        return $this->hasMany(PackageServiceFree::class);
    }

    public function usersPackages(): HasMany
    {
        return $this->hasMany(UserPackage::class);
    }

    /**
     * Get the total market value of all services in the package.
     */
    public function getTotalValueAttribute()
    {
        $paidSum = $this->packageServicePaid->sum(function($item) {
            return $item->service->price ?? 0;
        });

        $freeSum = $this->packageServiceFree->sum(function($item) {
            return $item->service->price ?? 0;
        });

        return $paidSum + $freeSum;
    }
}
