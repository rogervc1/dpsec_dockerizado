<?php

namespace App\Http\Controllers;

use App\Models\PageSection;
use Inertia\Inertia;

class SeguimientoGraduadoController extends Controller
{
    public function __invoke(): mixed
    {
        return Inertia::render('public/SeguimientoGraduado', [
            'sections' => PageSection::forPage('seguimiento-graduado')
                ->get()
                ->keyBy('section_key'),
        ]);
    }
}
