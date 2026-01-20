<?php

namespace App\Models;

use App\Enums\PageEnum;
use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use Translatable, CreatedAtTrait, UpdatedAtTrait;

    public $translatedAttributes = ['value'];
    protected $hidden = ['translations'];

    protected $table = 'pages';
    protected $fillable = ['type'];

    protected $casts = [
        'type' => PageEnum::class,
    ];
}
