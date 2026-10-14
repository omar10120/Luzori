<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use CreatedAtTrait, UpdatedAtTrait;

    public const TYPE_PRODUCT = 'product';
    public const TYPE_BOOKING = 'booking';

    protected $connection = 'central';
    protected $table = 'cart_items';

    protected $fillable = [
        'user_id',
        'center_id',
        'branch_id',
        'item_type',
        'item_id',
        'qty',
        'booking_number',
        'worker_id',
        'booking_date',
        'from_time',
        'to_time',
        'user_package_ids',
        'name',
        'sku',
        'image_url',
        'unit_price',
        'total_price',
        'request_payload',
        'dedupe_key',
    ];

    protected $casts = [
        'qty' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'user_package_ids' => 'array',
        'request_payload' => 'array',
        'booking_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(AppUser::class, 'user_id');
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class, 'center_id');
    }

    public function isProduct(): bool
    {
        return $this->item_type === self::TYPE_PRODUCT;
    }

    public static function productDedupeKey(int $userId, int $centerId, int $branchId, int $productId): string
    {
        return md5("{$userId}:{$centerId}:{$branchId}:product:{$productId}");
    }
}
