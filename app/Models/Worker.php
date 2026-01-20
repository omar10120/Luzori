<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\HasMediaTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;

class Worker extends Model implements HasMedia
{
    use HasFactory, CreatedAtTrait, UpdatedAtTrait, HasMediaTrait, SoftDeletes;

    protected $table = 'workers';
    protected $fillable = [
        'name',
        'email',
        'country_code',
        'phone',
        'has_commission',
        'branch_id',
        'shift_id',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(WorkerService::class);
    }

    public function vacations(): HasMany
    {
        return $this->hasMany(Vacation::class);
    }
}
