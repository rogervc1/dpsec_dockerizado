<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Award,
    Calendar,
    Users,
    ArrowRight,
    FileText,
    Heart,
    Leaf,
    MapPin,
    FlameKindling,
    ChevronRight,
    TrendingUp,
    ExternalLink,
    X,
    UserCheck
} from '@lucide/vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Button } from '@/components/ui/button';
import PublicLayout from '@/layouts/PublicLayout.vue';

const props = defineProps<{
    events?: any[];
    documents?: any[];
    stats?: any[];
    videos?: any[];
    subUnits?: any[];
    sections?: any[];
}>();

// Map string icon names to Lucide components
const iconMap: Record<string, any> = {
    Award,
    Calendar,
    Users,
    ArrowRight,
    FileText,
    Heart,
    Leaf,
    MapPin,
    FlameKindling,
    TrendingUp,
    ExternalLink,
    X,
    UserCheck
};

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

// Real DB stats with fallback
const stats = computed(() => {
    if (props.stats && props.stats.length > 0) {
        return props.stats.map(s => ({
            label: s.label,
            value: s.value,
            description: s.description,
            icon: iconMap[s.icon_name] || Award,
            color: s.color_class || 'text-indigo-500 bg-indigo-500/10'
        }));
    }

    return [
        { label: 'Proyectos de Proyección', value: '184', description: 'Ejecutados este año', icon: Award, color: 'text-amber-500 bg-amber-500/10' },
        { label: 'Estudiantes Voluntarios', value: '2,450+', description: 'Participación activa', icon: Users, color: 'text-indigo-500 bg-indigo-500/10' },
        { label: 'Comunidades Beneficiadas', value: '45+', description: 'En toda la región Puno', icon: Heart, color: 'text-red-500 bg-red-500/10' },
        { label: 'Eventos Culturales', value: '38', description: 'Ciclos y festivales anuales', icon: FlameKindling, color: 'text-emerald-500 bg-emerald-500/10' }
    ];
});

// Map DB events to template-friendly format (Máximo 3 actividades)
const latestActivities = computed(() => (props.events ?? []).slice(0, 3).map(e => ({
    ...e,
    image: e.image_path,
    event_images: e.event_images ?? [],
    date: e.event_date ? new Date(e.event_date).toLocaleDateString('es-PE', { day: 'numeric', month: 'long', year: 'numeric' }) : '',
    fbLink: e.fb_link,
})));

// Real DB documents with fallback
const documents = computed(() => {
    if (props.documents && props.documents.length > 0) {
        return props.documents.map(d => ({
            id: d.id,
            title: d.title,
            code: d.code,
            category: d.category,
            type: d.category.endsWith('s') ? d.category.slice(0, -1) : d.category,
            date: d.published_date ? new Date(d.published_date).toLocaleDateString('es-PE', { day: 'numeric', month: 'short', year: 'numeric' }) : '',
            size: d.file_size || 'N/A',
            description: d.description,
            file_path: d.file_path ? (d.file_path.startsWith('http') ? d.file_path : '/storage/' + d.file_path) : '#'
        }));
    }

    return [
        { id: 1, title: 'Directiva N° 004-2026-DPESEC: Normas para Proyectos de Proyección Social', code: 'DIR-004-2026', date: '15 Ene 2026', type: 'Directiva', size: '1.8 MB', description: 'Establece los lineamientos técnicos para voluntariados y extensión cultural.', file_path: '/documentos' },
        { id: 2, title: 'Resolución Rectoral N° 1024-2026-R-UNAP: Aprobación del Calendario de Actividades Culturales', code: 'RR-1024-2026', date: '04 Mar 2026', type: 'Resolución', size: '2.4 MB', description: 'Ratifica las fechas de festivales y ciclos de danzas universitarias.', file_path: '/documentos' },
        { id: 3, title: 'Guía Metodológica para la Formulación de Informes de Extensión Universitaria', code: 'GUIA-01-2026', date: '10 May 2026', type: 'Guía', size: '950 KB', description: 'Formularios oficiales para documentar el impacto de proyectos sociales.', file_path: '/documentos' }
    ];
});

// Carousel Slides (from real events or static fallback)
const slides = computed(() => {
    const list = latestActivities.value.map(e => e.image).filter(Boolean);

    if (list.length > 0) {
return list;
}

    return [
        'https://scontent.fjul1-1.fna.fbcdn.net/v/t39.30808-6/599715893_884448530603047_8830935029040207180_n.jpg?stp=dst-jpg_tt6&cstp=mx2048x1365&ctp=s2048x1365&_nc_cat=105&ccb=1-7&_nc_sid=127cfc&_nc_eui2=AeHQTG8nj3AVQPEHA1wTgQHYKeP-NFCKFbkp4_40UIoVuSWAiJSKTpXbty2XufqHJ3TjfkBChQkMKeic-hENsQFS&_nc_ohc=y0aeGiZKoI8Q7kNvwHlWwdS&_nc_oc=AdrAcbj07hIEXl4hMGQ0WxmZ3uxiOjPk3rSDXBstr9iXtNUKrJ_uMeBgXLuI-L3q0iQ&_nc_zt=23&_nc_ht=scontent.fjul1-1.fna&_nc_gid=4KCvHQKz0MhLi4lQ3kNatQ&_nc_ss=7b2a8&oh=00_AQCPYaYVBsK7Adxqddq_DlU6zk0tr2nCG3amFSJjCR6x5g&oe=6A547587',
        'https://scontent.fjul1-1.fna.fbcdn.net/v/t39.30808-6/605296083_769669972809513_1888138256417761411_n.jpg?stp=dst-jpg_tt6&cstp=mx2048x1267&ctp=s2048x1267&_nc_cat=111&ccb=1-7&_nc_sid=127cfc&_nc_eui2=AeErCVDX-2bhd-oXj91oxi8uP3zmIbV9bRQ_fOYhtX1tFEc26QCfmlKGVmIt8eHzOj4lP3fH8TmfbOE-Qa7r3CBf&_nc_ohc=y4O89CSTX3kQ7kNvwPldty&_nc_oc=AdpXVqBXvCSHpZcxdtDOuOmzbcQxOARxVFWoV-b-eBj1cMdhr10LjIsIwnH7cQ57iro&_nc_zt=23&_nc_ht=scontent.flim20-1.fna&_nc_gid=A_T1_6T7cM_y5_u4_9f2_1&_nc_ss=7b290&oh=00_AQAIEfjqOqi6lNXRJGchTrJDiR7lgLTCn1Tsr0u-PBGCMQ&oe=6A55939C'
    ];
});

// Real DB subUnits with fallback
const subunitsFloating = computed(() => {
    if (props.subUnits && props.subUnits.length > 0) {
        return props.subUnits.map(s => ({
            name: s.name,
            fbUrl: s.fb_url || s.href,
            logo: s.logo_path ? (s.logo_path.startsWith('http') ? s.logo_path : '/storage/' + s.logo_path) : 'https://images.unsplash.com/photo-1546410531-bb4caa6b424d?w=100&h=100&fit=crop'
        }));
    }

    return [
        {
            name: 'Proyección Social y Extensión Universitaria',
            fbUrl: 'https://www.facebook.com/p/Direcci%C3%B3n-de-Proyecci%C3%B3n-Social-y-Extensi%C3%B3n-Cultural-UNA-Puno-100071137256988/',
            logo: 'https://cdn.phototourl.com/free/2026-07-31-f705bacb-02f5-4ea3-aeed-7e4e724a1d9b.png'
        },
        {
            name: 'Gestión Ambiental',
            fbUrl: 'https://www.facebook.com/p/Gesti%C3%B3n-Ambiental-UNA-PUNO-Oficial-61552848737780/',
            logo: 'https://cdn.phototourl.com/free/2026-07-31-aaa207df-3d13-45da-8947-299c143f1f7b.jpg'
        },
        {
            name: 'Seguimiento y Desarrollo del Graduado',
            fbUrl: 'https://www.facebook.com/p/Egresados-y-Graduados-UNA-Puno-100092995523250/',
            logo: 'https://cdn.phototourl.com/free/2026-07-31-466e4242-9697-4d02-a2a8-8bb38185b202.jpg'
        }
    ];
});

// Real DB videos with fallback
const mappedVideos = computed(() => {
    if (props.videos && props.videos.length > 0) {
        return props.videos.map(v => ({
            id: v.id,
            title: v.title,
            description: v.description,
            embedUrl: v.embed_url
        }));
    }

    return [
        { id: 1, title: 'Danza Originaria | Chunchos de Esquilaya | Educación Primaria UNA Puno', description: 'Chunchos de Esquilaya, Danza originaria de Puno presentado por la Escuela Profesional de Educación Primaria en el Festival del Folklore de la Universidad Nacional del Altiplano.', embedUrl: 'https://www.youtube.com/embed/t-jVFZWDpqU' },
        { id: 2, title: 'Danza Originaria | Wifala de San Antonio de Putina | Ing. Agronómica UNA Puno', description: 'Wifala de San Antonio de Putina, Danza originaria de Puno presentado por la Escuela Profesional de Ingeniería Agronómica en el Festival del Folklore de la Universidad Nacional del Altiplano.', embedUrl: 'https://youtube.com/embed/lkkRJhGqoQI' },
        { id: 3, title: 'Bajada del Arco - Estudiantina Unificada de la UNA Puno. 2018', description: 'Concierto de la Estudiantina Unificada de la Universidad Nacional del Altiplano de Puno 2018.', embedUrl: 'https://youtube.com/embed/xKwZOed6a7o' }
    ];
});

const currentSlide = ref(0);
let timer: ReturnType<typeof setInterval> | null = null;

const showFloatingBar = ref(false);
const handleScroll = () => {
    const scrollY = window.scrollY;

    // Check scroll past 400px (to show the bar)
    const isPastHero = scrollY > 400;

    // Check if we are colliding with the footer
    const footer = document.querySelector('footer');
    let collidesWithFooter = false;

    if (footer) {
        const footerTop = footer.getBoundingClientRect().top + window.scrollY;
        const windowHeight = window.innerHeight;
        // Pinned at center top-1/2 (scrollY + windowHeight/2) + half height + margin
        const barBottomEdge = scrollY + (windowHeight / 2) + 140;

        if (barBottomEdge >= footerTop) {
            collidesWithFooter = true;
        }
    }

    showFloatingBar.value = isPastHero && !collidesWithFooter;
};

const activitiesContainerRef = ref<HTMLElement | null>(null);
const videosContainerRef = ref<HTMLElement | null>(null);
const statsContainerRef = ref<HTMLElement | null>(null);

const activitiesIndex = ref(0);
const videosIndex = ref(0);
const statsIndex = ref(0);

let activitiesInterval: ReturnType<typeof setInterval> | null = null;
let videosInterval: ReturnType<typeof setInterval> | null = null;
let statsInterval: ReturnType<typeof setInterval> | null = null;

const stepNextSlide = (container: HTMLElement | null, currentIndexRef: { value: number }) => {
    if (!container || window.innerWidth >= 768 || !container.children.length) {
return;
}

    const totalCards = container.children.length;
    currentIndexRef.value = (currentIndexRef.value + 1) % totalCards;
    const targetCard = container.children[currentIndexRef.value] as HTMLElement;

    if (targetCard) {
        container.scrollTo({
            left: targetCard.offsetLeft - container.offsetLeft,
            behavior: 'smooth'
        });
    }
};

onMounted(() => {
    timer = setInterval(() => {
        currentSlide.value = (currentSlide.value + 1) % slides.value.length;
    }, 6000); // 6 seconds

    window.addEventListener('scroll', handleScroll);

    // Auto-scroll mobile sliders sequentially (Card 1 -> Card 2 -> Card 3 -> Card 1)
    activitiesInterval = setInterval(() => stepNextSlide(activitiesContainerRef.value, activitiesIndex), 4500);
    videosInterval = setInterval(() => stepNextSlide(videosContainerRef.value, videosIndex), 5000);
    statsInterval = setInterval(() => stepNextSlide(statsContainerRef.value, statsIndex), 5500);

    // Scroll Reveal Intersection Observer (active on scroll down and scroll up)
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
            } else {
                entry.target.classList.remove('is-visible');
            }
        });
    }, {
        threshold: 0.12, // trigger when 12% is visible
        rootMargin: '0px 0px -40px 0px'
    });

    const sections = document.querySelectorAll('.reveal-section');
    sections.forEach((section) => revealObserver.observe(section));
});

onUnmounted(() => {
    if (timer) {
        clearInterval(timer);
    }

    if (activitiesInterval) {
clearInterval(activitiesInterval);
}

    if (videosInterval) {
clearInterval(videosInterval);
}

    if (statsInterval) {
clearInterval(statsInterval);
}

    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <PublicLayout title="Inicio">
        <section
            class="relative flex flex-col md:flex-row md:items-center overflow-hidden bg-neutral-950 text-white h-[calc(100vh-136px)] md:h-[calc(100vh-96px)] min-h-[480px]">
            <!-- Top 58% Image Carousel Block on Mobile, Full-Screen Overlay on Desktop -->
            <div class="h-[58%] md:h-full w-full relative md:absolute md:inset-0 z-0 overflow-hidden shrink-0">
                <transition-group name="fade">
                    <div v-for="(slide, index) in slides" v-show="currentSlide === index" :key="slide"
                        class="absolute inset-0 bg-cover bg-center transition-all duration-1000 ease-in-out transform scale-105"
                        :style="{ backgroundImage: `url(${slide})` }"></div>
                </transition-group>
                <!-- Dark Overlay for Desktop -->
                <div
                    class="hidden md:block absolute inset-0 bg-gradient-to-r from-black/90 via-black/70 to-transparent dark:from-black/95 dark:via-black/80 dark:to-transparent z-10">
                </div>
            </div>

            <!-- Bottom 42% Glassmorphism Text & Buttons Block on Mobile, Clean Non-Glass Text Block on Desktop -->
            <div class="h-[42%] md:h-auto w-full max-md:bg-neutral-950/40 max-md:backdrop-blur-xl max-md:border-t max-md:border-white/20 max-md:shadow-2xl md:bg-transparent md:backdrop-blur-none md:border-0 md:shadow-none px-0 py-0 sm:px-6 md:px-8 max-w-7xl mx-auto flex flex-col justify-between shrink-0 z-20 md:my-auto">
                <div class="w-full h-full flex flex-col justify-between text-left space-y-1 md:space-y-6">
                    <!-- Taller Square Badge on Mobile (Glass Tinted), Clean Solid Badge on Desktop -->
                    <div
                        class="w-full md:w-fit flex md:inline-flex items-center gap-2.5 px-4 py-3 md:py-1.5 rounded-none md:rounded-full max-md:bg-indigo-500/35 max-md:backdrop-blur-md md:bg-indigo-600/90 md:backdrop-blur-none text-indigo-100 text-xs sm:text-sm font-black md:font-semibold uppercase tracking-wider border-l-4 border-indigo-400 border-y border-r border-white/20 md:border md:border-indigo-400/40 shadow-sm shrink-0">
                        <TrendingUp class="size-5 shrink-0 text-indigo-300 md:text-white" />
                        <span class="truncate">Compromiso Social y Cultural</span>
                    </div>

                    <!-- Inner Container with side padding (px-4) for Heading & Description on Mobile -->
                    <div class="px-4 md:px-0 space-y-2 md:space-y-4 my-auto">
                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black md:font-extrabold tracking-tight leading-tight md:leading-[1.1] text-white drop-shadow-md">
                            Conectando la <span
                                class="bg-gradient-to-r from-indigo-400 via-blue-400 to-indigo-300 bg-clip-text text-transparent">Universidad</span>
                            <br class="hidden md:block" />
                            con nuestra Sociedad
                        </h1>

                        <p class="text-xs sm:text-sm md:text-lg text-neutral-100 leading-normal md:leading-relaxed max-w-2xl font-semibold md:font-normal line-clamp-3 md:line-clamp-none drop-shadow-xs">
                            La Dirección de Proyección Social y Extensión Cultural de la UNA Puno lidera programas
                            integradores, voluntariados, preservación del patrimonio cultural altiplánico y proyectos
                            sostenibles para el desarrollo regional.
                        </p>
                    </div>

                    <!-- Taller Side-By-Side Edge-To-Edge Buttons on Mobile (h-13, font-black, size-5 icons), Original Flex Buttons on Desktop -->
                    <div class="grid grid-cols-2 md:flex md:flex-wrap gap-0 md:gap-4 pt-0 md:pt-2 w-full md:w-auto shrink-0">
                        <Link href="/proyeccion-social" class="w-full md:w-auto">
                            <Button
                                class="w-full md:w-auto rounded-none md:rounded-xl h-13 md:h-12 px-2 md:px-6 bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-600/30 transition-all text-xs sm:text-sm md:text-base font-black md:font-semibold flex items-center justify-center md:justify-start gap-2 cursor-pointer border-0 border-r border-indigo-700/50 md:border-r-0">
                                <span class="truncate">Ver Actividades</span>
                                <ArrowRight class="size-5 shrink-0" />
                            </Button>
                        </Link>
                        <Link href="/documentos" class="w-full md:w-auto">
                            <Button variant="outline"
                                class="w-full md:w-auto rounded-none md:rounded-xl h-13 md:h-12 px-2 md:px-6 border-0 md:border border-white/30 md:border-white/20 bg-white/15 md:bg-white/10 text-white hover:bg-white/25 md:hover:bg-white/20 transition-all text-xs sm:text-sm md:text-base font-black md:font-semibold flex items-center justify-center md:justify-start gap-2 cursor-pointer max-md:backdrop-blur-md md:backdrop-blur-none">
                                <FileText class="size-5 text-indigo-300 shrink-0" />
                                <span class="truncate">Normativas y Guías</span>
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>
        </section>



        <!-- 2. FEATURED LATEST ACTIVITIES (Inspriado de Facebook) -->
        <section class="reveal-section py-20 lg:py-28 bg-neutral-50/80 dark:bg-neutral-900/40 border-b border-neutral-200/60 dark:border-neutral-800/40">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 space-y-12">

                <!-- Section Header -->
                <div class="text-center max-w-2xl mx-auto space-y-3">
                    <span
                        class="text-xs font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Noticias
                        y Publicaciones</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">Actividades Recientes</h2>
                    <p class="text-neutral-500 dark:text-neutral-400 text-sm md:text-base">
                        Mantente al día con los últimos eventos, campañas de voluntariado y proyectos de proyección
                        social publicados en nuestros canales oficiales.
                    </p>
                </div>

                <!-- Activities Horizontal Slider on Mobile, Grid on Desktop (Auto-scroll & manual swipeable) -->
                <div ref="activitiesContainerRef" class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar space-x-4 pb-4 px-1 md:px-0 md:grid md:grid-cols-2 lg:grid-cols-3 md:space-x-0 md:pb-0 md:gap-8 scroll-smooth">
                    <div v-for="activity in latestActivities" :key="activity.id"
                        class="w-[85vw] sm:w-[320px] md:w-auto shrink-0 snap-center group relative flex flex-col bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer"
                        @click="openActivityModal(activity)">
                        <!-- Image Section -->
                        <div class="h-52 relative overflow-hidden bg-neutral-150 dark:bg-neutral-950 shrink-0">
                            <div
                                class="flex h-full"
                                :class="{ 'event-gallery-track': getEventImages(activity).length > 1 }"
                                :style="galleryTrackStyle(activity)"
                            >
                                <div
                                    v-for="image in getEventImages(activity)"
                                    :key="image"
                                    class="h-full min-w-0 flex-1 bg-neutral-100 dark:bg-neutral-950"
                                >
                                    <img :src="image" :alt="activity.title" class="w-full h-full object-contain" />
                                </div>
                            </div>
                            <!-- Category Badge -->
                            <div class="absolute top-4 left-4 z-20">
                                <span
                                    class="text-[9px] font-extrabold uppercase tracking-wider px-2.5 py-1.5 rounded-lg bg-white/95 dark:bg-neutral-950/95 text-indigo-600 dark:text-indigo-400 shadow-xs border border-neutral-200/30 dark:border-neutral-800/30">
                                    {{ activity.category }}
                                </span>
                            </div>
                        </div>

                        <!-- Content Section -->
                        <div class="flex-grow p-6 flex flex-col justify-between text-left">
                            <div class="space-y-2">
                                <!-- Date & Location -->
                                <div
                                    class="flex items-center gap-2.5 text-[11px] text-neutral-500 dark:text-neutral-400 font-semibold">
                                    <span class="flex items-center gap-1">
                                        <Calendar class="size-3.5 text-indigo-600/70 dark:text-indigo-400/70" />
                                        {{ activity.date }}
                                    </span>
                                    <span class="w-1 h-1 rounded-full bg-neutral-300 dark:bg-neutral-700"></span>
                                    <span class="flex items-center gap-1">
                                        <MapPin class="size-3.5 text-indigo-600/70 dark:text-indigo-400/70" />
                                        {{ activity.location }}
                                    </span>
                                </div>

                                <!-- Title -->
                                <h3
                                    class="text-base font-extrabold text-neutral-900 dark:text-white leading-snug line-clamp-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                    {{ activity.title }}
                                </h3>

                                <!-- Description -->
                                <p
                                    class="text-xs text-neutral-500 dark:text-neutral-400 line-clamp-2 leading-relaxed font-normal">
                                    {{ activity.description }}
                                </p>
                            </div>

                            <!-- Divider and Read More -->
                            <div
                                class="border-t border-neutral-100 dark:border-neutral-800/60 w-full pt-4 mt-5 flex items-center justify-between">
                                <span
                                    class="text-xs font-bold text-indigo-600 dark:text-indigo-400 group-hover:text-indigo-700 dark:group-hover:text-indigo-300 flex items-center gap-1 transition-colors">
                                    Leer más detalles
                                    <ChevronRight class="size-3.5" />
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Centered "Ver más Actividades" Button Below Grid -->
                <div class="flex justify-center pt-2">
                    <Link href="/eventos">
                        <Button variant="outline"
                            class="rounded-xl border-neutral-300 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800 text-indigo-600 dark:text-indigo-400 font-semibold group flex items-center gap-2 px-6 py-2.5 cursor-pointer shadow-xs">
                            Ver más Actividades
                            <ArrowRight class="size-4 group-hover:translate-x-1 transition-transform" />
                        </Button>
                    </Link>
                </div>
            </div>
        </section>

        <!-- VIDEOS SECTOR (YOUTUBE EMBEDS) -->
        <section class="reveal-section py-20 lg:py-28 bg-white dark:bg-neutral-950">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 space-y-12">
                <div class="text-center max-w-2xl mx-auto space-y-3">
                    <span
                        class="text-xs font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Multimedia
                        y Registro Audiovisual</span>
                    <h2 class="text-3xl font-extrabold tracking-tight">Videos Recientes</h2>
                    <p class="text-neutral-500 dark:text-neutral-400 text-sm">
                        Revive los mejores momentos de nuestras campañas, inauguraciones y actividades de proyección
                        social en video.
                    </p>
                </div>

                <!-- Videos Horizontal Slider on Mobile, Grid on Desktop (Auto-scroll & manual swipeable) -->
                <div ref="videosContainerRef" class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar space-x-4 pb-4 px-1 md:px-0 md:grid md:grid-cols-2 lg:grid-cols-3 md:space-x-0 md:pb-0 md:gap-8 scroll-smooth">
                    <!-- Dynamic Videos -->
                    <div v-for="vid in mappedVideos" :key="vid.id"
                        class="w-[85vw] sm:w-[320px] md:w-auto shrink-0 snap-center group relative flex flex-col bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300">
                        <div class="relative aspect-video overflow-hidden bg-neutral-950">
                            <iframe class="w-full h-full border-0" :src="vid.embedUrl"
                                :title="vid.title"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen></iframe>
                        </div>
                        <div class="p-5 text-left flex-grow flex flex-col justify-between">
                            <div>
                                <h3
                                    class="text-sm font-extrabold text-neutral-900 dark:text-white leading-snug line-clamp-1">
                                    {{ vid.title }}
                                </h3>
                                <p
                                    class="text-xs text-neutral-500 dark:text-neutral-400 mt-1.5 leading-relaxed line-clamp-2">
                                    {{ vid.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- See More Redirection Button -->
                <div class="pt-4 text-center">
                    <a href="https://www.youtube.com/@direccionderesponsabilidad3871" target="_blank"
                        rel="noopener noreferrer">
                        <Button
                            class="rounded-xl h-12 px-6 bg-red-600 hover:bg-red-700 text-white shadow-lg shadow-red-600/25 transition-all font-bold inline-flex items-center gap-2 cursor-pointer border-0">
                            <svg class="size-4 fill-current shrink-0" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M23.498 6.163a3.003 3.003 0 0 0-2.11-2.11C19.517 3.545 12 3.545 12 3.545s-7.517 0-9.388.508a3.003 3.003 0 0 0-2.11 2.11C0 8.033 0 12 0 12s0 3.967.502 5.837a3.003 3.003 0 0 0 2.11 2.11c1.871.508 9.388.508 9.388.508s7.517 0 9.388-.508a3.003 3.003 0 0 0 2.11-2.11C24 15.967 24 12 24 12s0-3.967-.502-5.837zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                            </svg>
                            Ver más videos
                        </Button>
                    </a>
                </div>
            </div>
        </section>

        <!-- 3. DIRECTORY OF SUBUNIDADES -->
        <section
            class="reveal-section py-20 lg:py-28 bg-neutral-50/80 dark:bg-neutral-900/40 border-y border-neutral-200/60 dark:border-neutral-800/40">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 space-y-12">
                <div class="text-center max-w-2xl mx-auto space-y-3">
                    <span
                        class="text-xs font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Directorio
                        de Sub Unidades</span>
                    <h2 class="text-3xl font-extrabold tracking-tight">Accede a nuestras Sub Unidades</h2>
                    <p class="text-neutral-500 dark:text-neutral-400 text-sm">
                        Conoce las dependencias que forman parte de la Dirección de Proyección Social y Extensión
                        Cultural.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Card 1: Proyección Social -->
                    <div
                        class="p-8 rounded-2xl border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900/60 shadow-xs space-y-5 text-left flex flex-col h-full justify-between">
                        <div class="space-y-4">
                            <div
                                class="size-12 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <Award class="size-6" />
                            </div>
                            <h3 class="text-xl font-bold text-neutral-950 dark:text-white">Proyección Social y Extensión
                                Universitaria</h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed">
                                Foco principal de gestión. Coordina y aprueba proyectos sociales, prácticas en
                                comunidades y eventos culturales impulsados por las facultades.
                            </p>
                        </div>
                        <Link href="/proyeccion-social" class="pt-4 block">
                            <Button variant="outline"
                                class="w-full rounded-xl flex items-center justify-center gap-2 border-neutral-300 dark:border-neutral-700 cursor-pointer">
                                Más información
                                <ArrowRight class="size-4" />
                            </Button>
                        </Link>
                    </div>

                    <!-- Card 2: Gestión Ambiental -->
                    <div
                        class="p-8 rounded-2xl border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900/60 shadow-xs space-y-5 text-left flex flex-col h-full justify-between">
                        <div class="space-y-4">
                            <div
                                class="size-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <Leaf class="size-6" />
                            </div>
                            <h3 class="text-xl font-bold text-neutral-950 dark:text-white">Gestión Ambiental</h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed">
                                Área dedicada a la conservación ecológica de la Ciudad Universitaria, voluntariados de
                                segregación de residuos y educación ambiental.
                            </p>
                        </div>
                        <a href="https://www.unap.edu.pe" target="_blank" rel="noopener noreferrer" class="pt-4 block">
                            <Button variant="outline"
                                class="w-full rounded-xl flex items-center justify-center gap-2 border-neutral-300 dark:border-neutral-700 cursor-pointer">
                                Más información
                                <ExternalLink class="size-4 text-neutral-500" />
                            </Button>
                        </a>
                    </div>

                    <!-- Card 3: Seguimiento y Desarrollo del Graduado -->
                    <div
                        class="p-8 rounded-2xl border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900/60 shadow-xs space-y-5 text-left flex flex-col h-full justify-between">
                        <div class="space-y-4">
                            <div
                                class="size-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                <Users class="size-6" />
                            </div>
                            <h3 class="text-xl font-bold text-neutral-950 dark:text-white">Seguimiento y Desarrollo del Graduado</h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed">
                                Plataforma futura de inserción laboral, encuestas de egresados y contacto con las redes
                                de exalumnos de la UNA Puno.
                            </p>
                        </div>
                        <Link href="/seguimiento-graduado" class="pt-4 block">
                            <Button variant="outline"
                                class="w-full rounded-xl flex items-center justify-center gap-2 border-neutral-300 dark:border-neutral-700 cursor-pointer">
                                Más información
                                <ArrowRight class="size-4" />
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. RECENT DOCUMENTS PREVIEW -->
        <section class="reveal-section py-20 lg:py-28 bg-white dark:bg-neutral-950">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

                <!-- Left Details -->
                <div class="lg:col-span-5 space-y-6 text-left">
                    <span
                        class="text-xs font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Transparencia
                        y Normas</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">Documentos de Gestión</h2>
                    <p class="text-neutral-600 dark:text-neutral-300 text-sm md:text-base leading-relaxed">
                        Descarga las resoluciones, directivas vigentes, reglamentos y guías metodológicas para registrar
                        tus proyectos de proyección social y extensión cultural adecuadamente.
                    </p>
                    <Link href="/documentos">
                        <Button
                            class="rounded-xl bg-indigo-700 hover:bg-indigo-800 text-white flex items-center gap-2 cursor-pointer">
                            Ver Todos los Documentos de Gestión
                            <ArrowRight class="size-4" />
                        </Button>
                    </Link>
                </div>

                <!-- Right Documents Table / Cards -->
                <div class="lg:col-span-7 space-y-4 w-full">
                    <div v-for="doc in documents" :key="doc.id"
                        class="p-5 rounded-2xl border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900/60 shadow-xs hover:border-indigo-500/50 dark:hover:border-indigo-400/50 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-left">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-[9px] bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 px-1.5 py-0.5 rounded font-bold uppercase tracking-wider">{{
                                        doc.type }}</span>
                                <span class="text-[10px] text-neutral-400 font-mono">{{ doc.code }}</span>
                            </div>
                            <h4 class="font-bold text-sm text-neutral-900 dark:text-white line-clamp-1">{{ doc.title }}
                            </h4>
                            <p class="text-xs text-neutral-400">Publicado: {{ doc.date }}</p>
                        </div>
                        <Link href="/documentos">
                            <Button size="sm" variant="outline"
                                class="rounded-xl flex items-center gap-1.5 text-xs border-neutral-300 dark:border-neutral-700 shrink-0 self-start sm:self-auto cursor-pointer">
                                <FileText class="size-3.5 text-indigo-500" />
                                Descargar PDF
                            </Button>
                        </Link>
                    </div>
                </div>

            </div>
        </section>

        <!-- 5. STATS SECTION (GLASSMORPHIC CARDS) -->
        <section
            class="reveal-section py-16 bg-neutral-50/80 dark:bg-neutral-900/40 border-y border-neutral-200/60 dark:border-neutral-800/40">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <!-- Stats Horizontal Slider on Mobile, Grid on Desktop (Auto-scroll & manual swipeable) -->
                <div ref="statsContainerRef" class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar space-x-4 pb-2 px-1 md:px-0 md:grid md:grid-cols-2 lg:grid-cols-4 md:space-x-0 md:pb-0 md:gap-6 scroll-smooth">
                    <div v-for="stat in stats" :key="stat.label"
                        class="w-[75vw] sm:w-[260px] md:w-auto shrink-0 snap-center p-6 rounded-2xl bg-white dark:bg-neutral-900/50 border border-neutral-200/60 dark:border-neutral-800/60 shadow-xs flex items-start gap-4 hover:shadow-md transition-shadow">
                        <div class="p-3 rounded-xl shrink-0" :class="stat.color">
                            <component :is="stat.icon" class="size-6" />
                        </div>
                        <div class="space-y-1">
                            <span class="text-3xl font-extrabold text-neutral-900 dark:text-white tracking-tight">{{
                                stat.value }}</span>
                            <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">{{ stat.label }}
                            </h3>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ stat.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. FACEBOOK SOCIAL COMMUNITY BANNER -->
        <!-- <section class="reveal-section py-12 bg-gradient-to-r from-indigo-700 to-indigo-900 dark:from-indigo-950 dark:to-neutral-950 text-white">
            <div class="max-w-5xl mx-auto px-6 text-center space-y-6">
                <h3 class="text-2xl md:text-3xl font-extrabold tracking-tight">¿Quieres ver fotos y videos en tiempo real?</h3>
                <p class="text-white/80 text-sm md:text-base max-w-2xl mx-auto">
                    Nuestra comunidad más activa está en Facebook. Publicamos diariamente convocatorias de voluntariados, transmisiones en vivo de festivales y testimonios de beneficiarios.
                </p>
                <div class="pt-2">
                    <a 
                        href="https://www.facebook.com/ProyeccionSocialUNAPuno" 
                        target="_blank" 
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-white text-indigo-950 hover:bg-neutral-100 transition-colors font-bold text-sm shadow-md"
                    >
                        <svg class="size-5 fill-current text-[#1877F2]" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1V12h3l-.5 3h-2.5v6.8c4.56-.93 8-4.96 8-9.8z"/>
                        </svg>
                        Seguir en Facebook
                        <ExternalLink class="size-4 opacity-75" />
                    </a>
                </div>
            </div>
        </section> -->

        <!-- BEAUTIFUL RESPONSIVE MODAL FOR ACTIVITY DETAILS -->
        <Transition name="modal">
            <div v-if="isModalOpen && selectedActivity"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-md p-4"
                @click.self="closeActivityModal">
                <!-- Modal Panel -->
                <div
                    class="relative w-full max-w-3xl bg-white dark:bg-neutral-900 rounded-3xl overflow-hidden shadow-2xl border border-neutral-200 dark:border-neutral-800 flex flex-col md:flex-row h-auto max-h-[90vh] md:h-[500px] text-left transform transition-all duration-300">

                    <div class="grid grid-cols-1 md:grid-cols-12 w-full h-full">
                        <!-- Left side: Image -->
                        <div
                            class="md:col-span-5 relative h-[180px] sm:h-[220px] md:h-full overflow-hidden bg-neutral-100 dark:bg-neutral-950 shrink-0">
                            <div
                                class="flex h-full"
                                :class="{ 'event-gallery-track': getEventImages(selectedActivity).length > 1 }"
                                :style="galleryTrackStyle(selectedActivity)"
                            >
                                <div
                                    v-for="image in getEventImages(selectedActivity)"
                                    :key="image"
                                    class="h-full min-w-0 flex-1 bg-neutral-100 dark:bg-neutral-950"
                                >
                                    <img :src="image" :alt="selectedActivity.title" class="w-full h-full object-contain" />
                                </div>
                            </div>
                            <div class="absolute top-4 left-4 flex gap-1.5 flex-wrap">
                                <span
                                    class="text-[9px] font-bold uppercase tracking-wider px-2.5 py-1 rounded bg-indigo-600 text-white shadow-md"
                                    :class="selectedActivity.tagColor">
                                    {{ selectedActivity.category }}
                                </span>
                            </div>
                        </div>

                        <!-- Right side: Details -->
                        <div
                            class="md:col-span-7 p-6 md:p-8 flex flex-col justify-between h-full overflow-y-auto max-h-[calc(90vh-180px)] sm:max-h-[calc(90vh-220px)] md:max-h-full">
                            <div class="space-y-4">
                                <!-- Header and Close button -->
                                <div class="flex items-center justify-between">
                                    <span class="text-[9px] font-bold uppercase tracking-wider px-2.5 py-1 rounded"
                                        :class="selectedActivity.tagColor">
                                        {{ selectedActivity.category }}
                                    </span>

                                    <!-- Close Button -->
                                    <button @click="closeActivityModal"
                                        class="size-8 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400 flex items-center justify-center hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors cursor-pointer">
                                        <X class="size-4" />
                                    </button>
                                </div>

                                <!-- Title -->
                                <h2
                                    class="text-base sm:text-lg md:text-xl font-black text-neutral-900 dark:text-white leading-tight">
                                    {{ selectedActivity.title }}
                                </h2>

                                <!-- Divider -->
                                <div class="border-t border-neutral-100 dark:border-neutral-800"></div>

                                <!-- Meta Grid (2 cols) -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                    <div
                                        class="flex items-center gap-2 text-neutral-600 dark:text-neutral-300 sm:col-span-2">
                                        <Calendar class="size-4 text-indigo-600 dark:text-indigo-400 shrink-0" />
                                        <span class="font-semibold">{{ selectedActivity.date }}</span>
                                    </div>
                                    <div
                                        class="flex items-start gap-2 text-neutral-600 dark:text-neutral-300 sm:col-span-2">
                                        <MapPin class="size-4 text-red-500 shrink-0 mt-0.5" />
                                        <span class="leading-tight">{{ selectedActivity.location }}</span>
                                    </div>
                                </div>

                                <!-- Divider -->
                                <div class="border-t border-neutral-100 dark:border-neutral-800"></div>

                                <!-- Description -->
                                <div class="space-y-1.5">
                                    <h4 class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider">Detalles
                                    </h4>
                                    <p
                                        class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed">
                                        {{ selectedActivity.description }}
                                    </p>
                                </div>
                            </div>

                            <!-- Footer Actions -->
                            <div
                                class="border-t border-neutral-100 dark:border-neutral-800/80 pt-4 mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <a href="https://www.facebook.com/ProyeccionSocialUNAPuno" target="_blank"
                                    rel="noopener noreferrer" class="w-full sm:w-auto" @click.stop>
                                    <Button size="sm"
                                        class="w-full sm:w-auto rounded-xl bg-indigo-700 hover:bg-indigo-800 text-white text-xs font-semibold flex items-center justify-center gap-1.5 cursor-pointer">
                                        <UserCheck class="size-3.5" />
                                        Inscribirse/Participar
                                    </Button>
                                </a>

                                <a href="https://www.facebook.com/ProyeccionSocialUNAPuno" target="_blank"
                                    class="text-xs text-neutral-400 hover:text-blue-500 transition-colors flex items-center gap-1"
                                    @click.stop>
                                    <svg class="size-4 fill-current" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1V12h3l-.5 3h-2.5v6.8c4.56-.93 8-4.96 8-9.8z" />
                                    </svg>
                                    Ver en Facebook
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </Transition>

        <!-- Floating Vertical Subunits Bar (Sticky, appears after Hero on the left) -->
        <div class="fixed left-4 top-1/2 -translate-y-1/2 z-50 hidden sm:flex flex-col gap-4 transition-all duration-500 ease-in-out"
            :class="showFloatingBar ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-16 pointer-events-none'">
            <div
                class="flex flex-col items-center pb-3 bg-white/70 dark:bg-neutral-950/70 backdrop-blur-md rounded-2xl border border-neutral-200/50 dark:border-neutral-800/50 shadow-lg w-14">
                <!-- Header Facebook Indicator (Takes the top shape of the bar) -->
                <div class="w-full h-10 flex items-center justify-center bg-[#1877f2] text-white shrink-0 mb-3 rounded-t-2xl"
                    title="Páginas de Facebook Oficiales">
                    <svg class="size-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1V12h3l-.5 3h-2.5v6.8c4.56-.93 8-4.96 8-9.8z" />
                    </svg>
                </div>

                <!-- Subunits items -->
                <div class="flex flex-col gap-3 px-2">
                    <a v-for="sub in subunitsFloating" :key="sub.name" :href="sub.fbUrl" target="_blank"
                        rel="noopener noreferrer"
                        class="group relative flex items-center justify-center size-10 rounded-full border-2 border-white dark:border-neutral-800 bg-white shadow-md hover:shadow-xl hover:scale-110 transition-all duration-300 cursor-pointer">
                        <img :src="sub.logo" :alt="sub.name" class="w-full h-full object-cover rounded-full" />
                        <!-- Tooltip name on hover -->
                        <div
                            class="absolute left-12 top-1/2 -translate-y-1/2 px-3 py-1.5 rounded-xl bg-white/90 dark:bg-neutral-950/90 backdrop-blur-md border border-neutral-200/60 dark:border-neutral-800/60 text-indigo-600 dark:text-indigo-400 text-[10px] font-extrabold whitespace-nowrap opacity-0 -translate-x-3 pointer-events-none group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300 shadow-xl z-50">
                            {{ sub.name }}
                        </div>
                    </a>
                </div>
            </div>
        </div>

    </PublicLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 1.5s ease-in-out;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Scroll Reveal animation for home sections (notoria al bajar y subir) */
.reveal-section {
    opacity: 0;
    transform: translateY(50px) scale(0.97);
    transition: opacity 0.85s cubic-bezier(0.16, 1, 0.3, 1), transform 0.85s cubic-bezier(0.16, 1, 0.3, 1);
    will-change: transform, opacity;
}

.reveal-section.is-visible {
    opacity: 1;
    transform: translateY(0) scale(1);
}

/* Modal Transition Animations */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
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

/* Hide scrollbar for clean mobile sliding */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
