<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowDown, ArrowLeft, Calendar, Check, Clock, ExternalLink, FileText, MapPin, Trophy, UserCheck, Users } from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

const props = defineProps<{ event: Record<string, any> }>();
const now = ref(Date.now());
let countdownInterval: ReturnType<typeof setInterval> | undefined;

const eventView = computed(() => {
    const e = props.event;
    const dateValue = e.event_date ? String(e.event_date).slice(0, 10) : '';
    const date = dateValue ? new Date(`${dateValue}T00:00:00`) : null;
    const words = String(e.title || 'Evento especial').trim().split(/\s+/);
    const splitAt = Math.max(1, Math.ceil(words.length / 2));
    return {
        ...e,
        image: e.cover_image_path || e.image_path,
        titleFirst: words.slice(0, splitAt).join(' '),
        titleSecond: words.slice(splitAt).join(' '),
        date: date?.toLocaleDateString('es-PE', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) || 'Fecha por confirmar',
        registrationLink: e.registration_link,
        fbLink: e.fb_link,
        statusLabel: e.status === 'Proximos' ? 'Próximo' : e.status === 'EnCurso' ? 'En curso' : 'Finalizado',
        targetDate: date?.getTime() ?? null,
        mapLink: `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(e.location || '')}`,
    };
});

const countdown = computed(() => {
    const remaining = eventView.value.targetDate === null ? 0 : Math.max(0, eventView.value.targetDate - now.value);
    const totalSeconds = Math.floor(remaining / 1000);
    return [
        { label: 'Días', value: String(Math.floor(totalSeconds / 86400)).padStart(2, '0') },
        { label: 'Horas', value: String(Math.floor((totalSeconds % 86400) / 3600)).padStart(2, '0') },
        { label: 'Minutos', value: String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0') },
        { label: 'Segundos', value: String(totalSeconds % 60).padStart(2, '0') },
    ];
});

const sections = [
    { id: 'documentos', label: 'Documentos', icon: FileText },
    { id: 'cronograma', label: 'Cronograma', icon: Calendar },
    { id: 'participantes', label: 'Registro', icon: Users },
    { id: 'ubicaciones', label: 'Ubicaciones', icon: MapPin },
    { id: 'resultados', label: 'Resultados', icon: Trophy },
];

onMounted(() => { countdownInterval = setInterval(() => { now.value = Date.now(); }, 1000); });
onUnmounted(() => { if (countdownInterval) clearInterval(countdownInterval); });

const scrollTo = (id: string) => document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
</script>

<template>
    <PublicLayout :title="eventView.title">
        <section class="relative isolate flex min-h-[calc(100svh-6rem)] items-center overflow-hidden bg-slate-950 text-white">
            <img v-if="eventView.image" :src="eventView.image" :alt="eventView.title" class="absolute inset-0 -z-20 size-full object-cover" />
            <div class="absolute inset-0 -z-10 bg-gradient-to-r from-slate-950/95 via-slate-950/80 to-slate-950/30" />
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-slate-950/80 via-transparent to-slate-950/20" />
            <div class="mx-auto grid w-full max-w-7xl gap-10 px-5 py-16 sm:px-8 lg:grid-cols-2 lg:items-center lg:gap-16 lg:px-10 lg:py-20">
                <div class="max-w-2xl">
                    <Link href="/eventos" class="mb-8 inline-flex items-center gap-2 text-sm font-semibold text-white/75 transition hover:text-white">
                        <ArrowLeft class="size-4" /> Volver a eventos
                    </Link>
                    <div class="mb-5 flex flex-wrap items-center gap-2">
                        <span class="rounded-full border border-white/25 bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-wider backdrop-blur">{{ eventView.type }}</span>
                        <span class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wider" :class="eventView.status === 'Proximos' ? 'bg-amber-400 text-slate-950' : eventView.status === 'EnCurso' ? 'bg-emerald-500 text-white' : 'bg-white/20 text-white'">{{ eventView.statusLabel }}</span>
                    </div>
                    <h1 class="text-5xl font-black leading-[0.98] tracking-tight sm:text-6xl lg:text-7xl xl:text-8xl">
                        <span class="block text-white">{{ eventView.titleFirst }}</span>
                        <span v-if="eventView.titleSecond" class="mt-2 block text-amber-300">{{ eventView.titleSecond }}</span>
                    </h1>
                    <div class="mt-8 flex flex-col gap-3 text-sm font-medium text-white/85 sm:flex-row sm:flex-wrap sm:gap-x-6">
                        <span class="inline-flex items-center gap-2"><Calendar class="size-4 text-amber-300" />{{ eventView.date }}</span>
                        <span v-if="eventView.time" class="inline-flex items-center gap-2"><Clock class="size-4 text-amber-300" />{{ eventView.time }}</span>
                        <span v-if="eventView.location" class="inline-flex items-center gap-2"><MapPin class="size-4 text-amber-300" />{{ eventView.location }}</span>
                    </div>
                    <button class="mt-9 inline-flex items-center gap-2 text-sm font-bold text-white/80 transition hover:text-amber-300" @click="scrollTo('sobre-el-evento')">
                        Conoce el evento <ArrowDown class="size-4" />
                    </button>
                </div>

                <div class="w-full max-w-xl lg:justify-self-end">
                    <p class="mb-4 text-xs font-extrabold uppercase tracking-[0.25em] text-amber-200">{{ eventView.status === 'Pasados' ? 'Evento finalizado' : eventView.status === 'EnCurso' ? 'El evento está en curso' : 'El evento comienza en' }}</p>
                    <div class="grid grid-cols-4 gap-2.5 sm:gap-4">
                        <div v-for="unit in countdown" :key="unit.label" class="rounded-2xl border border-white/20 bg-black/30 px-2 py-4 text-center shadow-xl backdrop-blur-md sm:rounded-3xl sm:px-4 sm:py-7">
                            <div class="text-3xl font-black tabular-nums sm:text-5xl lg:text-6xl">{{ unit.value }}</div>
                            <div class="mt-2 text-[10px] font-bold uppercase tracking-wider text-white/65 sm:text-xs">{{ unit.label }}</div>
                        </div>
                    </div>
                    <div id="sobre-el-evento" class="mt-6 scroll-mt-32 rounded-2xl border border-white/15 bg-black/25 p-5 backdrop-blur-md sm:p-6">
                        <h2 class="text-xs font-extrabold uppercase tracking-[0.2em] text-amber-200">Sobre el evento</h2>
                        <p class="mt-3 text-sm leading-7 text-white/85 sm:text-base">{{ eventView.description }}</p>
                        <div class="mt-5 grid gap-3 border-t border-white/15 pt-4 text-xs text-white/70 sm:grid-cols-2">
                            <p><span class="block font-bold uppercase tracking-wider text-white/45">Organiza</span>{{ eventView.organizer }}</p>
                            <p><span class="block font-bold uppercase tracking-wider text-white/45">Categoría</span>{{ eventView.category }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <nav aria-label="Secciones del evento" class="sticky top-[7.5rem] z-30 border-b border-neutral-200 bg-white/95 shadow-sm backdrop-blur dark:border-neutral-800 dark:bg-neutral-950/95 lg:top-24">
            <div class="mx-auto flex max-w-7xl gap-2 overflow-x-auto px-4 py-3 sm:grid sm:grid-cols-5 sm:gap-3 sm:px-8 lg:px-10">
                <button v-for="(section, index) in sections" :key="section.id" @click="scrollTo(section.id)" class="flex min-w-max items-center justify-center gap-2 rounded-xl border border-neutral-200 px-4 py-3 text-xs font-bold text-neutral-700 transition hover:border-amber-400 hover:bg-amber-50 hover:text-amber-800 dark:border-neutral-800 dark:text-neutral-200 dark:hover:bg-amber-400/10 dark:hover:text-amber-200 sm:min-w-0 sm:px-2 sm:text-sm">
                    <component :is="section.icon" class="size-4 shrink-0" />
                    <span>{{ section.label }}</span>
                    <span class="hidden text-[10px] text-neutral-400 lg:inline">0{{ index + 1 }}</span>
                </button>
            </div>
        </nav>

        <main class="bg-neutral-50 dark:bg-neutral-950">
            <section id="documentos" class="scroll-mt-40 border-b border-neutral-200 px-5 py-16 dark:border-neutral-800 sm:px-8 md:py-24">
                <div class="mx-auto max-w-7xl">
                    <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-amber-600 dark:text-amber-300">01 / Recursos</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-neutral-900 dark:text-white sm:text-5xl">Documentos</h2>
                    <div class="mt-8 flex flex-col items-start gap-5 rounded-3xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900 sm:flex-row sm:items-center sm:p-8">
                        <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-amber-100 text-amber-800 dark:bg-amber-300/10 dark:text-amber-200"><FileText class="size-6" /></span>
                        <div><h3 class="font-bold text-neutral-900 dark:text-white">Material del evento</h3><p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Los documentos y materiales se publicarán aquí cuando estén disponibles.</p></div>
                        <a v-if="eventView.fbLink" :href="eventView.fbLink" target="_blank" rel="noopener noreferrer" class="sm:ml-auto inline-flex items-center gap-2 text-sm font-bold text-amber-700 hover:underline dark:text-amber-300">Novedades del evento <ExternalLink class="size-4" /></a>
                    </div>
                </div>
            </section>

            <section id="cronograma" class="scroll-mt-40 border-b border-neutral-200 bg-white px-5 py-16 dark:border-neutral-800 dark:bg-neutral-900/40 sm:px-8 md:py-24">
                <div class="mx-auto max-w-7xl">
                    <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-amber-600 dark:text-amber-300">02 / Agenda</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-neutral-900 dark:text-white sm:text-5xl">Cronograma</h2>
                    <div class="mt-8 grid gap-4 md:grid-cols-[1fr_auto] md:items-center rounded-3xl border border-neutral-200 bg-neutral-50 p-6 dark:border-neutral-800 dark:bg-neutral-900 sm:p-8">
                        <div class="flex items-start gap-4"><span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-amber-100 text-amber-800 dark:bg-amber-300/10 dark:text-amber-200"><Calendar class="size-5" /></span><div><h3 class="font-bold text-neutral-900 dark:text-white">{{ eventView.date }}</h3><p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">{{ eventView.time || 'Horario por confirmar' }}</p></div></div>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">El programa detallado se compartirá próximamente.</p>
                    </div>
                </div>
            </section>

            <section id="participantes" class="scroll-mt-40 border-b border-neutral-200 px-5 py-16 dark:border-neutral-800 sm:px-8 md:py-24">
                <div class="mx-auto max-w-7xl">
                    <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-amber-600 dark:text-amber-300">03 / Participación</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-neutral-900 dark:text-white sm:text-5xl">Registro de participantes</h2>
                    <div class="mt-8 flex flex-col gap-6 rounded-3xl bg-slate-900 p-6 text-white sm:flex-row sm:items-center sm:justify-between sm:p-9">
                        <div><h3 class="text-xl font-extrabold">Súmate a este evento</h3><p class="mt-2 max-w-xl text-sm leading-6 text-white/65">Consulta la disponibilidad y completa tu inscripción mediante el formulario oficial.</p></div>
                        <a v-if="eventView.registrationLink" :href="eventView.registrationLink" target="_blank" rel="noopener noreferrer" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-amber-300 px-5 py-3 text-sm font-extrabold text-slate-950 transition hover:bg-amber-200"><UserCheck class="size-4" /> Inscribirme <ExternalLink class="size-4" /></a>
                        <span v-else class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-white/10 px-5 py-3 text-sm font-bold text-white/65"><Check class="size-4" /> Registro no disponible</span>
                    </div>
                </div>
            </section>

            <section id="ubicaciones" class="scroll-mt-40 border-b border-neutral-200 bg-white px-5 py-16 dark:border-neutral-800 dark:bg-neutral-900/40 sm:px-8 md:py-24">
                <div class="mx-auto max-w-7xl">
                    <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-amber-600 dark:text-amber-300">04 / Cómo llegar</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-neutral-900 dark:text-white sm:text-5xl">Ubicación</h2>
                    <div class="mt-8 flex flex-col gap-5 rounded-3xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900 sm:flex-row sm:items-center sm:p-8">
                        <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-amber-100 text-amber-800 dark:bg-amber-300/10 dark:text-amber-200"><MapPin class="size-6" /></span>
                        <div><h3 class="font-bold text-neutral-900 dark:text-white">{{ eventView.location || 'Ubicación por confirmar' }}</h3><p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Consulta el mapa para planificar tu llegada.</p></div>
                        <a :href="eventView.mapLink" target="_blank" rel="noopener noreferrer" class="sm:ml-auto inline-flex items-center gap-2 rounded-xl border border-neutral-200 px-4 py-3 text-sm font-bold text-neutral-800 hover:bg-neutral-50 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-800">Abrir mapa <ExternalLink class="size-4" /></a>
                    </div>
                </div>
            </section>

            <section id="resultados" class="scroll-mt-40 px-5 py-16 sm:px-8 md:py-24">
                <div class="mx-auto max-w-7xl">
                    <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-amber-600 dark:text-amber-300">05 / Cierre</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-neutral-900 dark:text-white sm:text-5xl">Resultados</h2>
                    <div class="mt-8 rounded-3xl border border-dashed border-neutral-300 p-8 text-center dark:border-neutral-700 sm:p-14">
                        <Trophy class="mx-auto size-9 text-amber-500" />
                        <h3 class="mt-4 font-bold text-neutral-900 dark:text-white">Los resultados aparecerán aquí</h3>
                        <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-neutral-500 dark:text-neutral-400">Publicaremos los resultados y comunicados finales de {{ eventView.title }} en esta sección.</p>
                    </div>
                </div>
            </section>
        </main>
    </PublicLayout>
</template>
