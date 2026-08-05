<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Document extends Model
{
    protected $fillable = [
        'title', 'code', 'category', 'published_date',
        'file_size', 'description', 'file_path', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'published_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<Document>  $query
     * @return Builder<Document>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
