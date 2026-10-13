<?php

use App\Models\Event;

test('la pagina de un evento responde correctamente', function () {
    $event = Event::create([
        'title' => 'Jornada de Prueba DPSEC',
        'type' => 'Cultural',
        'category' => 'Extensión Cultural',
        'event_date' => now()->addDays(7)->toDateString(),
        'time' => '10:00 AM',
        'location' => 'Ciudad Universitaria',
        'organizer' => 'DPSEC',
        'description' => 'Evento de prueba para la subpágina informativa.',
        'image_path' => null,
        'fb_link' => null,
        'is_proyeccion_social' => false,
        'sort_order' => 1,
    ]);

    $this->get(route('eventos.show', $event))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/Evento')
            ->where('event.id', $event->id)
            ->where('event.title', 'Jornada de Prueba DPSEC')
        );
});

test('un evento inexistente responde 404', function () {
    $this->get('/eventos/999999')->assertNotFound();
});
