<?php

namespace App\Models;

use App\Traits\CreatedAtTrait;
use App\Traits\UpdatedAtTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vacation extends Model
{
    use CreatedAtTrait, UpdatedAtTrait;

    protected $table = 'vacations';
    protected $fillable = [
        'worker_id',
        'day',
        'describe',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }
}
