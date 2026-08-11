<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Mail, GraduationCap, ArrowLeft, ArrowRight, Bell, Sparkles } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import PublicLayout from '@/layouts/PublicLayout.vue';

const email = ref('');
const subscribed = ref(false);

const handleSubscribe = (e: Event) => {
    e.preventDefault();

    if (email.value.trim()) {
        subscribed.value = true;
        email.value = '';
    }
};
</script>

<template>
    <PublicLayout title="Seguimiento al Graduado">
        <!-- Hero Header -->
        <section 
            class="relative h-[60vh] min-h-[350px] flex items-end pb-8 md:items-center md:pb-0 overflow-hidden bg-cover bg-center text-white"
            style="background-image: url('https://cdn.phototourl.com/free/2026-08-05-2af7bdd7-7eb6-4cca-9c51-1fc58cff7eeb.jpg');"
        >
            <!-- Gradient Overlay (Bottom gradient on mobile, side gradient on desktop) -->
            <div class="absolute inset-0 bg-gradient-to-t from-neutral-950 via-neutral-950/80 to-transparent md:bg-gradient-to-r md:from-neutral-950/90 md:via-neutral-950/70 md:to-transparent z-10"></div>
            
            <div class="max-w-7xl mx-auto w-full px-6 lg:px-8 text-left relative z-20 space-y-2 md:space-y-3">
                <span class="text-xs font-bold uppercase tracking-widest text-blue-400">Sub Unidad</span>
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Seguimiento al Graduado</h1>
                <p class="text-xs md:text-sm text-white/80 max-w-3xl leading-relaxed">
                    Vinculación profesional y acompañamiento al egresado de la Universidad Nacional del Altiplano en su inserción al mercado laboral.
                </p>
            </div>
        </section>

        <!-- Main Body -->
        <div class="relative py-20 px-6 overflow-hidden bg-neutral-50/50 dark:bg-neutral-900/10">
            <!-- Animated background glow -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full bg-gradient-to-br from-indigo-500/10 to-purple-500/10 blur-3xl -z-10 animate-pulse"></div>

            <div class="max-w-2xl mx-auto text-center space-y-8 relative z-10">
                <!-- Icon with glowing ring -->
                <div class="relative inline-flex items-center justify-center">
                    <div class="absolute inset-0 rounded-3xl bg-indigo-500/20 blur-xl animate-pulse"></div>
                    <div class="size-20 rounded-3xl bg-gradient-to-tr from-indigo-600 to-indigo-900 text-white flex items-center justify-center shadow-xl relative z-10 border border-indigo-400/20">
                        <GraduationCap class="size-10 text-amber-300" />
                    </div>
                </div>

                <!-- Announcement Details -->
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-bold uppercase tracking-wider">
                        <Sparkles class="size-3.5" />
                        <span>Módulo en Desarrollo</span>
                    </div>
                    
                    <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-neutral-900 dark:text-white">
                        Portal del Egresado
                    </h2>
                    
                    <p class="text-neutral-500 dark:text-neutral-400 text-sm leading-relaxed">
                        Estamos diseñando una plataforma digital innovadora orientada a nuestros egresados de la Universidad Nacional del Altiplano. Pronto podrás acceder a la Bolsa de Trabajo UNA Puno, registrar tu perfil profesional, rellenar encuestas de empleabilidad y participar de ferias de empleo exclusivas.
                    </p>
                </div>

                <!-- Interactive Email subscription mock -->
                <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 p-6 rounded-2xl shadow-xs text-left max-w-lg mx-auto">
                    <h3 class="font-bold text-sm text-neutral-900 dark:text-white flex items-center gap-2 mb-2">
                        <Bell class="size-4 text-indigo-500" />
                        ¿Eres egresado? Déjanos tu correo
                    </h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-4 leading-normal">
                        Te enviaremos una notificación oficial en cuanto el sistema esté listo para registrar tu cuenta institucional.
                    </p>

                    <div v-if="subscribed" class="p-3 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs rounded-xl font-medium border border-emerald-500/20">
                        ¡Gracias! Hemos registrado tu interés. Te avisaremos cuando se lance el portal de egresados.
                    </div>

                    <form v-else @submit="handleSubscribe" class="flex gap-2 flex-col sm:flex-row">
                        <div class="relative flex-grow">
                            <input 
                                v-model="email" 
                                type="email" 
                                required
                                placeholder="egresado@unap.edu.pe" 
                                class="w-full text-xs pl-9 pr-4 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                            <Mail class="absolute left-3 top-3.5 size-3.5 text-neutral-400" />
                        </div>
                        <Button type="submit" class="rounded-xl text-xs py-2.5 font-bold bg-indigo-700 hover:bg-indigo-800 text-white cursor-pointer shrink-0">
                            Notificarme
                        </Button>
                    </form>
                </div>

                <!-- Navigation buttons back to home -->
                <div class="flex justify-center gap-4">
                    <Link href="/">
                        <Button variant="ghost" class="rounded-xl flex items-center gap-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 cursor-pointer">
                            <ArrowLeft class="size-4" />
                            Regresar a Inicio
                        </Button>
                    </Link>
                    <Link href="/proyeccion-social">
                        <Button variant="outline" class="rounded-xl flex items-center gap-2 text-xs font-semibold border-neutral-300 dark:border-neutral-700 cursor-pointer">
                            Ver Proyección Social
                            <ArrowRight class="size-4 text-indigo-500" />
                        </Button>
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
