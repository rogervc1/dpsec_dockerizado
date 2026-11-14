<script setup lang="ts">
import { Check, Download, ExternalLink, FileText, Minus, Plus, Trophy, UserCheck, X } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import EventExclusiveHero from '@/components/EventExclusiveHero.vue';
import EventScheduleTimeline from '@/components/EventScheduleTimeline.vue';
import EventRegistrationForm from '@/components/EventRegistrationForm.vue';
import { getExclusiveEventView } from '@/lib/exclusiveEventView';
import PublicLayout from '@/layouts/PublicLayout.vue';

interface PublicEventDocument {
    id: number; title: string; description: string | null; original_name: string;
    mime_type: string; size_bytes: number; uploaded_at: string | null;
    preview_url: string; download_url: string;
}
const props = defineProps<{ event: Record<string, any>; documents: PublicEventDocument[] }>();
const view = computed(() => getExclusiveEventView(props.event));
const selectedDocument = ref<PublicEventDocument | null>(null);
const selectedRegistrationForm = ref<any | null>(null);
const registrationForms = computed(() => {
    const section = props.event.exclusive_page_content?.participantes || {};
    const forms = Array.isArray(section.forms)
        ? section.forms.map((form: any, index: number) => ({ form, key: String(index) }))
        : section.forms && typeof section.forms === 'object'
            ? Object.entries(section.forms).map(([key, form]) => ({ form, key }))
            : section.form ? [{ form: section.form, key: 'default' }] : [];
    return forms.filter(({ form }: any) => form && typeof form === 'object').map(({ form, key }: any, index: number) => ({
        ...form, id: String(form.id || (Array.isArray(section.forms) ? `form-${index + 1}` : key)),
    }));
});
const registrationFormStatus = (form: Record<string, any>) => {
    if (form.enabled !== true) return 'Inscripciones deshabilitadas';
    const now = Date.now();
    const opensAt = form.opens_at ? new Date(form.opens_at).getTime() : null;
    const closesAt = form.closes_at ? new Date(form.closes_at).getTime() : null;
    if (opensAt && now < opensAt) return `Abre el ${new Date(form.opens_at).toLocaleString('es-PE')}`;
    if (closesAt && now > closesAt) return 'Inscripciones cerradas';
    return 'Inscripciones abiertas';
};
const registrationFormIsOpen = (form: Record<string, any>) => registrationFormStatus(form) === 'Inscripciones abiertas';
const documentZoom = ref(100);
const activeLocationTooltip = ref<string | null>(null);
const locationSection = computed(() => props.event.exclusive_page_content?.ubicaciones || {});
const resultsSection = computed(() => props.event.exclusive_page_content?.resultados || {});
const resultCategories = computed(() => Array.isArray(resultsSection.value.categories) ? resultsSection.value.categories.filter((category: any) => category?.name && Array.isArray(category.winners) && category.winners.length) : []);
const activeResultCategory = ref(0);
const galleryItems = computed(() => Array.isArray(resultsSection.value.gallery) ? resultsSection.value.gallery.filter((item: any) => item?.image) : []);
const activeGalleryItem = ref(0);
let resultCarouselTimer: ReturnType<typeof setInterval> | null = null;
let galleryCarouselTimer: ReturnType<typeof setInterval> | null = null;
const youtubeEmbedUrl = computed(() => {
    const raw = String(resultsSection.value.youtube_url || '').trim();
    if (!raw) return '';
    try {
        const url = new URL(raw);
        let videoId = '';
        if (url.hostname === 'youtu.be' || url.hostname.endsWith('.youtu.be')) videoId = url.pathname.split('/').filter(Boolean)[0] || '';
        else if (url.hostname.includes('youtube.com')) videoId = url.searchParams.get('v') || url.pathname.match(/\/(?:embed|shorts|live)\/([^/?]+)/)?.[1] || '';
        return /^[\w-]{11}$/.test(videoId) ? `https://www.youtube-nocookie.com/embed/${videoId}` : '';
    } catch { return ''; }
});
const locationMapViewport = ref<HTMLElement | null>(null);
const locationMapImage = ref<HTMLImageElement | null>(null);
const locationMapSize = ref({ width: 0, height: 0 });
let locationMapObserver: ResizeObserver | null = null;
const fitLocationMap = () => {
    const viewport = locationMapViewport.value;
    const image = locationMapImage.value;
    if (!viewport || !image?.naturalWidth || !image.naturalHeight) return;
    const maxHeight = window.innerHeight * 0.72;
    const scale = Math.min(viewport.clientWidth / image.naturalWidth, maxHeight / image.naturalHeight);
    locationMapSize.value = {
        width: Math.floor(image.naturalWidth * scale),
        height: Math.floor(image.naturalHeight * scale),
    };
};
const locationMapStyle = computed(() => ({
    width: `${locationMapSize.value.width}px`,
    height: `${locationMapSize.value.height}px`,
}));
onMounted(() => {
    if (locationMapViewport.value) {
        locationMapObserver = new ResizeObserver(fitLocationMap);
        locationMapObserver.observe(locationMapViewport.value);
    }
    window.addEventListener('resize', fitLocationMap);
    fitLocationMap();
    if (resultCategories.value.length > 1) {
        resultCarouselTimer = setInterval(() => { activeResultCategory.value = (activeResultCategory.value + 1) % resultCategories.value.length; }, 5000);
    }
    if (galleryItems.value.length > 1) {
        galleryCarouselTimer = setInterval(() => { activeGalleryItem.value = (activeGalleryItem.value + 1) % galleryItems.value.length; }, 5000);
    }
});
onBeforeUnmount(() => {
    locationMapObserver?.disconnect();
    window.removeEventListener('resize', fitLocationMap);
    if (resultCarouselTimer) clearInterval(resultCarouselTimer);
    if (galleryCarouselTimer) clearInterval(galleryCarouselTimer);
});
const locationItems = computed(() => Array.isArray(locationSection.value.locations)
    ? locationSection.value.locations.map((item: any) => ({ ...item, image: item?.image || item?.image_url || item?.image_path || '', x: Number(item?.x ?? 50), y: Number(item?.y ?? 50), color: item?.color || '#34d399' }))
        .filter((item: any) => item && item.name && item.image)
    : []);
const isDocumentImage = computed(() => !!selectedDocument.value?.mime_type.startsWith('image/'));
const canPreviewDocument = computed(() => !!selectedDocument.value && (selectedDocument.value.mime_type === 'application/pdf' || isDocumentImage.value || ['text/plain', 'text/csv'].includes(selectedDocument.value.mime_type)));
const previewUrl = computed(() => selectedDocument.value ? `${selectedDocument.value.preview_url}#toolbar=1&navpanes=0&zoom=${documentZoom.value}` : '');
const openDocument = (document: PublicEventDocument) => { selectedDocument.value = document; documentZoom.value = 100; };
const closeDocument = () => { selectedDocument.value = null; };
const formatDocumentSize = (bytes: number) => {
    if (bytes < 1024) return `${bytes} B`;
    const units = ['KB', 'MB', 'GB']; let value = bytes / 1024; let unit = 0;
    while (value >= 1024 && unit < units.length - 1) { value /= 1024; unit += 1; }
    return `${value.toFixed(1)} ${units[unit]}`;
};
const eventView = computed(() => ({
    ...props.event, ...view.value, registrationLink: props.event.registration_link, fbLink: props.event.fb_link,
    exclusiveSections: props.event.exclusive_page_content || {},
    mapLink: `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(view.value.location)}`,
}));
const scrollTo = (id: string) => document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
</script>

<template>
    <PublicLayout :title="eventView.title">
        <div class="w-full min-w-0 max-w-full overflow-x-hidden">
            <EventExclusiveHero :view="view" mobile-header-overlay @navigate="scrollTo" />
            <main class="bg-neutral-50 dark:bg-neutral-950">
                <section id="documentos" class="scroll-mt-40 border-b border-neutral-200 px-5 py-16 dark:border-neutral-800 sm:px-8 md:py-24">
                    <div class="mx-auto max-w-7xl"><p class="text-xs font-extrabold uppercase tracking-[0.24em] text-[var(--event-strong)] dark:text-[var(--event-accent)]">01 / Recursos</p><h2 class="mt-3 text-3xl font-black tracking-tight text-neutral-900 dark:text-white sm:text-5xl">Documentos</h2>
                        <div v-if="documents.length" class="mt-8 grid gap-3 md:grid-cols-2"><article v-for="document in documents" :key="document.id" class="flex min-w-0 items-start gap-4 rounded-2xl border border-neutral-200 bg-white p-5 dark:border-neutral-800 dark:bg-neutral-900"><span class="grid size-12 shrink-0 place-items-center rounded-xl" :style="{ backgroundColor: 'color-mix(in srgb, var(--event-accent) 18%, transparent)', color: 'var(--event-accent)' }"><FileText class="size-5" /></span><div class="min-w-0 flex-1"><h3 class="break-words font-bold text-neutral-900 dark:text-white">{{ document.title }}</h3><p v-if="document.description" class="mt-1 whitespace-pre-line text-sm text-neutral-500 dark:text-neutral-400">{{ document.description }}</p><p class="mt-2 truncate text-xs text-neutral-400">{{ document.original_name }} · {{ formatDocumentSize(document.size_bytes) }}<span v-if="document.uploaded_at"> · {{ new Date(document.uploaded_at).toLocaleDateString() }}</span></p></div><button type="button" @click="openDocument(document)" class="inline-flex shrink-0 items-center gap-2 rounded-lg px-3 py-2 text-xs font-extrabold text-white" :style="{ backgroundColor: 'var(--event-strong)' }">Ver <ExternalLink class="size-3.5" /></button></article></div>
                        <div v-else class="mt-8 rounded-3xl border border-dashed border-neutral-300 bg-white p-8 text-center dark:border-neutral-700 dark:bg-neutral-900"><FileText class="mx-auto size-8 text-neutral-400" /><p class="mt-3 text-sm text-neutral-500">Aún no hay documentos publicados para este evento.</p></div>
                        <a v-if="eventView.exclusiveSections.documentos?.link" :href="eventView.exclusiveSections.documentos.link" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-[var(--event-strong)] hover:underline dark:text-[var(--event-accent)]">Abrir recurso relacionado <ExternalLink class="size-4" /></a>
                    </div>
                </section>
                <section id="cronograma" class="scroll-mt-40 border-b border-neutral-200 bg-white px-5 py-16 dark:border-neutral-800 dark:bg-neutral-900/40 sm:px-8 md:py-24"><div class="mx-auto max-w-7xl"><p class="text-xs font-extrabold uppercase tracking-[0.24em] text-[var(--event-strong)] dark:text-[var(--event-accent)]">02 / Agenda</p><h2 class="mt-3 text-3xl font-black tracking-tight text-neutral-900 dark:text-white sm:text-5xl">Cronograma</h2><EventScheduleTimeline :event-date="view.eventDate" :event-time="view.time" :activities="eventView.exclusiveSections.cronograma?.activities || []" /></div></section>
                <section id="participantes" class="scroll-mt-40 border-b border-neutral-200 px-5 py-16 dark:border-neutral-800 sm:px-8 md:py-24"><div class="mx-auto max-w-7xl"><p class="text-xs font-extrabold uppercase tracking-[0.24em] text-[var(--event-strong)] dark:text-[var(--event-accent)]">03 / Participación</p><h2 class="mt-3 text-3xl font-black tracking-tight text-neutral-900 dark:text-white sm:text-5xl">Registro de participantes</h2>
                    <div v-if="registrationForms.length" class="mt-8 grid grid-cols-1 gap-4 md:grid-cols-2"><article v-for="form in registrationForms" :key="form.id" class="flex flex-col items-start gap-4 rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900"><div class="w-full"><div class="flex flex-wrap items-center justify-between gap-2"><h3 class="text-lg font-extrabold text-neutral-900 dark:text-white">{{ form.title || 'Formulario de inscripción' }}</h3><span class="rounded-full px-3 py-1 text-xs font-bold" :class="registrationFormIsOpen(form) ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300'">{{ registrationFormStatus(form) }}</span></div><p v-if="form.description" class="mt-2 whitespace-pre-line text-sm text-neutral-500 dark:text-neutral-400">{{ form.description }}</p></div><button type="button" @click="selectedRegistrationForm = form" class="mt-auto inline-flex min-h-11 items-center gap-2 rounded-xl px-5 py-3 text-sm font-extrabold text-slate-950 transition hover:brightness-95" :style="{ backgroundColor: 'var(--event-accent)' }"><UserCheck class="size-4" /> {{ registrationFormIsOpen(form) ? 'Abrir formulario e inscribirme' : 'Consultar formulario' }}</button></article></div>
                    <div v-else class="mt-8 flex flex-col gap-6 rounded-3xl bg-slate-900 p-6 text-white sm:flex-row sm:items-center sm:justify-between sm:p-9"><div><h3 class="text-xl font-extrabold">Súmate a este evento</h3><p class="mt-2 max-w-xl whitespace-pre-line text-sm leading-6 text-white/65">Consulta la disponibilidad y completa tu inscripción mediante el formulario oficial.</p></div><a v-if="eventView.exclusiveSections.participantes?.link || eventView.registrationLink" :href="eventView.exclusiveSections.participantes?.link || eventView.registrationLink" target="_blank" rel="noopener noreferrer" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-extrabold text-slate-950" :style="{ backgroundColor: 'var(--event-accent)' }"><UserCheck class="size-4" /> Inscribirme <ExternalLink class="size-4" /></a><span v-else class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-white/10 px-5 py-3 text-sm font-bold text-white/65"><Check class="size-4" /> Registro no disponible</span></div>
                </div></section>
                <section id="ubicaciones" aria-label="Ubicaciones del evento" class="flex w-full flex-col border-b border-neutral-800 bg-neutral-950 px-5 pb-4 pt-4 sm:px-8 sm:pb-6 sm:pt-6">
                    <header class="mx-auto w-full max-w-7xl shrink-0">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-[var(--event-strong)] dark:text-[var(--event-accent)]">04 / Espacios</p>
                        <h2 class="mt-2 text-3xl font-black tracking-tight text-white sm:text-5xl">Ubicaciones</h2>
                    </header>
                    <div v-if="locationSection.croquis" ref="locationMapViewport" class="mt-3 flex w-full justify-center overflow-hidden">
                        <div class="relative shrink-0" :style="locationMapStyle">
                            <img ref="locationMapImage" :src="locationSection.croquis" alt="Croquis de las ubicaciones" class="block h-full w-full object-contain" @load="fitLocationMap" />
                            <button v-for="location in locationItems" :key="location.id" type="button" :title="location.name" :aria-label="`Ubicación: ${location.name}`" :aria-expanded="activeLocationTooltip === String(location.id)" @click="activeLocationTooltip = activeLocationTooltip === String(location.id) ? null : String(location.id)" class="group absolute z-10 grid size-4 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full border-[3px] border-white shadow-lg focus:outline-none focus-visible:ring-4 focus-visible:ring-white" :style="{ left: `${location.x}%`, top: `${location.y}%`, backgroundColor: location.color }">
                                <span class="absolute bottom-full left-1/2 mb-1 grid size-16 -translate-x-1/2 place-items-center rounded-full border-[4px] bg-white shadow-xl transition-transform group-hover:scale-110 group-focus-visible:scale-110" :style="{ borderColor: location.color }"><img :src="location.image" :alt="location.name" class="size-full rounded-full object-cover" /></span>
                                <span class="pointer-events-none absolute bottom-full left-1/2 mb-[4.5rem] -translate-x-1/2 whitespace-nowrap rounded-lg px-3 py-1.5 text-xs font-extrabold text-white shadow-lg transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100" :class="activeLocationTooltip === String(location.id) ? 'opacity-100' : 'opacity-0'" :style="{ backgroundColor: location.color }">{{ location.name }}</span>
                            </button>
                        </div>
                    </div>
                </section>
                <section id="resultados" class="scroll-mt-40 border-b border-neutral-200 px-5 py-16 dark:border-neutral-800 sm:px-8 md:py-24">
                    <div class="mx-auto max-w-7xl"><p class="text-xs font-extrabold uppercase tracking-[0.24em] text-[var(--event-strong)] dark:text-[var(--event-accent)]">05 / Cierre</p><h2 class="mt-3 text-3xl font-black tracking-tight text-neutral-900 dark:text-white sm:text-5xl">Resultados</h2>
                        <div v-if="resultCategories.length || resultsSection.type" class="mt-10 grid items-start gap-12 lg:grid-cols-2 lg:gap-10">
                            <section v-if="resultCategories.length" aria-label="Ganadores por categoría" class="min-w-0">
                                <div class="relative mx-auto min-h-[26rem] max-w-xl overflow-hidden rounded-3xl bg-gradient-to-br from-amber-50 via-orange-50 to-rose-100 px-4 pb-8 pt-6 dark:from-neutral-900 dark:via-neutral-900 dark:to-neutral-800 sm:min-h-[30rem] sm:px-8">
                                    <Transition mode="out-in" enter-active-class="transition duration-500" enter-from-class="translate-y-2 opacity-0" enter-to-class="translate-y-0 opacity-100" leave-active-class="transition duration-300" leave-from-class="translate-y-0 opacity-100" leave-to-class="-translate-y-2 opacity-0">
                                        <div v-if="resultCategories[activeResultCategory]" :key="resultCategories[activeResultCategory].id || activeResultCategory" class="flex h-full flex-col items-center">
                                            <h3 class="text-center text-xl font-black text-neutral-900 dark:text-white sm:text-2xl">{{ resultCategories[activeResultCategory].name }}</h3>
                                            <div class="relative mt-8 flex h-64 w-full max-w-md items-center justify-center sm:mt-10 sm:h-72">
                                                <article v-for="(winner, index) in resultCategories[activeResultCategory].winners.slice(0, 3)" :key="winner.id || index" class="absolute top-4 w-[43%] max-w-[190px] bg-white p-2 pb-12 shadow-xl ring-1 ring-neutral-200/80 transition-transform sm:p-2.5 sm:pb-14" :class="[
                                                    resultCategories[activeResultCategory].winners.length === 1 ? 'z-20 rotate-0' : index === 0 ? 'z-30 rotate-0' : index === 1 ? 'z-10 -translate-x-[56%] -rotate-6 translate-y-2' : 'z-10 translate-x-[56%] rotate-6 translate-y-2',
                                                ]">
                                                    <img :src="winner.image" :alt="winner.name" class="aspect-[4/5] w-full bg-neutral-100 object-cover" />
                                                    <span class="absolute bottom-8 left-1/2 grid size-10 -translate-x-1/2 place-items-center rounded-full border-2 border-amber-200 bg-gradient-to-br from-amber-300 to-yellow-600 text-sm font-black text-white shadow-md sm:bottom-9 sm:size-11">{{ ['1.º', '2.º', '3.º'][index] }}</span>
                                                    <p class="absolute inset-x-1 bottom-2 line-clamp-2 text-center font-serif text-xs font-bold text-neutral-800 sm:text-sm">{{ winner.name }}</p>
                                                </article>
                                            </div>
                                            <div v-if="resultCategories.length > 1" class="mt-auto flex items-center justify-center gap-2 pt-4" aria-label="Categorías de resultados">
                                                <button v-for="(category, index) in resultCategories" :key="category.id || index" type="button" :aria-label="`Ver ${category.name}`" :aria-current="activeResultCategory === index" @click="activeResultCategory = index" class="size-2.5 rounded-full transition" :class="activeResultCategory === index ? 'scale-125 bg-[var(--event-strong)]' : 'bg-neutral-400/50 hover:bg-neutral-500'" />
                                            </div>
                                        </div>
                                    </Transition>
                                </div>
                            </section>
                            <section v-if="resultsSection.type" aria-label="Detalle de resultados" class="min-w-0">
                                <div v-if="resultsSection.type === 'results_image' && resultsSection.results_image" class="overflow-hidden rounded-2xl border border-neutral-200 bg-white p-3 shadow-sm dark:border-neutral-800 dark:bg-neutral-900"><img :src="resultsSection.results_image" alt="Imagen con los resultados del evento" class="mx-auto max-h-[760px] w-full object-contain" /></div>
                                <div v-else-if="resultsSection.type === 'table' && resultsSection.participants?.length" class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900"><h3 class="border-b border-neutral-200 px-5 py-4 text-lg font-extrabold dark:border-neutral-800 dark:text-white">Puntajes por conjunto</h3><div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-neutral-50 text-xs uppercase tracking-wide text-neutral-500 dark:bg-neutral-800/70"><tr><th class="px-5 py-3">N.º</th><th class="px-5 py-3">Participante</th><th class="px-5 py-3 text-right">Puntaje</th></tr></thead><tbody><tr v-for="(participant, index) in resultsSection.participants" :key="participant.id || index" class="border-t border-neutral-100 dark:border-neutral-800"><td class="px-5 py-3 font-semibold text-neutral-400">{{ index + 1 }}</td><td class="px-5 py-3 font-bold text-neutral-800 dark:text-neutral-100">{{ participant.name }}</td><td class="px-5 py-3 text-right font-black tabular-nums text-neutral-900 dark:text-white">{{ participant.score }}</td></tr></tbody></table></div></div>
                                <div v-else-if="resultsSection.type === 'youtube' && youtubeEmbedUrl" class="overflow-hidden rounded-2xl bg-black shadow-lg"><div class="aspect-video"><iframe :src="youtubeEmbedUrl" title="Video de resultados del evento" class="h-full w-full" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen /></div></div>
                                <div v-else-if="resultsSection.type === 'gallery' && galleryItems.length" class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                                    <div class="relative bg-neutral-100 dark:bg-neutral-950">
                                        <Transition mode="out-in" enter-active-class="transition duration-500" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-300" leave-from-class="opacity-100" leave-to-class="opacity-0"><img :key="galleryItems[activeGalleryItem]?.id || activeGalleryItem" :src="galleryItems[activeGalleryItem]?.image" :alt="galleryItems[activeGalleryItem]?.title || `Imagen del evento ${activeGalleryItem + 1}`" class="aspect-[4/3] w-full object-contain"/></Transition>
                                        <button v-if="galleryItems.length > 1" type="button" aria-label="Imagen anterior" @click="activeGalleryItem = (activeGalleryItem - 1 + galleryItems.length) % galleryItems.length" class="absolute left-3 top-1/2 grid size-10 -translate-y-1/2 place-items-center rounded-full bg-black/60 text-2xl text-white shadow hover:bg-black/80">‹</button><button v-if="galleryItems.length > 1" type="button" aria-label="Imagen siguiente" @click="activeGalleryItem = (activeGalleryItem + 1) % galleryItems.length" class="absolute right-3 top-1/2 grid size-10 -translate-y-1/2 place-items-center rounded-full bg-black/60 text-2xl text-white shadow hover:bg-black/80">›</button>
                                    </div>
                                    <div class="flex items-start gap-4 p-4"><div class="min-w-0 flex-1"><h3 v-if="galleryItems[activeGalleryItem]?.title" class="font-extrabold text-neutral-900 dark:text-white">{{ galleryItems[activeGalleryItem].title }}</h3><p v-if="galleryItems[activeGalleryItem]?.description" class="mt-1 whitespace-pre-line text-sm leading-6 text-neutral-600 dark:text-neutral-300">{{ galleryItems[activeGalleryItem].description }}</p></div><span v-if="galleryItems.length > 1" class="shrink-0 text-xs font-bold tabular-nums text-neutral-400">{{ activeGalleryItem + 1 }} / {{ galleryItems.length }}</span></div>
                                </div>
                            </section>
                        </div>
                        <div v-else class="mt-8 rounded-3xl border border-dashed border-neutral-300 p-8 text-center dark:border-neutral-700 sm:p-14"><Trophy class="mx-auto size-9 text-[var(--event-accent)]" /><h3 class="mt-4 font-bold text-neutral-900 dark:text-white">Los resultados aparecerán aquí</h3><p class="mx-auto mt-2 max-w-lg whitespace-pre-line text-sm leading-6 text-neutral-500 dark:text-neutral-400">{{ `Publicaremos los resultados y comunicados finales de ${eventView.title} en esta sección.` }}</p></div>
                    </div>
                </section>
            </main>
        </div>
        <div v-if="selectedRegistrationForm" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/75 p-3 backdrop-blur-sm sm:p-5" role="dialog" aria-modal="true" @click.self="selectedRegistrationForm = null" @keydown.esc="selectedRegistrationForm = null"><div class="max-h-[94dvh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-2xl dark:bg-neutral-900"><header class="sticky top-0 z-10 flex items-center justify-between border-b border-neutral-200 bg-white px-5 py-4 dark:border-neutral-800 dark:bg-neutral-900"><h2 class="text-base font-extrabold text-neutral-900 dark:text-white">{{ selectedRegistrationForm.title }}</h2><button type="button" aria-label="Cerrar formulario" @click="selectedRegistrationForm = null" class="grid size-9 place-items-center rounded-lg text-neutral-500"><X class="size-5" /></button></header><div class="p-4 sm:p-6"><EventRegistrationForm :key="selectedRegistrationForm.id" :event-id="props.event.id" :form-id="selectedRegistrationForm.id" :form="selectedRegistrationForm" accent="var(--event-strong)" /></div></div></div>
        <div v-if="selectedDocument" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/75 p-0 backdrop-blur-sm sm:p-5" role="dialog" aria-modal="true" :aria-label="selectedDocument.title" @click.self="closeDocument" @keydown.esc="closeDocument"><div class="flex h-[100dvh] w-full max-w-6xl flex-col overflow-hidden bg-white shadow-2xl dark:bg-neutral-900 sm:h-[92vh] sm:rounded-2xl"><header class="flex shrink-0 items-center gap-3 border-b border-neutral-200 px-4 py-3 dark:border-neutral-800 sm:px-6"><div class="min-w-0 flex-1"><h2 class="truncate text-sm font-extrabold text-neutral-900 dark:text-white">{{ selectedDocument.title }}</h2><p class="truncate text-xs text-neutral-500">{{ selectedDocument.original_name }}</p></div><template v-if="canPreviewDocument"><button type="button" aria-label="Reducir zoom" @click="documentZoom = Math.max(50, documentZoom - 10)" class="grid size-9 place-items-center rounded-lg border border-neutral-200 text-neutral-600 dark:border-neutral-700 dark:text-neutral-300"><Minus class="size-4" /></button><span class="min-w-12 text-center text-xs font-bold text-neutral-500">{{ documentZoom }}%</span><button type="button" aria-label="Aumentar zoom" @click="documentZoom = Math.min(200, documentZoom + 10)" class="grid size-9 place-items-center rounded-lg border border-neutral-200 text-neutral-600 dark:border-neutral-700 dark:text-neutral-300"><Plus class="size-4" /></button></template><a :href="selectedDocument.download_url" class="inline-flex items-center gap-2 rounded-lg bg-emerald-700 px-3 py-2 text-xs font-bold text-white"><Download class="size-4" /><span class="hidden sm:inline">Descargar</span></a><button type="button" @click="closeDocument" aria-label="Cerrar visor" class="grid size-9 place-items-center rounded-lg text-neutral-500"><X class="size-5" /></button></header><div v-if="canPreviewDocument" class="min-h-0 flex-1 overflow-auto bg-neutral-100 dark:bg-neutral-950"><img v-if="isDocumentImage" :src="selectedDocument.preview_url" :alt="selectedDocument.title" class="mx-auto max-w-none origin-top object-contain" :style="{ width: `${documentZoom}%`, minWidth: '50%' }" /><iframe v-else :src="previewUrl" :title="selectedDocument.title" class="h-full min-h-[60vh] w-full border-0 bg-white dark:bg-neutral-900"></iframe></div><div v-else class="grid min-h-0 flex-1 place-items-center overflow-auto bg-neutral-50 p-6 text-center dark:bg-neutral-950"><div class="max-w-md"><span class="mx-auto grid size-16 place-items-center rounded-2xl bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300"><FileText class="size-8" /></span><h3 class="mt-4 text-lg font-extrabold text-neutral-900 dark:text-white">Este formato se descarga para abrirlo</h3><p class="mt-2 text-sm text-neutral-500">El visor del navegador no puede mostrar este tipo de archivo.</p><a :href="selectedDocument.download_url" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white"><Download class="size-4" /> Descargar archivo</a></div></div></div></div>
    </PublicLayout>
</template>
