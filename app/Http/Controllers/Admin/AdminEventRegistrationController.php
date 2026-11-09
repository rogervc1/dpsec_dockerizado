<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistrationSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminEventRegistrationController extends Controller
{
    public function save(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'form' => 'required|array',
            'form.id' => 'required|string|regex:/^[a-zA-Z0-9_-]+$/|max:64',
            'form.enabled' => 'required|boolean',
            'form.title' => 'required|string|max:160',
            'form.description' => 'nullable|string|max:5000',
            'form.opens_at' => 'nullable|date',
            'form.closes_at' => 'nullable|date|after_or_equal:form.opens_at',
            'form.fields' => 'required|array|min:1|max:40',
            'form.fields.*.id' => 'required|string|regex:/^[a-zA-Z0-9_-]+$/|max:64',
            'form.fields.*.label' => 'required|string|max:160',
            'form.fields.*.type' => 'required|in:text,textarea,email,phone,number,date,select,radio,checkbox,file',
            'form.fields.*.required' => 'required|boolean',
            'form.fields.*.options' => 'nullable|array|max:50',
            'form.fields.*.options.*' => 'string|max:160',
        ]);
        $form = $validated['form'];
        $forms = data_get($event->exclusive_page_content, 'participantes.forms');
        if (!is_array($forms)) {
            $legacy = data_get($event->exclusive_page_content, 'participantes.form');
            $forms = is_array($legacy) ? [['id' => 'default', ...$legacy]] : [];
        }
        $index = collect($forms)->search(fn ($item) => ($item['id'] ?? 'default') === $form['id']);
        if ($index === false) $forms[] = $form; else $forms[$index] = $form;
        $content = $event->exclusive_page_content ?? [];
        $content['participantes']['forms'] = array_values($forms);
        unset($content['participantes']['form']);
        $event->update(['exclusive_page_content' => $content]);
        return back()->with('success', 'Formulario guardado correctamente.');
    }

    public function index(Event $event): JsonResponse
    {
        $forms = data_get($event->exclusive_page_content, 'participantes.forms');
        if (!is_array($forms)) {
            $legacy = data_get($event->exclusive_page_content, 'participantes.form');
            $forms = is_array($legacy) ? [['id' => 'default', ...$legacy]] : [];
        }
        $form = collect($forms)->first(fn ($item) => ($item['id'] ?? 'default') === request('form_id')) ?? [];
        $fields = collect($form['fields'] ?? [])->keyBy('id');
        $query = $event->registrationSubmissions()->where('form_id', $form['id'] ?? 'default');
        $submissions = $query->latest()->limit(500)->get();
        $breakdowns = [];
        foreach ($fields as $id => $field) {
            if (!in_array($field['type'] ?? '', ['select', 'radio', 'checkbox'], true)) continue;
            $counts = array_fill_keys($field['options'] ?? [], 0);
            foreach ($submissions as $submission) {
                $answer = $submission->answers[$id] ?? [];
                foreach (is_array($answer) ? $answer : [$answer] as $value) {
                    if (array_key_exists($value, $counts)) $counts[$value]++;
                }
            }
            $breakdowns[] = ['field' => $field['label'], 'options' => $counts];
        }

        return response()->json([
            'total' => $query->count(),
            'form' => $form,
            'breakdowns' => $breakdowns,
            'submissions' => $submissions->map(fn ($submission) => [
                'id' => $submission->id,
                'created_at' => $submission->created_at?->toISOString(),
                'answers' => $submission->answers,
                'attachments' => collect($submission->attachments ?? [])->map(fn ($attachment, $fieldId) => [
                    'field_id' => $fieldId,
                    'original_name' => $attachment['original_name'],
                    'url' => route('admin.eventos.exclusive-page.registration.attachments', [$event, $submission, $fieldId]),
                ])->values(),
            ]),
        ]);
    }

    public function download(Event $event, EventRegistrationSubmission $submission, string $fieldId): Response
    {
        abort_unless($submission->event_id === $event->id, 404);
        $attachment = $submission->attachments[$fieldId] ?? null;
        abort_unless($attachment && Storage::disk('local')->exists($attachment['path']), 404);

        return Storage::disk('local')->download($attachment['path'], $attachment['original_name'], [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
