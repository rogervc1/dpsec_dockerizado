<script setup lang="ts">
import { Check, Circle, Flag } from '@lucide/vue';
import { computed, ref } from 'vue';

interface Activity {
    id: string;
    subject: string;
    description?: string | null;
    date: string;
    time?: string | null;
}

const props = defineProps<{
    eventDate: string;
    eventTime?: string;
    activities?: Activity[];
}>();

const expandedId = ref<string | null>(null);
const today = new Date();
today.setHours(0, 0, 0, 0);
const todayIso = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

const entries = computed(() => {
    const validActivities = (props.activities || []).filter((activity) => activity.subject && activity.date);
    const startDate = props.eventDate || validActivities.map((activity) => activity.date).sort()[0] || todayIso;
    const endDate = props.eventDate || validActivities.map((activity) => activity.date).sort().at(-1) || startDate;
    const milestones = [
        { id: 'event-start', subject: 'Inicio del evento', description: '', date: startDate, time: props.eventTime || '', kind: 'start' as const, priority: 0 },
        ...validActivities.map((activity) => ({ ...activity, kind: 'activity' as const, priority: 1 })),
        { id: 'event-finished', subject: todayIso > endDate ? 'Finalizado' : 'Día del evento', description: '', date: endDate, time: '', kind: 'finish' as const, priority: 2 },
    ].sort((a, b) => {
        // El inicio es el punto de partida visual del cronograma, aunque una
        // actividad tenga una fecha anterior a la fecha principal del evento.
        if (a.kind === 'start' || b.kind === 'start') return a.kind === 'start' ? -1 : 1;
        const dateOrder = a.date.localeCompare(b.date);
        if (dateOrder !== 0) return dateOrder;
        if (a.kind === 'finish' || b.kind === 'finish') return a.kind === 'finish' ? 1 : -1;
        return (a.time || '').localeCompare(b.time || '') || a.priority - b.priority;
    });

    return milestones.map((item, index) => {
        const isEndpoint = item.kind !== 'activity';
        const above = item.kind === 'start' || (!isEndpoint && (index % 2 === 1));
        const y = isEndpoint ? 230 : above ? 185 : 275;
        const x = 120 + index * 250;
        return { ...item, x, y, above, completed: item.kind === 'finish' ? todayIso > endDate : item.date <= todayIso };
    });
});

const width = computed(() => Math.max(740, 240 + Math.max(entries.value.length - 1, 1) * 250));
const linePath = computed(() => entries.value.slice(1).reduce((path, point, index) => {
    const previous = entries.value[index];
    const middle = (point.x + previous.x) / 2;
    return `${path} C ${middle} ${previous.y}, ${middle} ${point.y}, ${point.x} ${point.y}`;
}, `M ${entries.value[0]?.x ?? 120} ${entries.value[0]?.y ?? 230}`));
const progressState = computed(() => {
    const routeSegments = Math.max(entries.value.length - 1, 1);
    const activities = entries.value.filter((entry) => entry.kind === 'activity');
    let routePosition = 0.35 * routeSegments;
    const finish = entries.value.at(-1)!;

    if (todayIso > finish.date) {
        routePosition = routeSegments;
    } else if (activities.length > 0) {
            const datedMilestones = [activities[0], ...activities.slice(1), finish];
            let previous = datedMilestones[0];
            let next = datedMilestones[1] || finish;
            let previousIndex = entries.value.indexOf(previous);

            if (todayIso >= previous.date) {
                for (let index = 1; index < datedMilestones.length; index++) {
                    if (datedMilestones[index].date > todayIso) {
                        next = datedMilestones[index];
                        break;
                    }
                    previous = datedMilestones[index];
                    previousIndex = entries.value.indexOf(previous);
                    next = datedMilestones[index + 1] || finish;
                }
            } else {
                previousIndex = 0;
                next = activities[0];
            }

            const nextIndex = entries.value.indexOf(next);
            const previousTime = new Date(`${previous.date}T00:00:00`).getTime();
            const nextTime = new Date(`${next.date}T00:00:00`).getTime();
            const todayTime = new Date(`${todayIso}T00:00:00`).getTime();
            const fraction = nextTime > previousTime
                ? Math.max(0, Math.min(1, (todayTime - previousTime) / (nextTime - previousTime)))
                : todayIso >= next.date ? 1 : 0;
            routePosition = previousIndex + (nextIndex - previousIndex) * fraction;

            // El hito se completa el día posterior al evento; ese día la ruta
            // llega hasta el recuadro, pero el recorrido total se completa después.
            if (todayIso === finish.date) routePosition = Math.min(routePosition, routeSegments - 0.01);
    }

    routePosition = Math.max(0, Math.min(routeSegments, routePosition));
    const segmentIndex = Math.min(Math.floor(routePosition), routeSegments - 1);
    const fraction = routePosition >= routeSegments ? 1 : routePosition - segmentIndex;
    const previous = entries.value[segmentIndex];
    const next = entries.value[segmentIndex + 1];
    const middle = (previous.x + next.x) / 2;
    const inverse = 1 - fraction;
    const x = inverse ** 3 * previous.x + 3 * inverse ** 2 * fraction * middle + 3 * inverse * fraction ** 2 * middle + fraction ** 3 * next.x;
    const y = inverse ** 3 * previous.y + 3 * inverse ** 2 * fraction * previous.y + 3 * inverse * fraction ** 2 * next.y + fraction ** 3 * next.y;
    return { percent: (routePosition / routeSegments) * 100, x, y };
});

const todayLabel = computed(() => new Date(`${todayIso}T00:00:00`).toLocaleDateString('es-PE', { day: 'numeric', month: 'short' }));

const formatDate = (date: string) => {
    const parsed = new Date(`${date}T00:00:00`);
    return parsed.toLocaleDateString('es-PE', { day: 'numeric', month: 'short', year: 'numeric' });
};
const toggleDescription = (id: string) => {
    expandedId.value = expandedId.value === id ? null : id;
};
</script>

<template>
    <div class="mt-9 overflow-hidden rounded-[28px] border border-emerald-100 bg-gradient-to-br from-emerald-50/80 via-white to-lime-50/70 p-3 shadow-sm dark:border-emerald-950 dark:from-neutral-900 dark:via-neutral-900 dark:to-emerald-950/30 sm:p-5">
        <p class="mb-2 px-2 text-xs font-semibold text-neutral-500 dark:text-neutral-400">Desliza el cronograma para recorrer todas las actividades.</p>
        <div class="overflow-x-auto overscroll-x-contain pb-2">
            <div class="relative h-[460px]" :style="{ width: `${width}px` }">
                <svg class="pointer-events-none absolute inset-0 h-full" :width="width" height="460" :viewBox="`0 0 ${width} 460`" fill="none" aria-hidden="true">
                    <path :d="linePath" stroke="currentColor" class="text-neutral-200 dark:text-neutral-700" stroke-width="13" stroke-linecap="round" />
                    <path :d="linePath" pathLength="100" stroke="currentColor" class="text-emerald-500 dark:text-emerald-500" stroke-width="13" stroke-linecap="round" :stroke-dasharray="`${progressState.percent} 100`" />
                    <path :d="linePath" stroke="currentColor" class="text-white dark:text-neutral-900" stroke-width="5" stroke-linecap="round" stroke-dasharray="2 10" />
                </svg>

                <div class="absolute z-20 flex -translate-x-1/2 flex-col items-center" :style="{ left: `${progressState.x}px`, top: `${progressState.y}px` }" aria-label="Fecha actual">
                    <span class="mb-1 whitespace-nowrap rounded-full bg-emerald-700 px-2.5 py-1 text-[10px] font-extrabold text-white shadow dark:bg-emerald-400 dark:text-emerald-950">Hoy · {{ todayLabel }}</span>
                    <span class="size-3 rounded-full border-2 border-white bg-emerald-600 shadow ring-2 ring-emerald-500 dark:border-neutral-900 dark:bg-emerald-400"></span>
                </div>

                <div v-for="entry in entries" :key="entry.id" class="absolute z-10" :style="{ left: `${entry.x}px`, top: `${entry.y}px`, transform: 'translate(-50%, -50%)' }">
                    <span class="absolute left-1/2 top-1/2 grid size-7 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full border-[5px] border-white bg-emerald-600 text-white shadow-md dark:border-neutral-900 dark:bg-emerald-400 dark:text-emerald-950">
                        <Check v-if="entry.completed" class="size-3" />
                        <Flag v-else-if="entry.kind !== 'activity'" class="size-2.5" />
                        <Circle v-else class="size-2 fill-current" />
                    </span>
                    <button type="button" :aria-expanded="expandedId === entry.id" :aria-label="entry.kind === 'start' ? entry.subject : `${entry.subject}, ${formatDate(entry.date)}${entry.time ? `, ${entry.time}` : ''}${entry.description ? '. Ver descripción' : ''}`" @click="toggleDescription(entry.id)" @mouseenter="expandedId = entry.id" @mouseleave="expandedId === entry.id && (expandedId = null)" class="group absolute left-1/2 flex w-[190px] -translate-x-1/2 flex-col items-center rounded-[22px] border px-3 py-3 text-center shadow-md transition duration-200 hover:-translate-y-1 hover:shadow-lg focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-300 dark:focus-visible:ring-emerald-900" :style="{ top: entry.above ? '-118px' : '28px' }" :class="entry.completed ? 'border-neutral-300 bg-neutral-100/95 text-neutral-500 opacity-65 grayscale dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-400' : entry.kind === 'activity' || (entry.kind === 'finish' && todayIso < entry.date) ? 'border-emerald-200 bg-white text-neutral-800 dark:border-emerald-900 dark:bg-neutral-900 dark:text-neutral-100' : 'border-emerald-300 bg-emerald-700 text-white dark:border-emerald-700 dark:bg-emerald-800'">
                        <span class="max-w-full break-words text-xs font-extrabold leading-snug">{{ entry.subject }}</span>
                        <template v-if="entry.kind !== 'start'">
                            <span class="mt-1 text-[10px] font-semibold" :class="entry.kind === 'activity' && !entry.completed ? 'text-emerald-700 dark:text-emerald-300' : ''">{{ formatDate(entry.date) }}<template v-if="entry.time"> · {{ entry.time }}</template></span>
                            <span v-if="entry.completed" class="mt-1 rounded-full bg-neutral-200/80 px-2 py-0.5 text-[9px] font-bold dark:bg-neutral-700">Realizado</span>
                            <span v-else-if="entry.kind === 'finish'" class="mt-1 rounded-full bg-white/15 px-2 py-0.5 text-[9px] font-bold">Cierre</span>
                        </template>
                    </button>
                    <span v-if="entry.description && expandedId === entry.id" class="pointer-events-none absolute left-1/2 z-30 w-56 -translate-x-1/2 rounded-xl border border-neutral-200 bg-white p-3 text-left text-xs leading-5 text-neutral-700 shadow-xl dark:border-neutral-700 dark:bg-neutral-950 dark:text-neutral-300" :style="{ top: entry.above ? '-205px' : '112px' }">{{ entry.description }}</span>
                </div>

                <span class="pointer-events-none absolute bottom-3 left-4 rounded-full bg-white/80 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-800 dark:bg-neutral-800/80 dark:text-emerald-300">{{ entries.length }} hitos</span>
            </div>
        </div>
    </div>
</template>
