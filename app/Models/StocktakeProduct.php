<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StocktakeProduct extends Model
{
    use HasFactory, CreatedAtTrait, UpdatedAtTrait;

    protected $fillable = [
        'stocktake_id',
        'product_id',
        'branch_id',
        'expected_qty',
        'counted_qty',
        'difference',
        'cost',
        'counted_by'
    ];

    protected $casts = [
        'expected_qty' => 'integer',
        'counted_qty' => 'integer',
        'difference' => 'integer',
        'cost' => 'decimal:2',
    ];

    public function stocktake(): BelongsTo
    {
        return $this->belongsTo(Stocktake::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function countedBy(): BelongsTo
    {
        return $this->belongsTo(CenterUser::class, 'counted_by');
    }
}
