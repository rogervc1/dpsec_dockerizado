<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventExclusiveDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EventExclusiveDocumentController extends Controller
{
    public function preview(Event $event, EventExclusiveDocument $document): Response
    {
        $this->ensurePublicDocument($event, $document);

        abort_unless($this->isInlineSafe($document->mime_type), 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($document->file_path), 404);

        return response()->file($disk->path($document->file_path), [
            'Content-Type' => $document->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function download(Event $event, EventExclusiveDocument $document): Response
    {
        $this->ensurePublicDocument($event, $document);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($document->file_path), 404);
        $extension = pathinfo($document->original_name, PATHINFO_EXTENSION);
        $filename = Str::slug(pathinfo($document->title, PATHINFO_FILENAME)) ?: 'documento';
        if ($extension !== '') $filename .= '.'.strtolower($extension);

        return $disk->download($document->file_path, $filename, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private function ensurePublicDocument(Event $event, EventExclusiveDocument $document): void
    {
        abort_unless($event->has_exclusive_page && $document->event_id === $event->id && $document->is_visible, 404);
    }

    private function isInlineSafe(string $mimeType): bool
    {
        return $mimeType === 'application/pdf'
            || str_starts_with($mimeType, 'image/')
            || in_array($mimeType, ['text/plain', 'text/csv'], true);
    }
}
