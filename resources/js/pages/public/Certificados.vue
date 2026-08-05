<script setup lang="ts">
import { Search, Download, Award, ShieldCheck, CheckCircle2, AlertCircle } from '@lucide/vue';
import { ref } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

interface Certificate {
    id: number;
    uuid: string;
    code: string;
    recipient_name: string;
    recipient_document: string;
    role: string;
    issue_date: string;
    template?: {
        name: string;
    };
}

const documentNumber = ref('');
const loading = ref(false);
const searched = ref(false);
const certificates = ref<Certificate[]>([]);
const error = ref('');

const searchCertificates = async () => {
    if (!documentNumber.value.trim()) {
return;
}
    
    loading.value = true;
    searched.value = true;
    error.value = '';
    certificates.value = [];

    try {
        const response = await fetch('/certificados/buscar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({ document: documentNumber.value }),
        });

        if (!response.ok) {
            throw new Error('Error al buscar certificados');
        }

        const data = await response.json();
        certificates.value = data.certificates;
    } catch (err) {
        console.error(err);
        error.value = 'Ocurrió un error al buscar tus certificados. Por favor, intenta de nuevo.';
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateStr: string) => {
    if (!dateStr) {
return '';
}

    const date = new Date(dateStr);
    const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    return `${date.getDate()} de ${months[date.getMonth()]} del ${date.getFullYear()}`;
};
</script>

<template>
    <PublicLayout title="Certificados Digitales">
        <!-- Hero Section -->
        <section class="relative min-h-[300px] py-16 flex items-center bg-radial from-neutral-900 via-neutral-950 to-black text-white overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(59,130,246,0.08)_0,transparent_100%)] z-0"></div>
            <div class="max-w-4xl mx-auto w-full px-6 text-center relative z-10 space-y-6">
                <span class="text-xs font-bold uppercase tracking-widest text-blue-400">Verificación e Impresión</span>
                <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight">Certificados Académicos</h1>
                <p class="text-sm md:text-base text-neutral-400 max-w-2xl mx-auto leading-relaxed">
                    Accede a tus constancias y certificados de eventos organizados por la DPSEC de la UNA Puno. Introduce tu documento de identidad para descargarlos de forma inmediata.
                </p>

                <!-- Search Card -->
                <div class="max-w-xl mx-auto mt-8 bg-neutral-900/60 backdrop-blur-xl border border-neutral-800 rounded-2xl p-6 shadow-2xl">
                    <form @submit.prevent="searchCertificates" class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-grow">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-neutral-500">
                                <Award class="w-5 h-5" />
                            </span>
                            <input 
                                v-model="documentNumber"
                                type="text" 
                                placeholder="Ingresa tu DNI o Código de estudiante" 
                                class="w-full pl-10 pr-4 py-3 bg-neutral-950 border border-neutral-800 rounded-xl text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm"
                                required
                            />
                        </div>
                        <button 
                            type="submit" 
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-blue-500/20 active:scale-98 transition-all"
                            :disabled="loading"
                        >
                            <span v-if="loading" class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-1"></span>
                            <Search v-else class="w-4 h-4" />
                            {{ loading ? 'Buscando...' : 'Buscar' }}
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Results Section -->
        <section class="py-16 max-w-5xl mx-auto px-6">
            <!-- Loading skeleton -->
            <div v-if="loading" class="space-y-4">
                <div class="h-20 bg-neutral-900 border border-neutral-800 rounded-xl animate-pulse" v-for="i in 3" :key="i"></div>
            </div>

            <!-- Error message -->
            <div v-else-if="error" class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-red-400 flex items-start gap-3">
                <AlertCircle class="w-5 h-5 flex-shrink-0 mt-0.5" />
                <p class="text-sm">{{ error }}</p>
            </div>

            <!-- List of certificates -->
            <div v-else-if="searched">
                <div v-if="certificates.length > 0" class="space-y-6">
                    <div class="flex items-center justify-between border-b border-neutral-800 pb-4">
                        <h2 class="text-lg font-bold text-neutral-800 dark:text-neutral-200 flex items-center gap-2">
                            <CheckCircle2 class="w-5 h-5 text-green-500" />
                            Se encontraron {{ certificates.length }} certificados disponibles
                        </h2>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div 
                            v-for="cert in certificates" 
                            :key="cert.id"
                            class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                        >
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold px-2.5 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full">
                                        {{ cert.role || 'Participante' }}
                                    </span>
                                    <span class="text-xs text-neutral-400 font-mono">
                                        {{ cert.code }}
                                    </span>
                                </div>
                                <h3 class="font-bold text-neutral-900 dark:text-white text-base md:text-lg leading-snug">
                                    {{ cert.template?.name || 'Evento Académico' }}
                                </h3>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                    Emitido a nombre de: <span class="font-semibold text-neutral-700 dark:text-neutral-300">{{ cert.recipient_name }}</span>
                                </p>
                                <p class="text-xs text-neutral-400">
                                    Fecha de Emisión: {{ formatDate(cert.issue_date) }}
                                </p>
                            </div>
                            
                            <div class="mt-6 pt-4 border-t border-neutral-100 dark:border-neutral-800 flex items-center justify-between">
                                <a 
                                    :href="'/verificar-certificado/' + cert.uuid"
                                    target="_blank"
                                    class="text-xs text-neutral-500 hover:text-blue-600 dark:hover:text-blue-400 flex items-center gap-1 transition-colors"
                                >
                                    <ShieldCheck class="w-4 h-4" />
                                    Verificar en línea
                                </a>
                                <a 
                                    :href="'/certificados/descargar/' + cert.id" 
                                    class="px-4 py-2 bg-neutral-900 hover:bg-neutral-800 dark:bg-neutral-850 dark:hover:bg-neutral-750 text-white rounded-lg text-xs font-semibold flex items-center gap-1.5 active:scale-95 transition-all shadow-sm cursor-pointer"
                                >
                                    <Download class="w-3.5 h-3.5" />
                                    Descargar PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12 space-y-4">
                    <div class="w-16 h-16 bg-neutral-100 dark:bg-neutral-900 text-neutral-400 rounded-full flex items-center justify-center mx-auto">
                        <Award class="w-8 h-8" />
                    </div>
                    <h3 class="font-bold text-neutral-800 dark:text-white text-lg">No se encontraron certificados</h3>
                    <p class="text-xs text-neutral-500 max-w-sm mx-auto">
                        Asegúrate de que tu DNI o código de estudiante esté bien escrito. Si crees que hay un error, comunícate con la oficina de la DPSEC.
                    </p>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
