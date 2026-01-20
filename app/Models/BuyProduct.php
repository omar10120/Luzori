<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BuyProduct extends Model
{
    use CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    protected $table = 'buy_products';
    protected $fillable = [
        'payment_type',
        'commission',
        'discount',
        'sales_worker_id',
        'worker_id',
        'created_by',
        'sale_id',
    ];

    public function sales_worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class, 'sales_worker_id');
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
}
