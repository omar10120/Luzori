<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MainStock extends Model
{
    protected $fillable = [
        'product_id',
        'sku_id',
        'quantity',
        'reserved_quantity',
        'reorder_level',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'reorder_level' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(ProductSku::class, 'sku_id');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->whereColumn('quantity', '>', 'reserved_quantity');
    }

    public function getAvailableQuantityAttribute(): int
    {
        return max(0, (int) $this->quantity - (int) $this->reserved_quantity);
    }
}
