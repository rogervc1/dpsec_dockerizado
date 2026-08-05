<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'page_slug', 'question', 'answer',
        'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<Faq>  $query
     * @return Builder<Faq>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * @param  Builder<Faq>  $query
     * @return Builder<Faq>
     */
    public function scopeForPage(Builder $query, string $pageSlug): Builder
    {
        return $query->where('page_slug', $pageSlug);
    }
}
