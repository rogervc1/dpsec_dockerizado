<script setup lang="ts">
import { Check, Circle, Flag } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

interface Activity {
    id: string;
    subject: string;
    description?: string | null;
    date: string;
    time?: string | null;
    important?: boolean;
}

const props = defineProps<{
    eventDate: string;
    eventTime?: string;
    activities?: Activity[];
}>();

const expandedId = ref<string | null>(null);
const timelineRef = ref<HTMLElement | null>(null);
const width = ref(800);
let timelineResizeObserver: ResizeObserver | null = null;
onMounted(() => {
    if (!timelineRef.value) return;
    timelineResizeObserver = new ResizeObserver(([entry]) => {
        width.value = Math.max(280, Math.round(entry.contentRect.width));
    });
    timelineResizeObserver.observe(timelineRef.value);
});
onBeforeUnmount(() => timelineResizeObserver?.disconnect());
const isMobileLayout = computed(() => width.value < 640);
const columns = computed(() => width.value >= 900 ? 7 : 3);
const rowGap = computed(() => isMobileLayout.value ? 225 : 250);
const cardWidth = computed(() => isMobileLayout.value
    ? Math.min(150, Math.max(84, width.value / 2 - 48))
    : Math.min(190, Math.max(100, width.value / columns.value - 16)));
const mobileVerticalGap = 138;
const today = new Date();
today.setHours(0, 0, 0, 0);
const todayIso = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

const entries = computed(() => {
    const validActivities = (props.activities || []).filter((activity) => activity.subject && activity.date);
    const startDate = props.eventDate || validActivities.map((activity) => activity.date).sort()[0] || todayIso;
    const endDate = props.eventDate || validActivities.map((activity) => activity.date).sort().at(-1) || startDate;
    const sortedActivities = [...validActivities].sort((a, b) => a.date.localeCompare(b.date)
        || (a.time || '').localeCompare(b.time || '')
        || a.subject.localeCompare(b.subject));
    const milestones = [
        { id: 'event-start', subject: 'Inicio del evento', description: '', date: startDate, time: props.eventTime || '', kind: 'start' as const },
        ...sortedActivities.map((activity) => ({ ...activity, important: Boolean(activity.important), kind: 'activity' as const })),
        { id: 'event-finished', subject: todayIso > endDate ? 'Finalizado' : 'Día del evento', description: '', date: endDate, time: '', kind: 'finish' as const },
    ];

    let activityPosition = 0;
    return milestones.map((item) => {
        const sequence = item.kind === 'start' ? null : activityPosition++;
        if (isMobileLayout.value) {
            const side = sequence === null ? 0 : sequence % 2 === 0 ? 1 : -1;
            const row = sequence === null ? 0 : sequence + 1;
            const x = width.value / 2 + side * Math.min(width.value * 0.12, 30);
            const y = 145 + row * mobileVerticalGap;
            return {
                ...item,
                row,
                x,
                y,
                side,
                cardOffsetX: 0,
                cardSide: side > 0 ? 'right' as const : side < 0 ? 'left' as const : 'center' as const,
                above: item.kind === 'start',
                completed: item.kind === 'finish' ? todayIso > endDate : item.date <= todayIso,
            };
        }

        let row = 0;
        let slot = 0;
        if (sequence !== null) {
            if (item.kind === 'finish' && sortedActivities.length === 0) {
                slot = columns.value - 1;
            } else if (sequence < columns.value - 1) {
                slot = sequence + 1;
            } else {
                const continuation = sequence - (columns.value - 1);
                row = 1 + Math.floor(continuation / columns.value);
                const offset = continuation % columns.value;
                slot = row % 2 === 1 ? columns.value - 1 - offset : offset;
            }
        }
        const x = ((slot + 0.5) / columns.value) * width.value;
        const continuation = sequence === null ? 0 : sequence - (columns.value - 1);
        const rowPosition = sequence === null ? 0 : row === 0 ? sequence : continuation % columns.value;
        const wave = sequence === null
            ? 0
            : row > 0 && rowPosition === 1
                ? 88
                : row > 0 && rowPosition >= 2
                    ? rowPosition % 2 === 0 ? -46 : 46
                : row > 0
                    ? 46
                    : sequence % 2 === 0 ? 46 : -46;
        const y = 145 + row * rowGap.value + wave;
        const isRowLanding = row > 0 && rowPosition === 0;
        const above = item.kind === 'start' || isRowLanding || wave < 0;
        const cardOffsetX = isRowLanding ? (x >= width.value / 2 ? -18 : 18) : 0;
        return { ...item, row, x, y, side: 0, cardOffsetX, cardSide: 'center' as const, above, completed: item.kind === 'finish' ? todayIso > endDate : item.date <= todayIso };
    });
});

const height = computed(() => isMobileLayout.value
    ? 250 + Math.max(entries.value.length - 1, 0) * mobileVerticalGap
    : 400 + Math.max(...entries.value.map((entry) => entry.row), 0) * rowGap.value);
const getSegmentControls = (previous: { x: number; y: number; row: number }, next: { x: number; y: number; row: number }) => {
    const deltaX = next.x - previous.x;
    const deltaY = next.y - previous.y;
    if (isMobileLayout.value) {
        return {
            x1: previous.x,
            y1: previous.y + deltaY * 0.34,
            x2: next.x,
            y2: previous.y + deltaY * 0.66,
        };
    }

    if (previous.row !== next.row) {
        const outward = previous.x >= width.value / 2 ? 1 : -1;
        const radius = isMobileLayout.value ? 42 : 58;
        return {
            x1: previous.x + outward * radius,
            y1: previous.y + deltaY * 0.25,
            x2: next.x + outward * radius,
            y2: previous.y + deltaY * 0.75,
        };
    }

    return {
        x1: previous.x + deltaX * 0.35,
        y1: previous.y,
        x2: previous.x + deltaX * 0.65,
        y2: next.y,
    };
};
const linePath = computed(() => entries.value.slice(1).reduce((path, point, index) => {
    const previous = entries.value[index];
    const controls = getSegmentControls(previous, point);
    return `${path} C ${controls.x1} ${controls.y1}, ${controls.x2} ${controls.y2}, ${point.x} ${point.y}`;
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
    const controls = getSegmentControls(previous, next);
    const inverse = 1 - fraction;
    const x = inverse ** 3 * previous.x + 3 * inverse ** 2 * fraction * controls.x1 + 3 * inverse * fraction ** 2 * controls.x2 + fraction ** 3 * next.x;
    const y = inverse ** 3 * previous.y + 3 * inverse ** 2 * fraction * controls.y1 + 3 * inverse * fraction ** 2 * controls.y2 + fraction ** 3 * next.y;
    return { percent: (routePosition / routeSegments) * 100, x, y };
});

const todayLabel = computed(() => new Date(`${todayIso}T00:00:00`).toLocaleDateString('es-PE', { day: 'numeric', month: 'short' }));

const formatDate = (date: string) => {
    const parsed = new Date(`${date}T00:00:00`);
    return parsed.toLocaleDateString('es-PE', { day: 'numeric', month: 'short', year: 'numeric' });
};
const formatCompactDate = (date: string) => {
    const parsed = new Date(`${date}T00:00:00`);
    return parsed.toLocaleDateString('es-PE', { day: 'numeric', month: 'short' });
};
const toggleDescription = (id: string) => {
    expandedId.value = expandedId.value === id ? null : id;
};
</script>

<template>
    <div class="mt-9 overflow-hidden rounded-[28px] border border-emerald-100 bg-gradient-to-br from-emerald-50/80 via-white to-lime-50/70 p-3 shadow-sm dark:border-emerald-950 dark:from-neutral-900 dark:via-neutral-900 dark:to-emerald-950/30 sm:p-5">
        <p class="mb-2 px-2 text-xs font-semibold text-neutral-500 dark:text-neutral-400">Recorrido cronológico · {{ entries.length }} hitos</p>
        <div ref="timelineRef" class="relative w-full" :style="{ height: `${height}px` }">
                <svg class="pointer-events-none absolute inset-0 size-full" :width="width" :height="height" :viewBox="`0 0 ${width} ${height}`" fill="none" aria-hidden="true">
                    <path :d="linePath" stroke="currentColor" class="text-neutral-200 dark:text-neutral-700" stroke-width="13" stroke-linecap="round" />
                    <path :d="linePath" pathLength="100" stroke="currentColor" class="text-emerald-500 dark:text-emerald-500" stroke-width="13" stroke-linecap="round" :stroke-dasharray="`${progressState.percent} 100`" />
                    <path :d="linePath" stroke="currentColor" class="text-white dark:text-neutral-900" stroke-width="5" stroke-linecap="round" stroke-dasharray="2 10" />
                </svg>

                <div class="absolute z-20 flex -translate-x-1/2 flex-col items-center" :style="{ left: `${progressState.x}px`, top: `${progressState.y}px` }" aria-label="Fecha actual">
                    <span class="mb-1 whitespace-nowrap rounded-full bg-emerald-700 px-2.5 py-1 text-[10px] font-extrabold text-white shadow dark:bg-emerald-400 dark:text-emerald-950">Hoy · {{ todayLabel }}</span>
                    <span class="size-3 rounded-full border-2 border-white bg-emerald-600 shadow ring-2 ring-emerald-500 dark:border-neutral-900 dark:bg-emerald-400"></span>
                </div>

                <div v-for="entry in entries" :key="entry.id" class="absolute z-10" :style="{ left: `${entry.x}px`, top: `${entry.y}px`, transform: 'translate(-50%, -50%)' }">
                    <span class="absolute left-1/2 top-1/2 grid size-7 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full border-[5px] border-white bg-emerald-600 text-white shadow-md dark:border-neutral-900 dark:bg-emerald-400 dark:text-emerald-950" :class="entry.kind === 'activity' && entry.important ? 'important-timeline-node' : ''">
                        <Check v-if="entry.completed" class="size-3" />
                        <Flag v-else-if="entry.kind !== 'activity'" class="size-2.5" />
                        <Circle v-else class="size-2 fill-current" />
                    </span>
                    <button type="button" :aria-expanded="expandedId === entry.id" :aria-label="entry.kind === 'start' ? entry.subject : `${entry.subject}, ${formatDate(entry.date)}${entry.time ? `, ${entry.time}` : ''}${entry.description ? '. Ver descripción' : ''}`" @click="toggleDescription(entry.id)" @mouseenter="expandedId = entry.id" @mouseleave="expandedId === entry.id && (expandedId = null)" class="group absolute flex flex-col items-center rounded-[18px] border text-center shadow-md transition duration-200 hover:-translate-y-1 hover:shadow-lg focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-300 dark:focus-visible:ring-emerald-900" :style="{ left: isMobileLayout ? (entry.cardSide === 'right' ? '16px' : entry.cardSide === 'left' ? `${-cardWidth - 16}px` : '50%') : `calc(50% + ${entry.cardOffsetX}px)`, top: isMobileLayout ? '50%' : entry.above ? '-104px' : '28px', transform: isMobileLayout ? (entry.cardSide === 'center' ? 'translate(-50%, -50%)' : 'translateY(-50%)') : 'translateX(-50%)', width: `${cardWidth}px` }" :class="[entry.completed ? 'border-neutral-300 bg-neutral-100/95 text-neutral-500 opacity-65 grayscale dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-400' : entry.kind === 'activity' || (entry.kind === 'finish' && todayIso < entry.date) ? 'border-emerald-200 bg-white text-neutral-800 dark:border-emerald-900 dark:bg-neutral-900 dark:text-neutral-100' : 'border-emerald-300 bg-emerald-700 text-white dark:border-emerald-700 dark:bg-emerald-800', entry.kind === 'activity' && entry.important ? 'important-timeline-card border-green-400 dark:border-green-600' : '', isMobileLayout ? 'px-1.5 py-2' : 'px-2.5 py-2.5']">
                        <span class="max-w-full break-words font-extrabold leading-snug" :class="isMobileLayout ? 'text-[10px]' : 'text-xs'">{{ entry.subject }}</span>
                        <template v-if="entry.kind !== 'start'">
                            <span class="mt-1 font-semibold" :class="[entry.kind === 'activity' && !entry.completed ? 'text-emerald-700 dark:text-emerald-300' : '', isMobileLayout ? 'text-[9px]' : 'text-[10px]']">{{ isMobileLayout ? formatCompactDate(entry.date) : formatDate(entry.date) }}<template v-if="entry.time"><span v-if="isMobileLayout" class="block">{{ entry.time }}</span><span v-else> · {{ entry.time }}</span></template></span>
                            <span v-if="entry.completed" class="mt-1 rounded-full bg-neutral-200/80 px-2 py-0.5 text-[9px] font-bold dark:bg-neutral-700">Realizado</span>
                            <span v-else-if="entry.kind === 'finish'" class="mt-1 rounded-full bg-white/15 px-2 py-0.5 text-[9px] font-bold">Cierre</span>
                        </template>
                    </button>
                    <span v-if="entry.description && expandedId === entry.id" class="pointer-events-none absolute left-1/2 z-30 -translate-x-1/2 rounded-xl border border-neutral-200 bg-white p-3 text-left text-xs leading-5 text-neutral-700 shadow-xl dark:border-neutral-700 dark:bg-neutral-950 dark:text-neutral-300" :style="{ top: entry.above ? '-160px' : '112px', width: `${Math.min(224, cardWidth + 40)}px` }">{{ entry.description }}</span>
                </div>

        </div>
    </div>
</template>

<style scoped>
.important-timeline-card {
    box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.24), 0 0 16px rgba(34, 197, 94, 0.2);
}

.important-timeline-node {
    box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.28), 0 0 12px rgba(34, 197, 94, 0.5);
}
</style>
