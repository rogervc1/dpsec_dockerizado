<script setup lang="ts">
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { CheckCircle, Upload } from '@lucide/vue';

interface RegistrationField {
    id: string;
    label: string;
    type: string;
    required: boolean;
    options?: string[];
}

const props = defineProps<{
    eventId: number;
    formId?: string;
    form: { id?: string; enabled?: boolean; title: string; description?: string; opens_at?: string | null; closes_at?: string | null; fields: RegistrationField[] };
    accent: string;
}>();

const sent = ref(false);
const registration = useForm({ answers: {} as Record<string, string | string[]>, attachments: {} as Record<string, File> });
const isOpen = computed(() => {
    const now = Date.now();
    const opensAt = props.form.opens_at ? new Date(props.form.opens_at).getTime() : null;
    const closesAt = props.form.closes_at ? new Date(props.form.closes_at).getTime() : null;
    return props.form.enabled !== false && (!opensAt || now >= opensAt) && (!closesAt || now <= closesAt);
});
const inputClass = 'mt-1 w-full rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-sm text-neutral-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 dark:border-neutral-700 dark:bg-neutral-950 dark:text-white dark:focus:ring-emerald-900';
const submit = () => registration.transform((data) => ({ ...data, form_id: props.formId || props.form.id || 'default' })).post(`/eventos/${props.eventId}/registro`, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => { sent.value = true; registration.reset(); },
});
</script>

<template>
    <div class="rounded-3xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:p-7">
        <template v-if="sent">
            <div class="py-8 text-center"><CheckCircle class="mx-auto size-10 text-emerald-600" /><h3 class="mt-3 text-xl font-black text-neutral-900 dark:text-white">Inscripción recibida</h3><p class="mt-2 text-sm text-neutral-500">Tus respuestas se guardaron correctamente.</p></div>
        </template>
        <template v-else-if="isOpen">
            <div><h3 class="text-xl font-black text-neutral-900 dark:text-white">{{ form.title }}</h3><p v-if="form.description" class="mt-2 whitespace-pre-line text-sm leading-6 text-neutral-600 dark:text-neutral-400">{{ form.description }}</p><p class="mt-2 text-xs text-neutral-500">Los campos con * son obligatorios.</p></div>
            <form class="mt-6 space-y-5" @submit.prevent="submit">
                <div v-for="field in form.fields" :key="field.id" class="space-y-1.5">
                    <label :for="`registration-${field.id}`" class="block text-sm font-bold text-neutral-800 dark:text-neutral-200">{{ field.label }}<span v-if="field.required" class="ml-1 text-red-600">*</span></label>
                    <textarea v-if="field.type === 'textarea'" :id="`registration-${field.id}`" v-model="registration.answers[field.id]" :required="field.required" rows="4" :class="inputClass" />
                    <select v-else-if="field.type === 'select'" :id="`registration-${field.id}`" v-model="registration.answers[field.id]" :required="field.required" :class="inputClass"><option value="">Selecciona una opción</option><option v-for="option in field.options" :key="option" :value="option">{{ option }}</option></select>
                    <div v-else-if="field.type === 'radio' || field.type === 'checkbox'" class="mt-2 grid gap-2 sm:grid-cols-2">
                        <label v-for="option in field.options" :key="option" class="flex items-center gap-2 rounded-xl border border-neutral-200 p-3 text-sm text-neutral-700 dark:border-neutral-800 dark:text-neutral-300"><input v-if="field.type === 'radio'" v-model="registration.answers[field.id]" type="radio" :name="field.id" :value="option" :required="field.required" /> <input v-else v-model="registration.answers[field.id]" type="checkbox" :value="option" />{{ option }}</label>
                    </div>
                    <label v-else-if="field.type === 'file'" :for="`registration-${field.id}`" class="mt-1 flex cursor-pointer items-center gap-3 rounded-xl border border-dashed border-neutral-300 p-4 text-sm text-neutral-600 hover:border-emerald-500 dark:border-neutral-700 dark:text-neutral-400"><Upload class="size-4 shrink-0" /><span class="min-w-0 flex-1 truncate">{{ registration.attachments[field.id]?.name || 'Adjuntar PDF, Word o imagen (máx. 10 MB)' }}</span><input :id="`registration-${field.id}`" type="file" class="sr-only" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp" :required="field.required" @change="(event) => { const file = (event.target as HTMLInputElement).files?.[0]; if (file) registration.attachments[field.id] = file; }" /></label>
                    <input v-else :id="`registration-${field.id}`" v-model="registration.answers[field.id]" :type="field.type === 'phone' ? 'tel' : field.type === 'text' ? 'text' : field.type" :required="field.required" :class="inputClass" />
                    <p v-if="registration.errors[`answers.${field.id}`] || registration.errors[`attachments.${field.id}`]" class="text-xs text-red-600">{{ registration.errors[`answers.${field.id}`] || registration.errors[`attachments.${field.id}`] }}</p>
                </div>
                <p v-if="registration.errors.registration" class="rounded-xl bg-red-50 p-3 text-sm text-red-700 dark:bg-red-950/40 dark:text-red-300">{{ registration.errors.registration }}</p>
                <button type="submit" :disabled="registration.processing" class="w-full rounded-xl px-5 py-3 text-sm font-extrabold text-white transition disabled:opacity-60" :style="{ backgroundColor: accent }">{{ registration.processing ? 'Enviando…' : 'Enviar inscripción' }}</button>
            </form>
        </template>
        <div v-else class="py-8 text-center"><h3 class="text-lg font-extrabold text-neutral-900 dark:text-white">Inscripciones cerradas</h3><p class="mt-2 text-sm text-neutral-500">El formulario no está recibiendo respuestas en este momento.</p></div>
    </div>
</template>
