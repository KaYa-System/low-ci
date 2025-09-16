<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class LegalDocument extends Model
{
use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'type',
        'reference_number',
        'publication_date',
        'effective_date',
        'journal_officiel',
        'status',
        'category_id',
        'source_url',
        'metadata',
        'views_count',
        'is_featured'
    ];

    protected $casts = [
        'publication_date' => 'date',
        'effective_date' => 'date',
        'metadata' => 'array',
        'views_count' => 'integer',
        'is_featured' => 'boolean'
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }
        });

        static::updating(function (self $model) {
            if ($model->isDirty('title') && !$model->isDirty('slug')) {
                $model->slug = Str::slug($model->title);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LegalCategory::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(LegalSection::class, 'document_id')->orderBy('sort_order');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(LegalArticle::class, 'document_id')->orderBy('sort_order');
    }

    public function rootSections(): HasMany
    {
        return $this->sections()->whereNull('parent_section_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePublishedAfter($query, $date)
    {
        return $query->where('publication_date', '>=', $date);
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function searchableAs(): string
    {
        return 'legal_documents_index';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'summary' => $this->summary,
            'type' => $this->type,
            'reference_number' => $this->reference_number,
            'category' => $this->category?->name
        ];
    }
}
