<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\PageSection;
use Inertia\Inertia;

class DocumentosController extends Controller
{
    public function __invoke(): mixed
    {
        return Inertia::render('public/Documentos', [
            'documents' => Document::active()->get(),
            'sections' => PageSection::forPage('documentos')
                ->get()
                ->keyBy('section_key'),
        ]);
    }
}
