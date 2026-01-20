<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyProductDetail extends Model
{
    use CreatedAtTrait, UpdatedAtTrait;

    protected $table = 'buy_products_details';
    protected $fillable = [
        'price',
        'buy_product_id',
        'product_id',
    ];

    public function buy_product(): BelongsTo
    {
        return $this->belongsTo(BuyProduct::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
