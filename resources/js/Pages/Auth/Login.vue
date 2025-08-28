<script setup>
import { useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import DarkToggle from '@/Components/DarkToggle.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});
</script>

<template>
  <AuthLayout>
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
            class="relative z-10 w-full max-w-sm bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8"
        >
            <!-- toggle dark -->
            <div class="absolute top-4 right-4">
                <DarkToggle />
            </div>

            <!-- logo -->
            <div class="flex justify-center mb-6">
                <img
                    src="/images/Logo.png"
                    alt="Logo"
                    class="h-16 object-contain"
                />
            </div>

            <!-- título -->
            <h1 class="text-3xl font-bold text-center text-gray-800 dark:text-white mb-2">
                Hello
            </h1>
            <p class="text-center text-gray-500 dark:text-gray-400 mb-8">
                welcome!
            </p>

            <!-- formulario -->
            <form @submit.prevent="form.post('/login')" class="space-y-5">
                <!-- email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Email address
                    </label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-secondary focus:border-secondary"
                        required
                    />
                </div>

                <!-- password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        Password
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-secondary focus:border-secondary"
                        required
                    />
                </div>

                <!-- opciones -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="rounded border-gray-300 text-secondary focus:ring-secondary"
                        />
                        <span class="ml-2 text-gray-600 dark:text-gray-300">Remember me</span>
                    </label>

                    <a href="/forgot-password" class="text-secondary hover:underline">
                        Forgot password?
                    </a>
                </div>

                <!-- botón -->
                <button
                    type="submit"
                    class="w-full bg-accent hover:bg-red-600 text-white font-semibold py-3 rounded-lg transition"
                >
                    Login
                </button>
            </form>

            <!-- Sign up -->
            <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-6">
                Don’t have an account?
                <a href="/register" class="text-secondary hover:underline">Sign up</a>
            </p>
        </div>
    </div>
  </AuthLayout>
</template>
