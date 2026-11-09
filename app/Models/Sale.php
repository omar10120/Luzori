<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    /** VAT is included in the sale total and equals 5% of that total. */
    public const VAT_RATE = 0.05;

    protected $table = 'sales';
    protected $fillable = [
        'subtotal',
        'tax',
        'tip',
        'total',
        'worker_id',
        'client_id',
        'branch_id',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'tip' => 'decimal:2',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function created_by_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function buyProducts(): HasMany
    {
        return $this->hasMany(BuyProduct::class);
    }

    /**
     * Split a VAT-inclusive amount.
     * VAT = 5% of the total, subtotal = total - VAT.
     *
     * @return array{subtotal: float, vat: float, inclusive: float}
     */
    public static function splitInclusiveVat(float $inclusive): array
    {
        $inclusive = round($inclusive, 2);
        $vat = round($inclusive * self::VAT_RATE, 2);

        return [
            'subtotal' => round($inclusive - $vat, 2),
            'vat' => $vat,
            'inclusive' => $inclusive,
        ];
    }

    /** Item prices before tip. Tip is excluded from the VAT base. */
    public function inclusiveAmount(): float
    {
        return round((float) $this->total - (float) ($this->tip ?? 0), 2);
    }

    /**
     * @return array{subtotal: float, vat: float, inclusive: float}
     */
    public function vatBreakdown(): array
    {
        return self::splitInclusiveVat($this->inclusiveAmount());
    }
}

