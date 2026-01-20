<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;

class Info extends Model
{
    use CreatedAtTrait, UpdatedAtTrait;

    protected $table = 'infos';
    protected $fillable = [
		'email',
		'phone',
		'facebook',
		'linkedin',
		'instagram',
		'twitter',
		'whatsapp',
		'youtube'
	];
}
