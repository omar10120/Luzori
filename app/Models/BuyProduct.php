<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class BuyProduct extends Model
{
    use CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    protected $table = 'buy_products';
    protected $fillable = [
        'payment_type',
        'commission',
        'commission_type',
        'discount',
        'sales_worker_id',
        'global_user_id',
        'worker_id',
        'created_by',
        'sale_id',
    ];

    public function sales_worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class, 'sales_worker_id');
    }
    public function saleItem(): MorphOne
    {
        return $this->morphOne(SaleItem::class, 'itemable');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(BuyProductDetail::class);
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function reportWorkerId(): ?int
    {
        return $this->worker_id ?: $this->sales_worker_id;
    }

    public function detailSalePrice($detail): float
    {
        $price = (float) ($detail->price ?? 0);
        if ($price <= 0) {
            $price = (float) ($detail->product?->retail_price ?: $detail->product?->supply_price ?: 0);
        }
        if (!empty($this->discount)) {
            $price -= ($price * (float) $this->discount) / 100;
        }

        return $price;
    }

    public function resolvedCommissionAmount(float $orderPrice): float
    {
        if ($this->commission === null || $this->commission === '') {
            return 0;
        }

        $type = $this->commission_type ?: 'percentage';

        return resolve_stored_commission($this->commission, $type, $orderPrice, true);
    }
}
