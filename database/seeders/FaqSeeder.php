<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'page_slug' => 'proyeccion-social',
                'question' => '¿Quiénes deben realizar Proyección Social en la UNA Puno?',
                'answer' => 'De acuerdo al estatuto universitario de la UNA Puno, todos los estudiantes de pregrado deben cumplir con horas de proyección social acreditadas por su respectiva Facultad antes del egreso.',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'page_slug' => 'proyeccion-social',
                'question' => '¿Cómo inscribirse en el Programa de Voluntariado Universitario?',
                'answer' => 'Las convocatorias se abren semestralmente a través de esta web y de la página oficial de Facebook DPESEC. Solo requieres estar matriculado en el semestre académico correspondiente.',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'page_slug' => 'proyeccion-social',
                'question' => '¿Cómo se registra y acredita un proyecto social independiente?',
                'answer' => 'El docente coordinador debe presentar la propuesta del proyecto mediante Mesa de Partes dirigida a la DPESEC, adjuntando el plan de trabajo con la lista de alumnos participantes y presupuesto sustentado.',
                'sort_order' => 3,
                'is_active' => true
            ]
        ];

        foreach ($faqs as $faq) {
            Faq::create($faq);
        }
    }
}
