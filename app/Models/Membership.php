<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\HasMediaTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;

class Membership extends Model implements HasMedia
{
    use CreatedAtTrait, UpdatedAtTrait, HasMediaTrait, SoftDeletes;

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
