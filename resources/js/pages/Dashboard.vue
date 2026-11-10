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
    Upload,
    Eye,
    EyeOff,
    Download,
    FileSpreadsheet,
    AlignCenter,
    AlignLeft,
    AlignRight,
    ArrowDown,
    ArrowLeft,
    ArrowRight,
    ArrowUp,
} from '@lucide/vue';
import { ref, computed } from 'vue';
import EventExclusiveHero from '@/components/EventExclusiveHero.vue';
import CertificateTab from '@/components/CertificateTab.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { dashboard } from '@/routes';
import { getExclusiveEventView } from '@/lib/exclusiveEventView';

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
    has_exclusive_page?: boolean;
    exclusive_documents?: any[];
    exclusive_page_content?: Record<string, any> | null;
    exclusive_page_settings?: Record<string, any> | null;
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
    flash?: { success?: string; error?: string; exclusive_event_id?: number };
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
const addEventComplete = computed(() => Boolean(eventForm.title.trim() && eventForm.type && eventForm.category && eventForm.event_date && eventForm.location.trim() && eventForm.organizer.trim() && eventForm.description.trim()));

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
// The crop area is 16:9, but the source image can have any aspect ratio.
// Keep the smallest useful zoom so the whole source image can be displayed.
const minimumZoom = ref(0.25);
const filePreviewUrls = new WeakMap<File, string>();

const getFilePreview = (file: File) => {
    if (!filePreviewUrls.has(file)) {
        filePreviewUrls.set(file, URL.createObjectURL(file));
    }

    return filePreviewUrls.get(file) as string;
};

const getMinimumZoom = () => {
    const container = dragContainerRef.value;
    const containerWidth = container?.clientWidth ?? 400;
    const containerHeight = container?.clientHeight ?? 225;
    const { width, height } = previewImageSize.value;

    if (!width || !height) {
        return 0.25;
    }

    const imageRatio = width / height;
    const containerRatio = containerWidth / containerHeight;
    const baseWidth = imageRatio > containerRatio ? containerHeight * imageRatio : containerWidth;
    const baseHeight = imageRatio > containerRatio ? containerHeight : containerWidth / imageRatio;

    return Math.min(1, containerWidth / baseWidth, containerHeight / baseHeight);
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
            minimumZoom.value = getMinimumZoom();
            zoom.value = minimumZoom.value;
            clampCropPosition();
        };
        preview.src = cropSource.value;
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
        // Keep letterboxed areas transparent instead of adding a black
        // background when the complete source image does not fill 16:9.
        ctx.clearRect(0, 0, targetWidth, targetHeight);
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

const submitAddEvent = (openExclusivePage = false) => {
    eventForm.transform((data) => ({
        ...data,
        registration_link: data.has_registration ? data.registration_link : null,
        open_exclusive_page: openExclusivePage,
    })).post('/admin/eventos', {
        preserveScroll: true,
        onSuccess: (page) => {
            isAddEventOpen.value = false;
            eventForm.reset();
            const eventId = (page.props.flash as { exclusive_event_id?: number })?.exclusive_event_id;
            if (openExclusivePage && eventId) openExclusivePageEditor(props.events.find((event) => event.id === eventId));
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
const editEventComplete = computed(() => Boolean(editEventForm.title.trim() && editEventForm.type && editEventForm.category && editEventForm.event_date && editEventForm.location.trim() && editEventForm.organizer.trim() && editEventForm.description.trim()));

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

    // Load the original image for editing. The cover is already a generated
    // 16:9 crop and must only be used for the public preview, not as the source
    // of a new crop.
    const currentImage = ev.image_path || ev.cover_image_path;

    if (currentImage) {
        loadCropPreviewFromUrl(currentImage);
    }
};

const submitEditEvent = (openExclusivePage = false) => {
    editEventForm.transform((data) => ({
        ...data,
        registration_link: data.has_registration ? data.registration_link : null,
        open_exclusive_page: openExclusivePage,
        _method: 'PUT'
    })).post(`/admin/eventos/${editingEventId.value}`, {
        preserveScroll: true,
        onSuccess: (page) => {
            isEditEventOpen.value = false;
            const eventId = (page.props.flash as { exclusive_event_id?: number })?.exclusive_event_id;
            if (openExclusivePage && eventId) openExclusivePageEditor(props.events.find((event) => event.id === eventId));
        },
    });
};

const isExclusivePageOpen = ref(false);
const exclusiveEventId = ref<number | null>(null);
const exclusiveEventTitle = ref('');
type ExclusiveLocation = { id: string; name: string; image: string; image_file: File | null; image_preview?: string; x: number; y: number; color: string; order?: number };
type ScheduleActivity = { id: string; subject: string; description: string; date: string; time: string };
type RegistrationFormConfig = { id: string; enabled: boolean; title: string; description: string; opens_at: string; closes_at: string; fields: any[] };
type ResultWinner = { id: string; name: string; image: string; image_file: File | null; image_preview?: string };
type ResultCategory = { id: string; name: string; winners: ResultWinner[] };
type ResultGalleryItem = { id: string; title: string; description: string; image: string; image_file: File | null; image_preview?: string };
type ExclusiveTab = 'principal' | 'documentos' | 'cronograma' | 'participantes' | 'ubicaciones' | 'resultados';
const exclusiveEditorTabs: { key: ExclusiveTab; label: string }[] = [
    { key: 'principal', label: 'Información principal' }, { key: 'documentos', label: 'Documentos' },
    { key: 'cronograma', label: 'Cronograma' }, { key: 'participantes', label: 'Registro' },
    { key: 'ubicaciones', label: 'Ubicaciones' }, { key: 'resultados', label: 'Resultados' },
];
const exclusiveEditorTab = ref<ExclusiveTab>('principal');
const exclusivePreviewMode = ref<'desktop' | 'mobile'>('desktop');
const exclusiveThemes = [
    { value: 'emerald', label: 'Esmeralda', accent: '#34d399', soft: '#a7f3d0' },
    { value: 'forest', label: 'Bosque', accent: '#4ade80', soft: '#bbf7d0' },
    { value: 'teal', label: 'Turquesa', accent: '#2dd4bf', soft: '#99f6e4' },
    { value: 'lime', label: 'Lima', accent: '#a3e635', soft: '#d9f99d' },
    { value: 'mint', label: 'Menta', accent: '#6ee7b7', soft: '#d1fae5' },
];
const exclusiveDocumentForm = useForm({ title: '', description: '', is_visible: true, file: null as File | null });
const exclusiveDocuments = ref<any[]>([]);
const editingExclusiveDocumentId = ref<number | null>(null);
const scheduleActivityDraft = ref({ subject: '', description: '', date: '', time: '' });
const registrationFormsDraft = ref<RegistrationFormConfig[]>([]);
const registrationSelectedId = ref('');
const registrationPanel = ref<'editor' | 'preview' | 'stats'>('editor');
const registrationStats = ref<any>(null);
const registrationStatsLoading = ref(false);
const registrationStatsError = ref('');
const selectedRegistrationForm = computed(() => registrationFormsDraft.value.find((form) => form.id === registrationSelectedId.value) || null);
const locationColorSwatches = ['#34d399', '#4ade80', '#2dd4bf', '#a3e635', '#6ee7b7'];
const locationPlacementMode = ref(false);
const selectedLocationId = ref<string | null>(null);
const localCroquisPreview = ref('');
const croquisImageElement = ref<HTMLImageElement | null>(null);
const croquisPreview = computed(() => localCroquisPreview.value || exclusivePageForm.sections.ubicaciones.croquis);
const exclusivePageForm = useForm({
    enabled: true,
    settings: {
        title: '', subtitle: '', event_date: '', time: '', location: '', about: '', theme: 'emerald',
        title_position: 'left', title_size: 76, title_offset_x: 0, title_offset_y: 0,
        desktop_cover: '', desktop_cover_zoom: 1, desktop_cover_x: 50, desktop_cover_y: 50,
        mobile_cover: '', mobile_cover_zoom: 1, mobile_cover_x: 50, mobile_cover_y: 50,
        desktop_cover_file: null as File | null,
    },
    sections: {
        documentos: { link: '' },
        cronograma: { link: '', activities: [] as ScheduleActivity[] },
        participantes: { link: '', form: null as RegistrationFormConfig | null, forms: [] as RegistrationFormConfig[] },
        ubicaciones: { link: '', croquis: '', croquis_file: null as File | null, locations: [] as ExclusiveLocation[] },
        resultados: { link: '', categories: [] as ResultCategory[], type: '' as '' | 'results_image' | 'table' | 'youtube' | 'gallery', results_image: '', results_image_file: null as File | null, results_image_preview: '', participants: [] as { id: string; name: string; score: string }[], youtube_url: '', gallery: [] as ResultGalleryItem[] },
    },
});
const openExclusivePageEditor = (event?: EventItem) => {
    if (!event) return;
    exclusiveEventId.value = event.id;
    exclusiveEventTitle.value = event.title;
    exclusiveDocuments.value = event.exclusive_documents || [];
    exclusiveEditorTab.value = 'principal';
    const settings = event.exclusive_page_settings || {};
    exclusivePageForm.enabled = true;
    exclusivePageForm.settings = {
        title: settings.title || event.title, subtitle: settings.subtitle || '',
        event_date: settings.event_date || (event.event_date ? event.event_date.slice(0, 10) : ''),
        time: settings.time ?? event.time ?? '', location: settings.location || event.location,
        about: settings.about || event.description, theme: settings.theme || 'emerald',
        title_position: settings.title_position || 'left', title_size: settings.title_size || 76,
        title_offset_x: settings.title_offset_x || 0, title_offset_y: settings.title_offset_y || 0,
        desktop_cover: settings.desktop_cover || event.cover_image_path || event.image_path || '',
        desktop_cover_zoom: Number(settings.desktop_cover_zoom || 1), desktop_cover_x: Number(settings.desktop_cover_x ?? 50),
        desktop_cover_y: Number(settings.desktop_cover_y ?? 50), mobile_cover: settings.desktop_cover || event.cover_image_path || event.image_path || '',
        mobile_cover_zoom: Number(settings.mobile_cover_zoom || 1), mobile_cover_x: Number(settings.mobile_cover_x ?? 50),
        mobile_cover_y: Number(settings.mobile_cover_y ?? 50), desktop_cover_file: null,
    };
    const content = event.exclusive_page_content || {};
    for (const key of ['documentos', 'cronograma', 'participantes', 'ubicaciones', 'resultados'] as const) {
        (exclusivePageForm.sections as any)[key].link = content[key]?.link || '';
    }
    exclusivePageForm.sections.cronograma.activities = content.cronograma?.activities || [];
    exclusivePageForm.sections.ubicaciones.croquis = content.ubicaciones?.croquis || '';
    exclusivePageForm.sections.ubicaciones.croquis_file = null;
    exclusivePageForm.sections.ubicaciones.locations = (content.ubicaciones?.locations || []).map((item: any) => ({
        ...item, x: Number(item.x ?? 50), y: Number(item.y ?? 50), color: item.color || locationColorSwatches[0], image_file: null, image_preview: '',
    }));
    const results = content.resultados || {};
    exclusivePageForm.sections.resultados.categories = (results.categories || []).map((category: any) => ({ ...category, winners: (category.winners || []).map((winner: any) => ({ ...winner, image_file: null, image_preview: '' })) }));
    exclusivePageForm.sections.resultados.type = results.type || '';
    exclusivePageForm.sections.resultados.results_image = results.results_image || '';
    exclusivePageForm.sections.resultados.results_image_file = null;
    exclusivePageForm.sections.resultados.results_image_preview = '';
    exclusivePageForm.sections.resultados.participants = results.participants || [];
    exclusivePageForm.sections.resultados.youtube_url = results.youtube_url || '';
    exclusivePageForm.sections.resultados.gallery = (results.gallery || []).map((item: any) => ({ ...item, image_file: null, image_preview: '' }));
    if (localCroquisPreview.value) URL.revokeObjectURL(localCroquisPreview.value);
    localCroquisPreview.value = '';
    locationPlacementMode.value = false;
    selectedLocationId.value = exclusivePageForm.sections.ubicaciones.locations[0]?.id || null;
    registrationFormsDraft.value = (content.participantes?.forms || (content.participantes?.form ? [{ id: 'default', ...content.participantes.form }] : [])).map((form: any) => ({ ...form, fields: (form.fields || []).map((field: any) => ({ ...field, options_text: (field.options || []).join('\n') })) }));
    exclusivePageForm.sections.participantes.forms = registrationFormsDraft.value;
    exclusivePageForm.sections.participantes.form = registrationFormsDraft.value[0] || null;
    registrationSelectedId.value = registrationFormsDraft.value[0]?.id || '';
    registrationPanel.value = 'editor';
    registrationStats.value = null;
    registrationStatsError.value = '';
    exclusivePageForm.clearErrors();
    exclusiveDocumentForm.reset();
    isExclusivePageOpen.value = true;
};
const saveExclusivePage = () => {
    if (!exclusiveEventId.value) return;
    exclusivePageForm.sections.participantes.forms = registrationFormsDraft.value;
    exclusivePageForm.transform((data) => ({ ...data, sections: { ...data.sections, participantes: { ...data.sections.participantes, forms: registrationFormsDraft.value.map((form: any) => ({ ...form, fields: form.fields.map(({ options_text, ...field }: any) => ({ ...field, options: ['select', 'radio', 'checkbox'].includes(field.type) ? String(options_text || '').split('\n').map((option: string) => option.trim()).filter(Boolean) : [] })) })) }, ubicaciones: { ...data.sections.ubicaciones, locations: data.sections.ubicaciones.locations.map(({ image_preview, ...location }: any) => location) }, resultados: { ...data.sections.resultados, results_image_preview: undefined, categories: data.sections.resultados.categories.map((category: any) => ({ ...category, winners: category.winners.map(({ image_preview, ...winner }: any) => winner) })), gallery: data.sections.resultados.gallery.map(({ image_preview, ...item }: any) => item) } }, _method: 'PUT' })).post(`/admin/eventos/${exclusiveEventId.value}/pagina-exclusiva`, {
        preserveScroll: true,
        onSuccess: () => { isExclusivePageOpen.value = false; if (localCroquisPreview.value) URL.revokeObjectURL(localCroquisPreview.value); localCroquisPreview.value = ''; },
    });
};
const makeResultId = () => `${Date.now().toString(36)}${Math.random().toString(36).slice(2, 7)}`;
const addResultCategory = () => exclusivePageForm.sections.resultados.categories.push({ id: makeResultId(), name: '', winners: [] });
const addResultWinner = (category: ResultCategory) => { if (category.winners.length < 3) category.winners.push({ id: makeResultId(), name: '', image: '', image_file: null, image_preview: '' }); };
const setResultWinnerImage = (winner: ResultWinner, event: Event) => { const file = (event.target as HTMLInputElement).files?.[0] || null; winner.image_file = file; winner.image_preview = file ? URL.createObjectURL(file) : ''; };
const setResultsImage = (event: Event) => { const file = (event.target as HTMLInputElement).files?.[0] || null; exclusivePageForm.sections.resultados.results_image_file = file; exclusivePageForm.sections.resultados.results_image_preview = file ? URL.createObjectURL(file) : ''; };
const addResultParticipant = () => exclusivePageForm.sections.resultados.participants.push({ id: makeResultId(), name: '', score: '' });
const addResultGalleryItem = () => exclusivePageForm.sections.resultados.gallery.push({ id: makeResultId(), title: '', description: '', image: '', image_file: null, image_preview: '' });
const setResultGalleryImage = (item: ResultGalleryItem, event: Event) => { const file = (event.target as HTMLInputElement).files?.[0] || null; item.image_file = file; item.image_preview = file ? URL.createObjectURL(file) : ''; };
const setCroquisFile = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0] || null;
    exclusivePageForm.sections.ubicaciones.croquis_file = file;
    if (localCroquisPreview.value) URL.revokeObjectURL(localCroquisPreview.value);
    localCroquisPreview.value = file ? URL.createObjectURL(file) : '';
    locationPlacementMode.value = false;
};
const setLocationImage = (location: ExclusiveLocation, event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0] || null;
    location.image_file = file;
    if (location.image_preview) URL.revokeObjectURL(location.image_preview);
    location.image_preview = file ? URL.createObjectURL(file) : '';
};
const startAddingLocation = () => {
    if (!croquisPreview.value) return;
    selectedLocationId.value = null;
    locationPlacementMode.value = true;
};
const handleLocationMapClick = (event: MouseEvent) => {
    if (!locationPlacementMode.value) return;
    const image = croquisImageElement.value;
    if (!image || !image.complete || !image.naturalWidth || !image.naturalHeight) {
        locationPlacementMode.value = false;
        return;
    }
    const rect = image.getBoundingClientRect();
    const x = (event.clientX - rect.left) / rect.width * 100;
    const y = (event.clientY - rect.top) / rect.height * 100;
    if (x < 0 || x > 100 || y < 0 || y > 100) {
        locationPlacementMode.value = false;
        return;
    }
    const location: ExclusiveLocation = {
        id: `${Date.now().toString(36)}${Math.random().toString(36).slice(2, 6)}`,
        name: '', image: '', image_file: null, image_preview: '', x: Number(x.toFixed(2)), y: Number(y.toFixed(2)), color: locationColorSwatches[0],
    };
    exclusivePageForm.sections.ubicaciones.locations.push(location);
    selectedLocationId.value = location.id;
    locationPlacementMode.value = false;
};
const removeLocation = (id: string) => {
    const locations = exclusivePageForm.sections.ubicaciones.locations;
    const index = locations.findIndex((location) => location.id === id);
    if (index >= 0) {
        if (locations[index].image_preview) URL.revokeObjectURL(locations[index].image_preview!);
        locations.splice(index, 1);
    }
    if (selectedLocationId.value === id) selectedLocationId.value = locations[0]?.id || null;
};
const addRegistrationField = (form: RegistrationFormConfig) => {
    form.fields.push({ id: `${Date.now().toString(36)}${Math.random().toString(36).slice(2, 6)}`, label: 'Nueva pregunta', type: 'text', required: false, options: [], options_text: '' });
};
const addRegistrationForm = () => {
    const id = `${Date.now().toString(36)}${Math.random().toString(36).slice(2, 6)}`;
    registrationFormsDraft.value.push({ id, enabled: true, title: 'Inscripción al evento', description: '', opens_at: '', closes_at: '', fields: [
        { id: 'name', label: 'Nombre completo', type: 'text', required: true },
    ] });
    registrationSelectedId.value = id;
    registrationPanel.value = 'editor';
};
const loadRegistrationStats = async () => {
    if (!exclusiveEventId.value || !registrationSelectedId.value) return;
    registrationStatsLoading.value = true; registrationStatsError.value = '';
    try {
        const response = await fetch(`/admin/eventos/${exclusiveEventId.value}/pagina-exclusiva/registro/estadisticas?form_id=${encodeURIComponent(registrationSelectedId.value)}`, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        if (!response.ok) throw new Error('No se pudieron cargar las estadísticas.');
        registrationStats.value = await response.json(); registrationPanel.value = 'stats';
    } catch (error) { registrationStatsError.value = error instanceof Error ? error.message : 'Ocurrió un error al cargar las estadísticas.'; }
    finally { registrationStatsLoading.value = false; }
};
const formatRegistrationAnswer = (answer: string | string[] | undefined) => Array.isArray(answer) ? answer.join(', ') : answer || '—';
const addActivity = () => {
    const draft = scheduleActivityDraft.value;
    if (!draft.subject.trim() || !draft.date) return;
    exclusivePageForm.sections.cronograma.activities.push({ ...draft, id: `${Date.now().toString(36)}${Math.random().toString(36).slice(2, 7)}` });
    scheduleActivityDraft.value = { subject: '', description: '', date: '', time: '' };
};
const removeActivity = (id: string) => { exclusivePageForm.sections.cronograma.activities = exclusivePageForm.sections.cronograma.activities.filter((item) => item.id !== id); };
const getExclusiveTheme = (theme: string) => exclusiveThemes.find((item) => item.value === theme) || exclusiveThemes[0];
const moveExclusiveTitle = (direction: 'left' | 'right' | 'up' | 'down') => {
    const settings = exclusivePageForm.settings;
    if (direction === 'left') settings.title_offset_x = Math.max(-120, settings.title_offset_x - 8);
    if (direction === 'right') settings.title_offset_x = Math.min(120, settings.title_offset_x + 8);
    if (direction === 'up') settings.title_offset_y = Math.max(-120, settings.title_offset_y - 8);
    if (direction === 'down') settings.title_offset_y = Math.min(120, settings.title_offset_y + 8);
};
const moveExclusiveCover = (device: 'desktop' | 'mobile', direction: 'left' | 'right' | 'up' | 'down') => {
    const xKey = device === 'desktop' ? 'desktop_cover_x' : 'mobile_cover_x';
    const yKey = device === 'desktop' ? 'desktop_cover_y' : 'mobile_cover_y';
    if (direction === 'left') exclusivePageForm.settings[xKey] = Math.max(0, exclusivePageForm.settings[xKey] - 5);
    if (direction === 'right') exclusivePageForm.settings[xKey] = Math.min(100, exclusivePageForm.settings[xKey] + 5);
    if (direction === 'up') exclusivePageForm.settings[yKey] = Math.max(0, exclusivePageForm.settings[yKey] - 5);
    if (direction === 'down') exclusivePageForm.settings[yKey] = Math.min(100, exclusivePageForm.settings[yKey] + 5);
};
const exclusivePreviewView = computed(() => {
    const form = exclusivePageForm.settings;
    const cover = form.desktop_cover_file ? getFilePreview(form.desktop_cover_file) : form.desktop_cover;
    return getExclusiveEventView({ ...(props.events.find((item) => item.id === exclusiveEventId.value) || {}), exclusive_page_settings: { ...form, desktop_cover: cover, mobile_cover: cover } });
});
const editExclusiveDocument = (document: any) => {
    editingExclusiveDocumentId.value = document.id;
    exclusiveDocumentForm.title = document.title;
    exclusiveDocumentForm.description = document.description || '';
    exclusiveDocumentForm.is_visible = document.is_visible;
    exclusiveDocumentForm.file = null;
};
const saveExclusiveDocument = () => {
    if (!exclusiveEventId.value) return;
    const id = editingExclusiveDocumentId.value;
    exclusiveDocumentForm.transform((data) => id ? { ...data, _method: 'PUT' } : data).post(id ? `/admin/eventos/${exclusiveEventId.value}/pagina-exclusiva/documentos/${id}` : `/admin/eventos/${exclusiveEventId.value}/pagina-exclusiva/documentos`, {
        preserveScroll: true,
        onSuccess: (page) => {
            const events = (page.props.events || []) as EventItem[];
            exclusiveDocuments.value = events.find((item) => item.id === exclusiveEventId.value)?.exclusive_documents || exclusiveDocuments.value;
            exclusiveDocumentForm.reset(); editingExclusiveDocumentId.value = null;
        },
    });
};
const deleteExclusiveDocument = (document: any) => {
    if (!exclusiveEventId.value || !confirm(`¿Eliminar “${document.title}”?`)) return;
    router.delete(`/admin/eventos/${exclusiveEventId.value}/pagina-exclusiva/documentos/${document.id}`, { preserveScroll: true });
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
                                            <span class="block text-[9px] font-bold" :class="item.has_exclusive_page ? 'text-emerald-600' : 'text-neutral-400'">{{ item.has_exclusive_page ? 'Página exclusiva activa' : 'Sin página exclusiva' }}</span>
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
                                        <button
                                            @click="openExclusivePageEditor(item)"
                                            :title="item.has_exclusive_page ? 'Configurar página exclusiva' : 'Crear página exclusiva'"
                                            class="p-2 rounded-lg hover:bg-amber-500/10 text-amber-600 cursor-pointer"
                                        >
                                            <FileText class="size-4" />
                                        </button>
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
                                class="aspect-video w-full relative overflow-hidden bg-neutral-150 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-2xl cursor-move select-none"
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
                                    :min="minimumZoom"
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
                        <Button type="button" :disabled="!addEventComplete || eventForm.processing" @click="submitAddEvent(true)" variant="outline" class="rounded-xl text-xs h-10 cursor-pointer">
                            Crear página exclusiva
                        </Button>
                        <Button type="submit" :disabled="!addEventComplete || eventForm.processing" class="rounded-xl text-xs h-10 bg-indigo-600 hover:bg-indigo-700 text-white font-bold cursor-pointer">
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
                                class="aspect-video w-full relative overflow-hidden bg-neutral-150 dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-2xl cursor-move select-none"
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
                                    :min="minimumZoom"
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
                        <Button type="button" :disabled="!editEventComplete || editEventForm.processing" @click="submitEditEvent(true)" variant="outline" class="rounded-xl text-xs h-10 cursor-pointer">
                            {{ editingEventId && props.events.find((event) => event.id === editingEventId)?.has_exclusive_page ? 'Configurar página exclusiva' : 'Crear página exclusiva' }}
                        </Button>
                        <Button type="submit" :disabled="!editEventComplete || editEventForm.processing" class="rounded-xl text-xs h-10 bg-indigo-600 hover:bg-indigo-700 text-white font-bold cursor-pointer">
                            {{ editEventForm.processing ? 'Guardando...' : 'Guardar Cambios' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: CONFIGURAR PÁGINA EXCLUSIVA -->
        <div v-if="isExclusivePageOpen" class="fixed inset-0 z-[60] flex items-center justify-center overflow-x-hidden bg-black/60 backdrop-blur-xs p-0 sm:p-5">
            <div class="flex h-[100dvh] max-h-[100dvh] w-full max-w-[1500px] min-w-0 flex-col overflow-hidden rounded-none border border-neutral-200 bg-white shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 sm:h-[95vh] sm:max-h-[95vh] sm:rounded-[24px]">
                <div class="flex shrink-0 items-start justify-between gap-4 border-b border-neutral-100 px-5 py-4 dark:border-neutral-800 sm:px-7">
                    <div><h3 class="text-lg font-black text-neutral-900 dark:text-white">Configurar página exclusiva</h3><p class="mt-1 text-xs text-neutral-500">{{ exclusiveEventTitle }} · Sube el croquis y configura sus puntos de ubicación.</p></div>
                    <button type="button" @click="isExclusivePageOpen = false" class="rounded-lg p-2 text-neutral-500 hover:bg-neutral-100 dark:hover:bg-neutral-800"><X class="size-5" /></button>
                </div>
                <form @submit.prevent="saveExclusivePage" class="grid min-h-0 flex-1 grid-cols-1 overflow-y-auto lg:overflow-hidden lg:grid-cols-[minmax(360px,0.85fr)_minmax(480px,1.15fr)] lg:grid-rows-[minmax(0,1fr)_auto]">
                    <div class="min-w-0 space-y-5 p-4 sm:p-7 lg:col-start-1 lg:row-start-1 lg:min-h-0 lg:overflow-y-auto">
                        <label class="flex items-center gap-2 rounded-xl bg-neutral-50 p-3 text-xs font-bold dark:bg-neutral-950"><input v-model="exclusivePageForm.enabled" type="checkbox" class="size-4 rounded" />Página exclusiva activa y visible al público</label>
                        <nav class="flex min-w-0 gap-1 overflow-x-auto rounded-xl border border-neutral-200 bg-neutral-50 p-1 dark:border-neutral-800 dark:bg-neutral-950" aria-label="Secciones de configuración"><button v-for="tab in exclusiveEditorTabs" :key="tab.key" type="button" @click="exclusiveEditorTab = tab.key" class="shrink-0 rounded-lg px-3 py-2 text-xs font-bold" :class="exclusiveEditorTab === tab.key ? 'bg-white text-emerald-700 shadow-sm dark:bg-neutral-800 dark:text-emerald-300' : 'text-neutral-500'">{{ tab.label }}</button></nav>
                        <section v-if="exclusiveEditorTab === 'principal'" class="space-y-4 rounded-2xl border border-neutral-200 p-4 dark:border-neutral-800"><div><h4 class="text-sm font-extrabold">Información principal</h4><p class="text-[11px] text-neutral-500">Personaliza la información y la portada del evento.</p></div><div class="space-y-1.5"><Label class="text-xs">Nombre del evento</Label><Input v-model="exclusivePageForm.settings.title" class="h-9 rounded-lg text-xs" /></div><div class="space-y-1.5"><Label class="text-xs">Subtítulo</Label><Input v-model="exclusivePageForm.settings.subtitle" class="h-9 rounded-lg text-xs" /></div><div class="grid grid-cols-2 gap-3"><div><Label class="text-xs">Fecha</Label><Input v-model="exclusivePageForm.settings.event_date" type="date" class="h-9 rounded-lg text-xs" /></div><div><Label class="text-xs">Hora</Label><Input v-model="exclusivePageForm.settings.time" class="h-9 rounded-lg text-xs" /></div></div><div><Label class="text-xs">Ubicación del evento</Label><Input v-model="exclusivePageForm.settings.location" class="h-9 rounded-lg text-xs" /></div><div><Label class="text-xs">Sobre el evento</Label><textarea v-model="exclusivePageForm.settings.about" rows="4" class="w-full rounded-lg border border-neutral-300 bg-white p-3 text-xs dark:border-neutral-700 dark:bg-neutral-950"></textarea></div><div><Label class="text-xs">Imagen de portada</Label><input type="file" accept="image/jpeg,image/png,image/webp" @change="(event) => exclusivePageForm.settings.desktop_cover_file = (event.target as HTMLInputElement).files?.[0] || null" class="mt-1 block w-full text-xs" /></div><div><Label class="text-xs">Color</Label><div class="mt-2 flex gap-2"><button v-for="theme in exclusiveThemes" :key="theme.value" type="button" @click="exclusivePageForm.settings.theme = theme.value" class="size-8 rounded-full" :style="{ backgroundColor: theme.accent }"></button></div></div></section>
                        <section v-else-if="exclusiveEditorTab === 'documentos'" class="space-y-4 rounded-2xl border border-neutral-200 p-4 dark:border-neutral-800"><div><h4 class="text-sm font-extrabold">Documentos del evento</h4><p class="text-[11px] text-neutral-500">Agrega recursos descargables para los participantes.</p></div><Input v-model="exclusiveDocumentForm.title" placeholder="Nombre del documento" class="h-9 rounded-lg text-xs" /><Input v-model="exclusiveDocumentForm.description" placeholder="Descripción (opcional)" class="h-9 rounded-lg text-xs" /><input type="file" @change="(event) => exclusiveDocumentForm.file = (event.target as HTMLInputElement).files?.[0] || null" class="block w-full text-xs" /><label class="flex items-center gap-2 text-xs"><input v-model="exclusiveDocumentForm.is_visible" type="checkbox" />Visible al público</label><div class="flex gap-2"><Button type="button" :disabled="exclusiveDocumentForm.processing || (!exclusiveDocumentForm.file && !editingExclusiveDocumentId)" @click="saveExclusiveDocument" class="rounded-lg bg-emerald-700 text-xs text-white">{{ editingExclusiveDocumentId ? 'Guardar cambios' : 'Agregar documento' }}</Button><Button v-if="editingExclusiveDocumentId" type="button" @click="exclusiveDocumentForm.reset(); editingExclusiveDocumentId = null" variant="outline" class="rounded-lg text-xs">Cancelar</Button></div><article v-for="document in exclusiveDocuments" :key="document.id" class="flex items-center justify-between gap-2 rounded-lg border p-3 text-xs"><span class="truncate font-bold">{{ document.title }} · {{ document.original_name }}</span><div class="flex gap-2"><button type="button" @click="editExclusiveDocument(document)" class="text-emerald-700">Editar</button><button type="button" @click="deleteExclusiveDocument(document)" class="text-red-600">Eliminar</button></div></article><p v-if="exclusivePageForm.sections.documentos.link" class="text-[10px] text-neutral-500">Enlace guardado: {{ exclusivePageForm.sections.documentos.link }}</p><Input v-model="exclusivePageForm.sections.documentos.link" type="url" placeholder="Enlace relacionado (opcional)" class="h-9 rounded-lg text-xs" /></section>
                        <section v-else-if="exclusiveEditorTab === 'cronograma'" class="space-y-4 rounded-2xl border border-neutral-200 p-4 dark:border-neutral-800"><div><h4 class="text-sm font-extrabold">Cronograma del evento</h4><p class="text-[11px] text-neutral-500">Programa, fechas y horarios.</p></div><Input v-model="scheduleActivityDraft.subject" placeholder="Actividad" class="h-9 rounded-lg text-xs" /><textarea v-model="scheduleActivityDraft.description" placeholder="Descripción" rows="2" class="w-full rounded-lg border p-2 text-xs dark:bg-neutral-950"></textarea><div class="grid grid-cols-2 gap-2"><Input v-model="scheduleActivityDraft.date" type="date" class="h-9 rounded-lg text-xs" /><Input v-model="scheduleActivityDraft.time" type="time" class="h-9 rounded-lg text-xs" /></div><Button type="button" @click="addActivity" class="rounded-lg bg-emerald-700 text-xs text-white">Agregar actividad</Button><article v-for="activity in exclusivePageForm.sections.cronograma.activities" :key="activity.id" class="flex justify-between gap-2 rounded-lg border p-3 text-xs"><span>{{ activity.subject }} · {{ activity.date }} {{ activity.time }}</span><button type="button" @click="removeActivity(activity.id)" class="text-red-600">Quitar</button></article><Input v-model="exclusivePageForm.sections.cronograma.link" type="url" placeholder="Enlace opcional" class="h-9 rounded-lg text-xs" /></section>
                        <section v-else-if="exclusiveEditorTab === 'participantes'" class="space-y-4 rounded-2xl border border-neutral-200 p-4 dark:border-neutral-800"><div><h4 class="text-sm font-extrabold">Registro de participantes</h4><p class="text-[11px] text-neutral-500">Conserva la configuración de inscripción de este evento.</p></div><div v-for="(form, index) in registrationFormsDraft" :key="form.id" @click="registrationSelectedId = form.id" class="space-y-2 rounded-lg border p-3 text-xs" :class="registrationSelectedId === form.id ? 'border-emerald-500 bg-emerald-50/40' : ''"><div class="flex items-center gap-2"><Input v-model="form.title" placeholder="Título del formulario" class="h-9 rounded-lg text-xs" /><button type="button" @click="form.enabled = !form.enabled" class="shrink-0 rounded border px-2 py-1">{{ form.enabled ? 'Publicado' : 'Borrador' }}</button><button type="button" @click="registrationFormsDraft.splice(index, 1); if (registrationSelectedId === form.id) registrationSelectedId = registrationFormsDraft[0]?.id || ''" class="text-red-600">Quitar</button></div><textarea v-model="form.description" placeholder="Descripción" rows="2" class="w-full rounded-lg border p-2 text-xs dark:bg-neutral-950"></textarea><div class="grid grid-cols-2 gap-2"><label class="text-[10px]">Abre<Input v-model="form.opens_at" type="datetime-local" class="h-8 text-xs" /></label><label class="text-[10px]">Cierra<Input v-model="form.closes_at" type="datetime-local" class="h-8 text-xs" /></label></div><div v-for="field in form.fields" :key="field.id" class="space-y-1 rounded border p-2"><div class="flex gap-2"><Input v-model="field.label" placeholder="Pregunta" class="h-8 rounded text-xs" /><select v-model="field.type" class="rounded border px-2 text-xs"><option value="text">Texto</option><option value="email">Correo</option><option value="textarea">Texto largo</option><option value="select">Lista</option><option value="radio">Opción única</option><option value="checkbox">Casillas</option></select></div><textarea v-if="['select','radio','checkbox'].includes(field.type)" v-model="field.options_text" rows="2" placeholder="Una opción por línea" class="w-full rounded border p-2 text-xs"></textarea></div><button type="button" @click="addRegistrationField(form)" class="text-emerald-700">+ Agregar pregunta</button></div><Button type="button" @click="addRegistrationForm" class="rounded-lg bg-emerald-700 text-xs text-white">Crear formulario</Button><Input v-model="exclusivePageForm.sections.participantes.link" type="url" placeholder="Enlace de inscripción (opcional)" class="h-9 rounded-lg text-xs" /></section>
                        <section v-else-if="exclusiveEditorTab === 'ubicaciones'" class="space-y-4 rounded-2xl border border-neutral-200 p-4 dark:border-neutral-800">
                            <div><h4 class="text-sm font-extrabold">Ubicaciones en el croquis</h4><p class="text-[11px] text-neutral-500">Primero carga el croquis. Después selecciona “Agregar ubicación” y marca el punto exacto en la vista previa.</p></div>
                            <div class="space-y-2 rounded-xl bg-neutral-50 p-3 dark:bg-neutral-950"><Label class="text-xs">Croquis obligatorio</Label><input type="file" accept="image/jpeg,image/png,image/webp" @change="setCroquisFile" class="block w-full text-xs text-neutral-500" /><p v-if="!croquisPreview" class="text-xs font-semibold text-amber-700">Carga un croquis para continuar.</p><p v-else class="text-[10px] text-emerald-700">Croquis cargado y visible en la vista previa.</p></div>
                            <div class="flex items-center justify-between gap-2"><h5 class="text-xs font-extrabold">Ubicaciones ({{ exclusivePageForm.sections.ubicaciones.locations.length }})</h5><Button type="button" :disabled="!croquisPreview" @click="startAddingLocation" class="rounded-lg bg-emerald-700 text-xs font-bold text-white disabled:opacity-40"><Plus class="mr-1 size-3.5" />Agregar ubicación</Button></div>
                            <p v-if="locationPlacementMode" class="rounded-lg border border-emerald-300 bg-emerald-50 p-2 text-xs font-semibold text-emerald-800">Haz clic sobre el croquis de la vista previa. Si haces clic fuera del mapa, se cancelará.</p>
                            <article v-for="(location, index) in exclusivePageForm.sections.ubicaciones.locations" :key="location.id" @click="selectedLocationId = location.id; locationPlacementMode = false" class="space-y-3 rounded-xl border p-3 dark:border-neutral-800" :class="selectedLocationId === location.id ? 'border-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/20' : 'border-neutral-200'"><div class="flex items-center justify-between gap-2"><button type="button" class="min-w-0 truncate text-left text-xs font-bold">{{ index + 1 }}. {{ location.name || 'Nueva ubicación' }} · {{ location.x }}%, {{ location.y }}%</button><button type="button" @click.stop="removeLocation(location.id)" class="shrink-0 rounded border border-red-200 px-2 py-1 text-xs text-red-600">Quitar</button></div><Input v-model="location.name" maxlength="160" placeholder="Nombre de la ubicación" class="h-9 rounded-lg text-xs" /><div><Label class="text-xs">Imagen de la ubicación</Label><input type="file" accept="image/jpeg,image/png,image/webp" @change="setLocationImage(location, $event)" class="mt-1 block w-full text-xs text-neutral-500" /><img v-if="location.image_preview || location.image" :src="location.image_preview || location.image" :alt="location.name" class="mt-2 size-20 rounded-full border-4 object-cover" :style="{ borderColor: location.color }" /></div><div><Label class="text-xs">Color del punto</Label><div class="mt-2 flex flex-wrap items-center gap-2"><button v-for="color in locationColorSwatches" :key="color" type="button" :aria-label="`Elegir color ${color}`" @click="location.color = color" class="size-7 rounded-full border-2" :style="{ backgroundColor: color, borderColor: location.color === color ? '#111827' : 'transparent' }"></button><label class="grid size-8 cursor-pointer place-items-center overflow-hidden rounded-full border text-xs" title="Elegir cualquier color"><input v-model="location.color" type="color" class="h-10 w-10 cursor-pointer" aria-label="Elegir cualquier color" /></label></div></div></article>
                        </section>
                        <section v-else class="space-y-5 rounded-2xl border border-neutral-200 p-4 dark:border-neutral-800">
                            <div><h4 class="text-sm font-extrabold">Resultados</h4><p class="text-[11px] text-neutral-500">Configura las categorías premiadas y elige un único contenido para la segunda sección.</p></div>
                            <div class="space-y-3 rounded-xl border p-3"><div class="flex items-center justify-between"><div><h5 class="text-xs font-bold">Ganadores por categoría</h5><p class="text-[10px] text-neutral-500">Cada categoría admite hasta tres ganadores.</p></div><Button type="button" @click="addResultCategory" class="rounded-lg bg-emerald-700 text-xs text-white">Agregar categoría</Button></div>
                                <article v-for="(category, categoryIndex) in exclusivePageForm.sections.resultados.categories" :key="category.id" class="space-y-3 rounded-lg border p-3"><div class="flex gap-2"><Input v-model="category.name" maxlength="160" :placeholder="`Nombre de categoría ${categoryIndex + 1}`" class="h-9 text-xs"/><button type="button" @click="exclusivePageForm.sections.resultados.categories.splice(categoryIndex, 1)" class="text-xs text-red-600">Quitar</button></div>
                                    <div v-for="(winner, winnerIndex) in category.winners" :key="winner.id" class="grid gap-2 rounded-md bg-neutral-50 p-2 dark:bg-neutral-950 sm:grid-cols-2"><div class="flex items-center gap-2"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-amber-100 text-xs font-black text-amber-800">{{ winnerIndex + 1 }}</span><Input v-model="winner.name" maxlength="160" placeholder="Nombre del ganador" class="h-8 text-xs"/><button type="button" @click="category.winners.splice(winnerIndex, 1)" class="text-xs text-red-600">Quitar</button></div><div><input type="file" accept="image/jpeg,image/png,image/webp" @change="setResultWinnerImage(winner, $event)" class="w-full text-[10px]"/><img v-if="winner.image_preview || winner.image" :src="winner.image_preview || winner.image" :alt="winner.name" class="mt-2 h-20 w-full rounded object-cover"/></div></div>
                                    <button v-if="category.winners.length < 3" type="button" @click="addResultWinner(category)" class="text-xs font-bold text-emerald-700">+ Agregar ganador ({{ category.winners.length }}/3)</button>
                                </article><p v-if="!exclusivePageForm.sections.resultados.categories.length" class="text-xs text-neutral-500">Aún no hay categorías.</p>
                            </div>
                            <div class="space-y-3 rounded-xl border p-3"><Label class="text-xs">Contenido de la segunda sección</Label><select v-model="exclusivePageForm.sections.resultados.type" class="h-9 w-full rounded-lg border bg-transparent px-3 text-xs"><option value="">Sin contenido</option><option value="results_image">Imagen de resultados</option><option value="table">Tabla de puntajes</option><option value="youtube">Video de YouTube</option><option value="gallery">Galería de imágenes</option></select>
                                <div v-if="exclusivePageForm.sections.resultados.type === 'results_image'" class="space-y-2"><Label class="text-xs">Imagen prediseñada de resultados</Label><input type="file" accept="image/jpeg,image/png,image/webp" @change="setResultsImage" class="w-full text-xs"/><img v-if="exclusivePageForm.sections.resultados.results_image_preview || exclusivePageForm.sections.resultados.results_image" :src="exclusivePageForm.sections.resultados.results_image_preview || exclusivePageForm.sections.resultados.results_image" alt="Vista previa de resultados" class="max-h-64 rounded-lg object-contain"/></div>
                                <div v-else-if="exclusivePageForm.sections.resultados.type === 'table'" class="space-y-2"><div v-for="(participant, index) in exclusivePageForm.sections.resultados.participants" :key="participant.id" class="grid grid-cols-[1fr_120px_auto] gap-2"><Input v-model="participant.name" maxlength="160" placeholder="Nombre del conjunto" class="h-8 text-xs"/><Input v-model="participant.score" maxlength="80" placeholder="Puntaje" class="h-8 text-xs"/><button type="button" @click="exclusivePageForm.sections.resultados.participants.splice(index, 1)" class="text-xs text-red-600">Quitar</button></div><Button type="button" @click="addResultParticipant" class="rounded-lg border text-xs">Agregar participante</Button></div>
                                <div v-else-if="exclusivePageForm.sections.resultados.type === 'youtube'"><Label class="text-xs">Enlace del video de YouTube</Label><Input v-model="exclusivePageForm.sections.resultados.youtube_url" type="url" placeholder="https://www.youtube.com/watch?v=..." class="h-9 rounded-lg text-xs"/></div>
                                <div v-else-if="exclusivePageForm.sections.resultados.type === 'gallery'" class="space-y-2"><div class="flex items-center justify-between"><Label class="text-xs">Imágenes del evento</Label><Button type="button" @click="addResultGalleryItem" class="rounded-lg border text-xs">Agregar imagen</Button></div><article v-for="(item, index) in exclusivePageForm.sections.resultados.gallery" :key="item.id" class="space-y-2 rounded-lg border p-2"><div class="flex justify-between"><span class="text-xs font-bold">Imagen {{ index + 1 }}</span><button type="button" @click="exclusivePageForm.sections.resultados.gallery.splice(index, 1)" class="text-xs text-red-600">Quitar</button></div><input type="file" accept="image/jpeg,image/png,image/webp" @change="setResultGalleryImage(item, $event)" class="w-full text-xs"/><Input v-model="item.title" maxlength="160" placeholder="Título" class="h-8 text-xs"/><textarea v-model="item.description" maxlength="2000" rows="2" placeholder="Descripción" class="w-full rounded border p-2 text-xs dark:bg-neutral-950"></textarea><img v-if="item.image_preview || item.image" :src="item.image_preview || item.image" :alt="item.title" class="h-24 rounded object-cover"/></article></div>
                            </div>
                        </section>
                    </div>
                    <aside class="min-w-0 space-y-5 border-t border-neutral-200 bg-neutral-50/70 p-4 dark:border-neutral-800 dark:bg-neutral-950/30 sm:p-7 lg:col-start-2 lg:row-start-1 lg:min-h-0 lg:overflow-y-auto lg:border-l lg:border-t-0">
                        <section v-if="exclusiveEditorTab === 'ubicaciones'" class="space-y-3">
                            <div><h4 class="text-sm font-extrabold text-neutral-900 dark:text-white">Vista previa del croquis</h4><p class="text-[11px] text-neutral-500">El mismo croquis se usa en escritorio y celular.</p></div>
                            <div class="relative flex h-[min(68vh,720px)] min-h-[320px] w-full items-center justify-center overflow-hidden rounded-2xl border border-neutral-200 bg-neutral-200 p-3 dark:border-neutral-700 dark:bg-neutral-900" :class="locationPlacementMode ? 'cursor-crosshair ring-2 ring-emerald-500' : ''" @click="handleLocationMapClick">
                                <div v-if="croquisPreview" class="relative inline-flex max-h-full max-w-full items-center justify-center">
                                    <img ref="croquisImageElement" :src="croquisPreview" alt="Croquis del evento" class="block max-h-[calc(68vh_-_2rem)] max-w-full rounded-lg object-contain" />
                                    <button v-for="location in exclusivePageForm.sections.ubicaciones.locations" :key="location.id" type="button" :aria-label="location.name || 'Ubicación sin nombre'" :aria-expanded="selectedLocationId === location.id" @click.stop="selectedLocationId = location.id; locationPlacementMode = false" class="group absolute z-10 grid size-4 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full border-[3px] border-white shadow-lg" :style="{ left: `${location.x}%`, top: `${location.y}%`, backgroundColor: location.color }"><span class="absolute bottom-full left-1/2 mb-1 grid size-14 -translate-x-1/2 place-items-center rounded-full border-[3px] bg-white shadow-lg transition-transform group-hover:scale-110" :style="{ borderColor: location.color }"><img v-if="location.image_preview || location.image" :src="location.image_preview || location.image" :alt="location.name" class="size-full rounded-full object-cover" /><span v-else class="text-xl font-black text-white">{{ location.name.slice(0, 1) || '•' }}</span></span><span class="pointer-events-none absolute bottom-full left-1/2 mb-16 -translate-x-1/2 whitespace-nowrap rounded-md px-2 py-1 text-[10px] font-bold text-white shadow transition-opacity group-hover:opacity-100" :class="selectedLocationId === location.id ? 'opacity-100' : 'opacity-0'" :style="{ backgroundColor: location.color }">{{ location.name || 'Nueva ubicación' }}</span></button>
                                </div>
                                <div v-else class="max-w-xs text-center text-sm text-neutral-500">Carga el croquis desde el panel izquierdo para verlo aquí y agregar ubicaciones.</div>
                                <p v-if="locationPlacementMode" class="pointer-events-none absolute bottom-3 left-1/2 -translate-x-1/2 rounded-full bg-emerald-700 px-4 py-2 text-xs font-bold text-white shadow-lg">Haz clic en el croquis para colocar el punto</p>
                            </div>
                        </section>
                        <section v-else-if="exclusiveEditorTab !== 'participantes'" class="space-y-3" :class="exclusivePreviewMode === 'mobile' ? 'grid w-full max-w-full grid-cols-[312px_240px] gap-3 overflow-x-auto' : ''">
                            <div class="flex flex-wrap items-center justify-between gap-2" :class="exclusivePreviewMode === 'mobile' ? 'col-span-2' : ''"><div><h4 class="text-sm font-extrabold text-neutral-900 dark:text-white">Vista previa</h4><p class="text-[11px] text-neutral-500">Se actualiza mientras editas.</p></div><div class="inline-flex rounded-lg border border-neutral-200 bg-white p-1 dark:border-neutral-700 dark:bg-neutral-900"><button type="button" @click="exclusivePreviewMode = 'desktop'" class="rounded-md px-3 py-1.5 text-[11px] font-bold" :class="exclusivePreviewMode === 'desktop' ? 'bg-indigo-600 text-white' : 'text-neutral-500'">Escritorio</button><button type="button" @click="exclusivePreviewMode = 'mobile'" class="rounded-md px-3 py-1.5 text-[11px] font-bold" :class="exclusivePreviewMode === 'mobile' ? 'bg-indigo-600 text-white' : 'text-neutral-500'">Celular</button></div></div>
                            <div class="flex w-full min-w-0 flex-col items-center gap-3 rounded-2xl bg-neutral-200 p-3 dark:bg-neutral-900 sm:p-4" :class="exclusivePreviewMode === 'mobile' ? 'xl:flex-row xl:items-start' : ''"><div class="flex w-full min-w-0 justify-center overflow-hidden" :class="exclusivePreviewMode === 'mobile' ? 'xl:w-[312px] xl:flex-none' : 'xl:flex-1'"><div v-if="exclusivePreviewMode === 'desktop'" class="aspect-video w-full max-w-[480px] overflow-hidden"><EventExclusiveHero :view="exclusivePreviewView" preview-mode="desktop" /></div><div v-else class="h-[500px] w-[312px] overflow-hidden"><EventExclusiveHero :view="exclusivePreviewView" preview-mode="mobile" /></div></div></div>
                            <div v-if="exclusiveEditorTab === 'principal'" class="grid gap-3 rounded-xl border border-neutral-200 bg-white p-3 dark:border-neutral-800 dark:bg-neutral-900 sm:gap-4 sm:p-4" :class="exclusivePreviewMode === 'mobile' ? 'grid-cols-1' : 'grid-cols-2'">
                                <div class="space-y-2"><h5 class="text-xs font-extrabold text-neutral-800 dark:text-neutral-200">Encuadre de imagen · {{ exclusivePreviewMode === 'desktop' ? 'Escritorio' : 'Celular' }}</h5><p class="text-[10px] text-neutral-500">Los desplazamientos responden al nivel de zoom.</p><div class="grid grid-cols-3 items-center justify-items-center gap-1"><span></span><button type="button" aria-label="Mover imagen arriba" @click="moveExclusiveCover(exclusivePreviewMode, 'up')" class="grid size-8 place-items-center rounded-md border"><ArrowUp class="size-4" /></button><span></span><button type="button" aria-label="Mover imagen a la izquierda" @click="moveExclusiveCover(exclusivePreviewMode, 'left')" class="grid size-8 place-items-center rounded-md border"><ArrowLeft class="size-4" /></button><span class="text-[9px] tabular-nums text-neutral-400">{{ exclusivePageForm.settings[`${exclusivePreviewMode}_cover_x`] }},{{ exclusivePageForm.settings[`${exclusivePreviewMode}_cover_y`] }}</span><button type="button" aria-label="Mover imagen a la derecha" @click="moveExclusiveCover(exclusivePreviewMode, 'right')" class="grid size-8 place-items-center rounded-md border"><ArrowRight class="size-4" /></button><span></span><button type="button" aria-label="Mover imagen abajo" @click="moveExclusiveCover(exclusivePreviewMode, 'down')" class="grid size-8 place-items-center rounded-md border"><ArrowDown class="size-4" /></button><span></span></div><label class="block text-[10px]">Zoom {{ Math.round(exclusivePageForm.settings[`${exclusivePreviewMode}_cover_zoom`] * 100) }}%<input v-model.number="exclusivePageForm.settings[`${exclusivePreviewMode}_cover_zoom`]" type="range" min="1" max="2.5" step="0.05" class="w-full accent-emerald-600" /></label></div>
                                <div class="space-y-2"><h5 class="text-xs font-extrabold text-neutral-800 dark:text-neutral-200">Posición del título</h5><div class="flex gap-2"><button v-for="alignment in [{ value: 'left', label: 'Izquierda', icon: AlignLeft }, { value: 'center', label: 'Centro', icon: AlignCenter }, { value: 'right', label: 'Derecha', icon: AlignRight }]" :key="alignment.value" type="button" :aria-label="alignment.label" @click="exclusivePageForm.settings.title_position = alignment.value" class="grid size-8 place-items-center rounded-lg border" :class="exclusivePageForm.settings.title_position === alignment.value ? 'border-emerald-600 bg-emerald-50 text-emerald-700' : 'border-neutral-200 text-neutral-500'"><component :is="alignment.icon" class="size-4" /></button></div><div class="grid grid-cols-3 items-center justify-items-center gap-1"><span></span><button type="button" aria-label="Mover título arriba" @click="moveExclusiveTitle('up')" class="grid size-7 place-items-center rounded-md border"><ArrowUp class="size-3.5" /></button><span></span><button type="button" aria-label="Mover título a la izquierda" @click="moveExclusiveTitle('left')" class="grid size-7 place-items-center rounded-md border"><ArrowLeft class="size-3.5" /></button><span class="text-[9px] tabular-nums text-neutral-400">{{ exclusivePageForm.settings.title_offset_x }},{{ exclusivePageForm.settings.title_offset_y }}</span><button type="button" aria-label="Mover título a la derecha" @click="moveExclusiveTitle('right')" class="grid size-7 place-items-center rounded-md border"><ArrowRight class="size-3.5" /></button><span></span><button type="button" aria-label="Mover título abajo" @click="moveExclusiveTitle('down')" class="grid size-7 place-items-center rounded-md border"><ArrowDown class="size-3.5" /></button><span></span></div><label class="block text-[10px]">Tamaño {{ exclusivePageForm.settings.title_size }} px<input v-model.number="exclusivePageForm.settings.title_size" type="range" min="32" max="112" step="2" class="w-full accent-emerald-600" /></label></div>
                            </div>
                        </section>
                        <section v-else class="space-y-4 rounded-2xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900">
                            <div class="flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-950"><button v-for="tab in [{key:'editor',label:'Editor'},{key:'preview',label:'Vista previa'},{key:'stats',label:'Estadísticas'}]" :key="tab.key" type="button" @click="tab.key === 'stats' ? loadRegistrationStats() : registrationPanel = tab.key as 'editor' | 'preview'" class="rounded-md px-3 py-1.5 text-[11px] font-bold" :class="registrationPanel === tab.key ? 'bg-white text-emerald-700 shadow dark:bg-neutral-800' : 'text-neutral-500'">{{ tab.label }}</button></div>
                            <div v-if="registrationPanel === 'editor'" class="space-y-2 text-xs"><p class="font-bold">Seleccionado: {{ selectedRegistrationForm?.title || 'Selecciona un formulario' }}</p><p class="text-neutral-500">Edita las preguntas y fechas desde el panel izquierdo. Los cambios se guardan con la página exclusiva.</p><Button type="button" @click="saveExclusivePage" class="rounded-lg bg-emerald-700 text-xs text-white">Guardar formulario</Button></div>
                            <div v-else-if="registrationPanel === 'preview' && selectedRegistrationForm" class="max-h-[65vh] space-y-3 overflow-y-auto rounded-xl bg-neutral-50 p-4 dark:bg-neutral-950"><h5 class="text-lg font-black">{{ selectedRegistrationForm.title }}</h5><p>{{ selectedRegistrationForm.description }}</p><div v-for="field in selectedRegistrationForm.fields" :key="field.id" class="space-y-1"><label class="text-xs font-bold">{{ field.label }}</label><textarea v-if="field.type === 'textarea'" disabled rows="2" class="w-full rounded border p-2"></textarea><select v-else-if="field.type === 'select'" disabled class="h-9 w-full rounded border"><option>Selecciona una opción</option><option v-for="option in field.options" :key="option">{{ option }}</option></select><input v-else disabled :type="field.type === 'phone' ? 'tel' : field.type" class="h-9 w-full rounded border px-2" /></div></div>
                            <div v-else-if="registrationPanel === 'stats'" class="max-h-[65vh] space-y-3 overflow-y-auto"><p v-if="registrationStatsError" class="rounded-xl bg-red-50 p-3 text-xs text-red-700">{{ registrationStatsError }}</p><p v-else-if="registrationStatsLoading" class="rounded-xl p-5 text-center text-xs text-neutral-500">Cargando respuestas…</p><template v-else-if="registrationStats"><div class="rounded-xl bg-emerald-50 p-4 dark:bg-emerald-950/40"><p class="text-[10px] font-bold uppercase text-emerald-800 dark:text-emerald-300">Inscripciones</p><p class="mt-1 text-2xl font-black">{{ registrationStats.total }}</p></div><article v-for="submission in registrationStats.submissions" :key="submission.id" class="rounded-xl border p-3 text-xs"><p class="mb-2 text-neutral-400">{{ new Date(submission.created_at).toLocaleString('es-PE') }}</p><div v-for="field in selectedRegistrationForm?.fields || []" :key="field.id"><strong>{{ field.label }}:</strong> {{ formatRegistrationAnswer(submission.answers[field.id]) }}</div></article></template></div>
                        </section>
                    </aside>
                    <div class="sticky bottom-0 z-20 col-span-full flex justify-end gap-3 border-t bg-white px-4 py-3 dark:border-neutral-800 dark:bg-neutral-900 sm:px-7 sm:py-4 lg:row-start-2"><Button type="button" variant="outline" @click="isExclusivePageOpen = false" class="rounded-xl text-xs">Cancelar</Button><Button type="submit" :disabled="exclusivePageForm.processing || (!exclusivePageForm.sections.ubicaciones.croquis && !exclusivePageForm.sections.ubicaciones.croquis_file)" class="rounded-xl bg-indigo-600 text-xs font-bold text-white">{{ exclusivePageForm.processing ? 'Guardando...' : 'Guardar página' }}</Button></div>
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
