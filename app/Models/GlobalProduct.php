<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GlobalProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'central';
    protected $table = 'global_products';

    protected $fillable = [
        'center_id',
        'product_id',
        'name',
        'sort_order',
    ];

    protected $casts = [
        'center_id' => 'integer',
        'product_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }
}
