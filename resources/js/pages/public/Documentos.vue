<script setup lang="ts">
import { 
    Search, 
    Download, 
    ExternalLink, 
    Calendar, 
    Tag, 
    HelpCircle,
    Info
} from '@lucide/vue';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import PublicLayout from '@/layouts/PublicLayout.vue';

const props = defineProps<{
    documents?: any[];
    sections?: any[];
}>();

const search = ref('');
const activeCategory = ref('Todos');

const categories = ['Todos', 'Resoluciones', 'Directivas', 'Reglamentos', 'Guías y Formatos'];

// Dynamic documents from DB with fallback
const documents = computed(() => {
    if (props.documents && props.documents.length > 0) {
        return props.documents.map(d => ({
            id: d.id,
            title: d.title,
            code: d.code,
            category: d.category,
            date: d.published_date ? new Date(d.published_date).toLocaleDateString('es-PE', { day: 'numeric', month: 'short', year: 'numeric' }) : '',
            size: d.file_size || 'N/A',
            description: d.description,
            file_path: d.file_path ? (d.file_path.startsWith('http') ? d.file_path : (d.file_path.startsWith('/') ? d.file_path : '/storage/' + d.file_path)) : '#'
        }));
    }

    return [
        {
            id: 1,
            title: 'Directiva N° 004-2026-DPESEC: Disposiciones para Proyectos de Proyección Social y Extensión Cultural',
            code: 'DIR-004-2026-DPESEC',
            category: 'Directivas',
            date: '15 Ene 2026',
            size: '1.8 MB',
            description: 'Establece los lineamientos técnicos, metodológicos y plazos para la formulación, registro, ejecución y evaluación de proyectos sociales.',
            file_path: '#'
        },
        {
            id: 2,
            title: 'Resolución Rectoral N° 1024-2026-R-UNAP: Aprobación del Reglamento General de Extensión Universitaria',
            code: 'RR-1024-2026-R-UNAP',
            category: 'Resoluciones',
            date: '04 Mar 2026',
            size: '2.4 MB',
            description: 'Ratifica el estatuto actualizado y el nuevo reglamento de proyección y extensión, adaptado a la ley universitaria vigente.',
            file_path: '#'
        },
        {
            id: 3,
            title: 'Guía Práctica para la Redacción de Informes Finales de Proyección Social',
            code: 'GUIA-01-2026-DPESEC',
            category: 'Guías y Formatos',
            date: '10 May 2026',
            size: '950 KB',
            description: 'Manual paso a paso que describe el formato requerido para la presentación de los informes de impacto y beneficiarios.',
            file_path: '#'
        },
        {
            id: 4,
            title: 'Formato F-01: Solicitud de Registro de Proyecto de Voluntariado Universitario',
            code: 'FORM-F01-VOL',
            category: 'Guías y Formatos',
            date: '12 Ene 2026',
            size: '120 KB',
            description: 'Ficha obligatoria a presentar por el docente coordinador para inscribir proyectos de voluntariado ante la DPESEC.',
            file_path: '#'
        },
        {
            id: 5,
            title: 'Resolución Rectoral N° 0512-2026-R-UNAP: Acreditación de Horas del Voluntariado Ambiental 2025-II',
            code: 'RR-0512-2026-R-UNAP',
            category: 'Resoluciones',
            date: '28 Feb 2026',
            size: '3.1 MB',
            description: 'Resolución rectoral que formaliza el reconocimiento de horas académicas a los estudiantes participantes del ciclo anterior.',
            file_path: '#'
        },
        {
            id: 6,
            title: 'Reglamento Interno de Funciones de la Dirección de Proyección Social (ROF)',
            code: 'REG-ROF-DPESEC',
            category: 'Reglamentos',
            date: '20 Dic 2025',
            size: '4.2 MB',
            description: 'Estructura orgánica, deberes y atribuciones de la dirección principal y sus respectivas SubUnidades administrativas.',
            file_path: '#'
        }
    ];
});

const filteredDocuments = computed(() => {
    return documents.value.filter(doc => {
        const matchesSearch = doc.title.toLowerCase().includes(search.value.toLowerCase()) || 
                              doc.code.toLowerCase().includes(search.value.toLowerCase()) || 
                              doc.description.toLowerCase().includes(search.value.toLowerCase());
        const matchesCategory = activeCategory.value === 'Todos' || doc.category === activeCategory.value;

        return matchesSearch && matchesCategory;
    });
});
</script>

<template>
    <PublicLayout title="Documentos de Gestión">
        <!-- Hero Header -->
        <section 
            class="relative h-[65vh] min-h-[260px] flex items-center overflow-hidden bg-cover bg-center text-white"
            style="background-image: url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=1600&q=80');"
        >
            <!-- Gradient Overlay for readability -->
            <div class="absolute inset-0 bg-gradient-to-r from-neutral-950/90 via-neutral-950/70 to-transparent z-10"></div>
            
            <div class="max-w-7xl mx-auto w-full px-6 lg:px-8 text-left relative z-20 space-y-3">
                <span class="text-xs font-bold uppercase tracking-widest text-blue-400">Normativa y Transparencia</span>
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Documentos de Gestión</h1>
                <p class="text-xs md:text-sm text-white/80 max-w-3xl leading-relaxed">
                    Consulta y descarga reglamentos, resoluciones, directivas vigentes y los formatos oficiales requeridos para las actividades de proyección social de la UNA Puno.
                </p>
            </div>
        </section>

        <!-- Main Content -->
        <section class="py-16 max-w-7xl mx-auto px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Filters Sidebar -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Search bar -->
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 shadow-xs space-y-4 text-left">
                    <h3 class="font-bold text-sm uppercase tracking-wider text-neutral-800 dark:text-neutral-200 flex items-center gap-1.5">
                        <Search class="size-4 text-indigo-500" />
                        Buscar Documento
                    </h3>
                    <div class="relative">
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Ej. DIR-004..." 
                            class="w-full text-sm pl-9 pr-4 py-2 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <Search class="absolute left-3 top-3 size-4 text-neutral-400" />
                    </div>
                </div>

                <!-- Categories -->
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 shadow-xs space-y-4 text-left">
                    <h3 class="font-bold text-sm uppercase tracking-wider text-neutral-800 dark:text-neutral-200 flex items-center gap-1.5">
                        <Tag class="size-4 text-indigo-500" />
                        Tipo de Documento
                    </h3>
                    <div class="flex flex-col gap-2">
                        <button 
                            v-for="cat in categories" 
                            :key="cat" 
                            @click="activeCategory = cat"
                            class="text-xs text-left px-3 py-2 rounded-lg transition-colors cursor-pointer"
                            :class="activeCategory === cat ? 'bg-indigo-600 text-white font-bold' : 'hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400'"
                        >
                            {{ cat }}
                        </button>
                    </div>
                </div>

                <!-- Info Help Card -->
                <div class="p-6 rounded-2xl bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 text-left space-y-4">
                    <h4 class="font-bold text-sm text-neutral-950 dark:text-white flex items-center gap-1.5">
                        <HelpCircle class="size-4.5 text-indigo-500" />
                        ¿Cómo enviar?
                    </h4>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed">
                        Los informes firmados y planes de trabajo deben ser subidos digitalmente por el docente coordinador a la Intranet, o presentados físicamente en Mesa de Partes en horario de oficina.
                    </p>
                    <a href="https://www.unap.edu.pe" target="_blank" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-bold flex items-center gap-1">
                        Mesa de Partes Virtual
                        <ExternalLink class="size-3" />
                    </a>
                </div>
            </div>

            <!-- Documents Table/List -->
            <div class="lg:col-span-9 space-y-6 text-left">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-extrabold text-neutral-950 dark:text-white">
                        Documentos Registrados
                        <span class="text-xs px-2 py-0.5 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-500 font-mono ml-2">{{ filteredDocuments.length }}</span>
                    </h2>
                </div>

                <div v-if="filteredDocuments.length === 0" class="p-12 text-center border border-dashed border-neutral-300 dark:border-neutral-800 rounded-2xl text-neutral-500 bg-white dark:bg-neutral-900/40">
                    <Info class="size-12 mx-auto mb-4 text-neutral-300" />
                    <p class="font-semibold">No se encontraron documentos con esos criterios</p>
                    <p class="text-xs mt-1">Intenta cambiar el término de búsqueda o la categoría seleccionada.</p>
                </div>

                <div v-else class="space-y-4">
                    <div 
                        v-for="doc in filteredDocuments" 
                        :key="doc.id" 
                        class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 shadow-xs hover:shadow-md transition-shadow flex flex-col md:flex-row md:items-center justify-between gap-6"
                    >
                        <div class="space-y-2 max-w-2xl">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                                    {{ doc.category }}
                                </span>
                                <span class="text-[10px] text-neutral-400 font-mono font-medium">{{ doc.code }}</span>
                            </div>
                            
                            <h3 class="font-bold text-base text-neutral-950 dark:text-white leading-snug">
                                {{ doc.title }}
                            </h3>
                            
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed">
                                {{ doc.description }}
                            </p>

                            <div class="flex items-center gap-3 text-xs text-neutral-400 pt-1">
                                <span class="flex items-center gap-1">
                                    <Calendar class="size-3.5" />
                                    {{ doc.date }}
                                </span>
                                <span class="w-1 h-1 rounded-full bg-neutral-300 dark:bg-neutral-700"></span>
                                <span>Tamaño: {{ doc.size }}</span>
                            </div>
                        </div>

                        <!-- Download button mock -->
                        <div class="shrink-0 flex items-center">
                            <a :href="doc.file_path" download target="_blank" class="w-full md:w-auto">
                                <Button class="rounded-xl bg-neutral-950 hover:bg-neutral-900 dark:bg-white dark:text-neutral-950 dark:hover:bg-neutral-100 flex items-center gap-2 w-full md:w-auto px-5 cursor-pointer">
                                    <Download class="size-4" />
                                    Descargar PDF
                                </Button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </PublicLayout>
</template>
