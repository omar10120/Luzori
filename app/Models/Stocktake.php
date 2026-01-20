<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stocktake extends Model
{
    use HasFactory, CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
        'started_at',
        'completed_at',
        'started_by',
        'reviewed_by',
        'notes'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(CenterUser::class, 'started_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(CenterUser::class, 'reviewed_by');
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'stocktake_branches');
    }

    public function stocktakeBranches(): HasMany
    {
        return $this->hasMany(StocktakeBranch::class);
    }

    public function stocktakeProducts(): HasMany
    {
        return $this->hasMany(StocktakeProduct::class);
    }
}
