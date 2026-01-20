<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Membership extends Model
{
    use CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    protected $table = 'memberships_cards';
    protected $fillable = [
        'membership_no',
        'percent',
        'start_at',
        'end_at',
        'user_id',
        'created_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function created_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
