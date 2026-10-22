<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketReply extends Model
{
    protected $connection = 'central';
    protected $table = 'support_ticket_replies';

    protected $fillable = [
        'ticket_id',
        'sender_type',
        'sender_id',
        'sender_name',
        'body',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }
}
