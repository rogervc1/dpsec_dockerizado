<script setup lang="ts">
import { 
    Search, 
    Filter, 
    Calendar, 
    MapPin, 
    Users, 
    UserCheck,
    CheckCircle,
    Info,
    HelpCircle,
    X,
    ChevronRight
} from '@lucide/vue';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { eventsList } from '@/lib/eventsData';

const props = defineProps<{
    events?: any[];
    faqs?: any[];
    sections?: any[];
}>();

// Modal states
const selectedActivity = ref<any>(null);
const isModalOpen = ref(false);

const openActivityModal = (activity: any) => {
    selectedActivity.value = activity;
    isModalOpen.value = true;
};

const closeActivityModal = () => {
    isModalOpen.value = false;
};

const getEventImages = (event: any): string[] => {
    const images = Array.isArray(event.event_images) ? event.event_images : [];

    return [...images, event.image].filter(Boolean).filter((image, index, list) => list.indexOf(image) === index);
};

const galleryTrackStyle = (event: any) => {
    const images = getEventImages(event);
    const count = Math.max(images.length, 1);

    return {
        width: `${count * 100}%`,
        animationDuration: `${count * 4}s`,
        '--gallery-item-width': `${100 / count}%`,
        '--gallery-shift': `-${((count - 1) / count) * 100}%`,
    };
};

// Mock Categories
const categories = ['Todos', 'Proyección Social', 'Extensión Cultural', 'Voluntariado Universitario'];

// Selected filters
const search = ref('');
const activeCategory = ref('Todos');
const activeStatus = ref('Todos');

// Filter shared activities specifically for Proyección Social (real DB with fallback)
const activities = computed(() => {
    if (props.events && props.events.length > 0) {
        return props.events.map(e => ({
            ...e,
            image: e.image_path,
            event_images: e.event_images ?? [],
            date: e.event_date ? new Date(e.event_date).toLocaleDateString('es-PE', { day: 'numeric', month: 'long', year: 'numeric' }) : '',
            fbLink: e.fb_link,
            isProyeccionSocial: e.is_proyeccion_social
        }));
    }

    return eventsList.filter(e => e.isProyeccionSocial);
});

// Computed list based on search and filters
const filteredActivities = computed(() => {
    return activities.value.filter(activity => {
        const matchesSearch = activity.title.toLowerCase().includes(search.value.toLowerCase()) || 
                              activity.description.toLowerCase().includes(search.value.toLowerCase()) ||
                              activity.location.toLowerCase().includes(search.value.toLowerCase());
        const matchesCategory = activeCategory.value === 'Todos' || activity.category === activeCategory.value;
        const matchesStatus = activeStatus.value === 'Todos' || activity.status === activeStatus.value;

        return matchesSearch && matchesCategory && matchesStatus;
    });
});

// FAQ list from DB with fallback
const faqs = computed(() => {
    if (props.faqs && props.faqs.length > 0) {
        return props.faqs.map(f => ({
            question: f.question,
            answer: f.answer
        }));
    }

    return [
        {
            question: '¿Quiénes deben realizar Proyección Social en la UNA Puno?',
            answer: 'De acuerdo al estatuto universitario de la UNA Puno, todos los estudiantes de pregrado deben cumplir con horas de proyección social acreditadas por su respectiva Facultad antes del egreso.'
        },
        {
            question: '¿Cómo inscribirse en el Programa de Voluntariado Universitario?',
            answer: 'Las convocatorias se abren semestralmente a través de esta web y de la página oficial de Facebook DPESEC. Solo requieres estar matriculado en el semestre académico correspondiente.'
        },
        {
            question: '¿Cómo se registra y acredita un proyecto social independiente?',
            answer: 'El docente coordinador debe presentar la propuesta del proyecto mediante Mesa de Partes dirigida a la DPESEC, adjuntando el plan de trabajo con la lista de alumnos participantes y presupuesto sustentado.'
        }
    ];
});
</script>

<template>
    <PublicLayout title="Proyección Social y Extensión Cultural">
        <!-- Hero Header -->
        <section 
            class="relative h-[60vh] min-h-[350px] flex items-end pb-8 md:items-center md:pb-0 overflow-hidden bg-cover bg-center text-white"
            style="background-image: url('https://cdn.phototourl.com/free/2026-08-05-2af7bdd7-7eb6-4cca-9c51-1fc58cff7eeb.jpg');"
        >
            <!-- Gradient Overlay (Bottom gradient on mobile, side gradient on desktop) -->
            <div class="absolute inset-0 bg-gradient-to-t from-neutral-950 via-neutral-950/80 to-transparent md:bg-gradient-to-r md:from-neutral-950/90 md:via-neutral-950/70 md:to-transparent z-10"></div>
            
            <div class="max-w-7xl mx-auto w-full px-6 lg:px-8 text-left relative z-20 space-y-2 md:space-y-3">
                <span class="text-xs font-bold uppercase tracking-widest text-blue-400">Oficina Principal</span>
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Proyección Social y Extensión Cultural</h1>
                <p class="text-xs md:text-sm text-white/80 max-w-3xl leading-relaxed">
                    Planificamos, organizamos y evaluamos las acciones de proyección social y extensión cultural de las Escuelas Profesionales de la UNA Puno para lograr el desarrollo integral y sostenido de la sociedad.
                </p>
            </div>
        </section>

        <!-- Main Content Section with Filters and Grid -->
        <section class="py-16 max-w-7xl mx-auto px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Sidebar Filters -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Search -->
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 shadow-xs space-y-4 text-left">
                    <h3 class="font-bold text-sm uppercase tracking-wider text-neutral-800 dark:text-neutral-200 flex items-center gap-1.5">
                        <Search class="size-4 text-indigo-500" />
                        Buscar
                    </h3>
                    <div class="relative">
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Buscar actividad o lugar..." 
                            class="w-full text-sm pl-9 pr-4 py-2 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <Search class="absolute left-3 top-3 size-4 text-neutral-400" />
                    </div>
                </div>

                <!-- Category Filters -->
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 shadow-xs space-y-4 text-left">
                    <h3 class="font-bold text-sm uppercase tracking-wider text-neutral-800 dark:text-neutral-200 flex items-center gap-1.5">
                        <Filter class="size-4 text-indigo-500" />
                        Categorías
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

                <!-- Status Filters -->
                <div class="p-6 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 shadow-xs space-y-4 text-left">
                    <h3 class="font-bold text-sm uppercase tracking-wider text-neutral-800 dark:text-neutral-200 flex items-center gap-1.5">
                        <CheckCircle class="size-4 text-indigo-500" />
                        Estado de Proyecto
                    </h3>
                    <div class="flex flex-col gap-2">
                        <button 
                            v-for="status in ['Todos', 'En Curso', 'Planificado', 'Concluido']" 
                            :key="status" 
                            @click="activeStatus = status"
                            class="text-xs text-left px-3 py-2 rounded-lg transition-colors cursor-pointer"
                            :class="activeStatus === status ? 'bg-indigo-600 text-white font-bold' : 'hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400'"
                        >
                            {{ status }}
                        </button>
                    </div>
                </div>

                <!-- Action Card (Form mock trigger) -->
                <!-- <div class="p-6 rounded-2xl bg-gradient-to-br from-indigo-800 to-indigo-950 text-white shadow-md text-left space-y-4">
                    <h4 class="font-bold text-base">¿Tienes una propuesta?</h4>
                    <p class="text-xs text-white/80 leading-relaxed">
                        Si eres docente de la UNA Puno y quieres registrar un proyecto o voluntariado social para este semestre, puedes iniciar el trámite aquí.
                    </p>
                    <Button class="w-full rounded-xl bg-white text-indigo-950 hover:bg-neutral-100 font-bold text-xs flex items-center justify-center gap-1.5 py-4 cursor-pointer">
                        <PlusCircle class="size-4 text-indigo-600" />
                        Registrar Proyecto
                    </Button>
                </div> -->
            </div>

            <!-- Activities Listings Grid -->
            <div class="lg:col-span-9 space-y-8">
                
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-extrabold text-neutral-900 dark:text-white flex items-center gap-2">
                        Proyectos y Programas
                        <span class="text-xs px-2 py-0.5 rounded-full bg-neutral-200 dark:bg-neutral-800 text-neutral-500 font-mono">{{ filteredActivities.length }}</span>
                    </h2>
                </div>

                <div v-if="filteredActivities.length === 0" class="p-12 text-center border border-dashed border-neutral-300 dark:border-neutral-800 rounded-2xl text-neutral-500">
                    <Info class="size-12 mx-auto mb-4 text-neutral-300" />
                    <p class="font-semibold">No se encontraron actividades con los filtros aplicados</p>
                    <p class="text-xs mt-1">Prueba a buscar otro término o cambiar de categoría.</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div 
                        v-for="act in filteredActivities" 
                        :key="act.id" 
                        class="group relative flex flex-col bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer"
                        @click="openActivityModal(act)"
                    >
                        <!-- Image Section -->
                        <div class="h-52 relative overflow-hidden bg-neutral-150 dark:bg-neutral-950 shrink-0">
                            <div
                                class="flex h-full"
                                :class="{ 'event-gallery-track': getEventImages(act).length > 1 }"
                                :style="galleryTrackStyle(act)"
                            >
                                <div
                                    v-for="image in getEventImages(act)"
                                    :key="image"
                                    class="h-full min-w-0 flex-1 bg-neutral-100 dark:bg-neutral-950"
                                >
                                    <img :src="image" :alt="act.title" class="w-full h-full object-cover" />
                                </div>
                            </div>
                            <!-- Top Badges -->
                            <div class="absolute top-4 left-4 z-20 flex gap-2 flex-wrap">
                                <span class="text-[9px] font-extrabold uppercase tracking-wider px-2.5 py-1.5 rounded-lg bg-white/95 dark:bg-neutral-950/95 text-indigo-600 dark:text-indigo-400 shadow-xs border border-neutral-200/30 dark:border-neutral-800/30">
                                    {{ act.category }}
                                </span>
                                <span class="text-[9px] font-extrabold uppercase tracking-wider px-2.5 py-1.5 rounded-lg text-white shadow-xs" :class="act.statusColor">
                                    {{ act.status }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Content Section -->
                        <div class="flex-grow p-6 flex flex-col justify-between text-left">
                            <div class="space-y-2">
                                <!-- Date & Location -->
                                <div class="flex items-center gap-2.5 text-[11px] text-neutral-500 dark:text-neutral-400 font-semibold">
                                    <span class="flex items-center gap-1">
                                        <Calendar class="size-3.5 text-indigo-600/70 dark:text-indigo-400/70" />
                                        {{ act.date }}
                                    </span>
                                    <span class="w-1 h-1 rounded-full bg-neutral-300 dark:bg-neutral-700"></span>
                                    <span class="flex items-center gap-1">
                                        <MapPin class="size-3.5 text-indigo-600/70 dark:text-indigo-400/70" />
                                        {{ act.location }}
                                    </span>
                                </div>
                                
                                <!-- Title -->
                                <h3 class="text-base font-extrabold text-neutral-900 dark:text-white leading-snug line-clamp-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                    {{ act.title }}
                                </h3>
                                
                                <!-- Description -->
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 line-clamp-2 leading-relaxed text-justify whitespace-pre-line font-normal">
                                    {{ act.description }}
                                </p>
                            </div>
                            
                            <!-- Divider and Read More -->
                            <div class="border-t border-neutral-100 dark:border-neutral-800/60 w-full pt-4 mt-5 flex items-center justify-between">
                                <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 group-hover:text-indigo-700 dark:group-hover:text-indigo-300 flex items-center gap-1 transition-colors">
                                    Leer más detalles
                                    <ChevronRight class="size-3.5" />
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ Section -->
                <div class="mt-16 bg-neutral-50/50 dark:bg-neutral-900/20 p-8 rounded-2xl border border-neutral-200/60 dark:border-neutral-800/60">
                    <div class="text-left mb-6">
                        <h3 class="text-lg font-bold text-neutral-950 dark:text-white flex items-center gap-1.5">
                            <HelpCircle class="size-5 text-indigo-500" />
                            Preguntas Frecuentes
                        </h3>
                        <p class="text-xs text-neutral-400 mt-1">Respuestas rápidas para estudiantes y docentes universitarios.</p>
                    </div>

                    <div class="space-y-4">
                        <div v-for="faq in faqs" :key="faq.question" class="text-left space-y-1.5">
                            <h4 class="font-semibold text-sm text-neutral-900 dark:text-white">{{ faq.question }}</h4>
                            <p class="text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed">{{ faq.answer }}</p>
                            <div class="h-px bg-neutral-200/50 dark:bg-neutral-800/50 pt-2"></div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- BEAUTIFUL RESPONSIVE MODAL FOR ACTIVITY DETAILS -->
        <Transition name="modal">
            <div 
                v-if="isModalOpen && selectedActivity" 
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-md p-4"
                @click.self="closeActivityModal"
            >
                <!-- Modal Panel -->
                <div class="relative w-full max-w-3xl bg-white dark:bg-neutral-900 rounded-3xl overflow-hidden shadow-2xl border border-neutral-200 dark:border-neutral-800 flex flex-col md:flex-row h-auto max-h-[90vh] md:h-[500px] text-left transform transition-all duration-300">
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 w-full h-full">
                        <!-- Left side: Image -->
                        <div class="md:col-span-5 relative h-[180px] sm:h-[220px] md:h-full overflow-hidden bg-neutral-100 dark:bg-neutral-950 shrink-0">
                            <div
                                class="flex h-full"
                                :class="{ 'event-gallery-track': getEventImages(selectedActivity).length > 1 }"
                                :style="galleryTrackStyle(selectedActivity)"
                            >
                                <div
                                    v-for="image in getEventImages(selectedActivity)"
                                    :key="image"
                                    class="h-full w-full shrink-0 flex items-center justify-center"
                                >
                                    <img :src="image" :alt="selectedActivity.title" class="w-full h-full object-contain" />
                                </div>
                            </div>
                            <div class="absolute top-4 left-4 flex gap-1.5 flex-wrap">
                                <span class="text-[9px] font-bold uppercase tracking-wider px-2.5 py-1 rounded bg-indigo-600 text-white shadow-md">
                                    {{ selectedActivity.category }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Right side: Details -->
                        <div class="md:col-span-7 p-6 md:p-8 flex flex-col justify-between h-full overflow-y-auto max-h-[calc(90vh-180px)] sm:max-h-[calc(90vh-220px)] md:max-h-full">
                            <div class="space-y-4">
                                <!-- Header with status and Close button -->
                                <div class="flex items-center justify-between">
                                    <span 
                                        class="text-[10px] font-extrabold uppercase tracking-widest px-2.5 py-1 rounded-md"
                                        :class="selectedActivity.statusColor"
                                    >
                                        {{ selectedActivity.status }}
                                    </span>
                                    
                                    <!-- Close Button -->
                                    <button 
                                        @click="closeActivityModal" 
                                        class="size-8 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400 flex items-center justify-center hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors cursor-pointer"
                                    >
                                        <X class="size-4" />
                                    </button>
                                </div>

                                <!-- Title -->
                                <h2 class="text-base sm:text-lg md:text-xl font-black text-neutral-900 dark:text-white leading-tight">
                                    {{ selectedActivity.title }}
                                </h2>

                                <!-- Coordinator -->
                                <div class="flex flex-wrap items-center gap-1 text-[11px] sm:text-xs text-neutral-400">
                                    <span class="font-bold text-neutral-600 dark:text-neutral-300">Coordinador:</span>
                                    <span>{{ selectedActivity.coordinator }}</span>
                                </div>

                                <!-- Divider -->
                                <div class="border-t border-neutral-100 dark:border-neutral-800"></div>

                                <!-- Meta Grid (2 cols) -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                    <div class="flex items-center gap-2 text-neutral-600 dark:text-neutral-300 sm:col-span-2">
                                        <Calendar class="size-4 text-indigo-600 dark:text-indigo-400 shrink-0" />
                                        <span class="font-semibold">{{ selectedActivity.date }}</span>
                                    </div>
                                    <div class="flex items-start gap-2 text-neutral-600 dark:text-neutral-300 sm:col-span-2">
                                        <MapPin class="size-4 text-red-500 shrink-0 mt-0.5" />
                                        <span class="leading-tight">{{ selectedActivity.location }}</span>
                                    </div>
                                    <div class="flex items-start gap-2 text-neutral-600 dark:text-neutral-300 sm:col-span-2">
                                        <Users class="size-4 text-indigo-600 dark:text-indigo-400 shrink-0 mt-0.5" />
                                        <span class="leading-tight">Beneficiarios: {{ selectedActivity.beneficiaries }}</span>
                                    </div>
                                </div>

                                <!-- Divider -->
                                <div class="border-t border-neutral-100 dark:border-neutral-800"></div>

                                <!-- Description -->
                                <div class="space-y-1.5">
                                    <h4 class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider">Detalles del Proyecto</h4>
                                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed text-justify whitespace-pre-line">
                                        {{ selectedActivity.description }}
                                    </p>
                                </div>
                            </div>

                            <!-- Footer Actions -->
                            <div class="border-t border-neutral-100 dark:border-neutral-800/80 pt-4 mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <Button size="sm" class="w-full sm:w-auto rounded-xl bg-indigo-700 hover:bg-indigo-800 text-white text-xs font-semibold flex items-center justify-center gap-1.5 cursor-pointer" @click.stop>
                                    <UserCheck class="size-3.5" />
                                    Inscribirse/Participar
                                </Button>

                                <a 
                                    href="https://www.facebook.com/ProyeccionSocialUNAPuno" 
                                    target="_blank" 
                                    class="text-xs text-neutral-400 hover:text-blue-500 transition-colors flex items-center gap-1"
                                    @click.stop
                                >
                                    <svg class="size-4 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1V12h3l-.5 3h-2.5v6.8c4.56-.93 8-4.96 8-9.8z"/>
                                    </svg>
                                    Ver en Facebook
                                </a>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </Transition>

    </PublicLayout>
</template>

<style scoped>
/* Modal Transition Animations */
.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s ease;
}
.modal-enter-from, .modal-leave-to {
  opacity: 0;
}
.modal-enter-active .relative, .modal-leave-active .relative {
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease;
}
.modal-enter-from .relative, .modal-leave-to .relative {
  transform: scale(0.92) translateY(20px);
  opacity: 0;
}

.event-gallery-track {
  animation: event-gallery-slide linear infinite;
}

.event-gallery-track > div {
  flex: 0 0 var(--gallery-item-width);
}

@keyframes event-gallery-slide {
  0%, 20% {
    transform: translateX(0);
  }
  80%, 100% {
    transform: translateX(var(--gallery-shift));
  }
}
</style>
