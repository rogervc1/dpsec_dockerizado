<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Event;
use App\Models\PageSection;
use App\Models\Statistic;
use App\Models\SubUnit;
use App\Models\Video;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('public/Home', [
            'events' => Event::orderBy('sort_order')->orderBy('event_date', 'desc')->take(6)->get(),
            'documents' => Document::active()->take(3)->get(),
            'stats' => Statistic::active()->get(),
            'videos' => Video::active()->get(),
            'subUnits' => SubUnit::active()->get(),
            'sections' => PageSection::forPage('home')
                ->get()
                ->keyBy('section_key'),
        ]);
    }
}
