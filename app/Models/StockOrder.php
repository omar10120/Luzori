<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOrder extends Model
{
    use HasFactory, CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    protected $fillable = [
        'order_number',
        'branch_id',
        'product_supplier_id',
        'deliver_from',
        'expected_at',
        'total_cost',
        'status',
        'created_by',
        'received_at',
    ];

    protected $casts = [
        'expected_at' => 'date',
        'received_at' => 'datetime',
        'total_cost' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(ProductSupplier::class, 'product_supplier_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CenterUser::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockOrderItem::class);
    }

    public static function nextOrderNumber(): string
    {
        $numbers = static::withTrashed()
            ->pluck('order_number')
            ->map(function ($number) {
                if (preg_match('/^P(\d+)$/i', (string) $number, $matches)) {
                    return (int) $matches[1];
                }
                return 0;
            });

        return 'P' . (($numbers->max() ?: 0) + 1);
    }
}
