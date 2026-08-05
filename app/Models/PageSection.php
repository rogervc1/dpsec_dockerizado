<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    protected $fillable = [
        'page_slug', 'section_key', 'eyebrow', 'title',
        'description', 'background_image', 'extra_data',
    ];

    protected function casts(): array
    {
        return [
            'extra_data' => 'array',
        ];
    }

    /**
     * @param  Builder<PageSection>  $query
     * @return Builder<PageSection>
     */
    public function scopeForPage(Builder $query, string $pageSlug): Builder
    {
        return $query->where('page_slug', $pageSlug);
    }
}
