<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificateFont;
use App\Models\CertificateTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;

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
     * Search for certificates by document after a CAPTCHA challenge.
     */
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'document' => 'required|string|max:50',
            'g-recaptcha-response' => 'required|string',
        ]);

        $captchaResponse = Http::asForm()->timeout(5)->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret'),
            'response' => $request->string('g-recaptcha-response')->toString(),
            'remoteip' => $request->ip(),
        ]);

        if (! $captchaResponse->successful() || ! $captchaResponse->json('success')) {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => 'Completa correctamente el CAPTCHA.',
            ]);
        }

        $certificates = Certificate::with('template')
            ->where('recipient_document', trim($request->document))
            ->get();

        return response()->json([
            'certificates' => $certificates->map(fn (Certificate $certificate) => [
                'uuid' => $certificate->uuid,
                'code' => $certificate->code,
                'recipient_name' => $certificate->recipient_name,
                'role' => $certificate->role,
                'issue_date' => $certificate->issue_date,
                'template' => $certificate->template ? ['name' => $certificate->template->name] : null,
            ])->values(),
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
            'certificate' => [
                'uuid' => $certificate->uuid,
                'code' => $certificate->code,
                'recipient_name' => $certificate->recipient_name,
                'role' => $certificate->role,
                'issue_date' => $certificate->issue_date,
                'template' => $certificate->template ? ['name' => $certificate->template->name] : null,
            ],
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

        // Fetch font families referenced by this template settings
        $usedFontFamilies = array_filter([
            $settings['name_field']['font_family'] ?? null,
            $settings['role_field']['font_family'] ?? null,
        ]);

        // 2. Prepare CSS styles for @font-face using direct storage path and Base64 embedding
        $fontStyles = '';
        if (!empty($usedFontFamilies)) {
            $fonts = CertificateFont::whereIn('font_name', $usedFontFamilies)->get();
            foreach ($fonts as $font) {
                // Determine direct physical path in storage/app/public/
                $relativePath = str_replace('/storage/', '', $font->file_path);
                $fontPath = storage_path('app/public/' . $relativePath);

                if (!file_exists($fontPath)) {
                    $fontPath = public_path('storage/' . $relativePath);
                }

                if (file_exists($fontPath)) {
                    $base64 = base64_encode(file_get_contents($fontPath));
                    $fontStyles .= "
                    @font-face {
                        font-family: '{$font->font_name}';
                        src: url('data:font/truetype;charset=utf-8;base64,{$base64}') format('truetype');
                        font-weight: normal;
                        font-style: normal;
                    }
                    @font-face {
                        font-family: '{$font->font_name}';
                        src: url('data:font/truetype;charset=utf-8;base64,{$base64}') format('truetype');
                        font-weight: bold;
                        font-style: normal;
                    }";
                }
            }
        }

        // Get local path of template background
        $bgRelative = str_replace('/storage/', '', $template->background_path);
        $bgPath = storage_path('app/public/' . $bgRelative);
        if (!file_exists($bgPath)) {
            $bgPath = public_path('storage/' . $bgRelative);
        }
        if (!file_exists($bgPath)) {
            $bgPath = asset($template->background_path);
        }

        // 3. Build HTML Template with exact custom layouts
        $html = view('pdf.certificate', [
            'certificate' => $certificate,
            'template' => $template,
            'settings' => $settings,
            'fontStyles' => $fontStyles,
            'bgPath' => $bgPath,
            'verificationUrl' => route('certificates.verify', ['identifier' => $certificate->code]),
            'qrDataUri' => $this->verificationQrDataUri($certificate),
        ])->render();

        // 4. Configure DomPDF and generate
        try {
            $pdf = Pdf::loadHTML($html);
            $dompdf = $pdf->getDomPDF();
            $dompdf->set_option('isRemoteEnabled', true);
            $dompdf->set_option('isFontSubsettingEnabled', false); // Prevents AdobeFontMetrics 'tree' error on custom fonts
            $pdf->setPaper('A4', 'landscape');
            $pdf->setWarnings(false);

            $filename = 'certificado-' . str_replace(' ', '-', strtolower($certificate->recipient_name)) . '.pdf';
            return $pdf->download($filename);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Certificate PDF font parsing failed, using fallback font: ' . $e->getMessage());

            $fallbackHtml = view('pdf.certificate', [
                'certificate' => $certificate,
                'template' => $template,
                'settings' => $settings,
                'fontStyles' => '',
                'bgPath' => $bgPath,
                'verificationUrl' => route('certificates.verify', ['identifier' => $certificate->code]),
                'qrDataUri' => $this->verificationQrDataUri($certificate),
            ])->render();

            $pdf = Pdf::loadHTML($fallbackHtml);
            $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
            $pdf->setPaper('A4', 'landscape');
            $pdf->setWarnings(false);

            $filename = 'certificado-' . str_replace(' ', '-', strtolower($certificate->recipient_name)) . '.pdf';
            return $pdf->download($filename);
        }
    }

    private function verificationQrDataUri(Certificate $certificate): string
    {
        $renderer = new GDLibRenderer(240, 8, 'png', 9);

        $png = (new Writer($renderer))->writeString(
            route('certificates.verify', ['identifier' => $certificate->code]),
            ecLevel: ErrorCorrectionLevel::M(),
        );

        return 'data:image/png;base64,' . base64_encode($png);
    }
}
