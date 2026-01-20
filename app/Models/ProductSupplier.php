<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSupplier extends Model
{
    use HasFactory, CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    protected $table = 'product_suppliers';
    
    protected $fillable = [
        'name',
        'email',
        'phone',
        'description',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_suppliers');
    }
}

