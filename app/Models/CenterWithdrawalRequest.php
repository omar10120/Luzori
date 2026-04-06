<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CenterWithdrawalRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'central';
    protected $table = 'center_withdrawal_requests';

    protected $fillable = [
        'center_id',
        'amount',
        'status',
        'admin_notes',
    ];

    public function center()
    {
        return $this->belongsTo(Center::class, 'center_id');
    }
}
