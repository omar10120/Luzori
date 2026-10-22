<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportMessage extends Model
{
    protected $connection = 'central';
    protected $table = 'support_messages';

    protected $fillable = [
        'conversation_id',
        'center_id',
        'sender_type',
        'sender_id',
        'sender_name',
        'sender_role',
        'body',
        'firebase_key',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(SupportConversation::class, 'conversation_id');
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function toChatArray(): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'center_id' => $this->center_id,
            'sender_type' => $this->sender_type,
            'sender_id' => $this->sender_id,
            'sender_name' => $this->sender_name,
            'sender_role' => $this->sender_role,
            'body' => $this->body,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'created_at_human' => optional($this->created_at)->diffForHumans(),
            'firebase_key' => $this->firebase_key,
        ];
    }
}
