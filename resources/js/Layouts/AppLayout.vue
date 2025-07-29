<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import AppHeader from '@/Components/AppHeader.vue';
import DarkToggle from '@/Components/DarkToggle.vue';

defineProps({
    title: String,
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex">
        <!-- Sidebar fijo -->
        <Sidebar />

        <!-- Contenido principal -->
        <div class="flex-1 flex flex-col">
            <!-- Header unificado -->
            <AppHeader :title="title" />

            <!-- Slot de página -->
            <main class="flex-1 p-6 overflow-y-auto">
                <slot />
            </main>
        </div>

        <!-- Toggle dark flotante -->
        <div class="fixed top-4 right-4 z-50">
            <DarkToggle />
        </div>
    </div>
</template>
