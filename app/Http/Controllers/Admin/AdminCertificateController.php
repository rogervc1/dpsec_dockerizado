<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CertificateFont;
use App\Models\CertificateTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminCertificateController extends Controller
{
    /**
     * Show certificates management dashboard.
     */
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'templates' => CertificateTemplate::withCount('certificates')->latest()->get(),
            'fonts' => CertificateFont::all(),
            'certificates' => Certificate::with('template')->latest()->paginate(15),
            'activeTab' => 'certificates',
        ]);
    }

    /**
     * Store a new custom font (.ttf file).
     */
    public function storeFont(Request $request): RedirectResponse
    {
        $request->validate([
            'font_name' => 'required|string|max:100|unique:certificate_fonts,font_name',
            'font_file' => 'required|file|max:10240', // Limit to 10MB
        ]);

        if ($request->hasFile('font_file')) {
            $file = $request->file('font_file');
            
            // Validate extension manually
            if (strtolower($file->getClientOriginalExtension()) !== 'ttf') {
                return redirect()->back()->withErrors(['font_file' => 'El archivo debe tener extensión .ttf']);
            }

            // Store font in public/fonts storage
            $path = $file->storeAs('fonts', Str::slug($request->font_name) . '.ttf', 'public');
            if ($path === false) {
                return redirect()->back()->with('error', 'Error al guardar la tipografía.');
            }

            CertificateFont::create([
                'font_name' => $request->font_name,
                'file_path' => Storage::url($path),
            ]);

            return redirect()->back()->with('success', 'Tipografía subida correctamente.');
        }

        return redirect()->back()->with('error', 'Error al subir la tipografía.');
    }

    /**
     * Delete a font.
     */
    public function destroyFont(CertificateFont $font): RedirectResponse
    {
        // Delete the physical file from storage
        $relativePath = str_replace('/storage/', '', $font->file_path);
        Storage::disk('public')->delete($relativePath);

        $font->delete();

        return redirect()->back()->with('success', 'Tipografía eliminada.');
    }

    /**
     * Store a new certificate template.
     */
    public function storeTemplate(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'background_image' => 'required|file|max:5120', // Max 5MB
            'settings' => 'required|json',
        ]);

        if ($request->hasFile('background_image')) {
            $file = $request->file('background_image');
            
            // Validate extension manually
            if (!in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png'])) {
                return redirect()->back()->withErrors(['background_image' => 'La imagen de fondo debe ser un archivo JPG, JPEG o PNG.']);
            }

            $path = $file->store('certificate_templates', 'public');
            if ($path === false) {
                return redirect()->back()->with('error', 'Error al guardar el fondo del certificado.');
            }

            CertificateTemplate::create([
                'name' => $request->name,
                'background_path' => Storage::url($path),
                'settings' => json_decode($request->settings, true),
            ]);

            return redirect()->back()->with('success', 'Plantilla creada con éxito.');
        }

        return redirect()->back()->with('error', 'Error al subir el fondo del certificado.');
    }

    /**
     * Update an existing certificate template.
     */
    public function updateTemplate(Request $request, CertificateTemplate $template): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'background_image' => 'nullable|file|max:5120',
            'settings' => 'required|json',
        ]);

        $data = [
            'name' => $request->name,
            'settings' => json_decode($request->settings, true),
        ];

        if ($request->hasFile('background_image')) {
            $file = $request->file('background_image');
            
            // Validate extension manually
            if (!in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png'])) {
                return redirect()->back()->withErrors(['background_image' => 'La imagen de fondo debe ser un archivo JPG, JPEG o PNG.']);
            }

            // Delete old file
            $oldPath = str_replace('/storage/', '', $template->background_path);
            Storage::disk('public')->delete($oldPath);

            $file = $request->file('background_image');
            $path = $file->store('certificate_templates', 'public');
            if ($path === false) {
                return redirect()->back()->with('error', 'Error al guardar el nuevo fondo del certificado.');
            }
            $data['background_path'] = Storage::url($path);
        }

        $template->update($data);

        return redirect()->back()->with('success', 'Plantilla actualizada con éxito.');
    }

    /**
     * Delete a template.
     */
    public function destroyTemplate(CertificateTemplate $template): RedirectResponse
    {
        $oldPath = str_replace('/storage/', '', $template->background_path);
        Storage::disk('public')->delete($oldPath);

        $template->delete();

        return redirect()->back()->with('success', 'Plantilla eliminada.');
    }

    /**
     * Import participants from CSV to database linked to a template.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'certificate_template_id' => 'required|exists:certificate_templates,id',
            'csv_file' => 'required|file|max:5120',
            'issue_date' => 'required|date',
        ]);

        $templateId = $request->certificate_template_id;
        $issueDate = $request->issue_date;

        if ($request->hasFile('csv_file')) {
            $file = $request->file('csv_file');
            
            // Validate extension manually
            if (!in_array(strtolower($file->getClientOriginalExtension()), ['csv', 'txt'])) {
                return redirect()->back()->withErrors(['csv_file' => 'El archivo debe tener extensión .csv o .txt']);
            }

            $filePath = $file->getRealPath();

            $handle = fopen($filePath, 'r');
            if ($handle !== false) {
                // Read first line to detect delimiter
                $firstLine = fgets($handle);
                if ($firstLine === false) {
                    fclose($handle);
                    return redirect()->back()->with('error', 'El archivo CSV está vacío.');
                }
                
                // Count occurrences to determine delimiter
                $commaCount = substr_count($firstLine, ',');
                $semicolonCount = substr_count($firstLine, ';');
                $delimiter = ($semicolonCount > $commaCount) ? ';' : ',';
                
                // Rewind file to start
                rewind($handle);

                // Read header and sanitize
                $header = fgetcsv($handle, 1000, $delimiter);
                if (!$header) {
                    fclose($handle);
                    return redirect()->back()->with('error', 'El archivo CSV está vacío o es inválido.');
                }

                // Sanitize header items (remove UTF-8 BOM if present, trim, lowercase)
                $header = array_map(function($h) {
                    $h = preg_replace('/[\x{FEFF}\x{FFFE}]/u', '', $h);
                    return strtolower(trim($h));
                }, $header);

                // Find indices
                $nameIndex = array_search('nombre', $header);
                if ($nameIndex === false) $nameIndex = array_search('name', $header);
                if ($nameIndex === false) $nameIndex = 0; // Default to first col

                $docIndex = array_search('dni', $header);
                if ($docIndex === false) $docIndex = array_search('documento', $header);
                if ($docIndex === false) $docIndex = array_search('codigo', $header);
                if ($docIndex === false) $docIndex = array_search('document', $header);
                if ($docIndex === false) $docIndex = 1; // Default to second col

                $roleIndex = array_search('rol', $header);
                if ($roleIndex === false) $roleIndex = array_search('role', $header);

                $emailIndex = array_search('email', $header);
                if ($emailIndex === false) $emailIndex = array_search('correo', $header);

                $count = 0;
                while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                    // Skip empty rows or single-field rows that are empty
                    if ($row === [null] || (count($row) === 1 && trim($row[0] ?? '') === '')) {
                        continue;
                    }

                    $recipientName = trim($row[$nameIndex] ?? '');
                    $recipientDoc = trim($row[$docIndex] ?? '');

                    if (empty($recipientName) || empty($recipientDoc)) {
                        continue;
                    }

                    $recipientEmail = $emailIndex !== false ? trim($row[$emailIndex] ?? '') : null;
                    $role = $roleIndex !== false ? trim($row[$roleIndex] ?? '') : 'Participante';

                    // Check if already registered for this template to prevent duplicates
                    $exists = Certificate::where('certificate_template_id', $templateId)
                        ->where('recipient_document', $recipientDoc)
                        ->exists();

                    if (!$exists) {
                        Certificate::create([
                            'certificate_template_id' => $templateId,
                            'recipient_name' => $recipientName,
                            'recipient_document' => $recipientDoc,
                            'recipient_email' => $recipientEmail,
                            'role' => $role,
                            'issue_date' => $issueDate,
                        ]);
                        $count++;
                    }
                }
                fclose($handle);

                return redirect()->back()->with('success', "Se importaron {$count} alumnos correctamente.");
            }
        }

        return redirect()->back()->with('error', 'Error al abrir el archivo CSV.');
    }

    /**
     * Delete an individual certificate record.
     */
    public function destroyCertificate(Certificate $certificate): RedirectResponse
    {
        $certificate->delete();
        return redirect()->back()->with('success', 'Registro de certificado eliminado.');
    }
}
