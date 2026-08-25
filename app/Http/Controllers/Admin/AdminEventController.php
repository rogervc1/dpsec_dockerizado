<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

use Illuminate\Support\Facades\Storage;

class AdminEventController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'events' => Event::orderBy('sort_order')->orderBy('event_date', 'desc')->get(),
            'activeTab' => 'events',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'type'                => 'required|string|max:100',
            'category'            => 'required|string|max:100',
            'event_date'          => 'required|date',
            'time'                => 'nullable|string|max:50',
            'location'            => 'required|string|max:255',
            'organizer'           => 'required|string|max:255',
            'description'         => 'required|string',
            'image_file'          => 'nullable|file|max:5120',
            'cover_image_file'    => 'nullable|file|max:5120',
            'image_files'         => 'nullable|array|max:10',
            'image_files.*'       => 'file|max:5120',
            'image_path'          => 'nullable|string|max:1000',
            'fb_link'             => 'nullable|url|max:1000',
            'registration_link'   => 'nullable|url|max:1000',
            'is_proyeccion_social' => 'boolean',
            'sort_order'          => 'integer',
        ]);

        $gallery = [];

        if ($request->hasFile('image_file') && !$request->hasFile('image_files')) {
            $file = $request->file('image_file');
            
            // Validate extension manually
            if (!in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'webp'])) {
                return redirect()->back()->withErrors(['image_file' => 'La imagen debe ser un archivo JPG, JPEG, PNG o WebP.']);
            }

            $path = $file->store('events', 'public');
            if ($path === false) {
                return redirect()->back()->with('error', 'Error al guardar la imagen del evento.');
            }
            $validated['image_path'] = Storage::url($path);
            $gallery[] = $validated['image_path'];
        }

        foreach ($request->file('image_files', []) as $file) {
            if (!in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'webp'])) {
                return redirect()->back()->withErrors(['image_files' => 'Las imágenes deben ser archivos JPG, JPEG, PNG o WebP.']);
            }

            $path = $file->store('events', 'public');
            if ($path === false) {
                return redirect()->back()->with('error', 'Error al guardar una de las imágenes del evento.');
            }

            $gallery[] = Storage::url($path);
        }

        if (count($gallery) > 0) {
            $validated['event_images'] = array_values(array_unique($gallery));
            $validated['image_path'] = $validated['event_images'][0];
        }

        if ($request->hasFile('cover_image_file')) {
            $coverPath = $request->file('cover_image_file')->store('events', 'public');
            if ($coverPath === false) {
                return redirect()->back()->with('error', 'Error al guardar la portada del evento.');
            }
            $validated['cover_image_path'] = Storage::url($coverPath);
        } else {
            $validated['cover_image_path'] = $validated['image_path'] ?? null;
        }

        unset($validated['image_file'], $validated['image_files'], $validated['cover_image_file']);

        Event::create($validated);

        return redirect()->back()->with('success', 'Evento registrado correctamente.');
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'title'               => 'required|string|max:255',
            'type'                => 'required|string|max:100',
            'category'            => 'required|string|max:100',
            'event_date'          => 'required|date',
            'time'                => 'nullable|string|max:50',
            'location'            => 'required|string|max:255',
            'organizer'           => 'required|string|max:255',
            'description'         => 'required|string',
            'image_file'          => 'nullable|file|max:5120',
            'cover_image_file'    => 'nullable|file|max:5120',
            'image_files'         => 'nullable|array|max:10',
            'image_files.*'       => 'file|max:5120',
            'image_path'          => 'nullable|string|max:1000',
            'fb_link'             => 'nullable|url|max:1000',
            'registration_link'   => 'nullable|url|max:1000',
            'is_proyeccion_social' => 'boolean',
            'sort_order'          => 'integer',
        ]);

        if ($request->hasFile('image_file') && !$request->hasFile('image_files') && !$request->hasFile('cover_image_file')) {
            // Delete old file
            if ($event->image_path && !str_starts_with($event->image_path, 'http')) {
                $oldPath = str_replace('/storage/', '', $event->image_path);
                Storage::disk('public')->delete($oldPath);
            }

            $file = $request->file('image_file');
            
            // Validate extension manually
            if (!in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'webp'])) {
                return redirect()->back()->withErrors(['image_file' => 'La imagen debe ser un archivo JPG, JPEG, PNG o WebP.']);
            }

            $path = $file->store('events', 'public');
            if ($path === false) {
                return redirect()->back()->with('error', 'Error al guardar la nueva imagen del evento.');
            }
            $validated['image_path'] = Storage::url($path);
            $validated['event_images'] = [$validated['image_path']];
        }

        if ($request->hasFile('image_files')) {
            $this->deleteLocalEventImages($event);

            $gallery = [];
            foreach ($request->file('image_files', []) as $file) {
                if (!in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'webp'])) {
                    return redirect()->back()->withErrors(['image_files' => 'Las imágenes deben ser archivos JPG, JPEG, PNG o WebP.']);
                }

                $path = $file->store('events', 'public');
                if ($path === false) {
                    return redirect()->back()->with('error', 'Error al guardar una de las nuevas imágenes del evento.');
                }

                $gallery[] = Storage::url($path);
            }

            $validated['event_images'] = $gallery;
            $validated['image_path'] = $gallery[0] ?? $validated['image_path'] ?? $event->image_path;
        }

        if ($request->hasFile('cover_image_file')) {
            $this->deleteLocalEventCover($event);
            $coverPath = $request->file('cover_image_file')->store('events', 'public');
            if ($coverPath === false) {
                return redirect()->back()->with('error', 'Error al guardar la portada del evento.');
            }
            $validated['cover_image_path'] = Storage::url($coverPath);
        } elseif ($request->hasFile('image_files')) {
            $validated['cover_image_path'] = $validated['image_path'] ?? $event->cover_image_path;
        }

        unset($validated['image_file'], $validated['image_files'], $validated['cover_image_file']);

        $event->update($validated);

        return redirect()->back()->with('success', 'Evento actualizado correctamente.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->deleteLocalEventImages($event);

        $event->delete();
        return redirect()->back()->with('success', 'Evento eliminado.');
    }

    private function deleteLocalEventImages(Event $event): void
    {
        $paths = array_values(array_unique(array_filter([
            $event->image_path,
            ...($event->event_images ?? []),
        ])));

        foreach ($paths as $path) {
            if (!str_starts_with($path, 'http')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $path));
            }
        }

        $this->deleteLocalEventCover($event);
    }

    private function deleteLocalEventCover(Event $event): void
    {
        if ($event->cover_image_path && !str_starts_with($event->cover_image_path, 'http')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $event->cover_image_path));
        }
    }
}
