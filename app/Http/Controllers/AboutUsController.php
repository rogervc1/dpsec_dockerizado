<?php

namespace App\Http\Controllers;

use App\Models\InstitutionalValue;
use App\Models\Objective;
use App\Models\PageSection;
use App\Models\TeamMember;
use Inertia\Inertia;
use Inertia\Response;

class AboutUsController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('public/AboutUs', [
            'team' => TeamMember::active()->get(),
            'objectives' => Objective::active()->get(),
            'values' => InstitutionalValue::active()->get(),
            'sections' => PageSection::forPage('nosotros')
                ->get()
                ->keyBy('section_key'),
        ]);
    }
}
