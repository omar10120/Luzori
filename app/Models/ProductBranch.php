<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductBranch extends Model
{
    use HasFactory, CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    protected $table = 'product_branches';
    
    protected $fillable = [
        'product_id',
        'branch_id',
        'stock_quantity'
    ];

    protected $casts = [
        'stock_quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}

