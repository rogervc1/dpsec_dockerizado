<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\PageSection;
use Inertia\Inertia;
use Inertia\Response;

class EventosController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('public/Eventos', [
            'events' => Event::active()->get(),
            'sections' => PageSection::forPage('eventos')
                ->get()
                ->keyBy('section_key'),
        ]);
    }

    public function show(Event $event): Response
    {
        abort_unless($event->has_exclusive_page, 404);

        $documents = $event->exclusiveDocuments()
            ->visible()
            ->latest()
            ->get()
            ->map(fn ($document) => [
                'id' => $document->id,
                'title' => $document->title,
                'description' => $document->description,
                'original_name' => $document->original_name,
                'mime_type' => $document->mime_type,
                'size_bytes' => $document->size_bytes,
                'uploaded_at' => $document->created_at?->toISOString(),
                'preview_url' => route('eventos.exclusive-documents.preview', [$event, $document]),
                'download_url' => route('eventos.exclusive-documents.download', [$event, $document]),
            ])
            ->values();

        return Inertia::render('public/Evento', [
            'event' => $event,
            'documents' => $documents,
        ]);
    }
}
