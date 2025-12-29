<template>
  <AppLayout>
    <Head title="Órdenes de Compra" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
              <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                  Órdenes de Compra
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mt-2">
                  Gestione todas las órdenes de compra del sistema
                </p>
              </div>
            </div>

            <!-- Filtros -->
            <div class="mb-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
              <form @submit.prevent="aplicarFiltros" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Buscar
                  </label>
                  <input
                    v-model="filtros.search"
                    type="text"
                    placeholder="N° Orden, Proveedor..."
                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Estado
                  </label>
                  <select
                    v-model="filtros.estado"
                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  >
                    <option value="">Todos</option>
                    <option v-for="(label, value) in estados" :key="value" :value="value">
                      {{ label }}
                    </option>
                  </select>
                </div>

                <div class="flex items-end space-x-2">
                  <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition"
                  >
                    Filtrar
                  </button>
                  <button
                    type="button"
                    @click="limpiarFiltros"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition"
                  >
                    Limpiar
                  </button>
                </div>
              </form>
            </div>

            <!-- Tabla de Órdenes -->
            <div v-if="ordenes.data.length > 0" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Número Orden
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Fecha
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Proveedor
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Presupuesto
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Monto
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Estado
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Acciones
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="orden in ordenes.data" :key="orden.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-4 py-3 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ orden.numero_orden }}
                      </div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ orden.created_at }}
                      </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                      {{ orden.fecha_orden }}
                    </td>
                    <td class="px-4 py-3">
                      <div v-if="orden.proveedor" class="text-sm">
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                          {{ orden.proveedor.razon_social }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                          CUIT: {{ orden.proveedor.cuit }}
                        </div>
                      </div>
                      <span v-else class="text-sm text-gray-500">-</span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                      <div v-if="orden.presupuesto" class="text-sm text-gray-900 dark:text-gray-100">
                        {{ orden.presupuesto.numero_presupuesto }}/{{ orden.presupuesto.ejercicio }}
                      </div>
                      <span v-else class="text-sm text-gray-500">-</span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                      <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        ${{ formatearMoneda(orden.monto_total) }}
                      </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                      <span
                        class="px-2 py-1 text-xs font-semibold rounded-full"
                        :class="getEstadoClass(orden.estado)"
                      >
                        {{ getEstadoLabel(orden.estado) }}
                      </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                      <div class="flex items-center space-x-2">
                        <!-- Ver -->
                        <Link
                          :href="route('ordenes-compra.show', orden.id)"
                          class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                          title="Ver detalle"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                          </svg>
                        </Link>

                        <!-- Imprimir PDF -->
                        <a
                          :href="route('ordenes-compra.pdf', orden.id)"
                          target="_blank"
                          class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                          title="Imprimir PDF"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                          </svg>
                        </a>

                        <!-- Anular -->
                        <button
                          v-if="orden.estado !== 'anulada'"
                          @click="anularOrden(orden)"
                          class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                          title="Anular orden"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Paginación -->
              <div class="mt-4">
                <Pagination :links="ordenes.links" />
              </div>
            </div>

            <!-- Sin datos -->
            <div v-else class="text-center py-12">
              <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                No hay órdenes de compra registradas
              </h3>
              <p class="text-gray-600 dark:text-gray-400 mb-4">
                Las órdenes se generan automáticamente desde ofertas seleccionadas.
              </p>
              <Link
                :href="route('presupuestos.index')"
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition"
              >
                Ver Presupuestos
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
  ordenes: Object,
  filters: Object,
  estados: Object
})

const filtros = ref({
  search: props.filters?.search || '',
  estado: props.filters?.estado || ''
})

const aplicarFiltros = () => {
  router.get(route('ordenes-compra.index'), filtros.value, {
    preserveState: true,
    preserveScroll: true
  })
}

const limpiarFiltros = () => {
  filtros.value = {
    search: '',
    estado: ''
  }
  aplicarFiltros()
}

const formatearMoneda = (monto) => {
  if (!monto) return '0.00'
  return parseFloat(monto).toLocaleString('es-AR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

const getEstadoLabel = (estado) => {
  return props.estados[estado] || estado
}

const getEstadoClass = (estado) => {
  const classes = {
    'borrador': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    'aprobada': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    'rechazada': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
    'enviada': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    'recibida': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
    'parcial': 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
    'completada': 'bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-300',
    'anulada': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
  }
  return classes[estado] || classes['borrador']
}

const anularOrden = (orden) => {
  if (!confirm(`¿Está seguro de anular la orden ${orden.numero_orden}?`)) {
    return
  }

  const motivo = prompt('Ingrese el motivo de anulación:')
  if (!motivo) {
    alert('Debe ingresar un motivo para anular la orden')
    return
  }

  router.post(route('ordenes-compra.anular', orden.id), {
    motivo: motivo
  }, {
    preserveScroll: true,
    onSuccess: () => {
      // Mensaje de éxito manejado por flash
    }
  })
}
</script>
