<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class TeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                'name' => 'Dra. Milder Zanabria Ortega',
                'role' => 'Directora',
                'department' => 'Dirección de Proyección Social y Extensión Cultural',
                'initials' => 'MZ',
                'image_path' => 'https://cdn.phototourl.com/free/2026-07-08-0148c525-1fa2-46ce-8343-d43e2fd7c5ca.png',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'name' => 'M.Sc. Wilkerson Palza Meza',
                'role' => 'Jefe de Sub Unidad',
                'department' => 'Sub Unidad de Proyección Social y Extensión Universitaria',
                'initials' => 'WP',
                'image_path' => 'https://cdn.phototourl.com/free/2026-07-09-8662ddab-51eb-4547-91ce-de75938f1053.png',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Ing. Yumy Romero Talavera',
                'role' => 'Jefa de Sub Unidad',
                'department' => 'Sub Unidad de Seguimiento y Desarrollo del Graduado',
                'initials' => 'YR',
                'image_path' => 'https://cdn.phototourl.com/free/2026-07-09-a6257f35-bbb0-483f-909e-18f184a22061.png',
                'sort_order' => 3,
                'is_active' => true
            ],
            [
                'name' => 'M.Sc. Marco Vera Zuñiga',
                'role' => 'Jefe de Sub Unidad',
                'department' => 'Sub Unidad de Gestión Ambiental',
                'initials' => 'MV',
                'image_path' => 'https://cdn.phototourl.com/free/2026-07-08-5a3b67b1-4af0-4dcb-9f3f-b6b2d73858dc.png',
                'sort_order' => 4,
                'is_active' => true
            ]
        ];

        foreach ($members as $member) {
            TeamMember::create($member);
        }
    }
}
