<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Certificate extends Model
{
    protected $fillable = [
        'certificate_template_id',
        'uuid',
        'code',
        'recipient_name',
        'recipient_document',
        'recipient_email',
        'role',
        'issue_date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($certificate) {
            if (empty($certificate->uuid)) {
                $certificate->uuid = (string) Str::uuid();
            }
            if (empty($certificate->code)) {
                // Generate a code e.g. DPSEC-2026-XXXXXX
                $certificate->code = 'DPSEC-' . date('Y') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    /**
     * @return BelongsTo<CertificateTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'certificate_template_id');
    }
}
