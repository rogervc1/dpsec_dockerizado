<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Faq;
use App\Models\PageSection;
use Inertia\Inertia;
use Inertia\Response;

class ProyeccionSocialController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('public/ProyeccionSocial', [
            'events' => Event::proyeccionSocial()->active()->get(),
            'faqs' => Faq::forPage('proyeccion-social')->active()->get(),
            'sections' => PageSection::forPage('proyeccion-social')
                ->get()
                ->keyBy('section_key'),
        ]);
    }
}
