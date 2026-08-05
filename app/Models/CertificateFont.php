<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateFont extends Model
{
    protected $fillable = [
        'font_name',
        'file_path',
    ];
}
