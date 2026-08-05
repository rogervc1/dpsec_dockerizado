<?php

namespace Database\Seeders;

use App\Models\SubUnit;
use Illuminate\Database\Seeder;

class SubUnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            [
                'name' => 'Proyección Social y Extensión Cultural',
                'description' => 'Coordinación de actividades comunitarias y extensión de la universidad.',
                'href' => '/proyeccion-social',
                'is_external' => false,
                'logo_path' => 'https://cdn.phototourl.com/free/2026-07-31-f705bacb-02f5-4ea3-aeed-7e4e724a1d9b.png',
                'fb_url' => 'https://www.facebook.com/p/Direcci%C3%B3n-de-Proyecci%C3%B3n-Social-y-Extensi%C3%B3n-Cultural-UNA-Puno-100071137256988/',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'name' => 'Gestión Ambiental',
                'description' => 'Oficina encargada de la sostenibilidad y ecoeficiencia de la UNA Puno.',
                'href' => 'https://gestionambiental.unap.edu.pe',
                'is_external' => true,
                'logo_path' => 'https://cdn.phototourl.com/free/2026-07-31-466e4242-9697-4d02-a2a8-8bb38185b202.jpg',
                'fb_url' => 'https://www.facebook.com/p/Gesti%C3%B3n-Ambiental-UNA-PUNO-Oficial-61552848737780/',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Seguimiento al Graduado',
                'description' => 'Servicios de bolsa de trabajo y vinculación con egresados.',
                'href' => '/seguimiento-graduado',
                'is_external' => false,
                'logo_path' => 'https://cdn.phototourl.com/free/2026-07-31-aaa207df-3d13-45da-8947-299c143f1f7b.jpg',
                'fb_url' => 'https://www.facebook.com/p/Egresados-y-Graduados-UNA-Puno-100092995523250/',
                'sort_order' => 3,
                'is_active' => true
            ]
        ];

        foreach ($units as $unit) {
            SubUnit::create($unit);
        }
    }
}
