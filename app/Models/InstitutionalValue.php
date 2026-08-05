<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InstitutionalValue extends Model
{
    protected $fillable = [
        'title', 'description', 'icon_name', 'glow_bg_class',
        'icon_container_class', 'accent_line_class',
        'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<InstitutionalValue>  $query
     * @return Builder<InstitutionalValue>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
