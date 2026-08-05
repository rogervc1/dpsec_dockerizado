<?php

namespace Database\Seeders;

use App\Models\Statistic;
use Illuminate\Database\Seeder;

class StatisticSeeder extends Seeder
{
    public function run(): void
    {
        $stats = [
            [
                'label' => 'Proyectos de Proyección',
                'value' => '184',
                'description' => 'Ejecutados este año',
                'icon_name' => 'Award',
                'color_class' => 'text-amber-500 bg-amber-500/10',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'label' => 'Estudiantes Voluntarios',
                'value' => '2,450+',
                'description' => 'Participación activa',
                'icon_name' => 'Users',
                'color_class' => 'text-indigo-500 bg-indigo-500/10',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'label' => 'Comunidades Beneficiadas',
                'value' => '45+',
                'description' => 'En toda la región Puno',
                'icon_name' => 'Heart',
                'color_class' => 'text-red-500 bg-red-500/10',
                'sort_order' => 3,
                'is_active' => true
            ],
            [
                'label' => 'Eventos Culturales',
                'value' => '38',
                'description' => 'Ciclos y festivales anuales',
                'icon_name' => 'FlameKindling',
                'color_class' => 'text-emerald-500 bg-emerald-500/10',
                'sort_order' => 4,
                'is_active' => true
            ]
        ];

        foreach ($stats as $stat) {
            Statistic::create($stat);
        }
    }
}
