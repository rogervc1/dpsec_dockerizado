<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CertificateFont;
use App\Models\CertificateTemplate;
use App\Models\Document;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $templateId = $request->input('template_id');

        $certificatesQuery = Certificate::with('template')
            ->when($templateId, function ($query) use ($templateId) {
                $query->where('certificate_template_id', $templateId);
            })
            ->latest();

        return Inertia::render('Dashboard', [
            'events'    => Event::orderBy('sort_order')->orderBy('event_date', 'desc')->get(),
            'documents' => Document::orderBy('sort_order')->orderBy('published_date', 'desc')->get(),
            'stats' => [
                'totalEvents'    => Event::count(),
                'totalDocs'      => Document::count(),
                'totalProyeccionSocial' => Event::where('is_proyeccion_social', true)->count(),
                'totalCertificates' => Certificate::count(),
                'totalTemplates' => CertificateTemplate::count(),
            ],
            'templates' => CertificateTemplate::withCount('certificates')->latest()->get(),
            'fonts' => CertificateFont::all(),
            'certificates' => $certificatesQuery->paginate(15)->withQueryString(),
            'activeTab' => $request->input('activeTab', 'overview'),
        ]);
    }
}
