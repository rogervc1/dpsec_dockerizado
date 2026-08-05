<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\PageSection;
use Inertia\Inertia;
use Inertia\Response;

class EventosController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('public/Eventos', [
            'events' => Event::active()->get(),
            'sections' => PageSection::forPage('eventos')
                ->get()
                ->keyBy('section_key'),
        ]);
    }
}
