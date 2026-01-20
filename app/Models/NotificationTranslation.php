<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = ['title', 'text'];
}
