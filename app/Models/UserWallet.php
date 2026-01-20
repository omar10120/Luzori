<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWallet extends Model
{
    use CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    protected $table = 'users_wallets';
    protected $fillable = [
        'wallet_type',
        'amount',
        'invoiced_amount',
        'commission',
        'wallet_id',
        'user_id',
        'worker_id',
        'branch_id',
        'created_by',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
