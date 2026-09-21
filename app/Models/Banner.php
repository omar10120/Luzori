<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\HasMediaTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;

class Banner extends Model implements HasMedia
{
    use HasFactory, CreatedAtTrait, UpdatedAtTrait, HasMediaTrait, SoftDeletes;

    protected $connection = 'central';

    protected $fillable = [
        'title',
        'description',
        'placement_type',
        'link_type',
        'external_url',
        'center_id',
        'global_category_id',
        'service_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function globalCategory(): BelongsTo
    {
        return $this->belongsTo(GlobalCategory::class);
    }
}
