<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use Translatable, HasFactory, CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    protected $table = 'branches';
    public $translatedAttributes = [
        'name',
        'city',
        'address'
    ];
    protected $hidden = ['translations'];
    protected $fillable = [
        'longitude',
        'latitude'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_branches')
            ->withPivot('stock_quantity')
            ->withTimestamps();
    }

    public function productBranches(): HasMany
    {
        return $this->hasMany(ProductBranch::class);
    }
}
