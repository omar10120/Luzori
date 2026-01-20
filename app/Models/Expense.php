<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'supplier_id',
        'expense_name',
        'payee',
        'amount',
        'start_date',
        'end_date',
        'date',
        'notes',
        'receipt_image',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'date' => 'date',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the branch that owns the expense.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the supplier that owns the expense.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the receipt image URL.
     */
    public function getReceiptImageUrlAttribute(): string
    {
        if ($this->receipt_image) {
            return asset('storage/' . $this->receipt_image);
        }
        return asset('images/no-image.png');
    }
}
