<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    protected $table = 'wallets';
    protected $fillable = [
        'code',
        'amount',
        'invoiced_amount',
        'used',
        'start_at',
        'end_at',
        'created_by'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(UserWallet::class);
    }

    public function created_by_user(): BelongsTo
    {
        return $this->belongsTo(CenterUser::class, 'created_by');
    }

    public function scopeForCenterUserBranch(Builder $query, ?CenterUser $centerUser = null): Builder
    {
        $centerUser = $centerUser ?: auth('center_user')->user();
        $branchId = $centerUser?->branch_id;

        if ($branchId === null) {
            return $query;
        }

        return $query->whereHas('created_by_user', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        });
    }
}
