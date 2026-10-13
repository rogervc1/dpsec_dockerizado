<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calendar,
    Clock,
    MapPin,
    UserCheck,
    Info,
    ExternalLink,
    ChevronRight,
} from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import PublicLayout from '@/layouts/PublicLayout.vue';

const props = defineProps<{
    event: Record<string, any>;
}>();

const eventView = computed(() => {
    const e = props.event;

    return {
        ...e,
        image: e.cover_image_path || e.image_path,
        date: e.event_date
            ? new Date(`${String(e.event_date).slice(0, 10)}T00:00:00`).toLocaleDateString('es-PE', {
                  weekday: 'long',
                  day: 'numeric',
                  month: 'long',
                  year: 'numeric',
              })
            : '',
        registrationLink: e.registration_link,
        fbLink: e.fb_link,
        statusLabel:
            e.status === 'Proximos' ? 'Próximo' : e.status === 'EnCurso' ? 'En Curso' : 'Finalizado',
    };
});

const canRegister = computed(
    () => eventView.value.status !== 'Pasados' && Boolean(eventView.value.registrationLink),
);

const scrollToAbout = () => {
    document.getElementById('sobre-el-evento')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const scrollToMore = () => {
    document.getElementById('ver-mas')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};
</script>

<template>
    <PublicLayout :title="eventView.title">
        <section
            class="relative min-h-[42vh] md:min-h-[52vh] flex items-end overflow-hidden bg-neutral-950 text-white"
        >
            <img
                v-if="eventView.image"
                :src="eventView.image"
                :alt="eventView.title"
                class="absolute inset-0 w-full h-full object-cover opacity-50"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-neutral-950 via-neutral-950/75 to-neutral-950/20"></div>

            <div class="relative z-10 w-full max-w-7xl mx-auto px-6 lg:px-8 pb-10 pt-28 space-y-5">
                <Link
                    href="/eventos"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-white/70 hover:text-white transition-colors"
                >
                    <ArrowLeft class="size-3.5" />
                    Volver a Eventos
                </Link>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-lg bg-white/15 border border-white/15">
                        {{ eventView.type }}
                    </span>
                    <span
                        class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-lg text-white"
                        :class="{
                            'bg-indigo-600': eventView.status === 'Proximos',
                            'bg-emerald-600': eventView.status === 'EnCurso',
                            'bg-neutral-500': eventView.status === 'Pasados',
                        }"
                    >
                        {{ eventView.statusLabel }}
                    </span>
                </div>

                <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight max-w-4xl leading-tight">
                    {{ eventView.title }}
                </h1>

                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-white/80">
                    <span class="inline-flex items-center gap-1.5">
                        <Calendar class="size-4 text-indigo-300" />
                        {{ eventView.date }}
                    </span>
                    <span v-if="eventView.time" class="inline-flex items-center gap-1.5">
                        <Clock class="size-4 text-indigo-300" />
                        {{ eventView.time }}
                    </span>
                    <span v-if="eventView.location" class="inline-flex items-center gap-1.5">
                        <MapPin class="size-4 text-indigo-300" />
                        {{ eventView.location }}
                    </span>
                </div>
            </div>
        </section>

        <section class="bg-white dark:bg-neutral-950 border-b border-neutral-200 dark:border-neutral-800 sticky top-16 z-30">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-4 flex flex-wrap items-center gap-3">
                <a
                    v-if="canRegister"
                    :href="eventView.registrationLink"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <Button class="rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold h-10 px-5 cursor-pointer">
                        <UserCheck class="size-3.5" />
                        Inscribirse
                    </Button>
                </a>
                <Button
                    v-else
                    disabled
                    variant="outline"
                    class="rounded-xl text-xs font-semibold h-10 px-5"
                >
                    <UserCheck class="size-3.5" />
                    Inscribirse
                </Button>

                <Button
                    variant="outline"
                    class="rounded-xl text-xs font-semibold h-10 px-5 cursor-pointer"
                    @click="scrollToAbout"
                >
                    <Info class="size-3.5" />
                    Sobre el evento
                </Button>

                <Button
                    variant="outline"
                    class="rounded-xl text-xs font-semibold h-10 px-5 cursor-pointer"
                    @click="scrollToMore"
                >
                    Ver más
                    <ChevronRight class="size-3.5" />
                </Button>
            </div>
        </section>

        <section class="py-14 md:py-20 bg-neutral-50/80 dark:bg-neutral-900/40">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div id="sobre-el-evento" class="lg:col-span-8 space-y-6 scroll-mt-32">
                    <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-3xl p-6 md:p-8 space-y-4">
                        <h2 class="text-xl font-extrabold tracking-tight">Sobre el evento</h2>
                        <p class="text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed whitespace-pre-line text-justify">
                            {{ eventView.description }}
                        </p>
                    </div>

                    <div id="ver-mas" class="bg-white dark:bg-neutral-900 border border-dashed border-neutral-300 dark:border-neutral-700 rounded-3xl p-6 md:p-8 space-y-3 scroll-mt-32">
                        <h2 class="text-lg font-extrabold tracking-tight">Espacio de trabajo</h2>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed">
                            Aquí iremos armando el panel exclusivo de este evento: galería, programa, convocatorias y contenido adicional.
                        </p>
                    </div>
                </div>

                <aside class="lg:col-span-4 space-y-4">
                    <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-3xl p-6 space-y-4">
                        <h3 class="text-sm font-extrabold uppercase tracking-wider text-neutral-400">Información</h3>
                        <dl class="space-y-3 text-sm">
                            <div>
                                <dt class="text-[11px] font-bold uppercase tracking-wider text-neutral-400">Organiza</dt>
                                <dd class="mt-0.5 font-semibold text-neutral-800 dark:text-neutral-200">{{ eventView.organizer }}</dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-bold uppercase tracking-wider text-neutral-400">Categoría</dt>
                                <dd class="mt-0.5 font-semibold text-neutral-800 dark:text-neutral-200">{{ eventView.category }}</dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-bold uppercase tracking-wider text-neutral-400">Lugar</dt>
                                <dd class="mt-0.5 font-semibold text-neutral-800 dark:text-neutral-200">{{ eventView.location }}</dd>
                            </div>
                        </dl>

                        <a
                            v-if="eventView.fbLink"
                            :href="eventView.fbLink"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline"
                        >
                            Ver en Facebook
                            <ExternalLink class="size-3.5" />
                        </a>
                    </div>
                </aside>
            </div>
        </section>
    </PublicLayout>
</template>
