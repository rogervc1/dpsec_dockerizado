<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    Sun,
    Moon,
    ChevronDown,
    ExternalLink,
    Menu,
    X,
    Mail,
    Phone,
    MapPin,
    Calendar,
    FileText,
    ArrowRight,
    Award
} from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useAppearance } from '@/composables/useAppearance';

// Props
defineProps<{
    title?: string;
}>();

// Page info
const page = usePage();

// Theme appearance
const { resolvedAppearance, updateAppearance } = useAppearance();
const toggleTheme = () => {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
};

// Mobile menu state
const mobileMenuOpen = ref(false);
const mobileSubmenuOpen = ref(false);

// Navigation links config
const suboffices = [
    {
        name: 'Proyección Social y Extensión Cultural',
        description: 'Coordinación de actividades comunitarias y extensión de la universidad.',
        href: '/proyeccion-social',
        external: false
    },
    {
        name: 'Gestión Ambiental',
        description: 'Oficina encargada de la sostenibilidad y ecoeficiencia de la UNA Puno.',
        href: 'https://gestionambiental.unap.edu.pe', // We can update this when they have the specific external link
        external: true
    },
    {
        name: 'Seguimiento al Graduado',
        description: 'Servicios de bolsa de trabajo y vinculación con egresados.',
        href: '/seguimiento-graduado',
        external: false
    }
];
</script>

<template>
    <div class="min-h-screen flex flex-col bg-background text-foreground transition-colors duration-300">

        <Head :title="title ? `${title} | DPESEC UNA Puno` : 'DPESEC - UNA Puno'" />



        <!-- 2. STICKY GLASSMORPHIC NAVBAR -->
        <header
            class="sticky top-0 z-50 w-full border-b border-neutral-200/80 bg-white/85 backdrop-blur-md dark:border-neutral-800/80 dark:bg-neutral-950/85">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 h-24 flex justify-between items-center">
                <!-- Logo & Brand -->
                <Link href="/" class="flex items-center gap-2.5 sm:gap-3 group shrink-0">
                    <!-- Left Logo (UNAP) -->
                    <img src="https://cdn.phototourl.com/free/2026-07-10-ea6ee316-4c97-416a-a4f4-52a5621cd3b2.png"
                        alt="Logo UNA Puno"
                        class="h-10 sm:h-12 w-auto object-contain transition-transform duration-300 group-hover:scale-105" />

                    <!-- Text in the middle -->
                    <div
                        class="flex flex-col items-center justify-center text-center max-w-[140px] sm:max-w-[200px] md:max-w-[260px] lg:max-w-[340px] leading-tight">
                        <span
                            class="font-extrabold text-[9px] sm:text-[11px] md:text-sm tracking-tight text-neutral-800 dark:text-neutral-100">
                            Dirección de Proyección Social
                        </span>
                        <span
                            class="font-extrabold text-[9px] sm:text-[11px] md:text-sm tracking-tight text-neutral-800 dark:text-neutral-100">
                            y Extensión Cultural
                        </span>
                    </div>


                    <!-- Right Logo (DPESEC) -->
                    <img src="https://cdn.phototourl.com/free/2026-07-31-f705bacb-02f5-4ea3-aeed-7e4e724a1d9b.png"
                        alt="Logo DPESEC"
                        class="h-10 sm:h-12 w-auto object-contain rounded-full border border-neutral-200/50 dark:border-neutral-800/50 transition-transform duration-300 group-hover:scale-105" />
                </Link>

                <!-- Desktop Navigation Links -->
                <nav class="hidden lg:flex items-center gap-8">
                    <Link href="/"
                        class="text-sm font-medium hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                        :class="page.url === '/' ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-neutral-600 dark:text-neutral-300'">
                        Inicio
                    </Link>

                    <Link href="/nosotros"
                        class="text-sm font-medium hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                        :class="page.url === '/nosotros' ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-neutral-600 dark:text-neutral-300'">
                        Nosotros
                    </Link>

                    <!-- Dropdown for Subunidades -->
                    <DropdownMenu>
                        <DropdownMenuTrigger
                            class="flex items-center gap-1 text-sm font-medium text-neutral-600 dark:text-neutral-300 hover:text-indigo-600 dark:hover:text-indigo-400 focus:outline-none cursor-pointer">
                            Subunidades
                            <ChevronDown class="size-4 opacity-75" />
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start"
                            class="w-80 p-2 mt-1 rounded-xl shadow-xl border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900">
                            <div
                                class="px-3 py-2 text-xs font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">
                                Subunidades
                            </div>
                            <div class="h-px bg-neutral-100 dark:bg-neutral-800 my-1"></div>

                            <template v-for="office in suboffices" :key="office.name">
                                <a v-if="office.external" :href="office.href" target="_blank" rel="noopener noreferrer"
                                    class="flex flex-col gap-1 p-3 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800/60 transition-colors group cursor-pointer">
                                    <div
                                        class="flex items-center justify-between font-semibold text-sm text-neutral-800 dark:text-neutral-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                                        {{ office.name }}
                                        <ExternalLink class="size-3.5 text-neutral-400 dark:text-neutral-500" />
                                    </div>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 leading-normal">{{
                                        office.description }}</p>
                                </a>
                                <Link v-else :href="office.href"
                                    class="flex flex-col gap-1 p-3 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800/60 transition-colors group cursor-pointer"
                                    :class="{ 'bg-neutral-50 dark:bg-neutral-800/40': page.url === office.href }">
                                    <div
                                        class="font-semibold text-sm text-neutral-800 dark:text-neutral-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 flex items-center justify-between">
                                        {{ office.name }}
                                        <span v-if="office.name.includes('Graduado')"
                                            class="text-[9px] bg-amber-500/10 text-amber-600 dark:text-amber-400 px-1.5 py-0.5 rounded font-bold uppercase tracking-wider">Próximamente</span>
                                    </div>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 leading-normal">{{
                                        office.description }}</p>
                                </Link>
                            </template>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <Link href="/documentos"
                        class="text-sm font-medium hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors flex items-center gap-1.5"
                        :class="page.url.startsWith('/documentos') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-neutral-600 dark:text-neutral-300'">
                        <FileText class="size-4" />
                        Documentos de Gestión
                    </Link>

                    <Link href="/eventos"
                        class="text-sm font-medium hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors flex items-center gap-1.5"
                        :class="page.url.startsWith('/eventos') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-neutral-600 dark:text-neutral-300'">
                        <Calendar class="size-4" />
                        Eventos
                    </Link>

                    <Link href="/certificados"
                        class="text-sm font-medium hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors flex items-center gap-1.5"
                        :class="page.url.startsWith('/certificados') ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-neutral-600 dark:text-neutral-300'">
                        <Award class="size-4" />
                        Certificados
                    </Link>
                </nav>


                <!-- Action buttons -->
                <div class="hidden lg:flex items-center gap-4">
                    <!-- Theme Toggle -->
                    <Button variant="ghost" size="icon" class="h-9 w-9 rounded-lg" @click="toggleTheme"
                        aria-label="Alternar tema oscuro">
                        <Sun v-if="resolvedAppearance === 'dark'" class="h-4 w-4 text-amber-400" />
                        <Moon v-else class="h-4 w-4 text-indigo-950" />
                    </Button>

                    <!-- Authentication / Portal access (Ocultado según requerimiento)
                    <Link v-if="authUser" href="/dashboard">
                        <Button variant="outline"
                            class="rounded-xl flex items-center gap-2 border-neutral-300 dark:border-neutral-700">
                            <User class="size-4 text-indigo-500" />
                            Mi Cuenta
                        </Button>
                    </Link>
                    <Link v-else href="/login">
                        <Button
                            class="rounded-xl bg-gradient-to-r from-indigo-700 to-indigo-900 text-white shadow-md shadow-indigo-600/10 hover:opacity-90 transition-opacity flex items-center gap-2">
                            <LogIn class="size-4" />
                            Intranet
                        </Button>
                    </Link>
                    -->
                </div>

                <!-- Mobile Menu Button -->
                <div class="lg:hidden flex items-center gap-2">
                    <Button variant="ghost" size="icon" class="h-9 w-9" @click="toggleTheme">
                        <Sun v-if="resolvedAppearance === 'dark'" class="h-4 w-4 text-amber-400" />
                        <Moon v-else class="h-4 w-4 text-indigo-950" />
                    </Button>

                    <Button variant="ghost" size="icon" class="h-9 w-9 rounded-lg"
                        @click="mobileMenuOpen = !mobileMenuOpen">
                        <Menu v-if="!mobileMenuOpen" class="h-5 w-5" />
                        <X v-else class="h-5 w-5" />
                    </Button>
                </div>
            </div>

            <!-- Mobile Navigation Menu Drawer -->
            <transition enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 translate-y-[-10px]" enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-[-10px]">
                <div v-if="mobileMenuOpen"
                    class="lg:hidden border-t border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-950 px-6 py-6 shadow-xl space-y-4">
                    <nav class="flex flex-col gap-4">
                        <Link href="/"
                            class="text-base font-semibold px-2 py-1.5 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-900 transition-colors"
                            :class="page.url === '/' ? 'text-indigo-600 dark:text-indigo-400 bg-neutral-50 dark:bg-neutral-900' : 'text-neutral-700 dark:text-neutral-300'"
                            @click="mobileMenuOpen = false">
                            Inicio
                        </Link>

                        <Link href="/nosotros"
                            class="text-base font-semibold px-2 py-1.5 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-900 transition-colors"
                            :class="page.url === '/nosotros' ? 'text-indigo-600 dark:text-indigo-400 bg-neutral-50 dark:bg-neutral-900' : 'text-neutral-700 dark:text-neutral-300'"
                            @click="mobileMenuOpen = false">
                            Nosotros
                        </Link>

                        <!-- Subunidades Mobile Section -->
                        <div class="space-y-2">
                            <button @click="mobileSubmenuOpen = !mobileSubmenuOpen"
                                class="w-full flex items-center justify-between text-base font-semibold px-2 py-1.5 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-900 transition-colors text-neutral-700 dark:text-neutral-300 text-left focus:outline-none">
                                Subunidades
                                <ChevronDown class="size-4 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': mobileSubmenuOpen }" />
                            </button>

                            <div v-show="mobileSubmenuOpen"
                                class="pl-4 border-l border-neutral-200 dark:border-neutral-800 space-y-2 py-1">
                                <template v-for="office in suboffices" :key="office.name">
                                    <a v-if="office.external" :href="office.href" target="_blank"
                                        rel="noopener noreferrer"
                                        class="flex items-center justify-between text-sm py-2 px-2 rounded-lg text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-900 hover:text-indigo-600 dark:hover:text-indigo-400">
                                        {{ office.name }}
                                        <ExternalLink class="size-3.5 opacity-60" />
                                    </a>
                                    <Link v-else :href="office.href"
                                        class="flex items-center justify-between text-sm py-2 px-2 rounded-lg"
                                        :class="page.url === office.href ? 'text-indigo-600 dark:text-indigo-400 bg-neutral-50 dark:bg-neutral-900 font-medium' : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-900'"
                                        @click="mobileMenuOpen = false">
                                        {{ office.name }}
                                        <span v-if="office.name.includes('Graduado')"
                                            class="text-[8px] bg-amber-500/10 text-amber-600 dark:text-amber-400 px-1.5 py-0.5 rounded font-bold uppercase tracking-wider">Próximamente</span>
                                    </Link>
                                </template>
                            </div>
                        </div>

                        <Link href="/documentos"
                            class="text-base font-semibold px-2 py-1.5 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-900 transition-colors flex items-center gap-2"
                            :class="page.url.startsWith('/documentos') ? 'text-indigo-600 dark:text-indigo-400 bg-neutral-50 dark:bg-neutral-900' : 'text-neutral-700 dark:text-neutral-300'"
                            @click="mobileMenuOpen = false">
                            <FileText class="size-5" />
                            Documentos de Gestión
                        </Link>

                        <Link href="/eventos"
                            class="text-base font-semibold px-2 py-1.5 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-900 transition-colors flex items-center gap-2"
                            :class="page.url.startsWith('/eventos') ? 'text-indigo-600 dark:text-indigo-400 bg-neutral-50 dark:bg-neutral-900' : 'text-neutral-700 dark:text-neutral-300'"
                            @click="mobileMenuOpen = false">
                            <Calendar class="size-5" />
                            Eventos
                        </Link>

                        <Link href="/certificados"
                            class="text-base font-semibold px-2 py-1.5 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-900 transition-colors flex items-center gap-2"
                            :class="page.url.startsWith('/certificados') ? 'text-indigo-600 dark:text-indigo-400 bg-neutral-50 dark:bg-neutral-900' : 'text-neutral-700 dark:text-neutral-300'"
                            @click="mobileMenuOpen = false">
                            <Award class="size-5" />
                            Certificados
                        </Link>
                    </nav>


                    <div class="h-px bg-neutral-200 dark:bg-neutral-800 my-4"></div>

                    <!-- Portal / Intranet Access (Mobile) (Ocultado según requerimiento)
                    <Link v-if="authUser" href="/dashboard" class="block w-full" @click="mobileMenuOpen = false">
                        <Button variant="outline" class="w-full rounded-xl flex items-center justify-center gap-2">
                            <User class="size-4 text-indigo-500" />
                            Panel de Administración
                        </Button>
                    </Link>
                    <Link v-else href="/login" class="block w-full" @click="mobileMenuOpen = false">
                        <Button
                            class="w-full rounded-xl bg-gradient-to-r from-indigo-700 to-indigo-900 text-white flex items-center justify-center gap-2">
                            <LogIn class="size-4" />
                            Acceso Intranet
                        </Button>
                    </Link>
                    -->
                </div>
            </transition>
        </header>

        <!-- 3. PAGE MAIN CONTENT -->
        <main class="flex-grow">
            <slot />
        </main>

        <!-- 4. GORGEOUS FOOTER -->
        <footer class="bg-neutral-950 text-neutral-400 pt-16 pb-8 border-t border-neutral-900">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">

                <!-- Col 1: Bio and Brand -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2.5">
                        <div class="size-12 rounded-lg  flex items-center justify-center text-white shadow-md">
                            <img src="https://cdn.phototourl.com/free/2026-07-10-ea6ee316-4c97-416a-a4f4-52a5621cd3b2.png"
                                alt="Description" class="size-12" />
                        </div>

                        <div class="flex flex-col">
                            <span class="font-bold text-lg text-white leading-none">DPESEC</span>
                            <span class="text-[8px] text-neutral-400 uppercase tracking-widest">UNA Puno</span>
                        </div>
                    </div>
                    <p class="text-sm leading-relaxed text-neutral-400">
                        Dirección de Proyección Social y Extensión Cultural. Promovemos el vínculo integrador entre la
                        Universidad Nacional del Altiplano y la sociedad civil, impulsando el desarrollo sostenible y la
                        valoración cultural del Altiplano.
                    </p>
                    <div class="pt-2 flex items-center gap-3">
                        <!-- Facebook -->
                        <a href="https://www.facebook.com/p/Dirección-de-Proyección-Social-y-Extensión-Cultural-UNA-Puno-100071137256988/"
                            target="_blank" rel="noopener noreferrer"
                            class="size-9 rounded-lg bg-neutral-900 hover:bg-blue-900/60 hover:text-blue-500 flex items-center justify-center text-neutral-300 transition-colors">
                            <svg class="size-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1V12h3l-.5 3h-2.5v6.8c4.56-.93 8-4.96 8-9.8z" />
                            </svg>
                        </a>

                        <!-- YouTube -->
                        <a href="https://www.youtube.com/@direccionderesponsabilidad3871" target="_blank"
                            rel="noopener noreferrer"
                            class="size-9 rounded-lg bg-neutral-900 hover:bg-red-900/60 hover:text-red-500 flex items-center justify-center text-neutral-300 transition-colors">
                            <svg class="size-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M21.582,6.186c-0.23-0.86-0.908-1.538-1.768-1.768C18.254,4,12,4,12,4S5.746,4,4.186,4.418 c-0.86,0.23-1.538,0.908-1.768,1.768C2,7.746,2,12,2,12s0,4.254,0.418,5.814c0.23,0.86,0.908,1.538,1.768,1.768 C5.746,20,12,20,12,20s6.254,0,7.814-0.418c0.86-0.23,1.538-0.908,1.768-1.768C22,16.254,22,12,22,12S22,7.746,21.582,6.186z M10,15.464V8.536L16,12L10,15.464z" />
                            </svg>
                        </a>

                        <!-- TikTok -->
                        <a href="https://www.tiktok.com/@responsabilidadsocialuna?is_from_webapp=1&sender_device=pc"
                            target="_blank" rel="noopener noreferrer"
                            class="size-9 rounded-lg bg-neutral-900 hover:bg-neutral-800 hover:text-white flex items-center justify-center text-neutral-300 transition-colors">
                            <svg class="size-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z" />
                            </svg>
                        </a>

                        <!-- WhatsApp -->
                        <a href="https://wa.me/51987947628?text=Hola,%20deseo%20realizar%20una%20consulta." target="_blank" rel="noopener noreferrer"
                            class="size-9 rounded-lg bg-neutral-900 hover:bg-green-900/60 hover:text-green-500 flex items-center justify-center text-neutral-300 transition-colors">
                            <svg class="size-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Col 2: Navigation Links -->
                <div class="space-y-4">
                    <h3 class="font-bold text-white tracking-wide text-sm uppercase">Enlaces Rápidos</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li>
                            <Link href="/" class="hover:text-white transition-colors flex items-center gap-1.5">
                                <ArrowRight class="size-3 text-indigo-500" />
                                Inicio
                            </Link>
                        </li>
                        <li>
                            <Link href="/nosotros" class="hover:text-white transition-colors flex items-center gap-1.5">
                                <ArrowRight class="size-3 text-indigo-500" />
                                Nosotros
                            </Link>
                        </li>
                        <li>
                            <Link href="/proyeccion-social"
                                class="hover:text-white transition-colors flex items-center gap-1.5">
                                <ArrowRight class="size-3 text-indigo-500" />
                                Proyección Social
                            </Link>
                        </li>
                        <li>
                            <a href="https://www.unap.edu.pe" target="_blank"
                                class="hover:text-white transition-colors flex items-center gap-1.5">
                                <ArrowRight class="size-3 text-indigo-500" />
                                Gestión Ambiental (Ext.)
                            </a>
                        </li>
                        <li>
                            <Link href="/seguimiento-graduado"
                                class="hover:text-white transition-colors flex items-center gap-1.5">
                                <ArrowRight class="size-3 text-indigo-500" />
                                Egresados / Graduados
                            </Link>
                        </li>
                        <li>
                            <Link href="/documentos"
                                class="hover:text-white transition-colors flex items-center gap-1.5">
                                <ArrowRight class="size-3 text-indigo-500" />
                                Documentos de Gestión
                            </Link>
                        </li>
                        <li>
                            <Link href="/eventos" class="hover:text-white transition-colors flex items-center gap-1.5">
                                <ArrowRight class="size-3 text-indigo-500" />
                                Eventos
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Col 3: Contact Info -->
                <div class="space-y-4">
                    <h3 class="font-bold text-white tracking-wide text-sm uppercase">Contacto e Informes</h3>
                    <ul class="space-y-3.5 text-sm">
                        <li class="flex items-start gap-2.5">
                            <MapPin class="size-5 text-indigo-500 shrink-0 mt-0.5" />
                            <span>Tercer piso del Auditorio Magno, Av. Floral 1153, Puno, Perú</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <Mail class="size-5 text-indigo-500 shrink-0" />
                            <a href="mailto:proyeccionsocial@unap.edu.pe"
                                class="hover:text-white transition-colors">drs@unap.edu.pe</a>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <Phone class="size-5 text-indigo-500 shrink-0" />
                            <span>987 947 628</span>
                        </li>
                    </ul>
                </div>

                <!-- Col 4: Horas de Atención / Enlaces UNA -->
                <div class="space-y-4">
                    <h3 class="font-bold text-white tracking-wide text-sm uppercase">Atención al Público</h3>
                    <div class="bg-neutral-900/60 p-4 rounded-xl border border-neutral-900 text-sm space-y-2">
                        <div class="flex justify-between">
                            <span class="text-neutral-400">Lunes a Viernes</span>
                        </div>
                        <div class="font-semibold text-white">08:00 AM - 02:00 PM</div>
                        <div class="h-px bg-neutral-800 my-2"></div>
                        <p class="text-xs text-neutral-500 leading-normal">
                            Consultas presenciales en la oficina DPSEC.
                        </p>
                    </div>
                </div>

            </div>

            <!-- Bottom Line -->
            <div
                class="max-w-7xl mx-auto px-6 lg:px-8 mt-12 pt-6 border-t border-neutral-900 text-xs flex flex-col md:flex-row justify-between items-center gap-4">
                <p>&copy; 2026 DPSEC UNA Puno. Todos los derechos reservados.</p>
                <div class="flex gap-6">
                    <a href="https://www.unap.edu.pe" target="_blank" rel="noopener noreferrer"
                        class="hover:text-white transition-colors">Portal UNA Puno</a>
                    <a href="/login" class="hover:text-white transition-colors">Acceso Interno</a>
                </div>
            </div>
        </footer>
    </div>
</template>
