<template>
  <AuthenticatedLayout>
    <Head title="Reportes por Oficina" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
              <div>
                <h2 class="text-2xl font-bold">Reportes por Oficina</h2>
                <p class="text-gray-600 dark:text-gray-400">
                  Estadísticas y métricas organizadas por oficina
                </p>
              </div>
              <div class="flex space-x-2">
                <button 
                  @click="exportarExcel"
                  class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md flex items-center"
                >
                  <ArrowDownTrayIcon class="w-4 h-4 mr-1" />
                  Excel
                </button>
              </div>
            </div>

            <!-- Filtros -->
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-6">
              <h3 class="text-lg font-semibold mb-4">Filtros</h3>
              <form @submit.prevent="buscar" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Fecha Desde
                  </label>
                  <input
                    v-model="filtros.fecha_desde"
                    type="date"
                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Fecha Hasta
                  </label>
                  <input
                    v-model="filtros.fecha_hasta"
                    type="date"
                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Estado
                  </label>
                  <select
                    v-model="filtros.estado"
                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md"
                  >
                    <option value="">Todos los estados</option>
                    <option v-for="(label, value) in estados" :key="value" :value="value">
                      {{ label }}
                    </option>
                  </select>
                </div>
                <div class="flex items-end">
                  <button
                    type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md"
                  >
                    Buscar
                  </button>
                  <button
                    type="button"
                    @click="limpiarFiltros"
                    class="ml-2 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md"
                  >
                    Limpiar
                  </button>
                </div>
              </form>
            </div>

            <!-- Resumen General -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
              <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4 text-center">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Total Oficinas</h3>
                <p class="text-2xl font-bold text-blue-900 dark:text-blue-200">
                  {{ estadisticas.total_oficinas }}
                </p>
              </div>
              <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4 text-center">
                <h3 class="text-sm font-medium text-green-800 dark:text-green-300">Total Notas</h3>
                <p class="text-2xl font-bold text-green-900 dark:text-green-200">
                  {{ estadisticas.total_notas }}
                </p>
              </div>
              <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-lg p-4 text-center">
                <h3 class="text-sm font-medium text-purple-800 dark:text-purple-300">Monto Total</h3>
                <p class="text-2xl font-bold text-purple-900 dark:text-purple-200">
                  {{ formatCurrency(estadisticas.monto_total) }}
                </p>
              </div>
              <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg p-4 text-center">
                <h3 class="text-sm font-medium text-orange-800 dark:text-orange-300">Promedio por Oficina</h3>
                <p class="text-2xl font-bold text-orange-900 dark:text-orange-200">
                  {{ formatCurrency(estadisticas.promedio_oficina) }}
                </p>
              </div>
            </div>

            <!-- Tabla de Oficinas -->
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                      Oficina
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                      Total Notas
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                      Monto Total
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                      Borrador
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                      Pendiente
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                      Confirmada
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                      Rechazada
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                      Completada
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                      Acciones
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="oficina in oficinas" :key="oficina.id">
                    <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                      {{ oficina.nombre }}
                    </td>
                    <td class="px-4 py-4 text-sm text-center text-gray-900 dark:text-gray-100">
                      {{ oficina.total_notas }}
                    </td>
                    <td class="px-4 py-4 text-sm text-right font-medium">
                      {{ formatCurrency(oficina.monto_total) }}
                    </td>
                    <td class="px-4 py-4 text-sm text-center">
                      <span class="text-gray-600 dark:text-gray-400">
                        {{ oficina.notas_por_estado?.borrador || 0 }}
                      </span>
                    </td>
                    <td class="px-4 py-4 text-sm text-center">
                      <span class="text-yellow-600 dark:text-yellow-400">
                        {{ oficina.notas_por_estado?.pendiente || 0 }}
                      </span>
                    </td>
                    <td class="px-4 py-4 text-sm text-center">
                      <span class="text-blue-600 dark:text-blue-400">
                        {{ oficina.notas_por_estado?.confirmada || 0 }}
                      </span>
                    </td>
                    <td class="px-4 py-4 text-sm text-center">
                      <span class="text-red-600 dark:text-red-400">
                        {{ oficina.notas_por_estado?.rechazada || 0 }}
                      </span>
                    </td>
                    <td class="px-4 py-4 text-sm text-center">
                      <span class="text-green-600 dark:text-green-400">
                        {{ oficina.notas_por_estado?.completada || 0 }}
                      </span>
                    </td>
                    <td class="px-4 py-4 text-sm">
                      <button 
                        @click="verDetalle(oficina)"
                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                      >
                        Detalle
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Gráfico de Distribución -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Distribución por Estado</h3>
                <div class="space-y-2">
                  <div v-for="(count, estado) in estadisticas.distribucion_estados" :key="estado" class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                      {{ estados[estado] }}:
                    </span>
                    <span class="text-sm font-medium">
                      {{ count }} ({{ ((count / estadisticas.total_notas) * 100).toFixed(1) }}%)
                    </span>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Top 5 Oficinas por Monto</h3>
                <div class="space-y-2">
                  <div 
                    v-for="(oficina, index) in oficinas.slice().sort((a, b) => b.monto_total - a.monto_total).slice(0, 5)" 
                    :key="oficina.id"
                    class="flex items-center justify-between"
                  >
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                      {{ index + 1 }}. {{ oficina.nombre }}
                    </span>
                    <span class="text-sm font-medium">
                      {{ formatCurrency(oficina.monto_total) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Sin resultados -->
            <div v-if="oficinas.length === 0" class="text-center py-8">
              <BuildingOfficeIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
              <p class="text-gray-500 dark:text-gray-400">
                No se encontraron datos para los filtros aplicados
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { BuildingOfficeIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  oficinas: Array,
  filtros: Object,
  estados: Object,
  estadisticas: Object
})

const filtros = ref({ ...props.filtros })

function buscar() {
  router.get(route('reportes.por-oficina'), filtros.value, {
    preserveState: true,
    preserveScroll: true
  })
}

function limpiarFiltros() {
  filtros.value = {}
  buscar()
}

function exportarExcel() {
  const params = new URLSearchParams(filtros.value).toString()
  window.open(`/reportes/exportar/oficinas/excel?${params}`)
}

function verDetalle(oficina) {
  const params = new URLSearchParams({
    ...filtros.value,
    oficina_id: oficina.id
  }).toString()
  window.open(`/reportes/todas-notas?${params}`)
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS'
  }).format(amount)
}

// Watch for filter changes and debounce search
watch(filtros, (newVal, oldVal) => {
  if (JSON.stringify(newVal) !== JSON.stringify(oldVal)) {
    buscar()
  }
}, { deep: true })
</script>