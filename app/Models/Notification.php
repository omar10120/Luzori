<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use Translatable, CreatedAtTrait, UpdatedAtTrait;

    public $translatedAttributes = ['title', 'text'];
    protected $hidden = ['translations'];

    protected $table = 'notifications';
    protected $fillable = ['created_at'];

    public function users()
    {
        return $this->morphedByMany(User::class, 'notifiable')->withPivot('is_read');
    }
}
