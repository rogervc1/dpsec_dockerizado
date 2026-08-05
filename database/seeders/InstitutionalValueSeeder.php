<?php

namespace Database\Seeders;

use App\Models\InstitutionalValue;
use Illuminate\Database\Seeder;

class InstitutionalValueSeeder extends Seeder
{
    public function run(): void
    {
        $values = [
            [
                'title' => 'Compromiso',
                'description' => 'Nos dedicamos plenamente a nuestra misión y a las comunidades que servimos.',
                'icon_name' => 'Heart',
                'glow_bg_class' => 'bg-rose-500',
                'icon_container_class' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 dark:bg-rose-500/20',
                'accent_line_class' => 'bg-rose-500',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'title' => 'Innovación',
                'description' => 'Buscamos constantemente nuevas soluciones a los desafíos sociales.',
                'icon_name' => 'Lightbulb',
                'glow_bg_class' => 'bg-amber-500',
                'icon_container_class' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 dark:bg-amber-500/20',
                'accent_line_class' => 'bg-amber-500',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'title' => 'Trabajo en Equipo',
                'description' => 'Creemos en el poder de la colaboración y el esfuerzo colectivo.',
                'icon_name' => 'Users',
                'glow_bg_class' => 'bg-blue-500',
                'icon_container_class' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 dark:bg-blue-500/20',
                'accent_line_class' => 'bg-blue-500',
                'sort_order' => 3,
                'is_active' => true
            ],
            [
                'title' => 'Integridad',
                'description' => 'Actuamos con transparencia y ética en todas nuestras acciones.',
                'icon_name' => 'ShieldCheck',
                'glow_bg_class' => 'bg-emerald-500',
                'icon_container_class' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 dark:bg-emerald-500/20',
                'accent_line_class' => 'bg-emerald-500',
                'sort_order' => 4,
                'is_active' => true
            ],
            [
                'title' => 'Pasión',
                'description' => 'Nos apasiona nuestro trabajo y el impacto que generamos.',
                'icon_name' => 'Flame',
                'glow_bg_class' => 'bg-orange-500',
                'icon_container_class' => 'bg-orange-500/10 text-orange-600 dark:text-orange-400 dark:bg-orange-500/20',
                'accent_line_class' => 'bg-orange-500',
                'sort_order' => 5,
                'is_active' => true
            ],
            [
                'title' => 'Sostenibilidad',
                'description' => 'Trabajamos por soluciones que perduren en el tiempo.',
                'icon_name' => 'Leaf',
                'glow_bg_class' => 'bg-teal-500',
                'icon_container_class' => 'bg-teal-500/10 text-teal-600 dark:text-teal-400 dark:bg-teal-500/20',
                'accent_line_class' => 'bg-teal-500',
                'sort_order' => 6,
                'is_active' => true
            ]
        ];

        foreach ($values as $val) {
            InstitutionalValue::create($val);
        }
    }
}
