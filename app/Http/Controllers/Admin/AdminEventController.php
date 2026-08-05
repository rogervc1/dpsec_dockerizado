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
            'image_path'          => 'nullable|string|max:1000',
            'fb_link'             => 'nullable|url|max:1000',
            'is_proyeccion_social' => 'boolean',
            'sort_order'          => 'integer',
        ]);

        if ($request->hasFile('image_file')) {
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
        }

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
            'image_path'          => 'nullable|string|max:1000',
            'fb_link'             => 'nullable|url|max:1000',
            'is_proyeccion_social' => 'boolean',
            'sort_order'          => 'integer',
        ]);

        if ($request->hasFile('image_file')) {
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
        }

        $event->update($validated);

        return redirect()->back()->with('success', 'Evento actualizado correctamente.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        // Delete old file
        if ($event->image_path && !str_starts_with($event->image_path, 'http')) {
            $oldPath = str_replace('/storage/', '', $event->image_path);
            Storage::disk('public')->delete($oldPath);
        }

        $event->delete();
        return redirect()->back()->with('success', 'Evento eliminado.');
    }
}
