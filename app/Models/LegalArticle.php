<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class LegalArticle extends Model
{
use SoftDeletes;

    protected $fillable = [
        'number',
        'title',
        'content',
        'commentary',
        'document_id',
        'section_id',
        'sort_order',
        'cross_references'
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'cross_references' => 'array'
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(LegalSection::class);
    }

    public function getFullReference(): string
    {
        return $this->document->reference_number . ' - Article ' . $this->number;
    }

    public function searchableAs(): string
    {
        return 'legal_articles_index';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'title' => $this->title,
            'content' => $this->content,
            'commentary' => $this->commentary,
            'document_title' => $this->document?->title,
            'document_type' => $this->document?->type,
            'section_title' => $this->section?->title
        ];
    }
}
