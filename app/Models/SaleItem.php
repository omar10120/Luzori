<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SaleItem extends Model
{
    use CreatedAtTrait, UpdatedAtTrait;

    protected $table = 'sale_items';
    protected $fillable = [
        'sale_id',
        'item_type',
        'itemable_id',
        'itemable_type',
        'quantity',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }
}

