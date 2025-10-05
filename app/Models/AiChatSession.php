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
        'last_activity',
        'ip_address',
        'user_agent',
        'country',
        'country_name',
        'city',
        'device_type',
        'browser',
        'browser_version',
        'operating_system',
        'platform',
        'is_mobile',
        'is_tablet',
        'is_desktop',
        'language',
        'timezone',
        'screen_width',
        'screen_height'
    ];

    protected $casts = [
        'context' => 'array',
        'last_activity' => 'datetime',
        'is_mobile' => 'boolean',
        'is_tablet' => 'boolean',
        'is_desktop' => 'boolean',
        'screen_width' => 'integer',
        'screen_height' => 'integer'
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
