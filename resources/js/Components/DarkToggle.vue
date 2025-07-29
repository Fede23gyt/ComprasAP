<script setup>
import { useDarkMode } from '@/Composables/useDarkMode';
const { isDark, toggle } = useDarkMode();
</script>

<template>
    <button
        @click="toggle"
        class="p-2 rounded-full text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition"
        aria-label="Cambiar tema"
    >
        <!-- Icono Luna / Sol -->
        <svg v-if="!isDark" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
        </svg>
        <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414z"/>
        </svg>
    </button>
</template>
