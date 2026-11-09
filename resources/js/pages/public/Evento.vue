<script setup lang="ts">
import { Check, Download, ExternalLink, FileText, Minus, Plus, Trophy, UserCheck, X } from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';
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
const activeLocationIndex = ref(0);
const locationTouchStart = ref<number | null>(null);
const locationSection = computed(() => props.event.exclusive_page_content?.ubicaciones || {});
const locationItems = computed(() => Array.isArray(locationSection.value.locations)
    ? locationSection.value.locations.map((item: any) => ({ ...item, image: item?.image || item?.image_url || item?.image_path || '' }))
        .filter((item: any) => item && (item.name || item.description || item.image))
    : []);
const activeLocation = computed(() => locationItems.value[activeLocationIndex.value] || null);
const selectLocation = (index: number) => { activeLocationIndex.value = (index + locationItems.value.length) % locationItems.value.length; };
let locationCarouselTimer: ReturnType<typeof setInterval> | undefined;
onMounted(() => {
    locationCarouselTimer = setInterval(() => {
        if (locationItems.value.length > 1) selectLocation(activeLocationIndex.value + 1);
    }, 6000);
});
onUnmounted(() => { if (locationCarouselTimer) clearInterval(locationCarouselTimer); });
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
            <EventExclusiveHero :view="view" @navigate="scrollTo" />
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
                <section id="ubicaciones" class="scroll-mt-40 border-b border-neutral-200 bg-white px-5 py-16 dark:border-neutral-800 dark:bg-neutral-900/40 sm:px-8 md:py-24">
                    <div class="mx-auto max-w-7xl">
                        <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-[var(--event-strong)] dark:text-[var(--event-accent)]">04 / Cómo llegar</p>
                        <h2 class="mt-3 text-3xl font-black tracking-tight text-neutral-900 dark:text-white sm:text-5xl">Ubicación</h2>
                        <div class="relative mt-8 flex min-h-[360px] flex-col overflow-hidden md:flex-row lg:min-h-[520px]">
                            <div v-if="locationSection.croquis" class="relative z-10 order-1 flex min-h-[260px] w-full items-center justify-center bg-white p-5 dark:bg-neutral-950 sm:min-h-[340px] md:min-h-[440px] md:w-1/2 lg:min-h-[520px] lg:p-8"><img :src="locationSection.croquis" alt="Croquis de las locaciones" class="max-h-[540px] w-full object-contain" /></div>
                            <div v-else-if="!activeLocation" class="relative z-10 order-1 flex min-h-[260px] w-full items-center justify-center bg-white p-8 text-center dark:bg-neutral-950 sm:min-h-[340px] md:min-h-[440px] md:w-1/2"><span class="text-sm text-neutral-500">Ubicación por confirmar</span></div>
                            <div v-else class="hidden md:order-1 md:block md:w-1/2" aria-hidden="true"></div>
                            <div v-if="activeLocation" class="relative isolate order-2 min-h-[360px] w-full flex-1 overflow-hidden bg-transparent text-white sm:min-h-[440px] md:min-h-[440px] md:w-1/2 lg:min-h-[520px]" @touchstart="locationTouchStart = $event.changedTouches[0]?.clientX ?? null" @touchend="(event) => { const end = event.changedTouches[0]?.clientX; if (locationTouchStart !== null && end !== undefined && Math.abs(end - locationTouchStart) > 45) selectLocation(activeLocationIndex + (end < locationTouchStart ? 1 : -1)); locationTouchStart = null; }">
                                <img v-if="activeLocation.image" :key="activeLocation.image" :src="activeLocation.image" :alt="activeLocation.name" class="absolute inset-0 z-0 h-full w-full bg-transparent object-cover object-center" />
                                <div v-if="activeLocation.image" class="pointer-events-none absolute inset-y-0 left-0 z-10 w-3/5 bg-gradient-to-r from-white via-white/60 to-transparent dark:from-[#121212] dark:via-[#121212]/60 dark:to-transparent"></div>
                                <div v-if="!activeLocation.image" class="absolute inset-0 z-0 bg-gradient-to-br from-emerald-900 via-neutral-900 to-neutral-950"></div>
                                <nav v-if="locationItems.length > 1" aria-label="Recorrido por las locaciones" class="absolute inset-x-5 top-5 z-20 flex sm:inset-x-8 sm:top-8"><div class="absolute left-[5%] right-[5%] top-5 h-0.5 bg-neutral-900/60 dark:bg-white/60"></div><button v-for="(item, index) in locationItems" :key="item.id || index" type="button" @click="selectLocation(index)" :aria-label="`Locación ${index + 1}: ${item.name}`" :aria-current="index === activeLocationIndex ? 'step' : undefined" class="group relative flex min-w-0 flex-1 flex-col items-center gap-2 text-[10px] font-bold text-white transition"><span class="grid size-10 place-items-center rounded-full border-2 border-white bg-neutral-900 text-xs font-extrabold text-white shadow-lg transition group-hover:bg-neutral-700" :class="index === activeLocationIndex ? 'scale-110 border-white bg-emerald-700 ring-4 ring-white/40' : ''">{{ index + 1 }}</span><span class="max-w-full truncate px-1 text-center text-white [text-shadow:0_2px_5px_#000,0_0_10px_#000]">{{ item.name || `Locación ${index + 1}` }}</span></button></nav>
                                <div class="absolute bottom-6 left-6 right-6 z-20 flex items-end justify-between gap-4 sm:bottom-8 sm:left-8 sm:right-8 lg:bottom-10 lg:left-10 lg:right-10"><div class="min-w-0 max-w-xl drop-shadow-[0_3px_8px_rgba(0,0,0,1)]"><p class="text-xs font-extrabold uppercase tracking-[0.2em] text-white">Locación {{ activeLocationIndex + 1 }}</p><h3 class="mt-2 text-3xl font-black text-white sm:text-4xl">{{ activeLocation.name || `Locación ${activeLocationIndex + 1}` }}</h3><p v-if="activeLocation.description" class="mt-3 whitespace-pre-line text-sm font-medium leading-6 text-white sm:text-base">{{ activeLocation.description }}</p></div><div v-if="locationItems.length > 1" class="flex shrink-0 gap-2"><button type="button" @click="selectLocation(activeLocationIndex - 1)" aria-label="Locación anterior" class="grid size-10 place-items-center rounded-full bg-neutral-950/85 text-white shadow-lg transition hover:bg-neutral-800">‹</button><button type="button" @click="selectLocation(activeLocationIndex + 1)" aria-label="Siguiente locación" class="grid size-10 place-items-center rounded-full bg-neutral-950/85 text-white shadow-lg transition hover:bg-neutral-800">›</button></div></div>
                            </div>
                            <div v-else-if="!locationSection.croquis" class="order-2 flex min-h-[260px] items-center justify-center p-8 text-center text-sm text-neutral-500">Ubicación por confirmar</div>
                        </div>
                    </div>
                </section>
                <section id="resultados" class="scroll-mt-40 px-5 py-16 sm:px-8 md:py-24"><div class="mx-auto max-w-7xl"><p class="text-xs font-extrabold uppercase tracking-[0.24em] text-[var(--event-strong)] dark:text-[var(--event-accent)]">05 / Cierre</p><h2 class="mt-3 text-3xl font-black tracking-tight text-neutral-900 dark:text-white sm:text-5xl">Resultados</h2><div class="mt-8 rounded-3xl border border-dashed border-neutral-300 p-8 text-center dark:border-neutral-700 sm:p-14"><Trophy class="mx-auto size-9 text-[var(--event-accent)]" /><h3 class="mt-4 font-bold text-neutral-900 dark:text-white">Los resultados aparecerán aquí</h3><p class="mx-auto mt-2 max-w-lg whitespace-pre-line text-sm leading-6 text-neutral-500 dark:text-neutral-400">{{ `Publicaremos los resultados y comunicados finales de ${eventView.title} en esta sección.` }}</p><a v-if="eventView.exclusiveSections.resultados?.link" :href="eventView.exclusiveSections.resultados.link" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-[var(--event-strong)] hover:underline dark:text-[var(--event-accent)]">Ver resultados <ExternalLink class="size-4" /></a></div></div></section>
            </main>
        </div>
        <div v-if="selectedRegistrationForm" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/75 p-3 backdrop-blur-sm sm:p-5" role="dialog" aria-modal="true" @click.self="selectedRegistrationForm = null" @keydown.esc="selectedRegistrationForm = null"><div class="max-h-[94dvh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-2xl dark:bg-neutral-900"><header class="sticky top-0 z-10 flex items-center justify-between border-b border-neutral-200 bg-white px-5 py-4 dark:border-neutral-800 dark:bg-neutral-900"><h2 class="text-base font-extrabold text-neutral-900 dark:text-white">{{ selectedRegistrationForm.title }}</h2><button type="button" aria-label="Cerrar formulario" @click="selectedRegistrationForm = null" class="grid size-9 place-items-center rounded-lg text-neutral-500"><X class="size-5" /></button></header><div class="p-4 sm:p-6"><EventRegistrationForm :key="selectedRegistrationForm.id" :event-id="props.event.id" :form-id="selectedRegistrationForm.id" :form="selectedRegistrationForm" accent="var(--event-strong)" /></div></div></div>
        <div v-if="selectedDocument" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/75 p-0 backdrop-blur-sm sm:p-5" role="dialog" aria-modal="true" :aria-label="selectedDocument.title" @click.self="closeDocument" @keydown.esc="closeDocument"><div class="flex h-[100dvh] w-full max-w-6xl flex-col overflow-hidden bg-white shadow-2xl dark:bg-neutral-900 sm:h-[92vh] sm:rounded-2xl"><header class="flex shrink-0 items-center gap-3 border-b border-neutral-200 px-4 py-3 dark:border-neutral-800 sm:px-6"><div class="min-w-0 flex-1"><h2 class="truncate text-sm font-extrabold text-neutral-900 dark:text-white">{{ selectedDocument.title }}</h2><p class="truncate text-xs text-neutral-500">{{ selectedDocument.original_name }}</p></div><template v-if="canPreviewDocument"><button type="button" aria-label="Reducir zoom" @click="documentZoom = Math.max(50, documentZoom - 10)" class="grid size-9 place-items-center rounded-lg border border-neutral-200 text-neutral-600 dark:border-neutral-700 dark:text-neutral-300"><Minus class="size-4" /></button><span class="min-w-12 text-center text-xs font-bold text-neutral-500">{{ documentZoom }}%</span><button type="button" aria-label="Aumentar zoom" @click="documentZoom = Math.min(200, documentZoom + 10)" class="grid size-9 place-items-center rounded-lg border border-neutral-200 text-neutral-600 dark:border-neutral-700 dark:text-neutral-300"><Plus class="size-4" /></button></template><a :href="selectedDocument.download_url" class="inline-flex items-center gap-2 rounded-lg bg-emerald-700 px-3 py-2 text-xs font-bold text-white"><Download class="size-4" /><span class="hidden sm:inline">Descargar</span></a><button type="button" @click="closeDocument" aria-label="Cerrar visor" class="grid size-9 place-items-center rounded-lg text-neutral-500"><X class="size-5" /></button></header><div v-if="canPreviewDocument" class="min-h-0 flex-1 overflow-auto bg-neutral-100 dark:bg-neutral-950"><img v-if="isDocumentImage" :src="selectedDocument.preview_url" :alt="selectedDocument.title" class="mx-auto max-w-none origin-top object-contain" :style="{ width: `${documentZoom}%`, minWidth: '50%' }" /><iframe v-else :src="previewUrl" :title="selectedDocument.title" class="h-full min-h-[60vh] w-full border-0 bg-white dark:bg-neutral-900"></iframe></div><div v-else class="grid min-h-0 flex-1 place-items-center overflow-auto bg-neutral-50 p-6 text-center dark:bg-neutral-950"><div class="max-w-md"><span class="mx-auto grid size-16 place-items-center rounded-2xl bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300"><FileText class="size-8" /></span><h3 class="mt-4 text-lg font-extrabold text-neutral-900 dark:text-white">Este formato se descarga para abrirlo</h3><p class="mt-2 text-sm text-neutral-500">El visor del navegador no puede mostrar este tipo de archivo.</p><a :href="selectedDocument.download_url" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white"><Download class="size-4" /> Descargar archivo</a></div></div></div></div>
    </PublicLayout>
</template>
