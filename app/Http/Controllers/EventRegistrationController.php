<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistrationSubmission;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EventRegistrationController extends Controller
{
    public function store(Request $request, Event $event): RedirectResponse
    {
        $formId = (string) $request->input('form_id', '');
        $forms = data_get($event->exclusive_page_content, 'participantes.forms');
        if (!is_array($forms)) {
            $legacy = data_get($event->exclusive_page_content, 'participantes.form');
            $forms = is_array($legacy) ? [['id' => 'default', ...$legacy]] : [];
        } elseif (!array_is_list($forms)) {
            $forms = collect($forms)->map(function ($item, $key) {
                return is_array($item) ? [...$item, 'id' => (string) ($item['id'] ?? $key)] : $item;
            })->values()->all();
        }
        $form = collect($forms)->first(fn ($item) => ($item['id'] ?? 'default') === $formId);
        if (!$form && count($forms) === 1 && $formId === '') $form = $forms[0];
        abort_unless($event->has_exclusive_page && is_array($form) && ($form['enabled'] ?? false), 404);

        $now = now();
        if (!empty($form['opens_at']) && $now->lt(Carbon::parse($form['opens_at']))) {
            return back()->withErrors(['registration' => 'El formulario todavía no está abierto.']);
        }
        if (!empty($form['closes_at']) && $now->gt(Carbon::parse($form['closes_at']))) {
            return back()->withErrors(['registration' => 'El periodo de inscripción ha terminado.']);
        }

        $fields = collect($form['fields'] ?? [])->filter(fn ($field) => is_array($field) && !empty($field['id']) && !empty($field['label']))->values();
        $rules = [];
        foreach ($fields as $field) {
            $id = $field['id'];
            $key = "answers.{$id}";
            $required = ($field['required'] ?? false) ? 'required' : 'nullable';
            if (($field['type'] ?? '') === 'file') {
                $rules["attachments.{$id}"] = ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp', 'max:10240'];
                continue;
            }
            $rules[$key] = match ($field['type'] ?? 'text') {
                'email' => [$required, 'string', 'email', 'max:255'],
                'number' => [$required, 'numeric'],
                'date' => [$required, 'date_format:Y-m-d'],
                'select', 'radio' => [$required, 'string', Rule::in($field['options'] ?? [])],
                'checkbox' => [$required, 'array'],
                'textarea' => [$required, 'string', 'max:5000'],
                default => [$required, 'string', 'max:255'],
            };
            if (($field['type'] ?? '') === 'checkbox') {
                $rules["{$key}.*"] = ['string', Rule::in($field['options'] ?? [])];
            }
        }

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) use ($request, $fields) {
            foreach ($fields->where('type', 'file') as $field) {
                $id = $field['id'];
                if (($field['required'] ?? false) && !$request->hasFile("attachments.{$id}")) {
                    $validator->errors()->add("attachments.{$id}", 'Adjunta un archivo para continuar.');
                }
            }
        });
        $validated = $validator->validate();

        $answers = $validated['answers'] ?? [];
        $attachments = [];
        foreach ($fields->where('type', 'file') as $field) {
            $file = $request->file("attachments.{$field['id']}");
            if (!$file) continue;
            $path = $file->store("event-registration/{$event->id}", 'local');
            $attachments[$field['id']] = [
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
            ];
        }

        EventRegistrationSubmission::create([
            'event_id' => $event->id,
            'form_id' => $form['id'] ?? 'default',
            'answers' => $answers,
            'attachments' => $attachments,
        ]);

        return back()->with('registration_success', 'Tu inscripción fue recibida correctamente.');
    }
}
