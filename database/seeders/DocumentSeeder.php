<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            [
                'title' => 'Directiva N° 004-2026-DPESEC: Disposiciones para Proyectos de Proyección Social y Extensión Cultural',
                'code' => 'DIR-004-2026-DPESEC',
                'category' => 'Directivas',
                'published_date' => '2026-01-15',
                'file_size' => '1.8 MB',
                'description' => 'Establece los lineamientos técnicos, metodológicos y plazos para la formulación, registro, ejecución y evaluación de proyectos sociales.',
                'file_path' => null,
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'title' => 'Resolución Rectoral N° 1024-2026-R-UNAP: Aprobación del Reglamento General de Extensión Universitaria',
                'code' => 'RR-1024-2026-R-UNAP',
                'category' => 'Resoluciones',
                'published_date' => '2026-03-04',
                'file_size' => '2.4 MB',
                'description' => 'Ratifica el estatuto actualizado y el nuevo reglamento de proyección y extensión, adaptado a la ley universitaria vigente.',
                'file_path' => null,
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'title' => 'Guía Práctica para la Redacción de Informes Finales de Proyección Social',
                'code' => 'GUIA-01-2026-DPESEC',
                'category' => 'Guías y Formatos',
                'published_date' => '2026-05-10',
                'file_size' => '950 KB',
                'description' => 'Manual paso a paso que describe el formato requerido para la presentación de los informes de impacto y beneficiarios.',
                'file_path' => null,
                'is_active' => true,
                'sort_order' => 3
            ],
            [
                'title' => 'Formato F-01: Solicitud de Registro de Proyecto de Voluntariado Universitario',
                'code' => 'FORM-F01-VOL',
                'category' => 'Guías y Formatos',
                'published_date' => '2026-01-12',
                'file_size' => '120 KB',
                'description' => 'Ficha obligatoria a presentar por el docente coordinator para inscribir proyectos de voluntariado ante la DPESEC.',
                'file_path' => null,
                'is_active' => true,
                'sort_order' => 4
            ],
            [
                'title' => 'Resolución Rectoral N° 0512-2026-R-UNAP: Acreditación de Horas del Voluntariado Ambiental 2025-II',
                'code' => 'RR-0512-2026-R-UNAP',
                'category' => 'Resoluciones',
                'published_date' => '2026-02-28',
                'file_size' => '3.1 MB',
                'description' => 'Resolución rectoral que formaliza el reconocimiento de horas académicas a los estudiantes participantes del ciclo anterior.',
                'file_path' => null,
                'is_active' => true,
                'sort_order' => 5
            ],
            [
                'title' => 'Reglamento Interno de Funciones de la Dirección de Proyección Social (ROF)',
                'code' => 'REG-ROF-DPESEC',
                'category' => 'Reglamentos',
                'published_date' => '2025-12-20',
                'file_size' => '4.2 MB',
                'description' => 'Estructura orgánica, deberes y atribuciones de la dirección principal y sus respectivas SubUnidades administrativas.',
                'file_path' => null,
                'is_active' => true,
                'sort_order' => 6
            ]
        ];

        foreach ($documents as $doc) {
            Document::create($doc);
        }
    }
}
