<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GlobalCategory extends Model
{
    use HasFactory, CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    protected $connection = 'central';
    protected $table = 'global_categories';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function centers(): BelongsToMany
    {
        return $this->belongsToMany(Center::class, 'center_global_category');
    }
}
