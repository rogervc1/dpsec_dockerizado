<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { 
    Calendar, 
    FileText, 
    Plus, 
    Search, 
    Trash2, 
    ExternalLink, 
    Sparkles, 
    TrendingUp, 
    Heart,
    PlusCircle,
    X,
    FolderUp,
    Pencil,
    CheckCircle,
    Clock,
    AlertCircle,
    Upload
} from '@lucide/vue';
import { ref, computed } from 'vue';
import CertificateTab from '@/components/CertificateTab.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';

// ─── Props from AdminDashboardController ────────────────────────────────────
interface EventItem {
    id: number;
    title: string;
    type: string;
    category: string;
    status: 'Proximos' | 'EnCurso' | 'Pasados';
    status_label: string;
    status_color: string;
    event_date: string;
    time: string;
    location: string;
    organizer: string;
    description: string;
    image_path: string;
    cover_image_path?: string | null;
    event_images?: string[];
    fb_link: string;
    registration_link?: string | null;
    is_proyeccion_social: boolean;
    sort_order: number;
}

interface DocumentItem {
    id: number;
    title: string;
    code: string;
    category: string;
    published_date: string;
    description: string;
    file_path: string;
    is_active: boolean;
    sort_order: number;
}

interface StatsItem {
    totalEvents: number;
    totalDocs: number;
    totalProyeccionSocial: number;
}


const props = withDefaults(defineProps<{
    events?: EventItem[];
    documents?: DocumentItem[];
    stats?: StatsItem;
    flash?: { success?: string; error?: string };
    templates?: any[];
    fonts?: any[];
    certificates?: any;
    activeTab?: 'overview' | 'events' | 'docs' | 'certificates';
}>(), {
    events: () => [],
    documents: () => [],
    stats: () => ({ totalEvents: 0, totalDocs: 0, totalProyeccionSocial: 0 }),
    templates: () => [],
    fonts: () => [],
    activeTab: 'overview',
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel Administrativo',
                href: dashboard(),
            },
        ],
    },
});

// ─── Active Tab ──────────────────────────────────────────────────────────────
const activeTab = ref<'overview' | 'events' | 'docs' | 'certificates'>(props.activeTab);


// ─── Search filters ──────────────────────────────────────────────────────────
const eventQuery = ref('');
const docQuery = ref('');

// ─── Computed filtered lists ─────────────────────────────────────────────────
const filteredEvents = computed(() =>
    props.events.filter(e =>
        e.title.toLowerCase().includes(eventQuery.value.toLowerCase()) ||
        e.category.toLowerCase().includes(eventQuery.value.toLowerCase()) ||
        e.location.toLowerCase().includes(eventQuery.value.toLowerCase())
    )
);

const filteredDocs = computed(() =>
    props.documents.filter(d =>
        d.title.toLowerCase().includes(docQuery.value.toLowerCase()) ||
        d.code.toLowerCase().includes(docQuery.value.toLowerCase()) ||
        d.category.toLowerCase().includes(docQuery.value.toLowerCase())
    )
);

const formatDate = (dateStr: string) => {
    if (!dateStr) {
return '';
}

    const date = new Date(dateStr);
    const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
};

// ─── ADD EVENT FORM ───────────────────────────────────────────────────────────
const isAddEventOpen = ref(false);
const eventForm = useForm({
    title: '',
    type: 'Cultural',
    category: 'Proyección Social',
    event_date: '',
    time: '',
    location: '',
    organizer: 'Dirección de Proyección Social y Extensión Cultural',
    description: '',
    image_file: null as File | null,
    cover_image_file: null as File | null,
    image_files: [] as File[],
    image_path: '',
    fb_link: 'https://www.facebook.com/ProyeccionSocialUNAPuno',
    has_registration: false,
    registration_link: '',
    is_proyeccion_social: true as boolean,
    sort_order: 0,
});

// Interactive Drag & Crop States
const cropSource = ref('');
const zoom = ref(1);
const positionX = ref(0);
const positionY = ref(0);
const dragContainerRef = ref<HTMLElement | null>(null);
const isDragging = ref(false);
const dragStart = ref({ x: 0, y: 0 });
const activeMode = ref<'add' | 'edit'>('add');
const previewImageIndex = ref(0);
const previewImageSize = ref({ width: 0, height: 0 });
const filePreviewUrls = new WeakMap<File, string>();

const getFilePreview = (file: File) => {
    if (!filePreviewUrls.has(file)) {
        filePreviewUrls.set(file, URL.createObjectURL(file));
    }

    return filePreviewUrls.get(file) as string;
};

const loadCropPreview = (file: File) => {
    const reader = new FileReader();
    reader.onload = (event) => {
        cropSource.value = event.target?.result as string;
        const preview = new Image();
        preview.onload = () => {
            previewImageSize.value = {
                width: preview.naturalWidth,
                height: preview.naturalHeight,
            };
            clampCropPosition();
        };
        preview.src = cropSource.value;
        zoom.value = 1;
        positionX.value = 0;
        positionY.value = 0;
        debouncedCrop();
    };
    reader.readAsDataURL(file);
};

const loadCropPreviewFromUrl = async (url: string) => {
    try {
        const response = await fetch(url);
        const blob = await response.blob();
        const extension = blob.type.split('/')[1] || 'jpg';
        loadCropPreview(new File([blob], `event_current_image.${extension}`, { type: blob.type }));
    } catch {
        cropSource.value = url;
        previewImageSize.value = { width: 0, height: 0 };
    }
};

const onEventImagesSelected = (e: Event, mode: 'add' | 'edit') => {
    activeMode.value = mode;
    const target = e.target as HTMLInputElement;
    const files = Array.from(target.files ?? []);
    previewImageIndex.value = 0;

    if (mode === 'edit') {
        editEventForm.image_files = files;
        editEventForm.image_file = null;
        editEventForm.cover_image_file = null;
    } else {
        eventForm.image_files = files;
        eventForm.image_file = null;
        eventForm.cover_image_file = null;
    }

    cropSource.value = '';

    if (files[0]) {
        loadCropPreview(files[0]);
    }
};

const selectPreviewImage = (index: number, mode: 'add' | 'edit') => {
    const files = mode === 'edit' ? editEventForm.image_files : eventForm.image_files;
    const file = files[index];

    if (!file) {
        return;
    }

    activeMode.value = mode;
    previewImageIndex.value = index;
    loadCropPreview(file);
};

const clampCropPosition = () => {
    const container = dragContainerRef.value;
    const containerWidth = container?.clientWidth ?? 400;
    const containerHeight = container?.clientHeight ?? 225;
    const imageRatio = previewImageSize.value.width && previewImageSize.value.height
        ? previewImageSize.value.width / previewImageSize.value.height
        : containerWidth / containerHeight;
    const containerRatio = containerWidth / containerHeight;
    const baseWidth = imageRatio > containerRatio ? containerHeight * imageRatio : containerWidth;
    const baseHeight = imageRatio > containerRatio ? containerHeight : containerWidth / imageRatio;
    const maxX = Math.max((baseWidth * zoom.value - containerWidth) / 2, 0);
    const maxY = Math.max((baseHeight * zoom.value - containerHeight) / 2, 0);

    positionX.value = Math.min(Math.max(positionX.value, -maxX), maxX);
    positionY.value = Math.min(Math.max(positionY.value, -maxY), maxY);
};

const onZoomChanged = () => {
    clampCropPosition();
    debouncedCrop();
};

const cropPreviewStyle = () => {
    const container = dragContainerRef.value;
    const containerWidth = container?.clientWidth ?? 400;
    const containerHeight = container?.clientHeight ?? 225;
    const imageRatio = previewImageSize.value.width && previewImageSize.value.height
        ? previewImageSize.value.width / previewImageSize.value.height
        : containerWidth / containerHeight;

    return {
        left: '50%',
        top: '50%',
        transform: `translate(-50%, -50%) translate(${positionX.value}px, ${positionY.value}px) scale(${zoom.value})`,
        width: imageRatio > containerWidth / containerHeight ? 'auto' : '100%',
        height: imageRatio > containerWidth / containerHeight ? '100%' : 'auto',
        maxWidth: 'none',
        maxHeight: 'none',
    };
};

const startDrag = (e: MouseEvent | TouchEvent) => {
    if (!cropSource.value) {
return;
}

    isDragging.value = true;
    const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
    const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY;
    dragStart.value = {
        x: clientX - positionX.value,
        y: clientY - positionY.value
    };
};

const onDrag = (e: MouseEvent | TouchEvent) => {
    if (!isDragging.value) {
return;
}

    const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
    const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY;
    positionX.value = clientX - dragStart.value.x;
    positionY.value = clientY - dragStart.value.y;
    clampCropPosition();
    debouncedCrop();
};

const stopDrag = () => {
    isDragging.value = false;
};

const cropImage = () => {
    if (!cropSource.value) {
return;
}

    const img = new Image();
    img.src = cropSource.value;
    img.onload = () => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');

        if (!ctx) {
return;
}

        // Target size: 800 x 450 (16:9)
        const targetWidth = 800;
        const targetHeight = 450;
        canvas.width = targetWidth;
        canvas.height = targetHeight;

        const targetRatio = targetWidth / targetHeight;
        const imgRatio = img.naturalWidth / img.naturalHeight;

        let drawWidth, drawHeight;

        if (imgRatio > targetRatio) {
            drawHeight = targetHeight;
            drawWidth = targetHeight * imgRatio;
        } else {
            drawWidth = targetWidth;
            drawHeight = targetWidth / imgRatio;
        }

        // Apply zoom
        drawWidth *= zoom.value;
        drawHeight *= zoom.value;

        // Container dimensions
        const containerWidth = dragContainerRef.value ? dragContainerRef.value.clientWidth : 400;
        const containerHeight = dragContainerRef.value ? dragContainerRef.value.clientHeight : 225;

        // Scaling factors
        const scaleX = targetWidth / containerWidth;
        const scaleY = targetHeight / containerHeight;

        // Center calculation
        const defaultX = (targetWidth - drawWidth) / 2;
        const defaultY = (targetHeight - drawHeight) / 2;

        const destX = defaultX + (positionX.value * scaleX);
        const destY = defaultY + (positionY.value * scaleY);

        // Draw image onto canvas
        ctx.fillStyle = '#000000';
        ctx.fillRect(0, 0, targetWidth, targetHeight);
        ctx.drawImage(img, destX, destY, drawWidth, drawHeight);

        // Export as WebP blob
        canvas.toBlob((blob) => {
            if (blob) {
                const croppedFile = new File([blob], 'event_image.webp', { type: 'image/webp' });

                if (activeMode.value === 'edit') {
                    editEventForm.cover_image_file = croppedFile;
                } else {
                    eventForm.cover_image_file = croppedFile;
                }
            }
        }, 'image/webp', 0.85);
    };
};

let cropTimeout: any = null;
const debouncedCrop = () => {
    if (cropTimeout) {
clearTimeout(cropTimeout);
}

    cropTimeout = setTimeout(() => {
        cropImage();
    }, 100);
};

const openAddEvent = () => {
    cropSource.value = '';
    zoom.value = 1;
    positionX.value = 0;
    positionY.value = 0;
    previewImageIndex.value = 0;
    eventForm.reset();
    isAddEventOpen.value = true;
};

const submitAddEvent = () => {
    eventForm.transform((data) => ({
        ...data,
        registration_link: data.has_registration ? data.registration_link : null,
    })).post('/admin/eventos', {
        preserveScroll: true,
        onSuccess: () => {
            isAddEventOpen.value = false;
            eventForm.reset();
        },
    });
};

// ─── EDIT EVENT FORM ──────────────────────────────────────────────────────────
const isEditEventOpen = ref(false);
const editingEventId = ref<number | null>(null);
const editEventForm = useForm({
    title: '',
    type: '',
    category: '',
    event_date: '',
    time: '',
    location: '',
    organizer: '',
    description: '',
    image_file: null as File | null,
    cover_image_file: null as File | null,
    image_files: [] as File[],
    image_path: '',
    fb_link: '',
    has_registration: false,
    registration_link: '',
    is_proyeccion_social: false as boolean,
    sort_order: 0,
});

const openEditEvent = (ev: EventItem) => {
    activeMode.value = 'edit';
    cropSource.value = '';
    zoom.value = 1;
    positionX.value = 0;
    positionY.value = 0;
    previewImageIndex.value = 0;
    editingEventId.value = ev.id;
    editEventForm.title = ev.title;
    editEventForm.type = ev.type;
    editEventForm.category = ev.category;
    editEventForm.event_date = ev.event_date ? ev.event_date.split('T')[0] : '';
    editEventForm.time = ev.time;
    editEventForm.location = ev.location;
    editEventForm.organizer = ev.organizer;
    editEventForm.description = ev.description;
    editEventForm.image_file = null;
    editEventForm.cover_image_file = null;
    editEventForm.image_files = [];
    editEventForm.image_path = ev.image_path;
    editEventForm.fb_link = ev.fb_link;
    editEventForm.has_registration = Boolean(ev.registration_link);
    editEventForm.registration_link = ev.registration_link || '';
    editEventForm.is_proyeccion_social = ev.is_proyeccion_social;
    editEventForm.sort_order = ev.sort_order;
    isEditEventOpen.value = true;

    const currentImage = ev.cover_image_path || ev.image_path;

    if (currentImage) {
        loadCropPreviewFromUrl(currentImage);
    }
};

const submitEditEvent = () => {
    editEventForm.transform((data) => ({
        ...data,
        registration_link: data.has_registration ? data.registration_link : null,
        _method: 'PUT'
    })).post(`/admin/eventos/${editingEventId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditEventOpen.value = false;
        },
    });
};

const deleteEvent = (id: number) => {
    if (confirm('¿Está seguro de que desea eliminar este evento? Esta acción no se puede deshacer.')) {
        router.delete(`/admin/eventos/${id}`, { preserveScroll: true });
    }
};

// ─── ADD DOCUMENT FORM ────────────────────────────────────────────────────────
const isAddDocOpen = ref(false);
const docForm = useForm({
    title: '',
    code: '',
    category: 'Directivas',
    published_date: '',
    description: '',
    file_file: null as File | null,
    is_active: true as boolean,
    sort_order: 0,
});

const submitAddDoc = () => {
    docForm.post('/admin/documentos', {
        preserveScroll: true,
        onSuccess: () => {
            isAddDocOpen.value = false;
            docForm.reset();
        },
    });
};

// ─── EDIT DOCUMENT FORM ───────────────────────────────────────────────────────
const isEditDocOpen = ref(false);
const editingDocId = ref<number | null>(null);
const editDocForm = useForm({
    title: '',
    code: '',
    category: '',
    published_date: '',
    description: '',
    file_file: null as File | null,
    file_path: '', // readonly to show current URL
    is_active: true as boolean,
    sort_order: 0,
    _method: 'PUT', // standard Laravel method spoofing field
});

const openEditDoc = (doc: DocumentItem) => {
    editingDocId.value = doc.id;
    editDocForm.title = doc.title;
    editDocForm.code = doc.code;
    editDocForm.category = doc.category;
    editDocForm.published_date = doc.published_date ? doc.published_date.split('T')[0] : '';
    editDocForm.description = doc.description;
    editDocForm.file_path = doc.file_path;
    editDocForm.file_file = null;
    editDocForm.is_active = doc.is_active;
    editDocForm.sort_order = doc.sort_order;
    isEditDocOpen.value = true;
};

const submitEditDoc = () => {
    // Send a POST request with the spoofed PUT method to support file uploads
    editDocForm.post(`/admin/documentos/${editingDocId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditDocOpen.value = false;
        },
    });
};

const deleteDoc = (id: number) => {
    if (confirm('¿Está seguro de que desea eliminar este documento?')) {
        router.delete(`/admin/documentos/${id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Panel de Administración" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-7xl mx-auto w-full text-left">
        
        <!-- Flash Messages -->
        <div v-if="flash?.success" class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 text-sm font-medium">
            <CheckCircle class="size-5 shrink-0" />
            {{ flash.success }}
        </div>
        <div v-if="flash?.error" class="flex items-center gap-3 p-4 rounded-xl bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm font-medium">
            <AlertCircle class="size-5 shrink-0" />
            {{ flash.error }}
        </div>

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-neutral-200 dark:border-neutral-800 pb-5">
            <div>
                <h1 class="text-2xl font-black text-neutral-900 dark:text-white flex items-center gap-2">
                    <Sparkles class="size-6 text-indigo-600 dark:text-indigo-400" />
                    Intranet de Gestión DPESEC
                </h1>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                    Administra los eventos, proyección social y documentos oficiales de la Dirección.
                </p>
            </div>

            <!-- Tab Buttons -->
            <div class="inline-flex p-1 bg-neutral-100 dark:bg-neutral-900 rounded-xl">
                <button 
                    @click="activeTab = 'overview'"
                    class="px-4 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer"
                    :class="activeTab === 'overview' ? 'bg-white dark:bg-neutral-800 shadow-xs text-indigo-600 dark:text-indigo-400' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900'"
                >
                    Resumen
                </button>
                <button 
                    @click="activeTab = 'events'"
                    class="px-4 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer"
                    :class="activeTab === 'events' ? 'bg-white dark:bg-neutral-800 shadow-xs text-indigo-600 dark:text-indigo-400' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900'"
                >
                    Eventos
                </button>
                <button 
                    @click="activeTab = 'docs'"
                    class="px-4 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer"
                    :class="activeTab === 'docs' ? 'bg-white dark:bg-neutral-800 shadow-xs text-indigo-600 dark:text-indigo-400' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900'"
                >
                    Documentos de Gestión
                </button>
                <button 
                    @click="activeTab = 'certificates'"
                    class="px-4 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer"
                    :class="activeTab === 'certificates' ? 'bg-white dark:bg-neutral-800 shadow-xs text-indigo-600 dark:text-indigo-400' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900'"
                >
                    Certificados
                </button>
            </div>

        </div>

        <!-- 1. OVERVIEW TAB PANEL -->
        <div v-if="activeTab === 'overview'" class="space-y-6 animate-in fade-in duration-300">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="p-6 rounded-2xl border border-neutral-200/80 dark:border-neutral-800/80 bg-white dark:bg-neutral-900/60 shadow-xs flex items-center justify-between">
                    <div class="space-y-1">
                        <span class="text-xs text-neutral-500 dark:text-neutral-400 font-bold uppercase tracking-wider">Total Eventos</span>
                        <h3 class="text-3xl font-black text-neutral-900 dark:text-white">{{ stats.totalEvents }}</h3>
                        <p class="text-[10px] text-neutral-400">Registrados en la BD</p>
                    </div>
                    <div class="size-12 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                        <Calendar class="size-6" />
                    </div>
                </div>

                <div class="p-6 rounded-2xl border border-neutral-200/80 dark:border-neutral-800/80 bg-white dark:bg-neutral-900/60 shadow-xs flex items-center justify-between">
                    <div class="space-y-1">
                        <span class="text-xs text-neutral-500 dark:text-neutral-400 font-bold uppercase tracking-wider">Proyección Social</span>
                        <h3 class="text-3xl font-black text-neutral-900 dark:text-white">{{ stats.totalProyeccionSocial }}</h3>
                        <p class="text-[10px] text-neutral-400">Eventos etiquetados</p>
                    </div>
                    <div class="size-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <Heart class="size-6" />
                    </div>
                </div>

                <div class="p-6 rounded-2xl border border-neutral-200/80 dark:border-neutral-800/80 bg-white dark:bg-neutral-900/60 shadow-xs flex items-center justify-between">
                    <div class="space-y-1">
                        <span class="text-xs text-neutral-500 dark:text-neutral-400 font-bold uppercase tracking-wider">Documentos</span>
                        <h3 class="text-3xl font-black text-neutral-900 dark:text-white">{{ stats.totalDocs }}</h3>
                        <p class="text-[10px] text-neutral-400">Directivas y resoluciones</p>
                    </div>
                    <div class="size-12 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                        <FileText class="size-6" />
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 p-8 rounded-2xl border border-neutral-200/80 dark:border-neutral-800/80 bg-white dark:bg-neutral-900/60 space-y-6">
                    <h3 class="text-lg font-black text-neutral-900 dark:text-white">Acciones Rápidas</h3>
                    <p class="text-xs text-neutral-500 leading-relaxed font-normal">
                        Los cambios que realizas aquí se reflejan inmediatamente en el sitio web público.
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <button 
                            @click="activeTab = 'events'; openAddEvent()"
                            class="p-4 rounded-xl border border-dashed border-indigo-200 dark:border-indigo-800 hover:border-indigo-500 bg-indigo-50/20 dark:bg-indigo-950/10 flex items-center gap-4 transition-all text-left group cursor-pointer"
                        >
                            <div class="size-10 rounded-lg bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center group-hover:scale-105 transition-all">
                                <PlusCircle class="size-5" />
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-neutral-900 dark:text-white">Crear Nuevo Evento</h4>
                                <p class="text-[10px] text-neutral-400">Festivales, jornadas, voluntariados</p>
                            </div>
                        </button>

                        <button 
                            @click="activeTab = 'docs'; isAddDocOpen = true"
                            class="p-4 rounded-xl border border-dashed border-purple-200 dark:border-purple-800 hover:border-purple-500 bg-purple-50/20 dark:bg-purple-950/10 flex items-center gap-4 transition-all text-left group cursor-pointer"
                        >
                            <div class="size-10 rounded-lg bg-purple-600/10 text-purple-600 dark:text-purple-400 flex items-center justify-center group-hover:scale-105 transition-all">
                                <FolderUp class="size-5" />
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-neutral-900 dark:text-white">Subir Documento</h4>
                                <p class="text-[10px] text-neutral-400">Directivas y resoluciones</p>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="p-8 rounded-2xl border border-neutral-200/80 dark:border-neutral-800/80 bg-white dark:bg-neutral-900/60 space-y-4">
                    <h3 class="text-sm font-bold text-neutral-900 dark:text-white flex items-center gap-1.5">
                        <TrendingUp class="size-4 text-emerald-600 dark:text-emerald-400" />
                        Estado de Eventos
                    </h3>
                    <p class="text-[11px] text-neutral-500 leading-relaxed font-normal">
                        El estado de cada evento (<strong>Próximo</strong>, <strong>En Curso</strong>, <strong>Finalizado</strong>) se calcula automáticamente a partir de la fecha ingresada. No necesitas asignarlo manualmente.
                    </p>
                    <div class="bg-neutral-50 dark:bg-neutral-950 p-4 rounded-xl space-y-2 border border-neutral-200/20">
                        <div class="flex items-center gap-2 text-[10px] font-semibold text-indigo-600 dark:text-indigo-400">
                            <span class="size-2 rounded-full bg-indigo-500 shrink-0"></span>
                            Fecha futura → Próximo
                        </div>
                        <div class="flex items-center gap-2 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                            <span class="size-2 rounded-full bg-emerald-500 shrink-0"></span>
                            Fecha hoy → En Curso
                        </div>
                        <div class="flex items-center gap-2 text-[10px] font-semibold text-neutral-500 dark:text-neutral-400">
                            <span class="size-2 rounded-full bg-neutral-400 shrink-0"></span>
                            Fecha pasada → Finalizado
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. EVENTS TAB PANEL -->
        <div v-if="activeTab === 'events'" class="space-y-6 animate-in fade-in duration-300">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="relative w-full sm:w-80">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-neutral-400" />
                    <Input 
                        v-model="eventQuery" 
                        placeholder="Buscar evento por título, área..."
                        class="pl-9 rounded-xl border-neutral-300 dark:border-neutral-700 text-xs h-10 w-full"
                    />
                </div>
                <Button 
                    @click="openAddEvent()"
                    class="rounded-xl h-10 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold flex items-center gap-1.5 text-xs w-full sm:w-auto cursor-pointer"
                >
                    <Plus class="size-4" />
                    Agregar Nuevo Evento
                </Button>
            </div>

            <!-- Events Table -->
            <div class="border border-neutral-200/80 dark:border-neutral-800/80 rounded-2xl overflow-hidden bg-white dark:bg-neutral-900/60 shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-neutral-50 dark:bg-neutral-950/40 text-neutral-500 dark:text-neutral-400 uppercase tracking-wider font-bold border-b border-neutral-200 dark:border-neutral-800">
                                <th class="p-4 font-bold">Título / Evento</th>
                                <th class="p-4 font-bold">Estado</th>
                                <th class="p-4 font-bold">Fecha</th>
                                <th class="p-4 font-bold">Categoría</th>
                                <th class="p-4 font-bold text-center">Proy. Social</th>
                                <th class="p-4 font-bold text-right w-28">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr 
                                v-for="item in filteredEvents" 
                                :key="item.id"
                                class="hover:bg-neutral-50/50 dark:hover:bg-neutral-950/20 transition-colors"
                            >
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <img v-if="item.image_path" :src="item.image_path" class="size-10 rounded-lg object-contain bg-neutral-100 shrink-0 border border-neutral-200/20" />
                                        <div v-else class="size-10 rounded-lg bg-indigo-500/10 flex items-center justify-center shrink-0">
                                            <Calendar class="size-5 text-indigo-500" />
                                        </div>
                                        <div>
                                            <span class="font-extrabold text-neutral-900 dark:text-white line-clamp-1 max-w-[280px] block">{{ item.title }}</span>
                                            <span class="text-[10px] text-neutral-400">{{ item.location }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span 
                                        class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase text-white"
                                        :class="item.status_color"
                                    >
                                        {{ item.status_label }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-1.5 text-neutral-700 dark:text-neutral-300 font-bold">
                                        <Clock class="size-3.5 text-neutral-400 shrink-0" />
                                        {{ formatDate(item.event_date) }}
                                    </div>
                                    <div class="text-[10px] text-neutral-400 mt-0.5 ml-5">{{ item.time }}</div>
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                                        {{ item.category }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <span 
                                        class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase"
                                        :class="item.is_proyeccion_social ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-neutral-100 text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400'"
                                    >
                                        {{ item.is_proyeccion_social ? 'Sí' : 'No' }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a v-if="item.fb_link" :href="item.fb_link" target="_blank" class="p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-500">
                                            <ExternalLink class="size-4" />
                                        </a>
                                        <button 
                                            @click="openEditEvent(item)"
                                            class="p-2 rounded-lg hover:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 cursor-pointer"
                                        >
                                            <Pencil class="size-4" />
                                        </button>
                                        <button 
                                            @click="deleteEvent(item.id)" 
                                            class="p-2 rounded-lg hover:bg-red-500/10 text-red-500 cursor-pointer"
                                        >
                                            <Trash2 class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredEvents.length === 0">
                                <td colspan="6" class="p-8 text-center text-neutral-400">No se encontraron eventos registrados.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. DOCUMENTS TAB PANEL -->
        <div v-if="activeTab === 'docs'" class="space-y-6 animate-in fade-in duration-300">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="relative w-full sm:w-80">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-neutral-400" />
                    <Input 
                        v-model="docQuery" 
                        placeholder="Buscar documento por título, código..."
                        class="pl-9 rounded-xl border-neutral-300 dark:border-neutral-700 text-xs h-10 w-full"
                    />
                </div>
                <Button 
                    @click="isAddDocOpen = true"
                    class="rounded-xl h-10 px-4 bg-purple-600 hover:bg-purple-700 text-white font-bold flex items-center gap-1.5 text-xs w-full sm:w-auto cursor-pointer"
                >
                    <Plus class="size-4" />
                    Subir Nuevo Documento
                </Button>
            </div>

            <!-- Documents Table -->
            <div class="border border-neutral-200/80 dark:border-neutral-800/80 rounded-2xl overflow-hidden bg-white dark:bg-neutral-900/60 shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-neutral-50 dark:bg-neutral-950/40 text-neutral-500 dark:text-neutral-400 uppercase tracking-wider font-bold border-b border-neutral-200 dark:border-neutral-800">
                                <th class="p-4 font-bold">Código</th>
                                <th class="p-4 font-bold">Título / Resolución</th>
                                <th class="p-4 font-bold">Categoría</th>
                                <th class="p-4 font-bold">Fecha</th>
                                <th class="p-4 font-bold text-center">Estado</th>
                                <th class="p-4 font-bold text-right w-28">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                            <tr 
                                v-for="doc in filteredDocs" 
                                :key="doc.id"
                                class="hover:bg-neutral-50/50 dark:hover:bg-neutral-950/20 transition-colors"
                            >
                                <td class="p-4 font-mono font-bold text-neutral-900 dark:text-white whitespace-nowrap">{{ doc.code }}</td>
                                <td class="p-4">
                                    <div class="font-extrabold text-neutral-900 dark:text-white line-clamp-1 max-w-[400px]">{{ doc.title }}</div>
                                    <div v-if="doc.description" class="text-[10px] text-neutral-400 line-clamp-1 mt-0.5">{{ doc.description }}</div>
                                </td>
                                <td class="p-4">
                                    <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold uppercase bg-purple-500/10 text-purple-600 dark:text-purple-400">
                                        {{ doc.category }}
                                    </span>
                                </td>
                                <td class="p-4 text-neutral-600 dark:text-neutral-300 font-bold whitespace-nowrap">{{ formatDate(doc.published_date) }}</td>
                                <td class="p-4 text-center">
                                    <span 
                                        class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase"
                                        :class="doc.is_active ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-neutral-100 text-neutral-500 dark:bg-neutral-800'"
                                    >
                                        {{ doc.is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a v-if="doc.file_path" :href="doc.file_path" target="_blank" class="p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-500">
                                            <ExternalLink class="size-4" />
                                        </a>
                                        <button 
                                            @click="openEditDoc(doc)"
                                            class="p-2 rounded-lg hover:bg-purple-500/10 text-purple-600 dark:text-purple-400 cursor-pointer"
                                        >
                                            <Pencil class="size-4" />
                                        </button>
                                        <button 
                                            @click="deleteDoc(doc.id)" 
                                            class="p-2 rounded-lg hover:bg-red-500/10 text-red-500 cursor-pointer"
                                        >
                                            <Trash2 class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredDocs.length === 0">
                                <td colspan="6" class="p-8 text-center text-neutral-400">No se encontraron documentos registrados.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 4. CERTIFICATES TAB PANEL -->
        <div v-if="activeTab === 'certificates'" class="space-y-6 animate-in fade-in duration-300">
            <CertificateTab 
                :templates="templates" 
                :fonts="fonts" 
                :certificates="certificates" 
            />
        </div>


        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- MODAL: AGREGAR EVENTO -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div v-if="isAddEventOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 animate-in fade-in duration-200">
            <div class="w-full max-w-lg bg-white dark:bg-neutral-900 rounded-[24px] border border-neutral-200 dark:border-neutral-800 shadow-2xl p-6 relative flex flex-col max-h-[90vh] overflow-y-auto">
                <button @click="isAddEventOpen = false" class="absolute top-4 right-4 p-2 hover:bg-neutral-100 dark:hover:bg-neutral-800 rounded-lg text-neutral-500 cursor-pointer">
                    <X class="size-5" />
                </button>
                
                <h3 class="text-lg font-black text-neutral-900 dark:text-white mb-1 flex items-center gap-1.5">
                    <Calendar class="size-5 text-indigo-600 dark:text-indigo-400" />
                    Registrar Nuevo Evento
                </h3>
                <p class="text-xs text-neutral-500 mb-6 font-normal">El estado (Próximo/En Curso/Finalizado) se calcula automáticamente desde la fecha.</p>
                
                <form @submit.prevent="submitAddEvent" class="space-y-4 text-left">
                    <div class="space-y-1">
                        <Label for="add-title" class="text-xs">Título de la Actividad</Label>
                        <Input v-model="eventForm.title" id="add-title" required placeholder="Ej. Concurso de Sikuris..." class="rounded-xl text-xs h-10" />
                        <p v-if="eventForm.errors.title" class="text-red-500 text-[10px]">{{ eventForm.errors.title }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label for="add-category" class="text-xs">Categoría</Label>
                            <select v-model="eventForm.category" id="add-category" class="w-full h-10 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option value="Proyección Social">Proyección Social</option>
                                <option value="Extensión Cultural">Extensión Cultural</option>
                                <option value="Voluntariado Universitario">Voluntariado Universitario</option>
                                <option value="Gestión Ambiental">Gestión Ambiental</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <Label for="add-type" class="text-xs">Tipo</Label>
                            <select v-model="eventForm.type" id="add-type" class="w-full h-10 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option value="Cultural">Cultural</option>
                                <option value="Social">Social</option>
                                <option value="Ambiental">Ambiental</option>
                                <option value="Académico">Académico</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label for="add-event-date" class="text-xs">Fecha del Evento</Label>
                            <Input v-model="eventForm.event_date" id="add-event-date" type="date" required class="rounded-xl text-xs h-10" />
                            <p v-if="eventForm.errors.event_date" class="text-red-500 text-[10px]">{{ eventForm.errors.event_date }}</p>
                        </div>
                        <div class="space-y-1">
                            <Label for="add-time" class="text-xs">Hora</Label>
                            <Input v-model="eventForm.time" id="add-time" placeholder="Ej. 09:00 AM" class="rounded-xl text-xs h-10" />
                        </div>
                    </div>

                    <div class="space-y-1">
                        <Label for="add-location" class="text-xs">Ubicación / Lugar</Label>
                        <Input v-model="eventForm.location" id="add-location" required placeholder="Ej. Campus Universitario..." class="rounded-xl text-xs h-10" />
                    </div>

                    <div class="space-y-1">
                        <Label for="add-organizer" class="text-xs">Organizador</Label>
                        <Input v-model="eventForm.organizer" id="add-organizer" required class="rounded-xl text-xs h-10" />
                    </div>

                    <div class="space-y-1">
                        <Label for="add-description" class="text-xs">Descripción</Label>
                        <textarea v-model="eventForm.description" id="add-description" required rows="3" placeholder="Resumen del evento..." class="w-full p-3 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
                    </div>

                    <!-- Image Upload with drag cropper -->
                    <div class="space-y-1.5">
                        <Label class="text-xs">Imagen del Evento (Se convertirá a WebP ligero)</Label>
                        
                        <!-- File Input -->
                        <div class="flex items-center gap-2">
                            <input 
                                type="file" 
                                accept="image/*" 
                                multiple
                                @change="e => onEventImagesSelected(e, 'add')"
                                id="add-image-file" 
                                class="hidden" 
                            />
                            <label 
                                for="add-image-file" 
                                class="flex items-center justify-center gap-2 px-4 py-2 border border-neutral-200 dark:border-neutral-800 rounded-xl text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-500/5 hover:bg-indigo-500/10 transition-colors cursor-pointer select-none"
                            >
                                <Upload class="size-4" />
                                Seleccionar imágenes
                            </label>
                            <span class="text-[10px] text-neutral-400 truncate max-w-[200px]" v-if="eventForm.image_files.length">
                                {{ eventForm.image_files.length }} imagen(es) seleccionada(s)
                            </span>
                        </div>
                        <div v-if="eventForm.image_files.length" class="flex gap-2 overflow-x-auto py-1">
                            <button
                                v-for="(file, index) in eventForm.image_files"
                                :key="`${file.name}-${index}`"
                                type="button"
                                class="size-14 shrink-0 overflow-hidden rounded-lg border-2 bg-neutral-100 dark:bg-neutral-950 cursor-pointer"
                                :class="index === previewImageIndex && activeMode === 'add' ? 'border-indigo-500' : 'border-transparent'"
                                :title="`Editar imagen ${index + 1}`"
                                @click="selectPreviewImage(index, 'add')"
                            >
                                <img :src="getFilePreview(file)" alt="" class="w-full h-full object-cover" />
                            </button>
                        </div>
                        <p v-if="eventForm.errors.image_file" class="text-red-500 text-[10px]">{{ eventForm.errors.image_file }}</p>
                        <p v-if="eventForm.errors.image_files" class="text-red-500 text-[10px]">{{ eventForm.errors.image_files }}</p>

                        <!-- Live Interactive Card Preview -->
                        <div v-if="cropSource" class="mt-4 space-y-2">
                            <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider block">Ajustar visualización en Tarjeta:</span>
                            
                            <!-- Card Image container box -->
                            <div 
                                ref="dragContainerRef"
                                class="h-52 w-full relative overflow-hidden bg-neutral-150 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-2xl cursor-move select-none"
                                @mousedown="startDrag"
                                @mousemove="onDrag"
                                @mouseup="stopDrag"
                                @mouseleave="stopDrag"
                                @touchstart="startDrag"
                                @touchmove="onDrag"
                                @touchend="stopDrag"
                            >
                                <img 
                                    :src="cropSource" 
                                    alt="Preview" 
                                    class="absolute pointer-events-none origin-center"
                                    :style="cropPreviewStyle()"
                                />
                                <!-- Cropper Helper Grid lines -->
                                <div class="absolute inset-0 border border-indigo-500/30 pointer-events-none flex items-center justify-center">
                                    <div class="w-full h-[33.3%] border-y border-dashed border-white/20"></div>
                                    <div class="h-full w-[33.3%] border-x border-dashed border-white/20 absolute"></div>
                                </div>
                            </div>
                            <p class="text-[10px] text-neutral-400">Arrastra la foto para centrarla dentro del recuadro.</p>

                            <!-- Zoom range control -->
                            <div class="flex items-center gap-3 pt-1">
                                <span class="text-[10px] font-bold text-neutral-500">Zoom:</span>
                                <input 
                                    type="range" 
                                    min="1" 
                                    max="3" 
                                    step="0.05" 
                                    v-model.number="zoom" 
                                    @input="onZoomChanged"
                                    class="flex-grow h-1.5 bg-neutral-200 dark:bg-neutral-800 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                                />
                                <span class="text-[10px] font-mono text-neutral-500">{{ zoom.toFixed(1) }}x</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <Label for="add-fb-link" class="text-xs">Enlace de Facebook</Label>
                        <Input v-model="eventForm.fb_link" id="add-fb-link" type="url" placeholder="https://facebook.com/..." class="rounded-xl text-xs h-10" />
                    </div>

                    <div class="space-y-2 rounded-xl border border-neutral-200 dark:border-neutral-800 p-3">
                        <label class="flex items-center gap-2 text-xs font-bold cursor-pointer">
                            <input v-model="eventForm.has_registration" type="checkbox" class="size-4 rounded border-neutral-300 cursor-pointer" />
                            Mostrar botón de registro
                        </label>
                        <Input v-if="eventForm.has_registration" v-model="eventForm.registration_link" type="url" placeholder="https://forms.google.com/..." class="rounded-xl text-xs h-10" />
                        <p v-if="eventForm.errors.registration_link" class="text-red-500 text-[10px]">{{ eventForm.errors.registration_link }}</p>
                    </div>

                    <div class="flex items-center space-x-3 py-2">
                        <input type="checkbox" v-model="eventForm.is_proyeccion_social" id="add-is-proyeccion-social" class="size-4 rounded border-neutral-300 cursor-pointer" />
                        <Label for="add-is-proyeccion-social" class="text-xs font-bold cursor-pointer select-none">¿Pertenece al área de Proyección Social?</Label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-neutral-100 dark:border-neutral-800">
                        <Button type="button" @click="isAddEventOpen = false" variant="outline" class="rounded-xl text-xs h-10 cursor-pointer">Cancelar</Button>
                        <Button type="submit" :disabled="eventForm.processing" class="rounded-xl text-xs h-10 bg-indigo-600 hover:bg-indigo-700 text-white font-bold cursor-pointer">
                            {{ eventForm.processing ? 'Guardando...' : 'Registrar Evento' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: EDITAR EVENTO -->
        <div v-if="isEditEventOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 animate-in fade-in duration-200">
            <div class="w-full max-w-lg bg-white dark:bg-neutral-900 rounded-[24px] border border-neutral-200 dark:border-neutral-800 shadow-2xl p-6 relative flex flex-col max-h-[90vh] overflow-y-auto">
                <button @click="isEditEventOpen = false" class="absolute top-4 right-4 p-2 hover:bg-neutral-100 dark:hover:bg-neutral-800 rounded-lg text-neutral-500 cursor-pointer">
                    <X class="size-5" />
                </button>
                <h3 class="text-lg font-black text-neutral-900 dark:text-white mb-1 flex items-center gap-1.5">
                    <Pencil class="size-5 text-indigo-600 dark:text-indigo-400" />
                    Editar Evento
                </h3>
                <p class="text-xs text-neutral-500 mb-6 font-normal">Modifica los campos y guarda para actualizar el sitio web.</p>
                
                <form @submit.prevent="submitEditEvent" class="space-y-4 text-left">
                    <div class="space-y-1">
                        <Label for="edit-title" class="text-xs">Título</Label>
                        <Input v-model="editEventForm.title" id="edit-title" required class="rounded-xl text-xs h-10" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label class="text-xs">Categoría</Label>
                            <select v-model="editEventForm.category" class="w-full h-10 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option value="Proyección Social">Proyección Social</option>
                                <option value="Extensión Cultural">Extensión Cultural</option>
                                <option value="Voluntariado Universitario">Voluntariado Universitario</option>
                                <option value="Gestión Ambiental">Gestión Ambiental</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <Label class="text-xs">Tipo</Label>
                            <select v-model="editEventForm.type" class="w-full h-10 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option value="Cultural">Cultural</option>
                                <option value="Social">Social</option>
                                <option value="Ambiental">Ambiental</option>
                                <option value="Académico">Académico</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label for="edit-event-date" class="text-xs">Fecha del Evento</Label>
                            <Input v-model="editEventForm.event_date" id="edit-event-date" type="date" required class="rounded-xl text-xs h-10" />
                        </div>
                        <div class="space-y-1">
                            <Label for="edit-time" class="text-xs">Hora</Label>
                            <Input v-model="editEventForm.time" id="edit-time" placeholder="Ej. 09:00 AM" class="rounded-xl text-xs h-10" />
                        </div>
                    </div>
                    <div class="space-y-1">
                        <Label for="edit-location" class="text-xs">Ubicación</Label>
                        <Input v-model="editEventForm.location" id="edit-location" required class="rounded-xl text-xs h-10" />
                    </div>
                    <div class="space-y-1">
                        <Label for="edit-organizer" class="text-xs">Organizador</Label>
                        <Input v-model="editEventForm.organizer" id="edit-organizer" required class="rounded-xl text-xs h-10" />
                    </div>
                    <div class="space-y-1">
                        <Label for="edit-description" class="text-xs">Descripción</Label>
                        <textarea v-model="editEventForm.description" id="edit-description" required rows="3" class="w-full p-3 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
                    </div>
                    <!-- Image Upload with drag cropper -->
                    <div class="space-y-1.5">
                        <Label class="text-xs">Imagen del Evento (Se convertirá a WebP ligero)</Label>
                        
                        <!-- Current image preview if exists -->
                        <div v-if="editEventForm.image_path && !cropSource" class="mb-2 relative w-full h-32 rounded-xl overflow-hidden bg-neutral-100 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800">
                            <img :src="editEventForm.image_path" class="w-full h-full object-cover" />
                            <div class="absolute inset-0 bg-black/45 flex items-center justify-center text-xs font-bold text-white">Imagen Actual</div>
                        </div>

                        <!-- File Input -->
                        <div class="flex items-center gap-2">
                            <input 
                                type="file" 
                                accept="image/*" 
                                multiple
                                @change="e => onEventImagesSelected(e, 'edit')"
                                id="edit-image-file" 
                                class="hidden" 
                            />
                            <label 
                                for="edit-image-file" 
                                class="flex items-center justify-center gap-2 px-4 py-2 border border-neutral-200 dark:border-neutral-800 rounded-xl text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-500/5 hover:bg-indigo-500/10 transition-colors cursor-pointer select-none"
                            >
                                <Upload class="size-4" />
                                Reemplazar imágenes
                            </label>
                            <span class="text-[10px] text-neutral-400 truncate max-w-[200px]" v-if="editEventForm.image_files.length">
                                {{ editEventForm.image_files.length }} imagen(es) seleccionada(s)
                            </span>
                        </div>
                        <div v-if="editEventForm.image_files.length" class="flex gap-2 overflow-x-auto py-1">
                            <button
                                v-for="(file, index) in editEventForm.image_files"
                                :key="`${file.name}-${index}`"
                                type="button"
                                class="size-14 shrink-0 overflow-hidden rounded-lg border-2 bg-neutral-100 dark:bg-neutral-950 cursor-pointer"
                                :class="index === previewImageIndex && activeMode === 'edit' ? 'border-indigo-500' : 'border-transparent'"
                                :title="`Editar imagen ${index + 1}`"
                                @click="selectPreviewImage(index, 'edit')"
                            >
                                <img :src="getFilePreview(file)" alt="" class="w-full h-full object-cover" />
                            </button>
                        </div>
                        <p v-if="editEventForm.errors.image_file" class="text-red-500 text-[10px]">{{ editEventForm.errors.image_file }}</p>
                        <p v-if="editEventForm.errors.image_files" class="text-red-500 text-[10px]">{{ editEventForm.errors.image_files }}</p>

                        <!-- Live Interactive Card Preview -->
                        <div v-if="cropSource" class="mt-4 space-y-2">
                            <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-wider block">Ajustar visualización en Tarjeta:</span>
                            
                            <!-- Card Image container box -->
                            <div 
                                ref="dragContainerRef"
                                class="h-52 w-full relative overflow-hidden bg-neutral-150 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-2xl cursor-move select-none"
                                @mousedown="startDrag"
                                @mousemove="onDrag"
                                @mouseup="stopDrag"
                                @mouseleave="stopDrag"
                                @touchstart="startDrag"
                                @touchmove="onDrag"
                                @touchend="stopDrag"
                            >
                                <img 
                                    :src="cropSource" 
                                    alt="Preview" 
                                    class="absolute pointer-events-none origin-center"
                                    :style="cropPreviewStyle()"
                                />
                                <!-- Cropper Helper Grid lines -->
                                <div class="absolute inset-0 border border-indigo-500/30 pointer-events-none flex items-center justify-center">
                                    <div class="w-full h-[33.3%] border-y border-dashed border-white/20"></div>
                                    <div class="h-full w-[33.3%] border-x border-dashed border-white/20 absolute"></div>
                                </div>
                            </div>
                            <p class="text-[10px] text-neutral-400">Arrastra la foto para centrarla dentro del recuadro.</p>

                            <!-- Zoom range control -->
                            <div class="flex items-center gap-3 pt-1">
                                <span class="text-[10px] font-bold text-neutral-500">Zoom:</span>
                                <input 
                                    type="range" 
                                    min="1" 
                                    max="3" 
                                    step="0.05" 
                                    v-model.number="zoom" 
                                    @input="onZoomChanged"
                                    class="flex-grow h-1.5 bg-neutral-200 dark:bg-neutral-800 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                                />
                                <span class="text-[10px] font-mono text-neutral-500">{{ zoom.toFixed(1) }}x</span>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <Label for="edit-fb-link" class="text-xs">Enlace de Facebook</Label>
                        <Input v-model="editEventForm.fb_link" id="edit-fb-link" type="url" class="rounded-xl text-xs h-10" />
                    </div>
                    <div class="space-y-2 rounded-xl border border-neutral-200 dark:border-neutral-800 p-3">
                        <label class="flex items-center gap-2 text-xs font-bold cursor-pointer">
                            <input v-model="editEventForm.has_registration" type="checkbox" class="size-4 rounded border-neutral-300 cursor-pointer" />
                            Mostrar botón de registro
                        </label>
                        <Input v-if="editEventForm.has_registration" v-model="editEventForm.registration_link" type="url" placeholder="https://forms.google.com/..." class="rounded-xl text-xs h-10" />
                        <p v-if="editEventForm.errors.registration_link" class="text-red-500 text-[10px]">{{ editEventForm.errors.registration_link }}</p>
                    </div>
                    <div class="flex items-center space-x-3 py-2">
                        <input type="checkbox" v-model="editEventForm.is_proyeccion_social" id="edit-is-proyeccion-social" class="size-4 rounded border-neutral-300 cursor-pointer" />
                        <Label for="edit-is-proyeccion-social" class="text-xs font-bold cursor-pointer select-none">¿Pertenece al área de Proyección Social?</Label>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-neutral-100 dark:border-neutral-800">
                        <Button type="button" @click="isEditEventOpen = false" variant="outline" class="rounded-xl text-xs h-10 cursor-pointer">Cancelar</Button>
                        <Button type="submit" :disabled="editEventForm.processing" class="rounded-xl text-xs h-10 bg-indigo-600 hover:bg-indigo-700 text-white font-bold cursor-pointer">
                            {{ editEventForm.processing ? 'Guardando...' : 'Guardar Cambios' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: AGREGAR DOCUMENTO -->
        <div v-if="isAddDocOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 animate-in fade-in duration-200">
            <div class="w-full max-w-md bg-white dark:bg-neutral-900 rounded-[24px] border border-neutral-200 dark:border-neutral-800 shadow-2xl p-6 relative flex flex-col max-h-[90vh] overflow-y-auto">
                <button @click="isAddDocOpen = false" class="absolute top-4 right-4 p-2 hover:bg-neutral-100 dark:hover:bg-neutral-800 rounded-lg text-neutral-500 cursor-pointer">
                    <X class="size-5" />
                </button>
                <h3 class="text-lg font-black text-neutral-900 dark:text-white mb-1 flex items-center gap-1.5">
                    <FileText class="size-5 text-purple-600 dark:text-purple-400" />
                    Registrar Nuevo Documento
                </h3>
                <p class="text-xs text-neutral-500 mb-6 font-normal">Registra la directiva o resolución aprobada.</p>
                
                <form @submit.prevent="submitAddDoc" class="space-y-4 text-left">
                    <div class="space-y-1">
                        <Label for="add-doc-title" class="text-xs">Título del Documento</Label>
                        <Input v-model="docForm.title" id="add-doc-title" required placeholder="Ej. Directiva N° 004-2026..." class="rounded-xl text-xs h-10" />
                        <p v-if="docForm.errors.title" class="text-red-500 text-[10px]">{{ docForm.errors.title }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label for="add-doc-code" class="text-xs">Código</Label>
                            <Input v-model="docForm.code" id="add-doc-code" required placeholder="DIR-004-2026" class="rounded-xl text-xs h-10" />
                            <p v-if="docForm.errors.code" class="text-red-500 text-[10px]">{{ docForm.errors.code }}</p>
                        </div>
                        <div class="space-y-1">
                            <Label for="add-doc-category" class="text-xs">Categoría</Label>
                            <select v-model="docForm.category" id="add-doc-category" class="w-full h-10 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-purple-500">
                                <option value="Directivas">Directivas</option>
                                <option value="Resoluciones">Resoluciones</option>
                                <option value="Reglamentos">Reglamentos</option>
                                <option value="Guías y Formatos">Guías y Formatos</option>
                            </select>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <Label for="add-doc-date" class="text-xs">Fecha de Aprobación</Label>
                        <Input v-model="docForm.published_date" id="add-doc-date" type="date" required class="rounded-xl text-xs h-10" />
                        <p v-if="docForm.errors.published_date" class="text-red-500 text-[10px]">{{ docForm.errors.published_date }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label for="add-doc-description" class="text-xs">Descripción breve</Label>
                        <textarea v-model="docForm.description" id="add-doc-description" rows="2" placeholder="Resumen del contenido..." class="w-full p-3 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs focus:outline-none focus:ring-1 focus:ring-purple-500"></textarea>
                    </div>
                    <div class="space-y-1">
                        <Label for="add-doc-file-file" class="text-xs font-bold">Subir Documento (PDF, Word, etc.)</Label>
                        <Input 
                            type="file" 
                            id="add-doc-file-file" 
                            @change="(e: any) => docForm.file_file = e.target.files[0]" 
                            required 
                            class="rounded-xl text-xs h-10 cursor-pointer" 
                        />
                        <p v-if="docForm.errors.file_file" class="text-red-500 text-[10px]">{{ docForm.errors.file_file }}</p>
                    </div>
                    <div class="flex items-center space-x-3 py-2">
                        <input type="checkbox" v-model="docForm.is_active" id="add-doc-is-active" class="size-4 rounded border-neutral-300 cursor-pointer" />
                        <Label for="add-doc-is-active" class="text-xs font-bold cursor-pointer select-none">Publicar como activo en el sitio web</Label>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-neutral-100 dark:border-neutral-800">
                        <Button type="button" @click="isAddDocOpen = false" variant="outline" class="rounded-xl text-xs h-10 cursor-pointer">Cancelar</Button>
                        <Button type="submit" :disabled="docForm.processing" class="rounded-xl text-xs h-10 bg-purple-600 hover:bg-purple-700 text-white font-bold cursor-pointer">
                            {{ docForm.processing ? 'Guardando...' : 'Guardar Documento' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: EDITAR DOCUMENTO -->
        <div v-if="isEditDocOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 animate-in fade-in duration-200">
            <div class="w-full max-w-md bg-white dark:bg-neutral-900 rounded-[24px] border border-neutral-200 dark:border-neutral-800 shadow-2xl p-6 relative flex flex-col max-h-[90vh] overflow-y-auto">
                <button @click="isEditDocOpen = false" class="absolute top-4 right-4 p-2 hover:bg-neutral-100 dark:hover:bg-neutral-800 rounded-lg text-neutral-500 cursor-pointer">
                    <X class="size-5" />
                </button>
                <h3 class="text-lg font-black text-neutral-900 dark:text-white mb-1 flex items-center gap-1.5">
                    <Pencil class="size-5 text-purple-600 dark:text-purple-400" />
                    Editar Documento
                </h3>
                <form @submit.prevent="submitEditDoc" class="space-y-4 text-left mt-4">
                    <div class="space-y-1">
                        <Label for="edit-doc-title" class="text-xs">Título</Label>
                        <Input v-model="editDocForm.title" id="edit-doc-title" required class="rounded-xl text-xs h-10" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label for="edit-doc-code" class="text-xs">Código</Label>
                            <Input v-model="editDocForm.code" id="edit-doc-code" required class="rounded-xl text-xs h-10" />
                        </div>
                        <div class="space-y-1">
                            <Label class="text-xs">Categoría</Label>
                            <select v-model="editDocForm.category" class="w-full h-10 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-purple-500">
                                <option value="Directivas">Directivas</option>
                                <option value="Resoluciones">Resoluciones</option>
                                <option value="Reglamentos">Reglamentos</option>
                                <option value="Guías y Formatos">Guías y Formatos</option>
                            </select>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <Label for="edit-doc-date" class="text-xs">Fecha de Aprobación</Label>
                        <Input v-model="editDocForm.published_date" id="edit-doc-date" type="date" required class="rounded-xl text-xs h-10" />
                    </div>
                    <div class="space-y-1">
                        <Label for="edit-doc-description" class="text-xs">Descripción</Label>
                        <textarea v-model="editDocForm.description" id="edit-doc-description" rows="2" class="w-full p-3 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 text-xs focus:outline-none focus:ring-1 focus:ring-purple-500"></textarea>
                    </div>
                    <div class="space-y-1">
                        <Label for="edit-doc-file-file" class="text-xs font-bold">Subir Nuevo Documento (Opcional)</Label>
                        <Input 
                            type="file" 
                            id="edit-doc-file-file" 
                            @change="(e: any) => editDocForm.file_file = e.target.files[0]" 
                            class="rounded-xl text-xs h-10 cursor-pointer" 
                        />
                        <p v-if="editDocForm.file_path" class="text-[10px] text-neutral-400 font-mono mt-1 break-all">
                            Archivo actual: {{ editDocForm.file_path }}
                        </p>
                        <p v-if="editDocForm.errors.file_file" class="text-red-500 text-[10px]">{{ editDocForm.errors.file_file }}</p>
                    </div>
                    <div class="flex items-center space-x-3 py-2">
                        <input type="checkbox" v-model="editDocForm.is_active" id="edit-doc-is-active" class="size-4 rounded border-neutral-300 cursor-pointer" />
                        <Label for="edit-doc-is-active" class="text-xs font-bold cursor-pointer select-none">Publicado (visible en el sitio web)</Label>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-neutral-100 dark:border-neutral-800">
                        <Button type="button" @click="isEditDocOpen = false" variant="outline" class="rounded-xl text-xs h-10 cursor-pointer">Cancelar</Button>
                        <Button type="submit" :disabled="editDocForm.processing" class="rounded-xl text-xs h-10 bg-purple-600 hover:bg-purple-700 text-white font-bold cursor-pointer">
                            {{ editDocForm.processing ? 'Guardando...' : 'Guardar Cambios' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</template>
