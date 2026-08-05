<script setup lang="ts">
import { 
    Users, 
    Heart, 
    Lightbulb, 
    ShieldCheck, 
    Flame, 
    Building2, 
    TrendingUp, 
    Scale, 
    UsersRound,
    Leaf
} from '@lucide/vue';
import { onMounted, onUnmounted, ref, computed } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

const props = defineProps<{
    team?: any[];
    objectives?: any[];
    values?: any[];
    sections?: any[];
}>();

// Map string icon names to Lucide components
const iconMap: Record<string, any> = {
    Users,
    Heart,
    Lightbulb,
    ShieldCheck,
    Flame,
    Building2,
    TrendingUp,
    Scale,
    UsersRound,
    Leaf
};

// Team members list
const team = computed(() => {
    if (props.team && props.team.length > 0) {
        return props.team.map(t => ({
            name: t.name,
            role: t.role,
            department: t.department,
            initials: t.initials || t.name.split(' ').map((n: string) => n[0]).join('').slice(0, 2).toUpperCase(),
            image: t.image_path ? (t.image_path.startsWith('http') ? t.image_path : '/storage/' + t.image_path) : '',
            textColor: 'text-indigo-600 dark:text-indigo-400',
            bgLight: 'bg-indigo-50 dark:bg-indigo-950/20',
            // Hover custom variables
            glowStart: 'rgba(68, 165, 76, 0.3)',
            glowEnd: 'rgba(152, 209, 43, 0.1)',
            glowHoverStart: 'rgba(68, 165, 76, 0.6)',
            glowHoverEnd: 'rgba(152, 209, 43, 0.3)',
            glowShadow: 'rgba(68, 165, 76, 0.05)',
            glowShadowHover: 'rgba(68, 165, 76, 0.15)',
            glowBorderHover: 'rgba(181, 228, 86, 0.5)',
            glowRing: 'rgba(68, 165, 76, 0.4)',
            glowRingPulse: 'rgba(68, 165, 76, 0.35)',
            bottomBgLight: 'rgba(244, 249, 244, 0.88)',
            bottomBgDark: 'rgba(8, 22, 10, 0.90)'
        }));
    }

    return [
        {
            name: 'Dra. Milder Zanabria Ortega',
            role: 'Directora',
            department: 'Dirección de Proyección Social y Extensión Cultural',
            initials: 'MZ',
            image: 'https://cdn.phototourl.com/free/2026-07-08-0148c525-1fa2-46ce-8343-d43e2fd7c5ca.png',
            textColor: 'text-indigo-600 dark:text-indigo-400',
            bgLight: 'bg-indigo-50 dark:bg-indigo-950/20',
            glowStart: 'rgba(68, 165, 76, 0.3)',
            glowEnd: 'rgba(152, 209, 43, 0.1)',
            glowHoverStart: 'rgba(68, 165, 76, 0.6)',
            glowHoverEnd: 'rgba(152, 209, 43, 0.3)',
            glowShadow: 'rgba(68, 165, 76, 0.05)',
            glowShadowHover: 'rgba(68, 165, 76, 0.15)',
            glowBorderHover: 'rgba(181, 228, 86, 0.5)',
            glowRing: 'rgba(68, 165, 76, 0.4)',
            glowRingPulse: 'rgba(68, 165, 76, 0.35)',
            bottomBgLight: 'rgba(244, 249, 244, 0.88)',
            bottomBgDark: 'rgba(8, 22, 10, 0.90)'
        },
        {
            name: 'M.Sc. Wilkerson Palza Meza',
            role: 'Jefe de Sub Unidad',
            department: 'Sub Unidad de Proyección Social y Extensión Universitaria',
            initials: 'WP',
            image: 'https://cdn.phototourl.com/free/2026-07-09-8662ddab-51eb-4547-91ce-de75938f1053.png',
            textColor: 'text-indigo-600 dark:text-indigo-400',
            bgLight: 'bg-indigo-50 dark:bg-indigo-950/20',
            glowStart: 'rgba(68, 165, 76, 0.3)',
            glowEnd: 'rgba(152, 209, 43, 0.1)',
            glowHoverStart: 'rgba(68, 165, 76, 0.6)',
            glowHoverEnd: 'rgba(152, 209, 43, 0.3)',
            glowShadow: 'rgba(68, 165, 76, 0.05)',
            glowShadowHover: 'rgba(68, 165, 76, 0.15)',
            glowBorderHover: 'rgba(181, 228, 86, 0.5)',
            glowRing: 'rgba(68, 165, 76, 0.4)',
            glowRingPulse: 'rgba(68, 165, 76, 0.35)',
            bottomBgLight: 'rgba(244, 249, 244, 0.88)',
            bottomBgDark: 'rgba(8, 22, 10, 0.90)'
        },
        {
            name: 'Ing. Yumy Romero Talavera',
            role: 'Jefa de Sub Unidad',
            department: 'Sub Unidad de Seguimiento y Desarrollo del Graduado',
            initials: 'YR',
            image: 'https://cdn.phototourl.com/free/2026-07-09-a6257f35-bbb0-483f-909e-18f184a22061.png',
            textColor: 'text-indigo-600 dark:text-indigo-400',
            bgLight: 'bg-indigo-50 dark:bg-indigo-950/20',
            glowStart: 'rgba(68, 165, 76, 0.3)',
            glowEnd: 'rgba(152, 209, 43, 0.1)',
            glowHoverStart: 'rgba(68, 165, 76, 0.6)',
            glowHoverEnd: 'rgba(152, 209, 43, 0.3)',
            glowShadow: 'rgba(68, 165, 76, 0.05)',
            glowShadowHover: 'rgba(68, 165, 76, 0.15)',
            glowBorderHover: 'rgba(181, 228, 86, 0.5)',
            glowRing: 'rgba(68, 165, 76, 0.4)',
            glowRingPulse: 'rgba(68, 165, 76, 0.35)',
            bottomBgLight: 'rgba(244, 249, 244, 0.88)',
            bottomBgDark: 'rgba(8, 22, 10, 0.90)'
        },
        {
            name: 'M.Sc. Marco Vera Zuñiga',
            role: 'Jefe de Sub Unidad',
            department: 'Sub Unidad de Gestión Ambiental',
            initials: 'MV',
            image: 'https://cdn.phototourl.com/free/2026-07-08-5a3b67b1-4af0-4dcb-9f3f-b6b2d73858dc.png',
            textColor: 'text-indigo-600 dark:text-indigo-400',
            bgLight: 'bg-indigo-50 dark:bg-indigo-950/20',
            glowStart: 'rgba(68, 165, 76, 0.3)',
            glowEnd: 'rgba(152, 209, 43, 0.1)',
            glowHoverStart: 'rgba(68, 165, 76, 0.6)',
            glowHoverEnd: 'rgba(152, 209, 43, 0.3)',
            glowShadow: 'rgba(68, 165, 76, 0.05)',
            glowShadowHover: 'rgba(68, 165, 76, 0.15)',
            glowBorderHover: 'rgba(181, 228, 86, 0.5)',
            glowRing: 'rgba(68, 165, 76, 0.4)',
            glowRingPulse: 'rgba(68, 165, 76, 0.35)',
            bottomBgLight: 'rgba(244, 249, 244, 0.88)',
            bottomBgDark: 'rgba(8, 22, 10, 0.90)'
        }
    ];
});

// Strategic Objectives list
const objectives = computed(() => {
    if (props.objectives && props.objectives.length > 0) {
        return props.objectives.map(o => ({
            title: o.title,
            description: o.description,
            icon: iconMap[o.icon_name] || Building2,
            color: o.color_class || 'text-indigo-600 dark:text-indigo-400 bg-indigo-500/10'
        }));
    }

    return [
        {
            title: 'Fortalecimiento Institucional',
            description: 'Consolidar nuestra organización como referente en el sector, con procesos eficientes y un equipo altamente capacitado.',
            icon: Building2,
            color: 'text-indigo-600 dark:text-indigo-400 bg-indigo-500/10'
        },
        {
            title: 'Impacto Social Ampliado',
            description: 'Incrementar nuestro alcance beneficiando a un 30% más de personas en los próximos 5 años.',
            icon: TrendingUp,
            color: 'text-blue-600 dark:text-blue-400 bg-blue-500/10'
        },
        {
            title: 'Innovación Continua',
            description: 'Desarrollar al menos 3 nuevos programas innovadores anuales que respondan a necesidades emergentes.',
            icon: Lightbulb,
            color: 'text-amber-500 dark:text-amber-400 bg-amber-500/10'
        },
        {
            title: 'Sostenibilidad Financiera',
            description: 'Diversificar nuestras fuentes de financiamiento para garantizar la continuidad de nuestros proyectos.',
            icon: Scale,
            color: 'text-emerald-600 dark:text-emerald-400 bg-emerald-500/10'
        },
        {
            title: 'Alianzas Estratégicas',
            description: 'Establecer colaboraciones con al menos 10 nuevas organizaciones afines en los próximos 3 años.',
            icon: UsersRound,
            color: 'text-purple-600 dark:text-purple-400 bg-purple-500/10'
        }
    ];
});

const getObjectiveStyle = (index: number) => {
    const positions = [
        { top: '6%', left: '50%' },
        { top: '35%', left: '83%' },
        { top: '82%', left: '72%' },
        { top: '82%', left: '28%' },
        { top: '35%', left: '17%' }
    ];

    return {
        top: positions[index].top,
        left: positions[index].left,
        transform: 'translate(-50%, -50%)'
    };
};

const valueImages = [
    'https://images.unsplash.com/photo-1517048676732-d65bc937f952?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1579202673506-ca3ce28943ef?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=600&auto=format&fit=crop&q=80',
    'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=600&auto=format&fit=crop&q=80'
];

// Distinct Theme Palettes for each Institutional Value card (Both Front & Back)
const valueColorThemes = [
    { // 0: Compromiso (Rose Red)
        headerBg: 'bg-rose-600 dark:bg-rose-700',
        backBg: 'bg-rose-50/90 dark:bg-rose-950/90 border-rose-200 dark:border-rose-800/70',
        iconBg: 'bg-rose-600 text-white shadow-rose-500/30',
        titleColor: 'text-rose-950 dark:text-rose-100',
        descColor: 'text-rose-800/90 dark:text-rose-200/80',
        badgeBorder: 'border-rose-300 dark:border-rose-700'
    },
    { // 1: Innovación (Amber Gold)
        headerBg: 'bg-amber-600 dark:bg-amber-700',
        backBg: 'bg-amber-50/90 dark:bg-amber-950/90 border-amber-200 dark:border-amber-800/70',
        iconBg: 'bg-amber-600 text-white shadow-amber-500/30',
        titleColor: 'text-amber-950 dark:text-amber-100',
        descColor: 'text-amber-800/90 dark:text-amber-200/80',
        badgeBorder: 'border-amber-300 dark:border-amber-700'
    },
    { // 2: Trabajo en Equipo (Blue)
        headerBg: 'bg-blue-600 dark:bg-blue-700',
        backBg: 'bg-blue-50/90 dark:bg-blue-950/90 border-blue-200 dark:border-blue-800/70',
        iconBg: 'bg-blue-600 text-white shadow-blue-500/30',
        titleColor: 'text-blue-950 dark:text-blue-100',
        descColor: 'text-blue-800/90 dark:text-blue-200/80',
        badgeBorder: 'border-blue-300 dark:border-blue-700'
    },
    { // 3: Integridad (Emerald Green)
        headerBg: 'bg-emerald-600 dark:bg-emerald-700',
        backBg: 'bg-emerald-50/90 dark:bg-emerald-950/90 border-emerald-200 dark:border-emerald-800/70',
        iconBg: 'bg-emerald-600 text-white shadow-emerald-500/30',
        titleColor: 'text-emerald-950 dark:text-emerald-100',
        descColor: 'text-emerald-800/90 dark:text-emerald-200/80',
        badgeBorder: 'border-emerald-300 dark:border-emerald-700'
    },
    { // 4: Pasión (Orange)
        headerBg: 'bg-orange-600 dark:bg-orange-700',
        backBg: 'bg-orange-50/90 dark:bg-orange-950/90 border-orange-200 dark:border-orange-800/70',
        iconBg: 'bg-orange-600 text-white shadow-orange-500/30',
        titleColor: 'text-orange-950 dark:text-orange-100',
        descColor: 'text-orange-800/90 dark:text-orange-200/80',
        badgeBorder: 'border-orange-300 dark:border-orange-700'
    },
    { // 5: Sostenibilidad (Teal)
        headerBg: 'bg-teal-600 dark:bg-teal-700',
        backBg: 'bg-teal-50/90 dark:bg-teal-950/90 border-teal-200 dark:border-teal-800/70',
        iconBg: 'bg-teal-600 text-white shadow-teal-500/30',
        titleColor: 'text-teal-950 dark:text-teal-100',
        descColor: 'text-teal-800/90 dark:text-teal-200/80',
        badgeBorder: 'border-teal-300 dark:border-teal-700'
    }
];

// Values list with distinct theme assigned per item
const values = computed(() => {
    const defaultList = [
        {
            title: 'Compromiso',
            description: 'Nos dedicamos plenamente a nuestra misión y a las comunidades que servimos.',
            icon: Heart,
            image: 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?w=600&auto=format&fit=crop&q=80',
        },
        {
            title: 'Innovación',
            description: 'Buscamos constantemente nuevas soluciones a los desafíos sociales.',
            icon: Lightbulb,
            image: 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=600&auto=format&fit=crop&q=80',
        },
        {
            title: 'Trabajo en Equipo',
            description: 'Creemos en el poder de la colaboración y el esfuerzo colectivo.',
            icon: Users,
            image: 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=600&auto=format&fit=crop&q=80',
        },
        {
            title: 'Integridad',
            description: 'Actuamos con transparencia y ética en todas nuestras acciones.',
            icon: ShieldCheck,
            image: 'https://images.unsplash.com/photo-1579202673506-ca3ce28943ef?w=600&auto=format&fit=crop&q=80',
        },
        {
            title: 'Pasión',
            description: 'Nos apasiona nuestro trabajo y el impacto que generamos.',
            icon: Flame,
            image: 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=600&auto=format&fit=crop&q=80',
        },
        {
            title: 'Sostenibilidad',
            description: 'Trabajamos por soluciones que perduren en el tiempo.',
            icon: Leaf,
            image: 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=600&auto=format&fit=crop&q=80',
        }
    ];

    const source = (props.values && props.values.length > 0) ? props.values : defaultList;

    return source.map((v, index) => {
        const theme = valueColorThemes[index % valueColorThemes.length];

        return {
            title: v.title,
            description: v.description,
            icon: v.icon || iconMap[v.icon_name] || Heart,
            image: v.image || valueImages[index % valueImages.length],
            headerBg: theme.headerBg,
            backBg: theme.backBg,
            iconBg: theme.iconBg,
            titleColor: theme.titleColor,
            descColor: theme.descColor,
            badgeBorder: theme.badgeBorder,
        };
    });
});

// Scrollytelling Pinned Team Presentation Logic
const teamSectionRef = ref<HTMLElement | null>(null);
const activeCardIndex = ref(0);

const handleScrollPresentation = () => {
    if (!teamSectionRef.value) {
return;
}

    const rect = teamSectionRef.value.getBoundingClientRect();
    const sectionHeight = rect.height;
    const viewportHeight = window.innerHeight;
    
    // Scrolled distance within the section boundary
    const scrolledDistance = -rect.top;
    const totalScrollableDistance = sectionHeight - viewportHeight;
    
    if (totalScrollableDistance <= 0) {
        return;
    }
    
    let progress = scrolledDistance / totalScrollableDistance;
    progress = Math.max(0, Math.min(1, progress));
    
    // Map progress to card index
    const index = Math.min(Math.floor(progress * team.value.length), team.value.length - 1);
    activeCardIndex.value = index;
};

const scrollToCard = (index: number) => {
    if (!teamSectionRef.value) {
        return;
    }

    const rect = teamSectionRef.value.getBoundingClientRect();
    const sectionTop = window.scrollY + rect.top;
    const totalScrollableDistance = rect.height - window.innerHeight;
    
    const targetScrollY = sectionTop + (index / (team.value.length - 1 || 1)) * totalScrollableDistance + 10;
    
    window.scrollTo({
        top: targetScrollY,
        behavior: 'smooth'
    });
};

onMounted(() => {
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
            } else {
                entry.target.classList.remove('is-visible');
            }
        });
    }, {
        threshold: 0.08,
        rootMargin: '0px 0px -40px 0px'
    });

    const sections = document.querySelectorAll('.reveal-section');
    sections.forEach((section) => revealObserver.observe(section));

    // Register presentation scroll handler
    window.addEventListener('scroll', handleScrollPresentation);
    // Init state
    handleScrollPresentation();
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScrollPresentation);
});
</script>

<template>
    <PublicLayout title="Nosotros">
        <!-- 1. HERO SECTION -->
        <section 
            class="relative h-[65vh] min-h-[260px] flex items-center overflow-hidden bg-cover bg-center text-white"
            style="background-image: url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=1600&q=80');"
        >
            <!-- Gradient Overlay for readability -->
            <div class="absolute inset-0 bg-gradient-to-r from-neutral-950/90 via-neutral-950/70 to-transparent z-10"></div>
            
            <div class="max-w-7xl mx-auto w-full px-6 lg:px-8 text-left relative z-20 space-y-3">
                <span class="text-xs font-bold uppercase tracking-widest text-blue-400">¿Quiénes Somos?</span>
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Nosotros</h1>
                <p class="text-xs md:text-sm text-white/80 max-w-3xl leading-relaxed">
                    Conoce la misión, los valores y el equipo de profesionales que lidera la Dirección de Proyección Social y Extensión Cultural en su compromiso de vincular a la Universidad Nacional del Altiplano con el desarrollo de la región.
                </p>
            </div>
        </section>

        <!-- 2. MISSION SECTION -->
        <section class="reveal-section py-20 lg:py-28 max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left Details -->
                <div class="lg:col-span-5 space-y-6 text-left">
                    <span class="text-xs font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Propósito Institucional</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">Nuestra Misión</h2>
                    <div class="w-12 h-1.5 bg-indigo-600 dark:bg-indigo-400 rounded-full"></div>
                    <p class="text-neutral-600 dark:text-neutral-400 text-sm md:text-base leading-relaxed">
                        Definimos el norte de nuestro trabajo en base a la responsabilidad social universitaria, promoviendo el desarrollo de capacidades humanas en estrecha relación con las comunidades locales y la herencia cultural altiplánica.
                    </p>
                </div>

                <!-- Right Quote Box -->
                <div class="lg:col-span-7">
                    <div class="relative p-8 md:p-12 rounded-3xl bg-neutral-50 dark:bg-neutral-900 border border-neutral-200/60 dark:border-neutral-800 text-left overflow-hidden shadow-xs">
                        <div class="absolute -top-10 -left-6 text-neutral-200/40 dark:text-neutral-800/30 text-[180px] font-serif leading-none select-none">“</div>
                        
                        <blockquote class="relative z-10">
                            <p class="text-lg md:text-xl font-medium text-neutral-800 dark:text-neutral-100 leading-relaxed italic">
                                Formar profesionales y posgraduados competitivos, con capacidad de investigación, emprendimiento, la responsabilidad social e identidad cultural para contribuir al desarrollo humano y desarrollo sostenible de la región y del país.
                            </p>
                        </blockquote>
                        
                        <div class="mt-6 flex items-center gap-3">
                            <div class="h-px w-8 bg-neutral-300 dark:bg-neutral-700"></div>
                            <span class="text-xs font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                                Estatuto UNA Puno
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. TEAM SECTION (Scrollytelling pinned section) -->
        <section ref="teamSectionRef" class="relative h-[250vh] lg:h-[350vh] bg-neutral-50/50 dark:bg-neutral-900/10 border-y border-neutral-200/55 dark:border-neutral-800/40">
            <!-- Pinned Sticky Container (ocupa el alto de pantalla y queda fijo mientras se hace scroll) -->
            <div class="sticky top-20 h-[calc(100vh-80px)] lg:h-[calc(100vh-80px)] flex items-center justify-center overflow-hidden w-full">
                <div class="max-w-7xl mx-auto px-6 lg:px-8 w-full">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center w-full relative">
                        
                        <!-- Left Column: Sticky Header (centrado verticalmente) -->
                        <div class="lg:col-span-5 text-left space-y-4 lg:pr-8">
                            <span class="text-xs font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Liderazgo y Gestión</span>
                            <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-neutral-900 dark:text-white">Nuestro Equipo</h2>
                            <div class="w-12 h-1.5 bg-indigo-600 dark:bg-indigo-400 rounded-full"></div>
                            <p class="text-xs md:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed font-normal">
                                Contamos con un equipo multidisciplinario de profesionales apasionados por el cambio social, comprometidos con nuestros valores y enfocados en lograr resultados transformadores para nuestra comunidad universitaria y la región de Puno.
                            </p>

                            <!-- Navigation Dots Indicator -->
                            <div class="flex items-center gap-2 pt-4">
                                <button 
                                    v-for="(member, idx) in team" 
                                    :key="idx"
                                    @click="scrollToCard(idx)"
                                    class="h-2 rounded-full transition-all duration-300 cursor-pointer"
                                    :class="activeCardIndex === idx ? 'w-8 bg-indigo-600 dark:bg-indigo-400' : 'w-2 bg-neutral-300 dark:bg-neutral-700'"
                                    :aria-label="'Ver integrante ' + (idx + 1)"
                                ></button>
                            </div>
                        </div>

                        <!-- Right Column: Single Card Presentation with Transition Fade -->
                        <div class="lg:col-span-7 flex items-center justify-center relative h-[450px] w-full">
                            <template v-for="(member, index) in team" :key="member.name">
                                <transition name="card-fade">
                                    <div 
                                        v-show="activeCardIndex === index"
                                        class="team-card absolute group shadow-2xl"
                                        :style="{
                                            '--glow-shadow': member.glowShadow,
                                            '--glow-shadow-hover': member.glowShadowHover,
                                            '--tilt-angle': index % 2 === 0 ? '-2.5deg' : '2.5deg'
                                        }"
                                    >
                                        <!-- Photo Frame Container (No overlays on image, fully visible portrait) -->
                                        <div class="w-full h-[68%] rounded-2xl overflow-hidden bg-neutral-50 dark:bg-neutral-950 border border-neutral-200/60 dark:border-neutral-800/60 flex items-end justify-center relative">
                                            <img 
                                                :src="member.image" 
                                                :alt="member.name" 
                                                class="portrait-image" 
                                            />
                                        </div>

                                        <!-- Details Area (Below the photo frame, no overlay covering the image) -->
                                        <div class="w-full pt-4 px-1 flex flex-col items-center text-center">
                                            <h3 class="name-label leading-tight">{{ member.name }}</h3>
                                            <span class="inline-block px-2.5 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 mb-1.5 mt-1.5">
                                                {{ member.role }}
                                            </span>
                                            <p class="subunit-label line-clamp-2 max-w-[90%]">{{ member.department }}</p>
                                        </div>
                                    </div>
                                </transition>
                            </template>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <!-- 4. STRATEGIC OBJECTIVES SECTION -->
        <section class="reveal-section py-20 lg:py-28 max-w-7xl mx-auto px-6 lg:px-8 border-t border-neutral-200/50 dark:border-neutral-800/30">
            <div class="space-y-16">
                
                <!-- Centered Header -->
                <div class="text-center max-w-3xl mx-auto space-y-4">
                    <span class="text-xs font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Plan de Desarrollo</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">Objetivos Estratégicos</h2>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed">
                        Trazamos metas específicas a corto y mediano plazo para garantizar que nuestra labor tenga un impacto medible, estructurado y sostenible en la sociedad puneña.
                    </p>
                </div>

                <!-- Mobile / Tablet layout (Grid) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 md:hidden">
                    <div 
                        v-for="(obj, index) in objectives" 
                        :key="obj.title" 
                        class="group relative flex items-start gap-4 p-6 rounded-2xl border border-neutral-200/80 dark:border-neutral-800/80 bg-white/95 dark:bg-neutral-900/60 shadow-sm hover:shadow-md transition-all duration-300 text-left"
                    >
                        <span class="absolute top-4 right-6 text-[10px] font-black text-neutral-300 dark:text-neutral-700 select-none">
                            0{{ index + 1 }}
                        </span>
                        <div class="p-3 rounded-xl shrink-0" :class="obj.color">
                            <component :is="obj.icon" class="size-5" />
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-sm font-extrabold text-neutral-900 dark:text-white leading-tight">
                                {{ obj.title }}
                            </h3>
                            <p class="text-xs text-neutral-600 dark:text-neutral-400 leading-normal font-normal">
                                {{ obj.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Desktop Circular Layout -->
                <div class="relative w-full max-w-4xl mx-auto h-[620px] hidden md:block select-none overflow-visible py-10">
                    <!-- Concentric background rotating ring 1 (Dashed) -->
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[430px] h-[430px] rounded-full border-2 border-dashed border-indigo-500/20 dark:border-indigo-400/25 animate-[spin_60s_linear_infinite] z-0"></div>
                    <!-- Concentric background rotating ring 2 (Dashed, opposite) -->
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[460px] h-[460px] rounded-full border-2 border-dashed border-emerald-500/10 dark:border-emerald-400/15 animate-[spin_90s_linear_infinite_reverse] z-0"></div>

                    <!-- Center Badge Circle -->
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-20 flex flex-col items-center justify-center text-center size-36 rounded-full bg-gradient-to-tr from-indigo-600 via-indigo-700 to-emerald-600 dark:from-indigo-950 dark:via-indigo-900 dark:to-emerald-950 text-white shadow-2xl border-4 border-white dark:border-neutral-900 transition-transform duration-500 hover:scale-105 group">
                        <div class="absolute inset-0 rounded-full bg-indigo-500/20 blur-xl group-hover:blur-2xl transition-all duration-300"></div>
                        <span class="relative z-10 text-[9px] font-extrabold uppercase tracking-widest text-indigo-200 dark:text-indigo-300">DPESEC</span>
                        <h3 class="relative z-10 text-sm font-black uppercase mt-1 leading-tight tracking-wider">Objetivos<br/>Clave</h3>
                        <div class="relative z-10 size-1.5 rounded-full bg-emerald-400 mt-2 animate-ping"></div>
                    </div>

                    <!-- Dynamic Objective Cards around Center -->
                    <div 
                        v-for="(obj, index) in objectives" 
                        :key="obj.title + '-circular'" 
                        class="absolute z-10 w-[220px] flex flex-col p-5 bg-white/95 dark:bg-neutral-900/90 border border-neutral-200/80 dark:border-neutral-800/80 rounded-2xl shadow-md transition-all duration-300 hover:scale-105 hover:shadow-xl hover:border-indigo-500/50 dark:hover:border-indigo-400/50 hover:z-30 text-left"
                        :style="getObjectiveStyle(index)"
                    >
                        <div class="flex items-center gap-2.5">
                            <div class="p-2.5 rounded-lg text-indigo-600 dark:text-indigo-400 bg-indigo-500/10 shrink-0">
                                <component :is="obj.icon" class="size-4.5" />
                            </div>
                            <span class="text-[9px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">Objetivo 0{{ index + 1 }}</span>
                        </div>
                        <h4 class="text-xs font-black text-neutral-900 dark:text-white mt-3 leading-snug">{{ obj.title }}</h4>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1 leading-normal font-normal line-clamp-3">{{ obj.description }}</p>
                    </div>
                </div>

            </div>
        </section>

        <!-- 5. VALUES SECTION -->
        <section class="reveal-section py-20 bg-neutral-50/50 dark:bg-neutral-900/10 border-t border-neutral-200/55 dark:border-neutral-800/40">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 space-y-16">
                
                <!-- Section Header -->
                <div class="text-center max-w-3xl mx-auto space-y-4">
                    <span class="text-xs font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Filosofía de Trabajo</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">Valores Institucionales</h2>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed">
                        Nuestra cultura organizacional se cimenta sobre principios éticos que guían cada proyecto, voluntariado y expresión cultural.
                    </p>
                </div>

                <!-- Continuous Marquee Carousel for Values -->
                <div class="relative w-full select-none">
                    <!-- Ambient fading gradients on the left and right edges for a premium vignette look -->
                    <div class="absolute left-0 top-0 bottom-0 w-24 bg-gradient-to-r from-neutral-50 dark:from-neutral-900 to-transparent z-10 pointer-events-none"></div>
                    <div class="absolute right-0 top-0 bottom-0 w-24 bg-gradient-to-l from-neutral-50 dark:from-neutral-900 to-transparent z-10 pointer-events-none"></div>

                    <!-- Marquee Track Wrapper (With vertical padding to prevent 3D flip scale clipping) -->
                    <div class="marquee-wrapper flex w-full overflow-hidden py-12">
                        <div class="marquee-track flex gap-8 animate-marquee">
                            
                            <!-- Duplicate items once so the marquee loop is seamless -->
                            <div 
                                v-for="(val, index) in [...values, ...values]" 
                                :key="val.title + '-' + index" 
                                class="value-placard-container flex flex-col items-center shrink-0 w-[260px] h-[310px]"
                                :style="{ '--label-tilt': index % 2 === 0 ? '-3.5deg' : '3.5deg' }"
                            >
                                <!-- The Placard Card with 3D Flip capability (Larger, h-[310px]) -->
                                <div class="value-card w-full h-[310px] relative z-10">
                                    <div class="card-inner w-full h-full">
                                        
                                        <!-- FRONT SIDE -->
                                        <div class="card-front bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 shadow-md">
                                            <!-- Top Portion: Tilted Color Label for Icon & Title only (shorter, h-[30%], text-[14px]) -->
                                            <div class="tilted-label h-[30%] w-[106%] -ml-[3%] -mt-[2%] rounded-2xl flex items-center justify-between px-4 py-2 text-white shadow-md z-20" :class="val.headerBg">
                                                <div class="flex items-center gap-2">
                                                    <!-- Icon with frosted glass backing -->
                                                    <div class="inline-flex size-8 rounded-lg items-center justify-center bg-white/20 border border-white/20 shadow-xs shrink-0">
                                                        <component :is="val.icon" class="size-4.5" />
                                                    </div>
                                                    <h3 class="font-extrabold text-[14px] tracking-wide leading-tight">
                                                        {{ val.title }}
                                                    </h3>
                                                </div>
                                                <!-- Indicator number -->
                                                <span class="text-[9px] font-black text-white/40 tracking-widest select-none shrink-0">
                                                    0{{ (index % values.length) + 1 }}
                                                </span>
                                            </div>

                                            <!-- Bottom Portion: Photograph Image (Larger, h-[70%], straight, no tilt) -->
                                            <div class="h-[70%] w-full overflow-hidden relative z-10 rounded-b-3xl">
                                                <img 
                                                    :src="val.image" 
                                                    :alt="val.title" 
                                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" 
                                                />
                                                <div class="absolute inset-0 bg-neutral-950/10 pointer-events-none"></div>
                                            </div>
                                        </div>

                                        <!-- BACK SIDE (Shows value description - themed per card) -->
                                        <div class="card-back border shadow-lg p-6 flex flex-col justify-center items-center text-center transition-colors duration-300" :class="val.backBg">
                                            <div class="inline-flex size-11 rounded-xl items-center justify-center border shadow-md shrink-0" :class="[val.iconBg, val.badgeBorder]">
                                                <component :is="val.icon" class="size-5.5" />
                                            </div>
                                            <h4 class="font-extrabold text-base mt-3.5 leading-tight tracking-tight" :class="val.titleColor">
                                                {{ val.title }}
                                            </h4>
                                            <p class="text-[12px] mt-2.5 leading-relaxed font-medium" :class="val.descColor">
                                                {{ val.description }}
                                            </p>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </section>
    </PublicLayout>
</template>

<style scoped>
/* Scroll Reveal animation for about sections */
.reveal-section {
  opacity: 0;
  transform: translateY(35px);
  transition: opacity 1s cubic-bezier(0.16, 1, 0.3, 1), transform 1s cubic-bezier(0.16, 1, 0.3, 1);
  will-change: transform, opacity;
}

.reveal-section.is-visible {
  opacity: 1;
  transform: translateY(0);
}

/* ===== PORTRAIT PHOTO STYLE (REAL PICTURE FRAME EDITION) ===== */
.team-card {
  position: absolute;
  inset: 0;
  margin: auto;
  width: 370px;
  height: 390px;
  padding: 12px;
  border-radius: 20px;
  background: #ffffff;
  /* Thick white border like a real photo frame */
  border: 12px solid #ffffff;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15), 
              0 0 0 1px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: center;
  z-index: 10;
  transform: rotate(var(--tilt-angle, 2.5deg));
  transition: transform 0.4s cubic-bezier(0.25, 1, 0.5, 1),
              box-shadow 0.4s ease;
}

.dark .team-card {
  background: #18181b;
  border: 12px solid #18181b;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 
              0 0 0 1px rgba(255, 255, 255, 0.05);
}

.team-card:hover {
  /* Keep the tilt on hover, but scale slightly and elevate */
  transform: translateY(-8px) scale(1.02) rotate(var(--tilt-angle, 2.5deg));
  box-shadow: 0 30px 60px rgba(0, 0, 0, 0.22);
}

.portrait-image {
  height: 98%;
  width: auto;
  max-width: 98%;
  object-fit: contain;
  object-position: bottom;
  transition: transform 0.4s ease;
  filter: drop-shadow(0 6px 12px rgba(0, 0, 0, 0.08));
}

.team-card:hover .portrait-image {
  transform: scale(1.03);
}

.name-label {
  margin: 0;
  font-size: 1.05rem; 
  font-weight: 850;
  color: #0f172a;
  letter-spacing: -0.3px;
  line-height: 1.2;
}

.dark .name-label {
  color: #f8fafc;
}

.subunit-label {
  margin: 0;
  font-size: 0.68rem;
  font-weight: 500;
  color: #64748b; 
  line-height: 1.3;
}

.dark .subunit-label {
  color: #94a3b8;
}

/* ===== CARD FADE TRANSITION ===== */
/* Nota: Puedes cambiar '0.32s' por cualquier otro tiempo para ajustar la velocidad */
.card-fade-enter-active,
.card-fade-leave-active {
  transition: opacity 0.32s ease-out,
              transform 0.32s cubic-bezier(0.25, 1, 0.5, 1),
              filter 0.32s ease-out;
}

.card-fade-enter-from {
  opacity: 0;
  /* Mantiene exactamente la misma escala y rotación para evitar saltos y desplazamientos laterales */
  transform: translateY(100px) rotate(var(--tilt-angle, 2.5deg));
  filter: blur(5px);
}

.card-fade-leave-to {
  opacity: 0;
  /* Mantiene la rotación constante mientras se desliza hacia arriba */
  transform: translateY(-100px) rotate(var(--tilt-angle, 2.5deg));
  filter: blur(5px);
}

@keyframes floatIdle {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}

/* ===== INFINITE MARQUEE CAROUSEL ===== */
.marquee-wrapper {
  display: flex;
  overflow: hidden;
  width: 100%;
}

.marquee-track {
  display: flex;
  animation: marquee 30s linear infinite;
  will-change: transform;
}

/* Pausa el deslizamiento al hacer hover sobre el carrusel */
.marquee-track:hover {
  animation-play-state: paused;
}

@keyframes marquee {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(-50%);
  }
}

/* ===== 3D CARD FLIP STYLING ===== */
.value-card {
  perspective: 1000px;
  border-radius: 24px;
}

.card-inner {
  position: relative;
  width: 100%;
  height: 100%;
  transition: transform 0.65s cubic-bezier(0.4, 0, 0.2, 1);
  transform-style: preserve-3d;
}

/* Al hacer hover: rotación de 180 grados sobre el eje Y para mostrar la descripción */
.value-placard-container:hover .card-inner {
  transform: rotateY(180deg);
}

.card-front, .card-back {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
  border-radius: 24px;
  overflow: hidden;
}

.card-front {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  z-index: 2;
  transform: rotateY(0deg);
}

.card-back {
  transform: rotateY(180deg);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 20px;
  z-index: 1;
}

/* Etiqueta inclinada por defecto (solo se inclina el cartel de color, no la foto ni la carta al hacer hover) */
.tilted-label {
  transform: rotate(var(--label-tilt, -3.5deg));
  transform-origin: center center;
  transition: transform 0.3s ease;
}

.value-placard-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: transform 0.4s cubic-bezier(0.25, 1, 0.5, 1);
  will-change: transform;
}

/* Al hacer hover: se hace más grande, se eleva y gira en 3D, pero se mantiene recto en el eje Z para mejor lectura */
.value-placard-container:hover {
  transform: scale(1.10) translateY(-8px);
  z-index: 50;
}

.value-placard-container:hover .value-card {
  box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.45);
  border-color: rgba(255, 255, 255, 0.3);
}

.dark .value-placard-container:hover .value-card {
  border-color: rgba(99, 102, 241, 0.4);
}
</style>
