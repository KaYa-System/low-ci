<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatMessage extends Model
{
    protected $fillable = [
        'session_id',
        'role',
        'content',
        'metadata',
        'sent_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime'
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->sent_at = now();
        });
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AiChatSession::class, 'session_id');
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function isAssistant(): bool
    {
        return $this->role === 'assistant';
    }

    public function getCitedDocuments(): array
    {
        return $this->metadata['cited_documents'] ?? [];
    }
}
