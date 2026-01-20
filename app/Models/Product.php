<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\HasMediaTrait;
use App\Traits\UpdatedAtTrait;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;

class Product extends Model implements HasMedia
{
    use Translatable, HasFactory, CreatedAtTrait, UpdatedAtTrait, HasMediaTrait, SoftDeletes;

    protected $table = 'products';
    public $translatedAttributes = [
        'name',
        'text'
    ];
    protected $hidden = ['translations'];
    protected $fillable = [
        'barcode',
        'brand_id',
        'category_id',
        'measure_unit',
        'measure_amount',
        'short_description',
        'supply_price',
        'retail_price',
        'markup',
        'allow_retail_sales',
        'track_stock'
    ];

    protected $casts = [
        'allow_retail_sales' => 'boolean',
        'track_stock' => 'boolean',
        'measure_amount' => 'decimal:2',
        'supply_price' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'markup' => 'decimal:2',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function productSuppliers(): BelongsToMany
    {
        return $this->belongsToMany(ProductSupplier::class, 'product_product_supplier');
    }

    public function skus(): HasMany
    {
        return $this->hasMany(ProductSku::class)->orderBy('order');
    }

    public function primarySku()
    {
        return $this->hasOne(ProductSku::class)->where('type', 'primary');
    }

    public function productBranches(): HasMany
    {
        return $this->hasMany(ProductBranch::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'product_branches')
            ->withPivot('stock_quantity')
            ->withTimestamps();
    }
}
