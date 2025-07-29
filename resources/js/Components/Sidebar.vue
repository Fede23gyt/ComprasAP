<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const open = ref(false);

function toggle() {
    open.value = !open.value;
}

function close() {
    open.value = false;
}

const menu = [
    {
        categoria: 'Mis aplicaciones',
        items: [
            { label: 'Mis Notas de Pedido', route: '/notas' },
            { label: 'Autorizar Notas', route: '/autorizar' },
            { label: 'Pre-Compras', route: '/precompras' },
            { label: 'Órdenes de Compra', route: '/ordenes' },
        ],
    },
    {
        categoria: 'Catálogos',
        items: [
            { label: 'Oficinas', route: '/oficinas' },
            { label: 'Proveedores', route: '/proveedores' },
            { label: 'Insumos', route: '/insumos' },
        ],
    },
    {
        categoria: 'Reportes',
        items: [
            { label: 'Adjudicaciones', route: '/reportes/adjudicaciones' },
            { label: 'Órdenes emitidas', route: '/reportes/ordenes' },
        ],
    },
    {
        categoria: 'Configuración',
        items: [
            { label: 'Usuarios y roles', route: '/admin/users' },
            { label: 'Parámetros generales', route: '/admin/settings' },
        ],
    },
];
</script>

<template>
    <!-- Botón hamburguesa (móvil) -->
    <button
        @click="toggle"
        class="fixed top-4 left-4 z-50 md:hidden bg-primary text-white p-2 rounded"
        aria-label="Abrir menú"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"
            />
        </svg>
    </button>

    <!-- Sidebar -->
    <aside
        :class="[
    open ? 'translate-x-0' : '-translate-x-full',
    'fixed inset-y-0 left-0 w-64 bg-gray-800 dark:bg-gray-400 text-white shadow-xl p-4 z-40 md:translate-x-0 ' +
     'md:relative md:shadow-none transition-transform duration-300'
    ]"
    >
        <div class="flex justify-end md:hidden">
            <button @click="close" class="text-white mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>

        <!-- Logo (oculto en móvil) -->
        <div class="hidden md:flex justify-center mb-6">
            <img src="/images/logo.png" class="h-12" alt="Logo" />
        </div>

        <!-- Menú -->
        <nav class="flex flex-col space-y-6">
            <div v-for="section in menu" :key="section.categoria">
                <h3 class="text-sm uppercase tracking-wider font-semibold mb-2 opacity-70">
                    {{ section.categoria }}
                </h3>
                <ul class="space-y-1">
                    <li v-for="item in section.items" :key="item.route">
                        <Link
                            :href="item.route"
                            @click="close"
                            class="block px-3 py-2 rounded-md text-sm hover:bg-secondary hover:text-primary transition"
                            :class="{ 'bg-secondary text-primary font-bold': $page.url.startsWith(item.route) }"
                        >
                            {{ item.label }}
                        </Link>
                    </li>
                </ul>
            </div>
        </nav>
    </aside>

    <!-- Overlay para cerrar al tocar fuera (solo móvil) -->
    <div
        v-if="open"
        @click="close"
        class="fixed inset-0 bg-black/40 z-30 md:hidden"
    ></div>
</template>
