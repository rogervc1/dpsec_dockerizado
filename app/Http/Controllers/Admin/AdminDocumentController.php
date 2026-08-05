<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminDocumentController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'documents' => Document::orderBy('sort_order')->orderBy('published_date', 'desc')->get(),
            'activeTab' => 'docs',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'code'           => 'required|string|max:100|unique:documents,code',
            'category'       => 'required|string|max:100',
            'published_date' => 'required|date',
            'description'    => 'nullable|string',
            'file_file'      => 'required|file|max:51200', // max 50MB
            'is_active'      => 'boolean',
            'sort_order'     => 'integer',
        ]);

        if ($request->hasFile('file_file')) {
            $file = $request->file('file_file');
            $path = $file->store('documents', 'public');
            if ($path === false) {
                return redirect()->back()->with('error', 'Error al guardar el archivo.');
            }
            $validated['file_path'] = Storage::url($path);
            $validated['file_size'] = $this->formatBytes($file->getSize());
        }

        Document::create($validated);

        return redirect()->back()->with('success', 'Documento registrado correctamente.');
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'code'           => 'required|string|max:100|unique:documents,code,' . $document->id,
            'category'       => 'required|string|max:100',
            'published_date' => 'required|date',
            'description'    => 'nullable|string',
            'file_file'      => 'nullable|file|max:51200', // max 50MB
            'is_active'      => 'boolean',
            'sort_order'     => 'integer',
        ]);

        if ($request->hasFile('file_file')) {
            // Delete old file if exists
            if ($document->file_path) {
                $oldPath = str_replace('/storage/', '', $document->file_path);
                Storage::disk('public')->delete($oldPath);
            }

            $file = $request->file('file_file');
            $path = $file->store('documents', 'public');
            if ($path === false) {
                return redirect()->back()->with('error', 'Error al guardar el nuevo archivo.');
            }
            $validated['file_path'] = Storage::url($path);
            $validated['file_size'] = $this->formatBytes($file->getSize());
        }

        $document->update($validated);

        return redirect()->back()->with('success', 'Documento actualizado correctamente.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        // Delete associated file
        if ($document->file_path) {
            $path = str_replace('/storage/', '', $document->file_path);
            Storage::disk('public')->delete($path);
        }

        $document->delete();
        return redirect()->back()->with('success', 'Documento eliminado.');
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = (int) min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
