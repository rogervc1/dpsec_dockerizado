<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistrationSubmission extends Model
{
    protected $fillable = ['event_id', 'form_id', 'answers', 'attachments'];

    protected function casts(): array
    {
        return ['answers' => 'array', 'attachments' => 'array'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
