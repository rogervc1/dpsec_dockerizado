<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventExclusiveDocument extends Model
{
    protected $fillable = [
        'event_id', 'title', 'description', 'original_name', 'file_path', 'mime_type', 'size_bytes', 'is_visible',
    ];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'size_bytes' => 'integer'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** @param Builder<EventExclusiveDocument> $query */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }
}
