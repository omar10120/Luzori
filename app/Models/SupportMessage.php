<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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
        'attachment_type',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'attachment_size',
        'firebase_key',
        'seen_at',
    ];

    protected $casts = [
        'seen_at' => 'datetime',
        'attachment_size' => 'integer',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(SupportConversation::class, 'conversation_id');
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function attachmentUrl(): ?string
    {
        if (!$this->attachment_path) {
            return null;
        }

        return Storage::disk('public')->url($this->attachment_path);
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
            'attachment_type' => $this->attachment_type,
            'attachment_url' => $this->attachmentUrl(),
            'attachment_name' => $this->attachment_name,
            'attachment_mime' => $this->attachment_mime,
            'attachment_size' => $this->attachment_size,
            'seen_at' => optional($this->seen_at)->toIso8601String(),
            'is_seen' => !empty($this->seen_at),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'created_at_human' => optional($this->created_at)->diffForHumans(),
            'firebase_key' => $this->firebase_key,
        ];
    }
}
