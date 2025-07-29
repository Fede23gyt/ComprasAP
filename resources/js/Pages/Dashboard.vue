<script setup>
import { Link } from '@inertiajs/vue3';
import DarkToggle from '@/Components/DarkToggle.vue';
import Sidebar from '@/Components/Sidebar.vue';

const cards = [
    { label: 'Mis Notas de Pedido',   route: '/notas',        icon: '📝' },
    { label: 'Autorizar Notas',       route: '/autorizar',    icon: '✅' },
    { label: 'Pre-Compras',           route: '/precompras',   icon: '🛒' },
    { label: 'Proveedores',           route: '/proveedores',  icon: '🤝' },
    { label: 'Órdenes de Compra',     route: '/ordenes',      icon: '📦' },
    { label: 'Reportes',              route: '/reportes',     icon: '📊' },
];
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex">
        <!-- Sidebar fijo -->
        <Sidebar />

        <!-- Contenido principal -->
        <main class="flex-1 p-6">
            <div class="max-w-5xl mx-auto">
                <!-- Logo + Título -->
                <div class="text-center mb-8">
                    <img src="/images/logo.png" alt="Logo" class="h-20 mx-auto mb-2" />
                    <h1 class="text-3xl font-bold text-primary dark:text-white">
                        Sistemas de Compras y Contrataciones
                    </h1>
                </div>

                <!-- Mis aplicaciones -->
                <section>
                    <h2 class="text-2xl font-semibold mb-6 text-gray-800 dark:text-gray-200">
                        Mis aplicaciones
                    </h2>

                    <!-- Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link
                            v-for="card in cards"
                            :key="card.route"
                            :href="card.route"
                            class="bg-primary hover:bg-opacity-90 text-white rounded-xl p-6 shadow-lg transition transform hover:scale-105"
                        >
                            <div class="text-4xl mb-3">{{ card.icon }}</div>
                            <div class="text-lg font-semibold">{{ card.label }}</div>
                        </Link>
                    </div>
                </section>
            </div>
        </main>

        <!-- Dark toggle flotante -->
        <div class="fixed top-4 right-4 z-50">
            <DarkToggle />
        </div>
    </div>
</template>
