<template>
  <AuthenticatedLayout>
    <Head title="Mis Notas de Pedido" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
              <div>
                <h2 class="text-2xl font-bold">Mis Notas de Pedido</h2>
                <p class="text-gray-600 dark:text-gray-400">
                  Consulte y exporte sus notas de pedido
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
                <button 
                  @click="exportarPDF"
                  class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md flex items-center"
                >
                  <ArrowDownTrayIcon class="w-4 h-4 mr-1" />
                  PDF
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

            <!-- Resumen Estadístico -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
              <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4 text-center">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Total</h3>
                <p class="text-2xl font-bold text-blue-900 dark:text-blue-200">
                  {{ estadisticas.total }}
                </p>
              </div>
              <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4 text-center">
                <h3 class="text-sm font-medium text-green-800 dark:text-green-300">Confirmadas</h3>
                <p class="text-2xl font-bold text-green-900 dark:text-green-200">
                  {{ estadisticas.confirmadas }}
                </p>
              </div>
              <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 text-center">
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Pendientes</h3>
                <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-200">
                  {{ estadisticas.pendientes }}
                </p>
              </div>
              <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-4 text-center">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Rechazadas</h3>
                <p class="text-2xl font-bold text-red-900 dark:text-red-200">
                  {{ estadisticas.rechazadas }}
                </p>
              </div>
            </div>

            <!-- Tabla de Notas -->
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                      Número
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                      Fecha
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                      Oficina
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                      Tipo
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                      Estado
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                      Importe
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                      Acciones
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="nota in notas.data" :key="nota.id">
                    <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                      {{ nota.numero_nota }}
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-100">
                      {{ formatDate(nota.created_at) }}
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-100">
                      {{ nota.oficina?.nombre }}
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-100">
                      {{ nota.tipo_nota?.nombre }}
                    </td>
                    <td class="px-4 py-4 text-sm">
                      <span 
                        :class="getEstadoBadgeClass(nota.estado)"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      >
                        {{ estados[nota.estado] }}
                      </span>
                    </td>
                    <td class="px-4 py-4 text-sm text-right font-medium">
                      {{ formatCurrency(nota.importe_total) }}
                    </td>
                    <td class="px-4 py-4 text-sm">
                      <Link 
                        :href="route('notas-pedido.show', nota.id)"
                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-2"
                      >
                        Ver
                      </Link>
                      <Link 
                        v-if="nota.estado === 'borrador'"
                        :href="route('notas-pedido.edit', nota.id)"
                        class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                      >
                        Editar
                      </Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Paginación -->
            <div class="mt-6">
              <Pagination :links="notas.links" />
            </div>

            <!-- Sin resultados -->
            <div v-if="notas.data.length === 0" class="text-center py-8">
              <DocumentTextIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
              <p class="text-gray-500 dark:text-gray-400">
                No se encontraron notas de pedido con los filtros aplicados
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
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { DocumentTextIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
  notas: Object,
  filtros: Object,
  estados: Object,
  estadisticas: Object
})

const filtros = ref({ ...props.filtros })

function buscar() {
  router.get(route('reportes.mis-notas'), filtros.value, {
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
  window.open(`/reportes/exportar/notas/excel?${params}`)
}

function exportarPDF() {
  const params = new URLSearchParams(filtros.value).toString()
  window.open(`/reportes/exportar/notas/pdf?${params}`)
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('es-ES')
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS'
  }).format(amount)
}

function getEstadoBadgeClass(estado) {
  const classes = {
    'borrador': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
    'pendiente': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-200',
    'confirmada': 'bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-200',
    'rechazada': 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-200',
    'en_proceso': 'bg-purple-100 text-purple-800 dark:bg-purple-700 dark:text-purple-200',
    'completada': 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-200'
  }
  return classes[estado] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
}

// Watch for filter changes and debounce search
watch(filtros, (newVal, oldVal) => {
  if (JSON.stringify(newVal) !== JSON.stringify(oldVal)) {
    buscar()
  }
}, { deep: true })
</script>