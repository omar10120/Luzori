<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSku extends Model
{
    use HasFactory, CreatedAtTrait, UpdatedAtTrait;

    protected $table = 'product_skus';
    
    protected $fillable = [
        'product_id',
        'sku',
        'type',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

