<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\HasMediaTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

class Setting extends Model implements HasMedia
{
    use HasMediaTrait, CreatedAtTrait, UpdatedAtTrait;

    protected $table = 'settings';
    protected $fillable = ['key', 'value'];
}
