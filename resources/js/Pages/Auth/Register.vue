<script setup>
import { useForm } from '@inertiajs/vue3';
import DarkToggle from '@/Components/DarkToggle.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});
</script>

<template>
    <div
        :class="[
      'min-h-screen flex items-center justify-center bg-cover bg-center',
      'bg-compras-light dark:bg-compras-dark'
    ]"
    >
        <!-- overlay -->
        <div class="absolute inset-0 bg-black/60"></div>

        <!-- card -->
        <div
            class="relative z-10 w-full max-sm md:max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8"
        >
            <!-- toggle dark -->
            <div class="absolute top-4 right-4">
                <DarkToggle />
            </div>

            <!-- logo -->
            <div class="flex justify-center mb-6">
                <img
                    src="/images/logo.png"
                    alt="Logo"
                    class="h-16 object-contain"
                />
            </div>

            <!-- título -->
            <h1 class="text-3xl font-bold text-center text-gray-800 dark:text-white mb-2">
                Crear Cuenta
            </h1>
            <p class="text-center text-gray-500 dark:text-gray-400 mb-8">
                Regístrate para continuar
            </p>

            <!-- formulario -->
            <form @submit.prevent="form.post('/register')" class="space-y-5">
                <!-- nombre -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Nombre completo
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-secondary focus:border-secondary"
                    />
                </div>

                <!-- email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Email address
                    </label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-secondary focus:border-secondary"
                    />
                </div>

                <!-- contraseña -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Contraseña
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-secondary focus:border-secondary"
                    />
                </div>

                <!-- confirmar -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Confirmar contraseña
                    </label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-secondary focus:border-secondary"
                    />
                </div>

                <!-- botón -->
                <button
                    type="submit"
                    class="w-full bg-accent hover:bg-red-600 text-white font-semibold py-3 rounded-lg transition"
                >
                    Registrarse
                </button>
            </form>

            <!-- link login -->
            <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-6">
                ¿Ya tienes cuenta?
                <a href="/login" class="text-secondary hover:underline">Inicia sesión</a>
            </p>
        </div>
    </div>
</template>
