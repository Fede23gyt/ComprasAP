<!-- resources/js/Pages/Memo/Show.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  memo: {
    type: Object,
    required: true
  }
});

const formatearFecha = (fecha) => {
  return new Date(fecha).toLocaleDateString('es-AR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>

<template>
  <AppLayout>
    <!-- Header Section -->
    <section class="bg-gray-100 dark:bg-gray-900 pt-4 pb-6">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-xl">
              <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>
            <div>
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Detalle del Memo</h1>
              <p class="text-gray-600 dark:text-gray-400 mt-1">
                Información completa del memo seleccionado
              </p>
            </div>
          </div>
          
          <!-- Botón de volver -->
          <div class="flex items-center space-x-3">
            <Link 
              :href="route('nomencladores.memos.index')"
              class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 shadow-md hover:shadow-lg flex items-center space-x-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              <span>Volver</span>
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- Información Principal -->
    <section class="max-w-7xl mx-auto px-4 py-6">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
        
        <!-- Información básica -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <!-- ID -->
          <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
            <div class="flex items-center space-x-3">
              <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                </svg>
              </div>
              <div>
                <div class="text-sm font-medium text-gray-900 dark:text-white">ID</div>
                <div class="text-sm text-gray-600 dark:text-gray-300">#{{ memo.id }}</div>
              </div>
            </div>
          </div>

          <!-- Estado -->
          <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
            <div class="flex items-center space-x-3">
              <div class="p-2 rounded-lg" :class="memo.estado === 'Habilitado' ? 'bg-green-100 dark:bg-green-900/20' : 'bg-red-100 dark:bg-red-900/20'">
                <svg class="w-5 h-5" :class="memo.estado === 'Habilitado' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="memo.estado === 'Habilitado'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div>
                <div class="text-sm font-medium text-gray-900 dark:text-white">Estado</div>
                <div class="text-sm" :class="memo.estado === 'Habilitado' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                  {{ memo.estado }}
                </div>
              </div>
            </div>
          </div>

          <!-- Fecha de creación -->
          <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
            <div class="flex items-center space-x-3">
              <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
              </div>
              <div>
                <div class="text-sm font-medium text-gray-900 dark:text-white">Creado</div>
                <div class="text-sm text-gray-600 dark:text-gray-300">{{ formatearFecha(memo.created_at) }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Nombre del memo -->
        <div class="mb-6">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">{{ memo.nombre }}</h2>
        </div>

        <!-- Descripción del memo -->
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Descripción</h3>
          <div class="prose dark:prose-invert max-w-none">
            <div class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">{{ memo.descripcion }}</div>
          </div>
        </div>

        <!-- Información de auditoría -->
        <div v-if="memo.updated_at !== memo.created_at" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-600">
          <div class="text-sm text-gray-500 dark:text-gray-400">
            <span class="font-medium">Última modificación:</span> {{ formatearFecha(memo.updated_at) }}
          </div>
        </div>
      </div>
    </section>
  </AppLayout>
</template>