<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, CalendarDays, FileText, ShieldCheck } from '@lucide/vue';
import { dashboard, login } from '@/routes';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const features = [
    { icon: CalendarDays, title: 'Eventos', text: 'Publica y administra las actividades institucionales.' },
    { icon: FileText, title: 'Documentos', text: 'Organiza y comparte documentos de gestión.' },
    { icon: ShieldCheck, title: 'Certificados', text: 'Emite y verifica certificados de forma sencilla.' },
];
</script>

<template>
    <Head title="Inicio" />
    <div class="min-h-screen bg-[#f5faf5] text-[#17351e] dark:bg-[#0b1710] dark:text-white">
        <header class="mx-auto flex max-w-6xl items-center justify-between px-6 py-6 lg:px-8">
            <Link href="/" class="flex items-center gap-3">
                <AppLogoIcon class="size-11 rounded-xl object-contain" />
                <div><p class="text-lg font-black tracking-tight">DPSEC</p><p class="text-[10px] font-semibold uppercase tracking-[.18em] text-[#5d8164]">UNA Puno</p></div>
            </Link>
            <Link v-if="$page.props.auth.user" :href="dashboard()" class="rounded-xl bg-[#27743a] px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-[#27743a]/20 hover:bg-[#1d5e2d]">Ir al panel</Link>
            <Link v-else :href="login()" class="rounded-xl border border-[#27743a]/25 px-5 py-2.5 text-sm font-bold text-[#27743a] hover:bg-[#27743a]/10 dark:text-[#9ee0a5]">Acceso administrativo</Link>
        </header>
        <main class="mx-auto max-w-6xl px-6 pb-16 pt-12 lg:px-8 lg:pt-24">
            <section class="grid items-center gap-12 lg:grid-cols-[1.1fr_.9fr]">
                <div>
                    <p class="mb-4 text-sm font-black uppercase tracking-[.2em] text-[#27743a]">Dirección de Proyección Social</p>
                    <h1 class="max-w-2xl text-4xl font-black leading-tight tracking-tight sm:text-6xl">Impulsamos el vínculo de la universidad con la sociedad.</h1>
                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-[#55705a] dark:text-[#b4c9b8]">Bienvenido al espacio digital del DPSEC. Conoce nuestras actividades, documentos, programas y servicios institucionales.</p>
                    <div class="mt-8 flex flex-wrap gap-3"><Link href="/eventos" class="inline-flex items-center gap-2 rounded-xl bg-[#27743a] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-[#27743a]/20 hover:bg-[#1d5e2d]">Ver eventos <ArrowRight class="size-4" /></Link><Link href="/nosotros" class="rounded-xl border border-[#27743a]/25 px-5 py-3 text-sm font-bold text-[#27743a] hover:bg-[#27743a]/10 dark:text-[#9ee0a5]">Conócenos</Link></div>
                </div>
                <div class="relative flex min-h-[300px] items-center justify-center overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#27743a] to-[#8dbb45] p-10 shadow-2xl shadow-[#27743a]/20"><div class="absolute -right-16 -top-16 size-56 rounded-full bg-white/10" /><div class="absolute -bottom-20 -left-12 size-64 rounded-full bg-black/10" /><AppLogoIcon class="relative size-48 rounded-3xl object-contain drop-shadow-2xl" /></div>
            </section>
            <section class="mt-20 grid gap-4 md:grid-cols-3"><article v-for="feature in features" :key="feature.title" class="rounded-2xl border border-[#27743a]/10 bg-white/80 p-6 shadow-sm dark:bg-white/5"><component :is="feature.icon" class="mb-5 size-6 text-[#27743a]" /><h2 class="font-black">{{ feature.title }}</h2><p class="mt-2 text-sm leading-relaxed text-[#55705a] dark:text-[#b4c9b8]">{{ feature.text }}</p></article></section>
        </main>
        <footer class="border-t border-[#27743a]/10 py-6 text-center text-xs text-[#55705a] dark:text-[#b4c9b8]">© {{ new Date().getFullYear() }} DPSEC · Universidad Nacional del Altiplano</footer>
    </div>
</template>
