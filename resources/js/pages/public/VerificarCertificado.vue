<script setup lang="ts">
import { ShieldCheck, Download, Calendar, User, FileCheck2, Home, Award } from '@lucide/vue';
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

defineProps<{
    certificate: Certificate;
}>();

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
    <PublicLayout title="Verificación de Certificado">
        <div class="py-16 px-6 max-w-2xl mx-auto">
            <!-- Verification Seal -->
            <div class="text-center space-y-4 mb-8">
                <div class="inline-flex items-center justify-center p-4 bg-green-500/10 text-green-500 border border-green-500/20 rounded-full animate-bounce">
                    <ShieldCheck class="w-12 h-12" />
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-neutral-900 dark:text-white tracking-tight">
                    Certificado de Autenticidad
                </h1>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    Este certificado ha sido emitido y registrado oficialmente por la DPSEC de la UNA Puno.
                </p>
                <div class="inline-block px-4 py-1.5 bg-green-500 text-white text-xs font-bold tracking-wide rounded-full shadow-lg shadow-green-500/20">
                    REGISTRO VÁLIDO
                </div>
            </div>

            <!-- Details Card -->
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-3xl p-6 md:p-8 shadow-xl space-y-6">
                
                <!-- Code & UUID Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-4 border-b border-neutral-100 dark:border-neutral-800 gap-2">
                    <div>
                        <span class="text-xs text-neutral-400 block font-semibold uppercase">CÓDIGO DE REGISTRO</span>
                        <span class="font-mono text-sm font-bold text-neutral-800 dark:text-neutral-200">{{ certificate.code }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-neutral-400 block font-semibold uppercase">ID DE VERIFICACIÓN (UUID)</span>
                        <span class="font-mono text-xs text-neutral-500">{{ certificate.uuid }}</span>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="space-y-4">
                    <!-- Participant Name -->
                    <div class="flex items-start gap-3">
                        <div class="p-2.5 bg-neutral-50 dark:bg-neutral-800 rounded-xl text-neutral-500 dark:text-neutral-400 flex-shrink-0">
                            <User class="w-5 h-5" />
                        </div>
                        <div>
                            <span class="text-xs text-neutral-400 font-semibold block uppercase">Nombre del Titular</span>
                            <span class="font-bold text-neutral-950 dark:text-white text-base md:text-lg">
                                {{ certificate.recipient_name }}
                            </span>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                DNI / Código: {{ certificate.recipient_document }}
                            </p>
                        </div>
                    </div>

                    <!-- Event -->
                    <div class="flex items-start gap-3">
                        <div class="p-2.5 bg-neutral-50 dark:bg-neutral-800 rounded-xl text-neutral-500 dark:text-neutral-400 flex-shrink-0">
                            <Award class="w-5 h-5" />
                        </div>
                        <div>
                            <span class="text-xs text-neutral-400 font-semibold block uppercase">Nombre del Evento</span>
                            <span class="font-bold text-neutral-800 dark:text-neutral-200 text-sm md:text-base">
                                {{ certificate.template?.name || 'Evento Académico' }}
                            </span>
                        </div>
                    </div>

                    <!-- Role -->
                    <div class="flex items-start gap-3">
                        <div class="p-2.5 bg-neutral-50 dark:bg-neutral-800 rounded-xl text-neutral-500 dark:text-neutral-400 flex-shrink-0">
                            <FileCheck2 class="w-5 h-5" />
                        </div>
                        <div>
                            <span class="text-xs text-neutral-400 font-semibold block uppercase">Participación</span>
                            <span class="font-bold text-neutral-850 dark:text-neutral-300 text-sm">
                                En calidad de {{ certificate.role || 'Participante' }}
                            </span>
                        </div>
                    </div>

                    <!-- Date -->
                    <div class="flex items-start gap-3">
                        <div class="p-2.5 bg-neutral-50 dark:bg-neutral-800 rounded-xl text-neutral-500 dark:text-neutral-400 flex-shrink-0">
                            <Calendar class="w-5 h-5" />
                        </div>
                        <div>
                            <span class="text-xs text-neutral-400 font-semibold block uppercase">Fecha de Emisión</span>
                            <span class="font-bold text-neutral-850 dark:text-neutral-300 text-sm">
                                {{ formatDate(certificate.issue_date) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="pt-6 border-t border-neutral-100 dark:border-neutral-800 flex flex-col sm:flex-row gap-3">
                    <a 
                        :href="'/certificados/descargar/' + certificate.id" 
                        class="flex-1 px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-2 active:scale-95 shadow-md shadow-blue-500/10 cursor-pointer transition-all"
                    >
                        <Download class="w-4 h-4" />
                        Descargar Certificado (PDF)
                    </a>
                    
                    <a 
                        :href="'/certificados'" 
                        class="px-5 py-3 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-750 text-neutral-700 dark:text-neutral-200 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 active:scale-95 cursor-pointer transition-all"
                    >
                        <Home class="w-4 h-4" />
                        Ir a búsqueda
                    </a>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
