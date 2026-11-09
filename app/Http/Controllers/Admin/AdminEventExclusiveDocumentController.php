<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventExclusiveDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminEventExclusiveDocumentController extends Controller
{
    private const ALLOWED_EXTENSIONS = 'pdf,doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp,txt,csv,jpg,jpeg,png,webp';

    public function store(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'is_visible' => 'required|boolean',
            'file' => 'required|file|max:51200|mimes:'.self::ALLOWED_EXTENSIONS,
        ]);

        $file = $request->file('file');
        $path = $file->store("events/{$event->id}/exclusive-documents", 'local');
        abort_if($path === false, 500, 'No se pudo guardar el documento.');

        try {
            $event->exclusiveDocuments()->create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'is_visible' => $validated['is_visible'],
                'original_name' => $this->safeOriginalName($file->getClientOriginalName()),
                'file_path' => $path,
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size_bytes' => $file->getSize(),
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return back()->with('success', 'Documento agregado a la página exclusiva.');
    }

    public function update(Request $request, Event $event, EventExclusiveDocument $document): RedirectResponse
    {
        abort_unless($document->event_id === $event->id, 404);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'is_visible' => 'required|boolean',
            'file' => 'nullable|file|max:51200|mimes:'.self::ALLOWED_EXTENSIONS,
        ]);

        $oldPath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $newPath = $file->store("events/{$event->id}/exclusive-documents", 'local');
            abort_if($newPath === false, 500, 'No se pudo guardar el nuevo archivo.');
            $oldPath = $document->file_path;
            $validated['file_path'] = $newPath;
            $validated['original_name'] = $this->safeOriginalName($file->getClientOriginalName());
            $validated['mime_type'] = $file->getMimeType() ?: 'application/octet-stream';
            $validated['size_bytes'] = $file->getSize();
        }

        try {
            $document->update($validated);
        } catch (\Throwable $exception) {
            if (isset($newPath)) Storage::disk('local')->delete($newPath);
            throw $exception;
        }

        if ($oldPath) Storage::disk('local')->delete($oldPath);

        return back()->with('success', 'Documento actualizado correctamente.');
    }

    public function destroy(Event $event, EventExclusiveDocument $document): RedirectResponse
    {
        abort_unless($document->event_id === $event->id, 404);

        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Documento eliminado.');
    }

    private function safeOriginalName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[[:cntrl:]]/', '', $name) ?: 'documento';

        return Str::limit($name, 255, '');
    }
}
