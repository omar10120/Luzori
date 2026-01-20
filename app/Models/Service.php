<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\HasMediaTrait;
use App\Traits\UpdatedAtTrait;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;

class Service extends Model implements HasMedia
{
    use HasFactory, Translatable, CreatedAtTrait, UpdatedAtTrait, HasMediaTrait, SoftDeletes;

    protected $table = 'services';
    public $translatedAttributes = [
        'name',
        'description'
    ];

    protected $hidden = ['translations'];
    protected $fillable = [
        'rooms_no',
        'free_book',
        'max_time',
        'extra_time',
        'price',
        'sort_order',
        'is_top',
        'has_commission'
    ];

    protected $casts = [
        'is_top' => 'boolean',
        'has_commission' => 'boolean'
    ];
}
