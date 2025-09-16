<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AiChatSession extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'title',
        'context',
        'last_activity'
    ];

    protected $casts = [
        'context' => 'array',
        'last_activity' => 'datetime'
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->session_id)) {
                $model->session_id = Str::uuid()->toString();
            }
            $model->last_activity = now();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiChatMessage::class, 'session_id')->orderBy('sent_at');
    }

    public function updateActivity(): void
    {
        $this->update(['last_activity' => now()]);
    }

    public function generateTitle(): string
    {
        $firstMessage = $this->messages()->where('role', 'user')->first();
        if ($firstMessage) {
            return Str::limit($firstMessage->content, 50);
        }
        return 'Nouvelle conversation';
    }

    public function getRouteKeyName(): string
    {
        return 'session_id';
    }
}
