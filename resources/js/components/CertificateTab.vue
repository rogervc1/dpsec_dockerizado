<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import { 
    Award, 
    Upload, 
    FileSpreadsheet, 
    Plus, 
    Trash2, 
    Type, 
    Settings, 
    Eye, 
    Users,
    AlertCircle
} from '@lucide/vue';
import { ref } from 'vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface FontItem {
    id: number;
    font_name: string;
    file_path: string;
}

interface TemplateItem {
    id: number;
    name: string;
    background_path: string;
    settings: any;
    certificates_count: number;
}

interface CertificateItem {
    id: number;
    code: string;
    recipient_name: string;
    recipient_document: string;
    role: string;
    issue_date: string;
    template?: {
        name: string;
    };
}

const props = defineProps<{
    templates: TemplateItem[];
    fonts: FontItem[];
    certificates: {
        data: CertificateItem[];
        links: any[];
    };
}>();

const subTab = ref<'templates' | 'imports' | 'fonts' | 'records'>('templates');

// --- Font Upload Form ---
const fontForm = useForm({
    font_name: '',
    font_file: null as File | null,
});

const submitFont = () => {
    fontForm.post('/admin/certificados/tipografia', {
        onSuccess: () => {
            fontForm.reset();
        }
    });
};

const deleteFont = (id: number) => {
    if (confirm('¿Estás seguro de eliminar esta tipografía?')) {
        fontForm.delete('/admin/certificados/tipografia/' + id);
    }
};


// --- Template Creation Form ---
const showAddTemplate = ref(false);
const editingTemplate = ref<TemplateItem | null>(null);
const modalTab = ref<'general' | 'name' | 'role'>('general');

const templateForm = useForm({
    name: '',
    background_image: null as File | null,
    settings: '',
});

// Default settings JSON
const defaultSettings = {
    name_field: { x: '0%', y: '45%', font_size: '32pt', color: '#111827', font_family: 'Helvetica' },
    role_field: { x: '0%', y: '68%', font_size: '16pt', color: '#4b5563', font_family: 'Helvetica' },
};

// Reactive settings for UI binding
const nameSettings = ref({ ...defaultSettings.name_field });
const roleSettings = ref({ ...defaultSettings.role_field });

const fontOptions = computed(() => {
    const list = ['Helvetica', 'Times-Roman', 'Courier', 'Arial'];
    props.fonts.forEach(f => {
        if (!list.includes(f.font_name)) {
            list.push(f.font_name);
        }
    });

    return list;
});

const bgPreviewUrl = computed(() => {
    if (templateForm.background_image) {
        return URL.createObjectURL(templateForm.background_image);
    }

    if (editingTemplate.value) {
        return editingTemplate.value.background_path;
    }

    return '';
});

const fontStylesHtml = computed(() => {
    let styles = '<style>';
    props.fonts.forEach(f => {
        styles += `
        @font-face {
            font-family: '${f.font_name}';
            src: url('${f.file_path}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: '${f.font_name}';
            src: url('${f.file_path}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        `;
    });
    styles += '</style>';

    return styles;
});

const syncSettingsToForm = (settingsObj: any) => {
    nameSettings.value = { ...defaultSettings.name_field, ...(settingsObj?.name_field || {}) };
    roleSettings.value = { ...defaultSettings.role_field, ...(settingsObj?.role_field || {}) };
};

const syncFormToSettings = () => {
    const fullSettings = {
        name_field: nameSettings.value,
        role_field: roleSettings.value,
    };
    templateForm.settings = JSON.stringify(fullSettings, null, 2);
};

const openAddTemplate = () => {
    editingTemplate.value = null;
    templateForm.name = '';
    templateForm.background_image = null;
    syncSettingsToForm(defaultSettings);
    modalTab.value = 'general';
    showAddTemplate.value = true;
};

const openEditTemplate = (template: TemplateItem) => {
    editingTemplate.value = template;
    templateForm.name = template.name;
    templateForm.background_image = null;
    syncSettingsToForm(template.settings);
    modalTab.value = 'general';
    showAddTemplate.value = true;
};

const submitTemplate = () => {
    syncFormToSettings();

    if (editingTemplate.value) {
        templateForm.post('/admin/certificados/plantilla/' + editingTemplate.value.id, {
            onSuccess: () => {
                showAddTemplate.value = false;
                templateForm.reset();
            }
        });
    } else {
        templateForm.post('/admin/certificados/plantilla', {
            onSuccess: () => {
                showAddTemplate.value = false;
                templateForm.reset();
            }
        });
    }
};

const deleteTemplate = (id: number) => {
    if (confirm('¿Estás seguro de eliminar esta plantilla? Todos los certificados asociados se perderán.')) {
        templateForm.delete('/admin/certificados/plantilla/' + id);
    }
};

// --- Import Form ---
const importForm = useForm({
    certificate_template_id: '',
    csv_file: null as File | null,
    issue_date: new Date().toISOString().substring(0, 10),
});

const submitImport = () => {
    importForm.post('/admin/certificados/importar', {
        onSuccess: () => {
            importForm.reset('csv_file');
            subTab.value = 'records';
        }
    });
};

// --- Delete individual certificate ---
const deleteCertificate = (id: number) => {
    if (confirm('¿Deseas revocar el registro de este certificado?')) {
        templateForm.delete('/admin/certificados/' + id);
    }
};

// --- Filter certificates by template/event ---
const selectedTemplateFilter = ref(new URLSearchParams(window.location.search).get('template_id') || '');

const filterByTemplate = () => {
    router.visit('/admin', {
        data: {
            activeTab: 'certificates',
            template_id: selectedTemplateFilter.value || undefined,
        },
        preserveState: true,
        preserveScroll: true,
        only: ['certificates'],
    });
};
</script>

<template>
    <div class="space-y-6">
        <!-- Dynamic font style loading in browser for accurate previews -->
        <div v-html="fontStylesHtml"></div>
        <!-- Tab Sub-Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 p-4 rounded-2xl border border-neutral-200/80 dark:border-neutral-800/80">
            <div class="flex items-center gap-2.5">
                <div class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-xl">
                    <Award class="w-6 h-6" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-neutral-900 dark:text-white">Generación de Certificados</h2>
                    <p class="text-xs text-neutral-400">Diseña plantillas, importa estudiantes y genera certificados bajo demanda.</p>
                </div>
            </div>

            <!-- Sub Nav buttons -->
            <div class="flex flex-wrap gap-2">
                <Button 
                    size="sm" 
                    variant="ghost" 
                    :class="subTab === 'templates' ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : 'text-neutral-500 hover:text-neutral-900'"
                    @click="subTab = 'templates'"
                    class="cursor-pointer"
                >
                    <Settings class="w-4 h-4 mr-1.5" />
                    Plantillas
                </Button>
                <Button 
                    size="sm" 
                    variant="ghost" 
                    :class="subTab === 'imports' ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : 'text-neutral-500 hover:text-neutral-900'"
                    @click="subTab = 'imports'"
                    class="cursor-pointer"
                >
                    <FileSpreadsheet class="w-4 h-4 mr-1.5" />
                    Importar CSV
                </Button>
                <Button 
                    size="sm" 
                    variant="ghost" 
                    :class="subTab === 'records' ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : 'text-neutral-500 hover:text-neutral-900'"
                    @click="subTab = 'records'"
                    class="cursor-pointer"
                >
                    <Users class="w-4 h-4 mr-1.5" />
                    Alumnos Registrados
                </Button>
                <Button 
                    size="sm" 
                    variant="ghost" 
                    :class="subTab === 'fonts' ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : 'text-neutral-500 hover:text-neutral-900'"
                    @click="subTab = 'fonts'"
                    class="cursor-pointer"
                >
                    <Type class="w-4 h-4 mr-1.5" />
                    Tipografías (.ttf)
                </Button>
            </div>
        </div>

        <!-- ──────────────── 1. SUBTAB: TEMPLATES ──────────────── -->
        <div v-if="subTab === 'templates'" class="space-y-6">
            <div class="flex justify-between items-center">
                <h3 class="text-sm font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Diseños de Plantillas</h3>
                <Button @click="openAddTemplate" size="sm" class="bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer flex items-center gap-1.5">
                    <Plus class="w-4 h-4" />
                    Crear Plantilla
                </Button>
            </div>

            <!-- Templates List Grid -->
            <div v-if="templates.length > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div 
                    v-for="tmpl in templates" 
                    :key="tmpl.id"
                    class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl overflow-hidden flex flex-col justify-between group shadow-sm hover:shadow-md transition-all"
                >
                    <!-- Background image container -->
                    <div class="relative aspect-video bg-neutral-100 dark:bg-neutral-950 flex items-center justify-center overflow-hidden border-b border-neutral-100 dark:border-neutral-800">
                        <img :src="tmpl.background_path" alt="Template Preview" class="w-full h-full object-cover" />
                        <div class="absolute inset-0 bg-neutral-950/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                            <Button size="icon" variant="secondary" @click="openEditTemplate(tmpl)" class="cursor-pointer">
                                <Settings class="w-4 h-4" />
                            </Button>
                            <Button size="icon" variant="destructive" @click="deleteTemplate(tmpl.id)" class="cursor-pointer">
                                <Trash2 class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                    <div class="p-5 space-y-2">
                        <h4 class="font-bold text-neutral-900 dark:text-white text-base leading-snug">{{ tmpl.name }}</h4>
                        <div class="flex items-center justify-between text-xs text-neutral-500">
                            <span>Alumnos asignados: <strong>{{ tmpl.certificates_count }}</strong></span>
                            <span class="px-2 py-0.5 bg-neutral-100 dark:bg-neutral-850 rounded font-semibold text-[10px]">ID: {{ tmpl.id }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-16 bg-white dark:bg-neutral-900 border border-dashed border-neutral-300 dark:border-neutral-800 rounded-2xl space-y-4">
                <Award class="w-12 h-12 text-neutral-400 mx-auto" />
                <h4 class="font-bold text-neutral-700 dark:text-neutral-200">No hay plantillas creadas</h4>
                <p class="text-xs text-neutral-400 max-w-sm mx-auto">Sube una imagen de fondo y configura los tamaños de letra de los campos para comenzar.</p>
                <Button @click="openAddTemplate" size="sm" class="cursor-pointer">Nueva Plantilla</Button>
            </div>
        </div>

        <!-- ──────────────── 2. SUBTAB: IMPORT CSV ──────────────── -->
        <div v-if="subTab === 'imports'" class="max-w-2xl mx-auto bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-2xl p-6 shadow-sm space-y-6">
            <div>
                <h3 class="font-bold text-neutral-900 dark:text-white text-base">Registrar Participantes vía CSV</h3>
                <p class="text-xs text-neutral-400 mt-1">Sube la lista de participantes en un archivo `.csv` mapeado con columnas de Nombre y DNI.</p>
            </div>

            <form @submit.prevent="submitImport" class="space-y-4">
                <!-- Select Template -->
                <div class="space-y-1.5">
                    <Label for="template_id">Plantilla del Certificado</Label>
                    <select 
                        v-model="importForm.certificate_template_id" 
                        id="template_id"
                        class="w-full px-3 py-2 border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950 rounded-lg text-sm text-neutral-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        required
                    >
                        <option value="" disabled>Selecciona una plantilla...</option>
                        <option v-for="tmpl in templates" :key="tmpl.id" :value="tmpl.id">{{ tmpl.name }}</option>
                    </select>
                    <p v-if="importForm.errors.certificate_template_id" class="text-red-500 text-[10px] mt-0.5">{{ importForm.errors.certificate_template_id }}</p>
                </div>

                <!-- Issue Date -->
                <div class="space-y-1.5">
                    <Label for="issue_date">Fecha de Emisión Oficial</Label>
                    <Input 
                        v-model="importForm.issue_date"
                        type="date"
                        id="issue_date"
                        required
                    />
                    <p v-if="importForm.errors.issue_date" class="text-red-500 text-[10px] mt-0.5">{{ importForm.errors.issue_date }}</p>
                </div>

                <!-- CSV File Upload -->
                <div class="space-y-1.5">
                    <Label>Archivo de Lista (.csv)</Label>
                    <div class="border-2 border-dashed border-neutral-200 dark:border-neutral-800 rounded-xl p-6 text-center hover:bg-neutral-50 dark:hover:bg-neutral-950/40 transition-colors relative">
                        <input 
                            type="file" 
                            accept=".csv"
                            @change="(e: any) => importForm.csv_file = e.target.files[0]"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                            required
                        />
                        <div class="space-y-2">
                            <Upload class="w-8 h-8 text-neutral-400 mx-auto" />
                            <div class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                                {{ importForm.csv_file ? importForm.csv_file.name : 'Selecciona o arrastra tu archivo CSV' }}
                            </div>
                            <p class="text-xs text-neutral-400">Columnas requeridas: "nombre" y "dni".</p>
                        </div>
                    </div>
                    <p v-if="importForm.errors.csv_file" class="text-red-500 text-[10px] mt-0.5 text-center">{{ importForm.errors.csv_file }}</p>
                </div>

                <!-- Info Alert -->
                <div class="p-4 bg-indigo-500/5 border border-indigo-500/10 rounded-xl text-xs text-indigo-700 dark:text-indigo-400 flex items-start gap-2.5">
                    <AlertCircle class="w-4 h-4 flex-shrink-0 mt-0.5" />
                    <div>
                        <span class="font-bold block">Generación On-Demand Activada</span>
                        Los certificados no se generarán en PDF de inmediato. Al importarlos, los alumnos quedarán registrados y el sistema compilará el PDF solo cuando el estudiante busque su DNI en la web pública.
                    </div>
                </div>

                <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800 flex justify-end">
                    <Button type="submit" :disabled="importForm.processing" class="bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer">
                        <span v-if="importForm.processing" class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-1.5"></span>
                        Importar Registros
                    </Button>
                </div>
            </form>
        </div>

        <!-- ──────────────── 3. SUBTAB: ALUMNOS REGISTRADOS ──────────────── -->
        <div v-if="subTab === 'records'" class="space-y-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h3 class="text-sm font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Registros Emitidos</h3>
                
                <!-- Event Selector Filter -->
                <div class="flex items-center gap-2">
                    <label for="filter_template" class="text-xs text-neutral-500 font-semibold whitespace-nowrap">Filtrar por Evento:</label>
                    <select 
                        id="filter_template"
                        v-model="selectedTemplateFilter" 
                        @change="filterByTemplate"
                        class="px-3 py-1.5 text-xs border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 rounded-lg text-neutral-800 dark:text-neutral-200 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                        <option value="">Todos los Eventos</option>
                        <option v-for="temp in templates" :key="temp.id" :value="temp.id">
                            {{ temp.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Table of issued certificates -->
            <div v-if="certificates.data.length > 0" class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-neutral-600 dark:text-neutral-300">
                        <thead class="bg-neutral-50 dark:bg-neutral-950 text-xs font-bold text-neutral-500 uppercase tracking-wider border-b border-neutral-200 dark:border-neutral-800">
                            <tr>
                                <th class="px-6 py-4">Código</th>
                                <th class="px-6 py-4">Estudiante / DNI</th>
                                <th class="px-6 py-4">Evento / Plantilla</th>
                                <th class="px-6 py-4">Rol</th>
                                <th class="px-6 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                            <tr v-for="cert in certificates.data" :key="cert.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-950/50 transition-colors">
                                <td class="px-6 py-4 font-mono text-xs font-bold text-neutral-800 dark:text-neutral-200">{{ cert.code }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-neutral-900 dark:text-white">{{ cert.recipient_name }}</div>
                                    <div class="text-xs text-neutral-400 font-mono">{{ cert.recipient_document }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs font-medium text-neutral-500">{{ cert.template?.name }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 rounded text-xs">
                                        {{ cert.role || 'Participante' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a 
                                             :href="'/verificar-certificado/' + cert.uuid" 
                                             target="_blank" 
                                             class="p-1.5 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </a>
                                        <button 
                                            @click="deleteCertificate(cert.id)"
                                            class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors cursor-pointer"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Enlaces de paginación -->
                <div v-if="certificates.links && certificates.links.length > 3" class="px-6 py-4 border-t border-neutral-150 dark:border-neutral-850 bg-neutral-50/50 dark:bg-neutral-950/20 flex items-center justify-center gap-1">
                    <button
                        v-for="(link, i) in certificates.links"
                        :key="i"
                        @click="link.url ? router.visit(link.url, { preserveScroll: true, preserveState: true, only: ['certificates'] }) : null"
                        v-html="link.label"
                        :disabled="!link.url || link.active"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all',
                            link.active 
                                ? 'bg-indigo-600 text-white shadow-xs' 
                                : 'bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-800 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer'
                        ]"
                    />
                </div>
            </div>

            <div v-else class="text-center py-12 bg-white dark:bg-neutral-900 border border-neutral-150 dark:border-neutral-800 rounded-2xl space-y-2">
                <Users class="w-8 h-8 text-neutral-400 mx-auto" />
                <h4 class="font-semibold text-neutral-700 dark:text-neutral-200">No hay registros de alumnos todavía</h4>
                <p class="text-xs text-neutral-400">Importa un archivo CSV para cargar los primeros certificados.</p>
            </div>
        </div>

        <!-- ──────────────── 4. SUBTAB: FONTS ──────────────── -->
        <div v-if="subTab === 'fonts'" class="grid gap-6 md:grid-cols-3">
            <!-- Upload font form -->
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-2xl p-6 shadow-sm space-y-4">
                <div>
                    <h3 class="font-bold text-neutral-900 dark:text-white text-base">Subir Tipografía</h3>
                    <p class="text-xs text-neutral-400 mt-1">Agrega fuentes TrueType (.ttf) personalizadas para tus diseños.</p>
                </div>

                <form @submit.prevent="submitFont" class="space-y-4">
                    <div class="space-y-1.5">
                        <Label for="font_name">Nombre de la Fuente</Label>
                        <Input 
                            v-model="fontForm.font_name" 
                            type="text" 
                            id="font_name" 
                            placeholder="ej: Monotype Corsiva" 
                            required 
                        />
                    </div>
                    <div class="space-y-1.5">
                        <Label>Archivo Fuente (.ttf)</Label>
                        <Input 
                            type="file" 
                            accept=".ttf" 
                            @change="(e: any) => fontForm.font_file = e.target.files[0]" 
                            required 
                        />
                    </div>
                    <Button type="submit" :disabled="fontForm.processing" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer mt-2">
                        <span v-if="fontForm.processing" class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-1.5"></span>
                        Subir Tipografía
                    </Button>
                </form>
            </div>

            <!-- Custom Font List -->
            <div class="md:col-span-2 bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-2xl p-6 shadow-sm space-y-4">
                <div>
                    <h3 class="font-bold text-neutral-900 dark:text-white text-base">Fuentes Disponibles</h3>
                    <p class="text-xs text-neutral-400 mt-1">Tipografías personalizadas que se pueden seleccionar al editar plantillas.</p>
                </div>

                <div v-if="fonts.length > 0" class="divide-y divide-neutral-100 dark:divide-neutral-800">
                    <div v-for="f in fonts" :key="f.id" class="py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-neutral-100 dark:bg-neutral-800 text-neutral-500 rounded-lg">
                                <Type class="w-4 h-4" />
                            </div>
                            <div>
                                <span class="font-bold text-neutral-800 dark:text-neutral-200 text-sm">{{ f.font_name }}</span>
                                <span class="text-[10px] block text-neutral-400 font-mono">{{ f.file_path }}</span>
                            </div>
                        </div>
                        <button 
                            @click="deleteFont(f.id)"
                            class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg cursor-pointer transition-colors"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <div v-else class="text-center py-12 border border-dashed border-neutral-200 dark:border-neutral-800 rounded-2xl space-y-2">
                    <Type class="w-8 h-8 text-neutral-400 mx-auto" />
                    <h4 class="font-semibold text-neutral-700 dark:text-neutral-200">No hay fuentes subidas</h4>
                    <p class="text-xs text-neutral-400">Usa el formulario lateral para agregar tu primera fuente .ttf.</p>
                </div>
            </div>
        </div>

        <!-- ──────────────── MODAL: AGREGAR/EDITAR PLANTILLA ──────────────── -->
        <div v-if="showAddTemplate" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl w-full max-w-5xl overflow-hidden shadow-2xl animate-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950">
                    <h3 class="font-bold text-neutral-900 dark:text-white text-base">
                        {{ editingTemplate ? 'Editar Plantilla' : 'Nueva Plantilla de Certificado' }}
                    </h3>
                    <button @click="showAddTemplate = false" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-white cursor-pointer">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitTemplate" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Columna 1: Panel de Control de Campos -->
                        <div class="space-y-4">
                            <!-- Tabs buttons inside modal -->
                            <div class="flex border-b border-neutral-100 dark:border-neutral-800 text-[10px] sm:text-xs overflow-x-auto gap-2 pb-2">
                                <button type="button" @click="modalTab = 'general'" :class="modalTab === 'general' ? 'border-indigo-500 font-bold text-indigo-600' : 'border-transparent text-neutral-500'" class="px-2 py-1 border-b-2 whitespace-nowrap cursor-pointer">General</button>
                                <button type="button" @click="modalTab = 'name'" :class="modalTab === 'name' ? 'border-indigo-500 font-bold text-indigo-600' : 'border-transparent text-neutral-500'" class="px-2 py-1 border-b-2 whitespace-nowrap cursor-pointer">Nombre</button>
                                <button type="button" @click="modalTab = 'role'" :class="modalTab === 'role' ? 'border-indigo-500 font-bold text-indigo-600' : 'border-transparent text-neutral-500'" class="px-2 py-1 border-b-2 whitespace-nowrap cursor-pointer">Rol</button>
                            </div>

                            <!-- TAB: GENERAL -->
                            <div v-if="modalTab === 'general'" class="space-y-4 pt-2">
                                <div class="space-y-1.5">
                                    <Label for="name">Nombre de la Plantilla</Label>
                                    <Input v-model="templateForm.name" type="text" id="name" placeholder="ej: Congreso de Inteligencia Artificial 2026" required />
                                    <p v-if="templateForm.errors.name" class="text-red-500 text-[10px] mt-0.5">{{ templateForm.errors.name }}</p>
                                </div>
                                <div class="space-y-1.5">
                                    <Label>Imagen de Fondo (A4 Horizontal - JPG o PNG)</Label>
                                    <Input type="file" accept="image/*" @change="(e: any) => templateForm.background_image = e.target.files[0]" :required="!editingTemplate" />
                                    <p v-if="templateForm.errors.background_image" class="text-red-500 text-[10px] mt-0.5">{{ templateForm.errors.background_image }}</p>
                                </div>
                            </div>

                            <!-- TAB: NOMBRE -->
                            <div v-if="modalTab === 'name'" class="space-y-4 pt-2">
                                <h4 class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Configuración del Nombre del Estudiante</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1.5 col-span-2">
                                        <Label class="flex justify-between">
                                            <span>Posición Horizontal (X Shift)</span>
                                            <span class="font-mono text-xs text-indigo-500 font-bold">{{ nameSettings.x }}</span>
                                        </Label>
                                        <input 
                                            type="range" 
                                            min="-50" 
                                            max="50" 
                                            :value="parseInt(nameSettings.x) || 0" 
                                            @input="e => nameSettings.x = e.target.value + '%'"
                                            class="w-full h-2 bg-neutral-200 dark:bg-neutral-800 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                                        />
                                    </div>
                                    <div class="space-y-1.5 col-span-2">
                                        <Label class="flex justify-between">
                                            <span>Posición Vertical (Y)</span>
                                            <span class="font-mono text-xs text-indigo-500 font-bold">{{ nameSettings.y }}</span>
                                        </Label>
                                        <input 
                                            type="range" 
                                            min="0" 
                                            max="100" 
                                            :value="parseInt(nameSettings.y) || 45" 
                                            @input="e => nameSettings.y = e.target.value + '%'"
                                            class="w-full h-2 bg-neutral-200 dark:bg-neutral-800 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                                        />
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label>Tamaño de Letra</Label>
                                        <Input v-model="nameSettings.font_size" type="text" placeholder="ej: 32pt" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label>Tipografía</Label>
                                        <select v-model="nameSettings.font_family" class="w-full px-3 py-2 border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950 rounded-lg text-sm text-neutral-900 dark:text-white focus:outline-none">
                                            <option v-for="font in fontOptions" :key="font" :value="font">{{ font }}</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1.5 col-span-2">
                                        <Label>Color del Texto</Label>
                                        <div class="flex gap-2 items-center">
                                            <input v-model="nameSettings.color" type="color" class="w-8 h-8 rounded border" />
                                            <Input v-model="nameSettings.color" type="text" class="flex-1" placeholder="#000000" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB: ROL -->
                            <div v-if="modalTab === 'role'" class="space-y-4 pt-2">
                                <h4 class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Configuración del Texto del Rol</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1.5 col-span-2">
                                        <Label class="flex justify-between">
                                            <span>Posición Horizontal (X Shift)</span>
                                            <span class="font-mono text-xs text-indigo-500 font-bold">{{ roleSettings.x }}</span>
                                        </Label>
                                        <input 
                                            type="range" 
                                            min="-50" 
                                            max="50" 
                                            :value="parseInt(roleSettings.x) || 0" 
                                            @input="e => roleSettings.x = e.target.value + '%'"
                                            class="w-full h-2 bg-neutral-200 dark:bg-neutral-800 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                                        />
                                    </div>
                                    <div class="space-y-1.5 col-span-2">
                                        <Label class="flex justify-between">
                                            <span>Posición Vertical (Y)</span>
                                            <span class="font-mono text-xs text-indigo-500 font-bold">{{ roleSettings.y }}</span>
                                        </Label>
                                        <input 
                                            type="range" 
                                            min="0" 
                                            max="100" 
                                            :value="parseInt(roleSettings.y) || 68" 
                                            @input="e => roleSettings.y = e.target.value + '%'"
                                            class="w-full h-2 bg-neutral-200 dark:bg-neutral-800 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                                        />
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label>Tamaño de Letra</Label>
                                        <Input v-model="roleSettings.font_size" type="text" placeholder="ej: 16pt" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <Label>Tipografía</Label>
                                        <select v-model="roleSettings.font_family" class="w-full px-3 py-2 border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-950 rounded-lg text-sm text-neutral-900 dark:text-white focus:outline-none">
                                            <option v-for="font in fontOptions" :key="font" :value="font">{{ font }}</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1.5 col-span-2">
                                        <Label>Color del Texto</Label>
                                        <div class="flex gap-2 items-center">
                                            <input v-model="roleSettings.color" type="color" class="w-8 h-8 rounded border" />
                                            <Input v-model="roleSettings.color" type="text" class="flex-1" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna 2: Vista Previa Interactiva en Canvas -->
                        <div class="flex flex-col justify-between bg-neutral-50 dark:bg-neutral-950 p-4 rounded-xl border border-neutral-200 dark:border-neutral-800">
                            <span class="text-xs font-bold text-neutral-400 self-start mb-2 uppercase">Vista Previa de Maquetación</span>
                            
                            <div class="relative w-full aspect-[297/210] bg-neutral-200 dark:bg-neutral-850 rounded-lg shadow-inner overflow-hidden border border-neutral-300 dark:border-neutral-700 flex items-center justify-center" style="container-type: inline-size;">
                                <img v-if="bgPreviewUrl" :src="bgPreviewUrl" class="absolute inset-0 w-full h-full object-fill z-0" />
                                <span v-else class="text-[10px] text-neutral-400 z-0">Sube una imagen de fondo para ver la vista previa</span>
                                
                                <!-- Campos sobrepuestos -->
                                <div v-if="bgPreviewUrl" class="absolute inset-0 z-10 select-none">
                                    <!-- Nombre -->
                                    <div class="absolute text-center" :style="{ left: nameSettings.x, top: nameSettings.y, fontSize: ((parseFloat(nameSettings.font_size) || 32) * 0.1188) + 'cqw', color: nameSettings.color, fontFamily: nameSettings.font_family, fontWeight: 'bold', width: '100%', lineHeight: 1 }">
                                        Juan Pérez Gómez
                                    </div>
                                    <!-- Rol -->
                                    <div class="absolute text-center" :style="{ left: roleSettings.x, top: roleSettings.y, fontSize: ((parseFloat(roleSettings.font_size) || 16) * 0.1188) + 'cqw', color: roleSettings.color, fontFamily: roleSettings.font_family, width: '100%', lineHeight: 1 }">
                                        En calidad de <strong class="font-bold">Participante</strong>
                                    </div>
                                </div>
                            </div>
                            
                            <span class="text-[9px] text-neutral-400 mt-2 text-center leading-normal">
                                Las tipografías personalizadas se cargarán en la vista previa si están instaladas en tu sistema local. En el PDF final se incrustarán correctamente.
                            </span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800 flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="showAddTemplate = false" class="cursor-pointer">
                            Cancelar
                        </Button>
                        <Button type="submit" :disabled="templateForm.processing" class="bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer">
                            <span v-if="templateForm.processing" class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-1.5"></span>
                            {{ editingTemplate ? 'Guardar Cambios' : 'Crear Plantilla' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
