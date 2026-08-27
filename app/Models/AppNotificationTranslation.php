<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotificationTranslation extends Model
{
    protected $connection = 'central';
    protected $table = 'notification_translations';

    protected $fillable = ['title', 'text', 'locale', 'notification_id'];
}
