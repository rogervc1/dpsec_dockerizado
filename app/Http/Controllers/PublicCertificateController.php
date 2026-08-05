<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificateFont;
use App\Models\CertificateTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class PublicCertificateController extends Controller
{
    /**
     * Display the public certificates search page.
     */
    public function index(): InertiaResponse
    {
        return Inertia::render('public/Certificados');
    }

    /**
     * Search for certificates by DNI/Document.
     */
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'document' => 'required|string|max:50',
        ]);

        $certificates = Certificate::with('template')
            ->where('recipient_document', trim($request->document))
            ->get();

        return response()->json([
            'certificates' => $certificates,
        ]);
    }

    /**
     * Verify a certificate by its unique UUID or human code.
     */
    public function verify(string $identifier): InertiaResponse
    {
        $certificate = Certificate::with('template')
            ->where('uuid', $identifier)
            ->orWhere('code', $identifier)
            ->firstOrFail();

        return Inertia::render('public/VerificarCertificado', [
            'certificate' => $certificate,
        ]);
    }

    /**
     * Generate and download the certificate PDF on-the-fly.
     */
    public function download(Certificate $certificate): Response
    {
        // Ensure storage/fonts directory exists for DomPDF font cache & metrics
        if (!file_exists(storage_path('fonts'))) {
            mkdir(storage_path('fonts'), 0777, true);
        }

        $template = $certificate->template;
        if (!$template) {
            abort(404, 'Plantilla de certificado no encontrada.');
        }
        $settings = $template->settings;

        // Fetch all custom fonts to inject @font-face rules
        $fonts = CertificateFont::all();

        // 2. Prepare CSS styles for @font-face
        $fontStyles = '';
        foreach ($fonts as $font) {
            // Convert storage public URL to local path and normalize slashes for DomPDF on Windows
            $fontPath = str_replace('\\', '/', public_path(str_replace('/storage/', 'storage/', $font->file_path)));
            if (file_exists($fontPath)) {
                $fontStyles .= "
                @font-face {
                    font-family: '{$font->font_name}';
                    src: url('{$fontPath}') format('truetype');
                    font-weight: normal;
                    font-style: normal;
                }
                @font-face {
                    font-family: '{$font->font_name}';
                    src: url('{$fontPath}') format('truetype');
                    font-weight: bold;
                    font-style: normal;
                }";
            }
        }

        // Get local path of template background and normalize slashes for DomPDF on Windows
        $bgPath = str_replace('\\', '/', public_path(str_replace('/storage/', 'storage/', $template->background_path)));
        if (!file_exists($bgPath)) {
            $bgPath = asset($template->background_path); // Fallback to URL
        }

        // 3. Build HTML Template with exact custom layouts
        $html = view('pdf.certificate', [
            'certificate' => $certificate,
            'template' => $template,
            'settings' => $settings,
            'fontStyles' => $fontStyles,
            'bgPath' => $bgPath,
        ])->render();

        // 4. Configure DomPDF and generate
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'landscape'); // Standard certificate format
        $pdf->setWarnings(false);

        // Output PDF to browser
        $filename = 'certificado-' . str_replace(' ', '-', strtolower($certificate->recipient_name)) . '.pdf';
        return $pdf->download($filename);
    }
}
