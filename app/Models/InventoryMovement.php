<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $table = 'products_inventory_movements';

    protected $fillable = [
        'product_id',
        'branch_id',
        'sku_id',
        'quantity',
        'movement_type',
        'reference_id',
        'reference_type',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_STOCK_ORDER = 'stock_order';
    public const TYPE_SALE = 'sale';
    public const TYPE_SALE_DELETED = 'sale_deleted';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_TRANSFER_OUT = 'transfer_out';
    public const TYPE_TRANSFER_IN = 'transfer_in';
    public const TYPE_INITIAL = 'initial';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(ProductSku::class, 'sku_id');
    }
}
