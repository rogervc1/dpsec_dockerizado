<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center bg-neutral-50 dark:bg-neutral-950 p-6 md:p-10 overflow-hidden"
    >
        <!-- Ambient Glow Circles -->
        <div class="absolute -left-24 -top-24 size-[380px] rounded-full bg-indigo-500/10 dark:bg-indigo-500/5 blur-3xl pointer-events-none"></div>
        <div class="absolute -right-24 -bottom-24 size-[380px] rounded-full bg-emerald-500/10 dark:bg-emerald-500/5 blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-md relative z-10">
            <div class="backdrop-blur-md bg-white/80 dark:bg-neutral-900/80 border border-neutral-200/60 dark:border-neutral-800/60 p-8 md:p-10 rounded-[32px] shadow-2xl flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-2 font-medium"
                    >
                        <div
                            class="mb-1 flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 dark:bg-indigo-500/20"
                        >
                            <AppLogoIcon
                                class="size-6 fill-current"
                            />
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-2xl font-black tracking-tight text-neutral-900 dark:text-white">{{ title }}</h1>
                        <p class="text-center text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed font-normal">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
