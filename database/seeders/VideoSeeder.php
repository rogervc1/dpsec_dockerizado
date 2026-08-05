<?php

namespace Database\Seeders;

use App\Models\Video;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    public function run(): void
    {
        $videos = [
            [
                'title' => 'Danza Originaria | Chunchos de Esquilaya | Educación Primaria UNA Puno',
                'description' => 'Chunchos de Esquilaya, Danza originaria de Puno presentado por la Escuela Profesional de Educación Primaria en el Festival del Folklore de la Universidad Nacional del Altiplano.',
                'youtube_url' => 'https://www.youtube.com/watch?v=t-jVFZWDpqU',
                'embed_url' => 'https://www.youtube.com/embed/t-jVFZWDpqU',
                'thumbnail_url' => 'https://img.youtube.com/vi/t-jVFZWDpqU/0.jpg',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'title' => 'Danza Originaria | Wifala de San Antonio de Putina | Ing. Agronómica UNA Puno',
                'description' => 'Wifala de San Antonio de Putina, Danza originaria de Puno presentado por la Escuela Profesional de Ingeniería Agronómica en el Festival del Folklore de la Universidad Nacional del Altiplano.',
                'youtube_url' => 'https://www.youtube.com/watch?v=lkkRJhGqoQI',
                'embed_url' => 'https://youtube.com/embed/lkkRJhGqoQI',
                'thumbnail_url' => 'https://img.youtube.com/vi/lkkRJhGqoQI/0.jpg',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'title' => 'Bajada del Arco - Estudiantina Unificada de la UNA Puno. 2018',
                'description' => 'Concierto de la Estudiantina Unificada de la Universidad Nacional del Altiplano de Puno 2018. Lugar: Plaza de Armas de la ciudad de Puno',
                'youtube_url' => 'https://www.youtube.com/watch?v=xKwZOed6a7o',
                'embed_url' => 'https://youtube.com/embed/xKwZOed6a7o',
                'thumbnail_url' => 'https://img.youtube.com/vi/xKwZOed6a7o/0.jpg',
                'sort_order' => 3,
                'is_active' => true
            ]
        ];

        foreach ($videos as $video) {
            Video::create($video);
        }
    }
}
