<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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

        $event = Event::create($validated);

        if ($request->boolean('open_exclusive_page')) {
            return redirect()->back()->with('exclusive_event_id', $event->id)->with('success', 'Evento registrado. Ahora configura su página exclusiva.');
        }

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

        if ($request->boolean('open_exclusive_page')) {
            return redirect()->back()->with('exclusive_event_id', $event->id)->with('success', 'Evento actualizado. Ahora configura su página exclusiva.');
        }

        return redirect()->back()->with('success', 'Evento actualizado correctamente.');
    }

    public function updateExclusivePage(Request $request, Event $event): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'enabled' => 'required|boolean',
            'sections' => 'required|array',
            'sections.documentos' => 'required|array',
            'sections.cronograma' => 'required|array',
            'sections.cronograma.activities' => 'nullable|array|max:100',
            'sections.cronograma.activities.*.id' => 'required|string|max:64',
            'sections.cronograma.activities.*.subject' => 'required|string|max:160',
            'sections.cronograma.activities.*.description' => 'nullable|string|max:3000',
            'sections.cronograma.activities.*.date' => 'required|date_format:Y-m-d',
            'sections.cronograma.activities.*.time' => 'nullable|date_format:H:i',
            'sections.participantes' => 'required|array',
            'sections.participantes.forms' => 'nullable|array|max:30',
            'sections.participantes.forms.*.id' => 'required|string|regex:/^[a-zA-Z0-9_-]+$/|max:64',
            'sections.participantes.forms.*.enabled' => 'required|boolean',
            'sections.participantes.forms.*.title' => 'required|string|max:160',
            'sections.participantes.forms.*.description' => 'nullable|string|max:5000',
            'sections.participantes.forms.*.opens_at' => 'nullable|date',
            'sections.participantes.forms.*.closes_at' => 'nullable|date',
            'sections.participantes.forms.*.fields' => 'required|array|min:1|max:40',
            'sections.participantes.forms.*.fields.*.id' => 'required|string|regex:/^[a-zA-Z0-9_-]+$/|max:64',
            'sections.participantes.forms.*.fields.*.label' => 'required|string|max:160',
            'sections.participantes.forms.*.fields.*.type' => 'required|in:text,textarea,email,phone,number,date,select,radio,checkbox,file',
            'sections.participantes.forms.*.fields.*.required' => 'required|boolean',
            'sections.participantes.forms.*.fields.*.options' => 'nullable|array|max:50',
            'sections.participantes.forms.*.fields.*.options.*' => 'string|max:160',
            'sections.ubicaciones' => 'required|array',
            'sections.ubicaciones.croquis_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'sections.ubicaciones.croquis' => 'nullable|string|max:1000',
            'sections.ubicaciones.locations' => 'nullable|array|max:30',
            'sections.ubicaciones.locations.*.id' => 'required|string|max:64',
            'sections.ubicaciones.locations.*.name' => 'required|string|max:160',
            'sections.ubicaciones.locations.*.image' => 'nullable|string|max:1000',
            'sections.ubicaciones.locations.*.image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'sections.ubicaciones.locations.*.x' => 'required|numeric|min:0|max:100',
            'sections.ubicaciones.locations.*.y' => 'required|numeric|min:0|max:100',
            'sections.ubicaciones.locations.*.color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'sections.resultados' => 'required|array',
            'sections.resultados.categories' => 'nullable|array|max:30',
            'sections.resultados.categories.*.id' => 'required|string|max:64',
            'sections.resultados.categories.*.name' => 'required|string|max:160',
            'sections.resultados.categories.*.winners' => 'nullable|array|max:3',
            'sections.resultados.categories.*.winners.*.id' => 'required|string|max:64',
            'sections.resultados.categories.*.winners.*.name' => 'required|string|max:160',
            'sections.resultados.categories.*.winners.*.image' => 'nullable|string|max:1000',
            'sections.resultados.categories.*.winners.*.image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'sections.resultados.type' => 'nullable|in:results_image,table,youtube,gallery',
            'sections.resultados.results_image' => 'nullable|string|max:1000',
            'sections.resultados.results_image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'sections.resultados.participants' => 'nullable|array|max:500',
            'sections.resultados.participants.*.id' => 'required|string|max:64',
            'sections.resultados.participants.*.name' => 'required|string|max:160',
            'sections.resultados.participants.*.score' => 'required|string|max:80',
            'sections.resultados.youtube_url' => 'nullable|url|max:1000',
            'sections.resultados.gallery' => 'nullable|array|max:100',
            'sections.resultados.gallery.*.id' => 'required|string|max:64',
            'sections.resultados.gallery.*.title' => 'nullable|string|max:160',
            'sections.resultados.gallery.*.description' => 'nullable|string|max:2000',
            'sections.resultados.gallery.*.image' => 'nullable|string|max:1000',
            'sections.resultados.gallery.*.image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'sections.*.link' => 'nullable|url|max:1000',
            'settings' => 'required|array',
            'settings.title' => 'required|string|max:255',
            'settings.subtitle' => 'nullable|string|max:255',
            'settings.subtitle_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'settings.subtitle_use_theme' => 'required|boolean',
            'settings.event_date' => 'required|date',
            'settings.time' => 'nullable|string|max:50',
            'settings.location' => 'required|string|max:255',
            'settings.about' => 'required|string|max:10000',
            'settings.theme' => 'required|in:emerald,forest,teal,lime,mint',
            'settings.theme_custom_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'settings.title_position' => 'required|in:left,center,right',
            'settings.title_size' => 'required|integer|min:32|max:112',
            'settings.title_offset_x' => 'required|integer|min:-120|max:120',
            'settings.title_offset_y' => 'required|integer|min:-120|max:40',
            'settings.mobile_title_position' => 'required|in:left,center,right',
            'settings.mobile_title_size' => 'required|integer|min:32|max:112',
            'settings.mobile_title_offset_x' => 'required|integer|min:-120|max:120',
            'settings.mobile_title_offset_y' => 'required|integer|min:-120|max:224',
            'settings.config_linked' => 'required|boolean',
            'settings.desktop_cover_zoom' => 'required|numeric|min:1|max:2.5',
            'settings.desktop_cover_x' => 'required|numeric|min:0|max:100',
            'settings.desktop_cover_y' => 'required|numeric|min:0|max:100',
            'settings.mobile_cover_zoom' => 'required|numeric|min:1|max:2.5',
            'settings.mobile_cover_x' => 'required|numeric|min:0|max:100',
            'settings.mobile_cover_y' => 'required|numeric|min:0|max:100',
            'settings.desktop_cover_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        $validator->after(function ($validator) use ($request) {
            $forms = $request->input('sections.participantes.forms', []);
            if (is_array($forms)) {
                foreach ($forms as $formIndex => $form) {
                    foreach (($form['fields'] ?? []) as $index => $field) {
                        if (!is_array($field)) continue;
                        if (in_array($field['type'] ?? '', ['select', 'radio', 'checkbox'], true)
                            && empty(array_filter($field['options'] ?? [], fn ($option) => is_string($option) && trim($option) !== ''))) {
                            $validator->errors()->add("sections.participantes.forms.{$formIndex}.fields.{$index}.options", 'Agrega al menos una opción a esta pregunta.');
                        }
                    }
                }
            }

            if (blank($request->input('sections.ubicaciones.croquis')) && !$request->hasFile('sections.ubicaciones.croquis_file')) {
                $validator->errors()->add('sections.ubicaciones.croquis_file', 'Carga el croquis antes de guardar las ubicaciones.');
            }

            foreach ($request->input('sections.ubicaciones.locations', []) as $index => $location) {
                if (!is_array($location)) continue;
                if (blank($location['image'] ?? null) && !$request->hasFile("sections.ubicaciones.locations.{$index}.image_file")) {
                    $validator->errors()->add("sections.ubicaciones.locations.{$index}.image_file", 'Carga una imagen para esta ubicación.');
                }
            }

            $results = $request->input('sections.resultados', []);
            foreach (($results['categories'] ?? []) as $categoryIndex => $category) {
                foreach (($category['winners'] ?? []) as $winnerIndex => $winner) {
                    if (is_array($winner) && blank($winner['image'] ?? null) && !$request->hasFile("sections.resultados.categories.{$categoryIndex}.winners.{$winnerIndex}.image_file")) {
                        $validator->errors()->add("sections.resultados.categories.{$categoryIndex}.winners.{$winnerIndex}.image_file", 'Carga una imagen para este ganador.');
                    }
                }
            }
            $type = $results['type'] ?? '';
            if ($type === 'results_image' && blank($results['results_image'] ?? null) && !$request->hasFile('sections.resultados.results_image_file')) {
                $validator->errors()->add('sections.resultados.results_image_file', 'Carga la imagen de resultados.');
            }
            if ($type === 'youtube' && blank($results['youtube_url'] ?? null)) {
                $validator->errors()->add('sections.resultados.youtube_url', 'Ingresa el enlace de YouTube.');
            }
            foreach (($results['gallery'] ?? []) as $index => $item) {
                if (is_array($item) && blank($item['image'] ?? null) && !$request->hasFile("sections.resultados.gallery.{$index}.image_file")) {
                    $validator->errors()->add("sections.resultados.gallery.{$index}.image_file", 'Carga una imagen para este elemento.');
                }
            }
        });
        $validated = $validator->validate();

        $locationSection = $validated['sections']['ubicaciones'];
        if ($request->hasFile('sections.ubicaciones.croquis_file')) {
            $locationSection['croquis'] = Storage::url($request->file('sections.ubicaciones.croquis_file')->store("events/{$event->id}/locations", 'public'));
        }
        unset($locationSection['croquis_file']);
        $locations = $locationSection['locations'] ?? [];
        foreach ($locations as $index => $location) {
            if ($request->hasFile("sections.ubicaciones.locations.{$index}.image_file")) {
                $location['image'] = Storage::url($request->file("sections.ubicaciones.locations.{$index}.image_file")->store("events/{$event->id}/locations", 'public'));
            }
            unset($location['image_file']);
            $location['order'] = $index + 1;
            $locations[$index] = $location;
        }
        $locationSection['locations'] = array_values($locations);
        $validated['sections']['ubicaciones'] = $locationSection;

        $results = $validated['sections']['resultados'];
        foreach (($results['categories'] ?? []) as $categoryIndex => $category) {
            foreach (($category['winners'] ?? []) as $winnerIndex => $winner) {
                $fileKey = "sections.resultados.categories.{$categoryIndex}.winners.{$winnerIndex}.image_file";
                if ($request->hasFile($fileKey)) {
                    $winner['image'] = Storage::url($request->file($fileKey)->store("events/{$event->id}/results", 'public'));
                }
                unset($winner['image_file']);
                $category['winners'][$winnerIndex] = $winner;
            }
            $results['categories'][$categoryIndex] = $category;
        }
        if ($request->hasFile('sections.resultados.results_image_file')) {
            $results['results_image'] = Storage::url($request->file('sections.resultados.results_image_file')->store("events/{$event->id}/results", 'public'));
        }
        unset($results['results_image_file']);
        foreach (($results['gallery'] ?? []) as $index => $item) {
            $fileKey = "sections.resultados.gallery.{$index}.image_file";
            if ($request->hasFile($fileKey)) {
                $item['image'] = Storage::url($request->file($fileKey)->store("events/{$event->id}/results", 'public'));
            }
            unset($item['image_file']);
            $results['gallery'][$index] = $item;
        }
        $validated['sections']['resultados'] = $results;

        foreach ($validated['sections'] as &$section) {
            if (!is_array($section)) continue;
            unset($section['title'], $section['content']);
        }
        unset($section);

        $settings = $validated['settings'];
        $settings['subtitle_use_theme'] = $request->boolean('settings.subtitle_use_theme');
        $settings['config_linked'] = $request->boolean('settings.config_linked');
        unset($settings['desktop_config_linked'], $settings['mobile_config_linked']);
        unset($settings['desktop_cover_file']);
        $previousSettings = $event->exclusive_page_settings ?? [];
        $defaultCover = $event->cover_image_path ?? $event->image_path;
        $previousDesktopCover = $previousSettings['desktop_cover'] ?? $defaultCover;
        $settings['desktop_cover'] = $previousDesktopCover;
        $settings['mobile_cover'] = $previousDesktopCover;
        if ($request->hasFile('settings.desktop_cover_file')) {
            $path = $request->file('settings.desktop_cover_file')->store('events/exclusive', 'public');
            $settings['desktop_cover'] = Storage::url($path);
            $settings['mobile_cover'] = $settings['desktop_cover'];
        }

        $event->update([
            'has_exclusive_page' => $validated['enabled'],
            'exclusive_page_content' => $validated['sections'],
            'exclusive_page_settings' => $settings,
        ]);

        return redirect()->back()->with('success', 'Página exclusiva guardada correctamente.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        foreach ($event->exclusiveDocuments as $document) {
            Storage::disk('local')->delete($document->file_path);
        }
        $event->exclusiveDocuments()->delete();
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
