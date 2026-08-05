<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SubUnit extends Model
{
    protected $fillable = [
        'name', 'description', 'href', 'is_external',
        'logo_path', 'fb_url', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_external' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<SubUnit>  $query
     * @return Builder<SubUnit>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
