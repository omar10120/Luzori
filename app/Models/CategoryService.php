<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryService extends Model
{
    use HasFactory, Translatable, CreatedAtTrait, UpdatedAtTrait, SoftDeletes;

    protected $table = 'categories_services';
    public $translatedAttributes = ['name', 'description', 'keywords'];

    protected $fillable = [
        'parent_id',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CategoryService::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CategoryService::class, 'parent_id');
    }
}

