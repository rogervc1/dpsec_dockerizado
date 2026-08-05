<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasskeyVerify from '@/components/PasskeyVerify.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Acceso Interno',
        description: 'Ingresa tus credenciales de la oficina para acceder a la Intranet',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Acceso Interno" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-emerald-600 dark:text-emerald-400"
    >
        {{ status }}
    </div>

    <PasskeyVerify />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6 text-left"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="email">Correo Institucional</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="correo@unap.edu.pe"
                    class="rounded-xl border-neutral-300 dark:border-neutral-700 focus-visible:ring-indigo-500"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password">Contraseña</Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-700"
                        :tabindex="5"
                    >
                        ¿Olvidaste tu contraseña?
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Contraseña"
                    class="rounded-xl border-neutral-300 dark:border-neutral-700 focus-visible:ring-indigo-500"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label for="remember" class="flex items-center space-x-3 cursor-pointer">
                    <Checkbox id="remember" name="remember" :tabindex="3" class="rounded-md border-neutral-300 dark:border-neutral-700 text-indigo-600 focus:ring-indigo-500" />
                    <span class="text-xs text-neutral-600 dark:text-neutral-400 select-none">Recordar mi cuenta</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-4 w-full h-11 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold transition-all shadow-lg shadow-indigo-600/10 cursor-pointer"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                Ingresar al Sistema
            </Button>
        </div>

        <div class="text-center text-xs text-neutral-500 dark:text-neutral-400">
            ¿No tienes una cuenta de administrador?
            <TextLink :href="register()" :tabindex="5" class="font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 ml-1">Regístrate</TextLink>
        </div>
    </Form>
</template>
