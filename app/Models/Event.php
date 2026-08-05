<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/** @property-read \Carbon\Carbon $event_date */
class Event extends Model
{
    protected $fillable = [
        'title', 'type', 'category', 'event_date', 'time', 'location', 'organizer',
        'description', 'image_path', 'fb_link', 'is_proyeccion_social', 'sort_order',
        'status', 'status_label', 'status_color',
    ];

    protected static function booted(): void
    {
        static::saving(function (Event $event) {
            $event->attributes['status'] = $event->status;
            $event->attributes['status_label'] = $event->status_label;
            $event->attributes['status_color'] = $event->status_color;
        });
    }

    /** @phpstan-ignore-next-line rules.modelAppends */
    protected $appends = ['status', 'status_label', 'status_color'];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'is_proyeccion_social' => 'boolean',
        ];
    }

    /**
     * Auto-calculate status from event_date.
     * - Pasados: event_date < today
     * - EnCurso: event_date == today
     * - Proximos: event_date > today
     */
    public function getStatusAttribute(): string
    {
        $date = $this->getRawOriginal('event_date');
        if (!$date) return 'Proximos';
        $today = Carbon::today();
        if ($this->event_date->lt($today)) return 'Pasados';
        if ($this->event_date->eq($today)) return 'EnCurso';
        return 'Proximos';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'Pasados' => 'Finalizado',
            'EnCurso' => 'En Curso',
            default   => 'Próximo',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'Pasados' => 'bg-neutral-500',
            'EnCurso' => 'bg-emerald-600',
            default   => 'bg-indigo-600',
        };
    }

    /**
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('event_date', 'desc');
    }

    /**
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopeProyeccionSocial(Builder $query): Builder
    {
        return $query->where('is_proyeccion_social', true);
    }
}
