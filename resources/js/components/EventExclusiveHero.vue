<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { ArrowLeft, Calendar, FileText, MapPin, Trophy, UserCheck, Users } from '@lucide/vue';
import type { ExclusiveEventView } from '@/lib/exclusiveEventView';

const props = defineProps<{
    view: ExclusiveEventView;
    previewMode?: 'desktop' | 'mobile' | null;
    mobileHeaderOverlay?: boolean;
}>();
const emit = defineEmits<{ navigate: [sectionId: string] }>();

const now = ref(Date.now());
let countdownInterval: ReturnType<typeof setInterval> | undefined;
const loadedCustomFaces = new Map<string, { url: string; face: FontFace }>();
const fontFamilyCss = (name: string) => `${name.includes(' ') ? `'${name}'` : name}, sans-serif`;
watch(() => props.view.customFonts, (fonts) => {
    if (typeof document === 'undefined' || typeof FontFace === 'undefined') return;
    fonts.forEach(({ name, url }) => {
        if (!url || loadedCustomFaces.get(name)?.url === url) return;
        const face = new FontFace(name, `url("${url}")`);
        face.load().then((loadedFace) => {
            const previous = loadedCustomFaces.get(name)?.face;
            if (previous) document.fonts.delete(previous);
            document.fonts.add(loadedFace);
            loadedCustomFaces.set(name, { url, face: loadedFace });
        }).catch(() => undefined);
    });
}, { deep: true, immediate: true });

const themeColors: Record<string, { accent: string; soft: string; strong: string }> = {
    emerald: { accent: '#34d399', soft: '#a7f3d0', strong: '#047857' },
    forest: { accent: '#4ade80', soft: '#bbf7d0', strong: '#15803d' },
    teal: { accent: '#2dd4bf', soft: '#99f6e4', strong: '#0f766e' },
    lime: { accent: '#a3e635', soft: '#d9f99d', strong: '#4d7c0f' },
    mint: { accent: '#6ee7b7', soft: '#d1fae5', strong: '#047857' },
};
const themeStyle = computed(() => {
    const color = themeColors[props.view.theme] || themeColors.emerald;
    const accent = props.view.customAccent || color.accent;
    const desktopOffsetScale = props.previewMode === 'desktop' ? 2 : 1;
    const mobileOffsetScale = props.previewMode === 'mobile' ? 1.25 : 1;
    return {
        '--event-accent': accent,
        '--event-soft': color.soft,
        '--event-strong': color.strong,
        '--event-title-offset-x': `${props.view.titleOffsetX * desktopOffsetScale}px`,
        '--event-title-offset-y': `${props.view.titleOffsetY * desktopOffsetScale}px`,
        '--event-mobile-title-size': `${30 * props.view.mobileTitleSizePercent / 100}px`,
        '--event-mobile-title-offset-x': `${props.view.mobileTitleOffsetX * mobileOffsetScale}px`,
        '--event-mobile-title-offset-y': `${props.view.mobileTitleOffsetY * mobileOffsetScale}px`,
        '--event-desktop-cover-zoom': String(props.view.desktopCoverZoom),
        '--event-desktop-cover-shift-x': `${(50 - props.view.desktopCoverX) * (props.view.desktopCoverZoom - 1)}%`,
        '--event-desktop-cover-shift-y': `${(50 - props.view.desktopCoverY) * (props.view.desktopCoverZoom - 1)}%`,
        '--event-mobile-cover-zoom': String(props.view.mobileCoverZoom),
        '--event-mobile-cover-x': `${props.view.mobileCoverX}%`,
        '--event-mobile-cover-y': `${props.view.mobileCoverY}%`,
        '--event-mobile-cover-shift-x': `${(50 - props.view.mobileCoverX) * (props.view.mobileCoverZoom - 1)}%`,
        '--event-mobile-cover-shift-y': `${(50 - props.view.mobileCoverY) * (props.view.mobileCoverZoom - 1)}%`,
    } as Record<string, string>;
});
const titleFontSize = computed(() => {
    if (props.previewMode === 'desktop') return `${76 * props.view.titleSizePercent / 100 * 0.7125 * 0.95 * 0.95}px`;
    if (props.previewMode === 'mobile') return `${36.375 * props.view.mobileTitleSizePercent / 100 * 0.95 * 0.95}px`;
    return `${76 * props.view.titleSizePercent / 100}px`;
});

const countdown = computed(() => {
    const remaining = props.view.targetDate === null ? 0 : Math.max(0, props.view.targetDate - now.value);
    const seconds = Math.floor(remaining / 1000);
    return [
        { label: 'Días', value: String(Math.floor(seconds / 86400)).padStart(2, '0') },
        { label: 'Horas', value: String(Math.floor((seconds % 86400) / 3600)).padStart(2, '0') },
        { label: 'Minutos', value: String(Math.floor((seconds % 3600) / 60)).padStart(2, '0') },
        { label: 'Segundos', value: String(seconds % 60).padStart(2, '0') },
    ];
});

const sections = [
    { id: 'documentos', label: 'Documentos', icon: FileText, iconTint: 'bg-sky-300/20 text-sky-100' },
    { id: 'cronograma', label: 'Cronograma', icon: Calendar, iconTint: 'bg-indigo-300/20 text-indigo-100' },
    { id: 'participantes', label: 'Registro', icon: Users, iconTint: 'bg-emerald-300/20 text-emerald-100' },
    { id: 'ubicaciones', label: 'Ubicaciones', icon: MapPin, iconTint: 'bg-violet-300/20 text-violet-100' },
    { id: 'resultados', label: 'Resultados', icon: Trophy, iconTint: 'bg-orange-300/20 text-orange-100' },
];

const selectSection = (sectionId: string) => {
    if (!props.previewMode) emit('navigate', sectionId);
};

onMounted(() => { countdownInterval = setInterval(() => { now.value = Date.now(); }, 1000); });
onUnmounted(() => { if (countdownInterval) clearInterval(countdownInterval); });
</script>

<template>
    <section
        class="event-hero relative isolate flex items-center overflow-hidden bg-slate-950 text-white"
        :class="[previewMode ? (previewMode === 'mobile' ? 'event-hero--mobile-preview h-[calc(100svh-9rem)] w-[320px] max-w-none flex-none origin-top scale-[0.8]' : 'event-hero--desktop-preview h-[200%] w-[200%] max-w-[2560px] origin-top-left scale-50') : 'min-h-[calc(100svh-9rem)] lg:min-h-[calc(100svh-6rem)]', { 'event-hero--mobile-header-overlay': mobileHeaderOverlay && !previewMode }]"
        :style="themeStyle"
    >
        <img v-if="view.image" :src="view.image" :alt="view.title" class="event-hero__background absolute inset-0 -z-20 size-full object-cover" />
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-slate-950/95 via-slate-950/80 to-slate-950/30" />
        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-slate-950/80 via-transparent to-slate-950/20" />

        <div v-if="previewMode" class="event-hero__preview-navbar absolute inset-x-0 top-0 z-10 h-24 bg-black text-white">
            <div class="mx-auto flex h-full max-w-7xl items-center justify-between gap-5 px-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 flex-1 items-center justify-center gap-1.5 sm:justify-start sm:gap-3">
                    <img src="/images/unap-logo.png" alt="Logo UNA Puno" class="h-10 w-auto shrink-0 object-contain sm:h-12" />
                    <div class="flex min-w-0 flex-col items-center text-center text-[9px] font-black leading-tight sm:text-xs lg:text-sm">
                        <span>Dirección de Proyección Social y</span>
                        <span>Extensión Cultural</span>
                    </div>
                    <img src="/images/dpsec-logo-20261113.png" alt="Logo DPSEC" class="h-10 w-auto shrink-0 rounded-full object-contain sm:h-12" />
                </div>
                <nav v-if="previewMode === 'desktop'" class="hidden shrink-0 items-center gap-4 text-xs font-medium text-white/85 lg:flex xl:gap-5" aria-label="Navegación principal">
                    <span>Inicio</span>
                    <span>Nosotros</span>
                    <span>Sub Unidades</span>
                    <span>Documentos</span>
                    <span>Eventos</span>
                    <span>Certificados</span>
                </nav>
            </div>
        </div>

        <a v-if="previewMode" href="#" @click.prevent :class="['absolute right-3 z-20 inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-black/30 px-2.5 py-1.5 text-[9px] font-bold text-white/85 backdrop-blur sm:right-5 sm:text-[10px]', previewMode === 'mobile' ? 'top-[116px]' : 'top-[124px]']">
            <ArrowLeft class="size-3" /> Volver a eventos
        </a>
        <a v-else href="/eventos" class="absolute right-5 top-5 z-10 inline-flex items-center gap-2 rounded-full border border-white/25 bg-black/30 px-4 py-2.5 text-xs font-bold text-white/85 shadow-lg backdrop-blur transition hover:bg-black/50 hover:text-white sm:right-8 sm:top-7 sm:text-sm">
            <ArrowLeft class="size-4" /> Volver a eventos
        </a>

        <div
            class="event-hero__content mx-auto flex w-full max-w-7xl flex-col justify-between gap-5 px-4 pb-5 pt-20 sm:px-8 sm:pb-7 sm:pt-24 lg:px-10 lg:py-14"
            :class="previewMode ? 'h-full' : 'min-h-[calc(100svh-9rem)] lg:min-h-[calc(100svh-6rem)]'"
        >
            <div class="event-hero__heading [container-type:inline-size]" :class="{ 'text-center': view.titlePosition === 'center', 'text-right': view.titlePosition === 'right', 'text-left': view.titlePosition === 'left', 'mobile-title-center': view.mobileTitlePosition === 'center', 'mobile-title-right': view.mobileTitlePosition === 'right' }">
                <div class="mb-3 flex flex-wrap gap-x-4 gap-y-1.5 text-xs font-semibold text-white/90 sm:mb-5 sm:gap-x-6 sm:gap-y-2 sm:text-sm lg:text-base" :class="[view.titlePosition === 'center' ? 'justify-center' : view.titlePosition === 'right' ? 'justify-end' : 'justify-start', view.mobileTitlePosition === 'center' ? 'mobile-meta-center' : view.mobileTitlePosition === 'right' ? 'mobile-meta-right' : 'mobile-meta-left']">
                    <span class="inline-flex min-w-0 items-center gap-1.5"><Calendar class="size-3.5 shrink-0 text-[var(--event-accent)] sm:size-4" /><span>{{ view.date }}<template v-if="view.time"> · {{ view.time }}</template></span></span>
                    <span v-if="view.location" class="inline-flex min-w-0 items-center gap-1.5"><MapPin class="size-3.5 shrink-0 text-[var(--event-accent)] sm:size-4" /><span class="break-words">{{ view.location }}</span></span>
                </div>
                <p v-if="view.subtitle" class="mb-2 max-w-3xl break-words text-sm font-semibold sm:mb-3 sm:text-base lg:text-lg" :class="[view.titlePosition === 'center' ? 'mx-auto' : view.titlePosition === 'right' ? 'ml-auto' : '', view.mobileTitlePosition === 'center' ? 'mobile-subtitle-center' : view.mobileTitlePosition === 'right' ? 'mobile-subtitle-right' : 'mobile-subtitle-left']" :style="{ color: view.subtitleColor, fontFamily: fontFamilyCss(view.subtitleFont), letterSpacing: `${view.subtitleLetterSpacing}px` }">{{ view.subtitle }}</p>
                <h1 :aria-label="view.title" :style="{ fontSize: titleFontSize, fontFamily: fontFamilyCss(view.titleFont), letterSpacing: `${view.titleLetterSpacing}px` }" class="event-hero__title overflow-hidden font-black leading-[1.02] tracking-tight text-balance">
                    <span class="block break-words" :style="{ color: view.titleFirstColor }">{{ view.titleFirst }}</span>
                    <span class="block break-words" :style="{ color: view.titleSecondColor }">{{ view.titleSecond }}</span>
                </h1>
            </div>

            <div class="event-hero__lower mt-auto">
                <div class="grid gap-3 lg:grid-cols-2 lg:items-end lg:gap-6">
                    <div class="min-w-0">
                        <h2 class="mb-2 text-[10px] font-extrabold uppercase tracking-[0.18em] text-[var(--event-soft)] sm:mb-3 sm:text-xs sm:tracking-[0.2em]">Sobre el evento</h2>
                        <div class="rounded-xl border border-white/15 bg-black/25 p-3 backdrop-blur-md sm:rounded-2xl sm:p-5">
                            <p class="line-clamp-3 break-words text-xs leading-5 text-white/85 sm:text-sm sm:leading-6 lg:text-base">{{ view.about }}</p>
                        </div>
                    </div>
                    <div class="w-full min-w-0 lg:justify-self-end">
                        <p class="mb-2 text-[10px] font-extrabold uppercase tracking-[0.16em] text-[var(--event-soft)] sm:mb-3 sm:text-xs sm:tracking-[0.25em]">{{ view.status === 'Pasados' ? 'Evento finalizado' : view.status === 'EnCurso' ? 'El evento está en curso' : 'El evento comienza en' }}</p>
                        <div class="grid grid-cols-4 gap-1.5 sm:gap-3">
                            <div v-for="unit in countdown" :key="unit.label" class="rounded-xl border border-white/20 bg-black/30 px-1 py-2 text-center shadow-xl backdrop-blur-md sm:rounded-2xl sm:px-3 sm:py-4">
                                <div class="text-xl font-black tabular-nums sm:text-3xl lg:text-5xl">{{ unit.value }}</div>
                                <div class="mt-0.5 text-[8px] font-bold uppercase tracking-wider text-white/65 sm:mt-1 sm:text-[10px]">{{ unit.label }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-2 flex w-full gap-1.5 overflow-x-auto pb-1 sm:mt-4 sm:gap-3" aria-label="Secciones del evento">
                    <button v-for="section in sections" :key="section.id" type="button" @click="selectSection(section.id)" class="flex min-h-11 min-w-[6.8rem] flex-1 items-center gap-1.5 rounded-xl border border-white/15 bg-black/25 px-2 py-1.5 text-left text-[10px] font-extrabold text-white shadow-lg backdrop-blur-md transition hover:bg-white/15 sm:min-h-14 sm:min-w-0 sm:gap-3 sm:rounded-2xl sm:px-4 sm:py-2 sm:text-sm">
                        <span class="grid size-7 shrink-0 place-items-center rounded-lg border border-white/10 shadow-sm sm:size-10 sm:rounded-xl" :class="section.iconTint"><component :is="section.icon" class="size-3.5 sm:size-5" /></span>
                        <span class="whitespace-nowrap">{{ section.label }}</span>
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@media (max-width: 1023px) {
    .event-hero--mobile-header-overlay {
        margin-top: -7.75rem;
        min-height: calc(100svh - 1.25rem) !important;
    }

    .event-hero--mobile-header-overlay .event-hero__content {
        min-height: calc(100svh - 1.25rem) !important;
        padding-top: 11.25rem !important;
    }
}

@media (min-width: 640px) and (max-width: 1023px) {
    .event-hero--mobile-header-overlay .event-hero__content {
        padding-top: 13.75rem !important;
    }
}

.event-hero__background {
    object-position: center;
    transform: translate(var(--event-desktop-cover-shift-x), var(--event-desktop-cover-shift-y)) scale(var(--event-desktop-cover-zoom));
}

.event-hero--mobile-preview .event-hero__background {
    object-position: var(--event-mobile-cover-x) var(--event-mobile-cover-y);
    transform: translate(var(--event-mobile-cover-shift-x), var(--event-mobile-cover-shift-y)) scale(var(--event-mobile-cover-zoom));
}

.event-hero--mobile-preview .event-hero__content {
    padding: 32px 16px 12px;
}

.event-hero__heading {
    transform: translate(var(--event-title-offset-x), var(--event-title-offset-y));
}

.event-hero--desktop-preview .event-hero__content {
    display: grid;
    grid-template-rows: minmax(0, 1fr) auto;
    justify-content: normal;
    padding-bottom: 12px;
}

.event-hero--desktop-preview .event-hero__heading {
    width: 90%;
    margin-inline: auto;
    min-height: 0;
    align-self: start;
}

.event-hero--desktop-preview .event-hero__heading > div:first-child {
    font-size: 12px;
}

.event-hero--desktop-preview .event-hero__heading > div:first-child svg {
    width: 14px;
    height: 14px;
}

.event-hero--desktop-preview .event-hero__lower {
    min-height: 0;
    margin-top: 0;
    align-self: end;
    transform: scale(0.9, 0.95);
    transform-origin: bottom center;
}

.event-hero--desktop-preview .event-hero__lower > .grid > div:first-child > div {
    position: relative;
    top: 6px;
    padding-block: 10px;
}

.event-hero--desktop-preview .event-hero__lower > .grid > div:first-child > div > p {
    font-size: 90%;
    line-height: 1.25;
}

.event-hero--desktop-preview .event-hero__lower > .grid > div:nth-child(2) .grid > div {
    position: relative;
    top: 6px;
    padding-block: 10px;
}

.event-hero--desktop-preview .event-hero__lower > .grid > div:nth-child(2) .grid > div > div:first-child {
    font-size: 2.5rem;
}

.event-hero--desktop-preview .event-hero__lower > .grid > div:nth-child(2) .grid > div > div:last-child {
    font-size: 9px;
}

@media (max-width: 767px) {
    .event-hero__background {
        object-position: var(--event-mobile-cover-x) var(--event-mobile-cover-y);
        transform: translate(var(--event-mobile-cover-shift-x), var(--event-mobile-cover-shift-y)) scale(var(--event-mobile-cover-zoom));
    }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__heading {
        transform: translate(var(--event-mobile-title-offset-x), var(--event-mobile-title-offset-y));
    }

    .event-hero__heading.mobile-title-center { text-align: center; }
    .event-hero__heading.mobile-title-right { text-align: right; }
    .mobile-meta-center { justify-content: center; }
    .mobile-meta-right { justify-content: flex-end; }
    .event-hero__heading.mobile-title-left > .mobile-meta-left { align-items: flex-start; }
    .event-hero__heading.mobile-title-center > .mobile-meta-center { align-items: center; }
    .event-hero__heading.mobile-title-right > .mobile-meta-right { align-items: flex-end; }
    .event-hero__heading > .mobile-subtitle-center { margin-right: auto; margin-left: auto; }
    .event-hero__heading > .mobile-subtitle-right { margin-right: 0; margin-left: auto; }
}

@media (max-width: 639px) {
    .event-hero:not(.event-hero--mobile-preview) .event-hero__title {
        font-size: var(--event-mobile-title-size) !important;
    }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__content {
        justify-content: flex-start;
        gap: 8px;
        padding: 56px 16px 12px;
    }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__heading {
        margin-top: 0;
    }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__lower {
        display: flex;
        flex: 1 1 0%;
        flex-direction: column;
        justify-content: flex-end;
        min-height: 0;
        margin-top: 0;
        padding-bottom: 60px;
    }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__heading > div:first-child {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
        margin-bottom: 8px;
        font-size: 11px;
    }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__heading.text-center > div:first-child {
        align-items: center;
    }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__heading.text-right > div:first-child {
        align-items: flex-end;
    }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__heading.mobile-title-left > .mobile-meta-left { align-items: flex-start; }
    .event-hero:not(.event-hero--mobile-preview) .event-hero__heading.mobile-title-center > .mobile-meta-center { align-items: center; }
    .event-hero:not(.event-hero--mobile-preview) .event-hero__heading.mobile-title-right > .mobile-meta-right { align-items: flex-end; }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__heading > h1 + p {
        display: -webkit-box;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        margin-top: 6px;
        font-size: 12px;
        line-height: 1.35;
    }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__lower > .grid {
        gap: 8px;
    }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__lower > .grid > div:first-child > h2 {
        margin-bottom: 5px;
        font-size: 10px;
    }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__lower > .grid > div:first-child > div {
        padding: 8px 10px;
    }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__lower > .grid > div:first-child p {
        -webkit-line-clamp: 2;
        font-size: 11px;
        line-height: 16px;
    }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__lower > .grid > div:nth-child(2) > p {
        margin-bottom: 5px;
        font-size: 9px;
    }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__lower > .grid > div:nth-child(2) .grid > div {
        padding: 6px 3px;
    }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__lower > .grid > div:nth-child(2) .grid > div > div:first-child {
        font-size: 18px;
    }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__lower > .grid > div:nth-child(2) .grid > div > div:last-child {
        font-size: 7px;
    }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__lower > div:last-child {
        position: absolute;
        right: 16px;
        bottom: 12px;
        left: 16px;
        width: auto;
        margin-top: 0;
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 3px;
    }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__lower > div:last-child button {
        min-height: 44px;
        min-width: 0;
        flex-direction: column;
        justify-content: center;
        gap: 2px;
        padding: 3px 1px;
        font-size: 6.5px;
        line-height: 1.05;
        text-align: center;
    }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__lower > div:last-child button > span:first-child {
        width: 18px;
        height: 18px;
    }

    .event-hero:not(.event-hero--mobile-preview) .event-hero__lower > div:last-child button > span:last-child {
        white-space: normal;
        overflow-wrap: anywhere;
    }
}

.event-hero--mobile-preview .event-hero__heading > div:first-child {
    margin-bottom: 4px;
}

.event-hero--mobile-preview .event-hero__heading > p {
    margin-bottom: 2px;
    font-size: 12.96px;
    line-height: 1.25;
}

.event-hero--mobile-preview .event-hero__heading {
    container-type: inline-size;
    transform: translate(var(--event-mobile-title-offset-x), var(--event-mobile-title-offset-y));
}

.event-hero--mobile-preview .event-hero__heading.mobile-title-center { text-align: center; }
.event-hero--mobile-preview .event-hero__heading.mobile-title-right { text-align: right; }
.event-hero--mobile-preview .mobile-meta-center { justify-content: center; }
.event-hero--mobile-preview .mobile-meta-right { justify-content: flex-end; }
.event-hero--mobile-preview .event-hero__heading.mobile-title-left > .mobile-meta-left { align-items: flex-start; }
.event-hero--mobile-preview .event-hero__heading.mobile-title-center > .mobile-meta-center { align-items: center; }
.event-hero--mobile-preview .event-hero__heading.mobile-title-right > .mobile-meta-right { align-items: flex-end; }
.event-hero--mobile-preview .event-hero__heading > .mobile-subtitle-center { margin-right: auto; margin-left: auto; }
.event-hero--mobile-preview .event-hero__heading > .mobile-subtitle-right { margin-right: 0; margin-left: auto; }

.event-hero--mobile-preview .event-hero__heading.text-center > div:first-child {
    align-items: center;
}

.event-hero--mobile-preview .event-hero__heading.text-right > div:first-child {
    align-items: flex-end;
}

.event-hero--mobile-preview .event-hero__background {
    object-position: var(--event-mobile-cover-x) var(--event-mobile-cover-y);
    transform: translate(var(--event-mobile-cover-shift-x), var(--event-mobile-cover-shift-y)) scale(var(--event-mobile-cover-zoom));
}

@media (min-width: 640px) {
    .event-hero--mobile-preview .event-hero__content {
        justify-content: flex-start;
        gap: 8px;
        padding: 32px 16px 12px;
    }

    .event-hero--mobile-preview .event-hero__lower {
        display: flex;
        flex: 1 1 0%;
        flex-direction: column;
        justify-content: flex-end;
        min-height: 0;
        margin-top: 0;
        padding-bottom: 60px;
    }

    .event-hero--mobile-preview .event-hero__heading > div:first-child {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
        margin-bottom: 8px;
        font-size: 9.9px;
    }

    .event-hero--mobile-preview .event-hero__heading > h1 + p {
        display: -webkit-box;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        margin-top: 6px;
        font-size: 12px;
        line-height: 1.35;
    }

    .event-hero--mobile-preview .event-hero__lower > .grid {
        grid-template-columns: minmax(0, 1fr);
        gap: 8px;
    }

    .event-hero--mobile-preview .event-hero__lower > .grid > div:first-child > h2 {
        margin-bottom: 5px;
        font-size: 10px;
    }

    .event-hero--mobile-preview .event-hero__lower > .grid > div:first-child > div {
        padding: 8px 10px;
    }

    .event-hero--mobile-preview .event-hero__lower > .grid p {
        font-size: 11px;
        line-height: 16px;
    }

    .event-hero--mobile-preview .event-hero__lower > .grid > div:first-child p {
        -webkit-line-clamp: 2;
    }

    .event-hero--mobile-preview .event-hero__lower > .grid > div:nth-child(2) > p {
        margin-bottom: 5px;
        font-size: 9px;
    }

    .event-hero--mobile-preview .event-hero__lower > .grid > div:nth-child(2) .grid > div {
        padding: 6px 3px;
    }

    .event-hero--mobile-preview .event-hero__lower > .grid > div:nth-child(2) .grid > div > div:first-child {
        font-size: 18px;
    }

    .event-hero--mobile-preview .event-hero__lower > .grid > div:nth-child(2) .grid > div > div:last-child {
        font-size: 7px;
    }

    .event-hero--mobile-preview .event-hero__lower > div:last-child {
        position: absolute;
        right: 16px;
        bottom: 12px;
        left: 16px;
        width: auto;
        margin-top: 0;
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 3px;
    }

    .event-hero--mobile-preview .event-hero__lower > div:last-child button {
        min-height: 44px;
        min-width: 0;
        flex-direction: column;
        justify-content: center;
        gap: 2px;
        padding: 3px 1px;
        font-size: 6.5px;
        line-height: 1.05;
        text-align: center;
    }

    .event-hero--mobile-preview .event-hero__lower > div:last-child button > span:first-child {
        width: 18px;
        height: 18px;
    }

    .event-hero--mobile-preview .event-hero__lower > div:last-child button > span:last-child {
        white-space: normal;
        overflow-wrap: anywhere;
    }
}

.event-hero--mobile-preview .event-hero__lower > .grid > div:first-child > div {
    position: relative;
    top: 5px;
    padding-block: 5px;
}

.event-hero--mobile-preview .event-hero__lower > .grid > div:first-child p {
    font-size: 9px;
    line-height: 13px;
}

.event-hero--mobile-preview .event-hero__lower > .grid > div:nth-child(2) .grid > div {
    position: relative;
    top: 5px;
    padding-block: 4px;
}

.event-hero--mobile-preview .event-hero__lower > .grid > div:nth-child(2) .grid > div > div:first-child {
    font-size: 15px;
}

.event-hero--mobile-preview .event-hero__lower > .grid > div:nth-child(2) .grid > div > div:last-child {
    font-size: 6px;
}

.event-hero--mobile-preview .event-hero__lower > .grid,
.event-hero--mobile-preview .event-hero__lower > div:last-child {
    transform: scaleX(0.9);
    transform-origin: center;
}

.event-hero--desktop-preview .event-hero__content {
    padding-top: 9.5rem;
}

.event-hero--mobile-preview .event-hero__content {
    padding-top: 9.5rem;
}

</style>
