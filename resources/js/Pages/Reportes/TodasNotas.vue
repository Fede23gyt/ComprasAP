<template>
  <AuthenticatedLayout>
    <Head title="Todas las Notas de Pedido" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
              <div>
                <h2 class="text-2xl font-bold">Todas las Notas de Pedido</h2>
                <p class="text-gray-600 dark:text-gray-400">
                  Reporte completo del sistema con todas las notas de pedido
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

            <!-- Filtros Avanzados -->
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-6">
              <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Filtros Avanzados</h3>
                <button
                  @click="mostrarFiltrosAvanzados = !mostrarFiltrosAvanzados"
                  class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm"
                >
                  {{ mostrarFiltrosAvanzados ? 'Ocultar' : 'Mostrar más filtros' }}
                </button>
              </div>
              
              <form @submit.prevent="buscar" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Filtros Básicos -->
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
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Oficina
                  </label>
                  <select
                    v-model="filtros.oficina_id"
                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md"
                  >
                    <option value="">Todas las oficinas</option>
                    <option v-for="oficina in oficinas" :key="oficina.id" :value="oficina.id">
                      {{ oficina.nombre }}
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

                <!-- Filtros Avanzados (se muestran con toggle) -->
                <template v-if="mostrarFiltrosAvanzados">
                  <div class="md:col-span-5 grid grid-cols-1 md:grid-cols-4 gap-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Importe Mínimo
                      </label>
                      <input
                        v-model="filtros.importe_min"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Importe Máximo
                      </label>
                      <input
                        v-model="filtros.importe_max"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="999999.99"
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Tipo de Nota
                      </label>
                      <select
                        v-model="filtros.tipo_nota_id"
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md"
                      >
                        <option value="">Todos los tipos</option>
                        <option v-for="tipo in tiposNota" :key="tipo.id" :value="tipo.id">
                          {{ tipo.descripcion }}
                        </option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Ordenar por
                      </label>
                      <select
                        v-model="filtros.ordenar_por"
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md"
                      >
                        <option value="created_at">Fecha Creación</option>
                        <option value="importe_total">Importe Total</option>
                        <option value="numero">Número</option>
                        <option value="estado">Estado</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Dirección orden
                      </label>
                      <select
                        v-model="filtros.orden_direccion"
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md"
                      >
                        <option value="desc">Descendente</option>
                        <option value="asc">Ascendente</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Resultados por página
                      </label>
                      <select
                        v-model="filtros.per_page"
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md"
                      >
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Búsqueda por texto
                      </label>
                      <input
                        v-model="filtros.busqueda"
                        type="text"
                        placeholder="Buscar en observaciones..."
                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md"
                      />
                    </div>
                    <div class="flex items-end">
                      <button
                        type="button"
                        @click="aplicarFiltrosAvanzados"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md"
                      >
                        Aplicar Filtros
                      </button>
                    </div>
                  </div>
                </template>
              </form>
            </div>

            <!-- Resumen Estadístico -->
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
              <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4 text-center">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Total</h3>
                <p class="text-2xl font-bold text-blue-900 dark:text-blue-200">
                  {{ estadisticas.total }}
                </p>
              </div>
              <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-700 rounded-lg p-4 text-center">
                <h3 class="text-sm font-medium text-gray-800 dark:text-gray-300">Borrador</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-200">
                  {{ estadisticas.borrador }}
                </p>
              </div>
              <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 text-center">
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Pendientes</h3>
                <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-200">
                  {{ estadisticas.pendiente }}
                </p>
              </div>
              <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4 text-center">
                <h3 class="text-sm font-medium text-green-800 dark:text-green-300">Confirmadas</h3>
                <p class="text-2xl font-bold text-green-900 dark:text-green-200">
                  {{ estadisticas.confirmada }}
                </p>
              </div>
              <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-4 text-center">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Rechazadas</h3>
                <p class="text-2xl font-bold text-red-900 dark:text-red-200">
                  {{ estadisticas.rechazada }}
                </p>
              </div>
              <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-lg p-4 text-center">
                <h3 class="text-sm font-medium text-purple-800 dark:text-purple-300">Completadas</h3>
                <p class="text-2xl font-bold text-purple-900 dark:text-purple-200">
                  {{ estadisticas.completada }}
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
                      Usuario
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
                      {{ nota.usuario?.name }}
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
                        class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 mr-2"
                      >
                        Editar
                      </Link>
                      <Link 
                        v-if="['administrador', 'supervisor'].includes(userRole) && nota.estado === 'pendiente'"
                        :href="route('notas-pedido.confirmar')"
                        class="text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300"
                      >
                        Confirmar
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
  oficinas: Array,
  estadisticas: Object,
  userRole: String,
  tiposNota: Array
})

const filtros = ref({ 
  ...props.filtros,
  importe_min: props.filtros.importe_min || '',
  importe_max: props.filtros.importe_max || '',
  tipo_nota_id: props.filtros.tipo_nota_id || '',
  ordenar_por: props.filtros.ordenar_por || 'created_at',
  orden_direccion: props.filtros.orden_direccion || 'desc',
  per_page: props.filtros.per_page || '20',
  busqueda: props.filtros.busqueda || ''
})

const mostrarFiltrosAvanzados = ref(false)

function buscar() {
  router.get(route('reportes.todas-notas'), filtros.value, {
    preserveState: true,
    preserveScroll: true
  })
}

function limpiarFiltros() {
  filtros.value = {
    fecha_desde: '',
    fecha_hasta: '',
    estado: '',
    oficina_id: '',
    importe_min: '',
    importe_max: '',
    tipo_nota_id: '',
    ordenar_por: 'created_at',
    orden_direccion: 'desc',
    per_page: '20',
    busqueda: ''
  }
  buscar()
}

function aplicarFiltrosAvanzados() {
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