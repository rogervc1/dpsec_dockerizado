<?php

namespace Database\Seeders;

use App\Models\PageSection;
use Illuminate\Database\Seeder;

class PageSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            // Home Page
            [
                'page_slug' => 'home',
                'section_key' => 'hero',
                'eyebrow' => 'Compromiso Social y Cultural',
                'title' => 'Conectando la Universidad con nuestra Sociedad',
                'description' => 'La Dirección de Proyección Social y Extensión Cultural de la UNA Puno lidera programas integradores, voluntariados, preservación del patrimonio cultural altiplánico y proyectos sostenibles para el desarrollo regional.',
                'background_image' => null,
                'extra_data' => null
            ],

            // Nosotros Page
            [
                'page_slug' => 'nosotros',
                'section_key' => 'hero',
                'eyebrow' => '¿Quiénes Somos?',
                'title' => 'Nosotros',
                'description' => 'Conoce la misión, los valores y el equipo de profesionales que lidera la Dirección de Proyección Social y Extensión Cultural en su compromiso de vincular a la Universidad Nacional del Altiplano con el desarrollo de la región.',
                'background_image' => 'https://scontent.flim26-1.fna.fbcdn.net/v/t39.30808-6/684209047_988255950222304_4304482649156688022_n.jpg?stp=dst-jpg_tt6&cstp=mx1600x1066&ctp=s1600x1066&_nc_cat=100&ccb=1-7&_nc_sid=127cfc&_nc_eui2=AeH8pRbDJterI_4kfAJrfhEth_fU78i5DjyH99TvyLkOPExIu168MRbh304r1boYntcawbcIzVWA6jNSHE0qw7vK&_nc_ohc=DSrFJcjrEkQQ7kNvwGyIDgy&_nc_oc=AdoCBBZlxeANzXpp9oF0Vksjh51HGnJ5Q7ldhYgtogCsyvNGr8IN7J-zKihz_V_BrE0&_nc_zt=23&_nc_ht=scontent.flim26-1.fna&_nc_gid=bNo6wW1ZIjfLU4SaWGl_xA&_nc_ss=7b2a8&oh=00_AQGJJ4rrIYMmva01quxB4XyQU25zJpRuvYgP5JOkZ2N_Hg&oe=6A72C0C9',
                'extra_data' => null
            ],
            [
                'page_slug' => 'nosotros',
                'section_key' => 'mission',
                'eyebrow' => 'Propósito Institucional',
                'title' => 'Nuestra Misión',
                'description' => 'Definimos el norte de nuestro trabajo en base a la responsabilidad social universitaria, promoviendo el desarrollo de capacidades humanas en estrecha relación con las comunidades locales y la herencia cultural altiplánica.',
                'background_image' => null,
                'extra_data' => [
                    'blockquote' => 'Formar profesionales y posgraduados competitivos, con capacidad de investigación, emprendimiento, la responsabilidad social e identidad cultural para contribuir al desarrollo humano y desarrollo sostenible de la región y del país.',
                    'attribution' => 'Estatuto UNA Puno'
                ]
            ],
            [
                'page_slug' => 'nosotros',
                'section_key' => 'team_intro',
                'eyebrow' => 'Liderazgo y Gestión',
                'title' => 'Nuestro Equipo',
                'description' => 'Contamos con un equipo multidisciplinario de profesionales apasionados por el cambio social, comprometidos con nuestros valores y enfocados en lograr resultados transformadores.',
                'background_image' => null,
                'extra_data' => null
            ],
            [
                'page_slug' => 'nosotros',
                'section_key' => 'objectives_intro',
                'eyebrow' => 'Plan de Desarrollo',
                'title' => 'Objetivos Estratégicos',
                'description' => 'Trazamos metas específicas a corto y mediano plazo para garantizar que nuestra labor tenga un impacto medible, estructurado y sostenible en la sociedad puneña.',
                'background_image' => null,
                'extra_data' => null
            ],
            [
                'page_slug' => 'nosotros',
                'section_key' => 'values_intro',
                'eyebrow' => 'Filosofía de Trabajo',
                'title' => 'Valores Institucionales',
                'description' => 'Nuestra cultura organizacional se cimenta sobre principios éticos que guían cada proyecto, voluntariado y expresión cultural.',
                'background_image' => null,
                'extra_data' => null
            ],

            // Proyección Social Page
            [
                'page_slug' => 'proyeccion-social',
                'section_key' => 'hero',
                'eyebrow' => 'Oficina Principal',
                'title' => 'Proyección Social y Extensión Cultural',
                'description' => 'Planificamos, organizamos y evaluamos las acciones de proyección social y extensión cultural de las Escuelas Profesionales de la UNA Puno para lograr el desarrollo integral y sostenido de la sociedad.',
                'background_image' => 'https://scontent.flim26-1.fna.fbcdn.net/v/t39.30808-6/684209047_988255950222304_4304482649156688022_n.jpg?stp=dst-jpg_tt6&cstp=mx1600x1066&ctp=s1600x1066&_nc_cat=100&ccb=1-7&_nc_sid=127cfc&_nc_eui2=AeH8pRbDJterI_4kfAJrfhEth_fU78i5DjyH99TvyLkOPExIu168MRbh304r1boYntcawbcIzVWA6jNSHE0qw7vK&_nc_ohc=DSrFJcjrEkQQ7kNvwGyIDgy&_nc_oc=AdoCBBZlxeANzXpp9oF0Vksjh51HGnJ5Q7ldhYgtogCsyvNGr8IN7J-zKihz_V_BrE0&_nc_zt=23&_nc_ht=scontent.flim26-1.fna&_nc_gid=bNo6wW1ZIjfLU4SaWGl_xA&_nc_ss=7b2a8&oh=00_AQGJJ4rrIYMmva01quxB4XyQU25zJpRuvYgP5JOkZ2N_Hg&oe=6A72C0C9',
                'extra_data' => null
            ],

            // Eventos Page
            [
                'page_slug' => 'eventos',
                'section_key' => 'hero',
                'eyebrow' => 'Agenda Institucional',
                'title' => 'Eventos',
                'description' => 'Sigue nuestro cronograma de festivales culturales, campañas de salud descentralizadas, convocatorias a voluntariados ecológicos y seminarios académicos de proyección social.',
                'background_image' => 'https://scontent.flim26-1.fna.fbcdn.net/v/t39.30808-6/684209047_988255950222304_4304482649156688022_n.jpg?stp=dst-jpg_tt6&cstp=mx1600x1066&ctp=s1600x1066&_nc_cat=100&ccb=1-7&_nc_sid=127cfc&_nc_eui2=AeH8pRbDJterI_4kfAJrfhEth_fU78i5DjyH99TvyLkOPExIu168MRbh304r1boYntcawbcIzVWA6jNSHE0qw7vK&_nc_ohc=DSrFJcjrEkQQ7kNvwGyIDgy&_nc_oc=AdoCBBZlxeANzXpp9oF0Vksjh51HGnJ5Q7ldhYgtogCsyvNGr8IN7J-zKihz_V_BrE0&_nc_zt=23&_nc_ht=scontent.flim26-1.fna&_nc_gid=bNo6wW1ZIjfLU4SaWGl_xA&_nc_ss=7b2a8&oh=00_AQGJJ4rrIYMmva01quxB4XyQU25zJpRuvYgP5JOkZ2N_Hg&oe=6A72C0C9',
                'extra_data' => null
            ],

            // Documentos Page
            [
                'page_slug' => 'documentos',
                'section_key' => 'hero',
                'eyebrow' => 'Normativa y Transparencia',
                'title' => 'Documentos de Gestión',
                'description' => 'Consulta y descarga reglamentos, resoluciones, directivas vigentes y los formatos oficiales requeridos para las actividades de proyección social de la UNA Puno.',
                'background_image' => 'https://scontent.flim26-1.fna.fbcdn.net/v/t39.30808-6/684209047_988255950222304_4304482649156688022_n.jpg?stp=dst-jpg_tt6&cstp=mx1600x1066&ctp=s1600x1066&_nc_cat=100&ccb=1-7&_nc_sid=127cfc&_nc_eui2=AeH8pRbDJterI_4kfAJrfhEth_fU78i5DjyH99TvyLkOPExIu168MRbh304r1boYntcawbcIzVWA6jNSHE0qw7vK&_nc_ohc=DSrFJcjrEkQQ7kNvwGyIDgy&_nc_oc=AdoCBBZlxeANzXpp9oF0Vksjh51HGnJ5Q7ldhYgtogCsyvNGr8IN7J-zKihz_V_BrE0&_nc_zt=23&_nc_ht=scontent.flim26-1.fna&_nc_gid=bNo6wW1ZIjfLU4SaWGl_xA&_nc_ss=7b2a8&oh=00_AQGJJ4rrIYMmva01quxB4XyQU25zJpRuvYgP5JOkZ2N_Hg&oe=6A72C0C9',
                'extra_data' => null
            ],

            // Seguimiento Graduado Page
            [
                'page_slug' => 'seguimiento-graduado',
                'section_key' => 'hero',
                'eyebrow' => 'Sub Unidad',
                'title' => 'Seguimiento al Graduado',
                'description' => 'Vinculación profesional y acompañamiento al egresado de la Universidad Nacional del Altiplano en su inserción al mercado laboral.',
                'background_image' => 'https://scontent.flim26-1.fna.fbcdn.net/v/t39.30808-6/684209047_988255950222304_4304482649156688022_n.jpg?stp=dst-jpg_tt6&cstp=mx1600x1066&ctp=s1600x1066&_nc_cat=100&ccb=1-7&_nc_sid=127cfc&_nc_eui2=AeH8pRbDJterI_4kfAJrfhEth_fU78i5DjyH99TvyLkOPExIu168MRbh304r1boYntcawbcIzVWA6jNSHE0qw7vK&_nc_ohc=DSrFJcjrEkQQ7kNvwGyIDgy&_nc_oc=AdoCBBZlxeANzXpp9oF0Vksjh51HGnJ5Q7ldhYgtogCsyvNGr8IN7J-zKihz_V_BrE0&_nc_zt=23&_nc_ht=scontent.flim26-1.fna&_nc_gid=bNo6wW1ZIjfLU4SaWGl_xA&_nc_ss=7b2a8&oh=00_AQGJJ4rrIYMmva01quxB4XyQU25zJpRuvYgP5JOkZ2N_Hg&oe=6A72C0C9',
                'extra_data' => null
            ],
            [
                'page_slug' => 'seguimiento-graduado',
                'section_key' => 'portal',
                'eyebrow' => 'Módulo en Desarrollo',
                'title' => 'Portal del Egresado (Próximamente)',
                'description' => 'Estamos diseñando una plataforma digital innovadora orientada a nuestros egresados de la Universidad Nacional del Altiplano. Pronto podrás acceder a la Bolsa de Trabajo UNA Puno, registrar tu perfil profesional, rellenar encuestas de empleabilidad y participar de ferias de empleo exclusivas.',
                'background_image' => null,
                'extra_data' => null
            ]
        ];

        foreach ($sections as $section) {
            PageSection::create($section);
        }
    }
}
