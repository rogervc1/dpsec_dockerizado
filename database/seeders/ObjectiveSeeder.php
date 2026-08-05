<?php

namespace Database\Seeders;

use App\Models\Objective;
use Illuminate\Database\Seeder;

class ObjectiveSeeder extends Seeder
{
    public function run(): void
    {
        $objectives = [
            [
                'title' => 'Fortalecimiento Institucional',
                'description' => 'Consolidar nuestra organización como referente en el sector, con procesos eficientes y un equipo altamente capacitado.',
                'icon_name' => 'Building2',
                'color_class' => 'text-indigo-600 dark:text-indigo-400 bg-indigo-500/10',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'title' => 'Impacto Social Ampliado',
                'description' => 'Incrementar nuestro alcance beneficiando a un 30% más de personas en los próximos 5 años.',
                'icon_name' => 'TrendingUp',
                'color_class' => 'text-blue-600 dark:text-blue-400 bg-blue-500/10',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'title' => 'Innovación Continua',
                'description' => 'Desarrollar al menos 3 nuevos programas innovadores anuales que respondan a necesidades emergentes.',
                'icon_name' => 'Lightbulb',
                'color_class' => 'text-amber-500 dark:text-amber-400 bg-amber-500/10',
                'sort_order' => 3,
                'is_active' => true
            ],
            [
                'title' => 'Sostenibilidad Financiera',
                'description' => 'Diversificar nuestras fuentes de financiamiento para garantizar la continuidad de nuestros proyectos.',
                'icon_name' => 'Scale',
                'color_class' => 'text-emerald-600 dark:text-emerald-400 bg-emerald-500/10',
                'sort_order' => 4,
                'is_active' => true
            ],
            [
                'title' => 'Alianzas Estratégicas',
                'description' => 'Establecer colaboraciones con al menos 10 nuevas organizaciones afines en los próximos 3 años.',
                'icon_name' => 'UsersRound',
                'color_class' => 'text-purple-600 dark:text-purple-400 bg-purple-500/10',
                'sort_order' => 5,
                'is_active' => true
            ]
        ];

        foreach ($objectives as $obj) {
            Objective::create($obj);
        }
    }
}
