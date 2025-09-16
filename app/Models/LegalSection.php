<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalSection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'number',
        'description',
        'content',
        'document_id',
        'parent_section_id',
        'sort_order'
    ];

    protected $casts = [
        'sort_order' => 'integer'
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class);
    }

    public function parentSection(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_section_id');
    }

    public function childrenSections(): HasMany
    {
        return $this->hasMany(self::class, 'parent_section_id')->orderBy('sort_order');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(LegalArticle::class, 'section_id')->orderBy('sort_order');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_section_id');
    }

    public function getFullNumber(): string
    {
        $numbers = collect();
        $section = $this;
        
        while ($section) {
            $numbers->prepend($section->number);
            $section = $section->parentSection;
        }
        
        return $numbers->filter()->implode(' - ');
    }
}
