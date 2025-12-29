<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head :title="title" />

    <!-- Header Section -->
    <section class="bg-gray-100 dark:bg-gray-900 pt-4 pb-6">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-xl">
              <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
              </svg>
            </div>
            <div>
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
              <p class="text-gray-600 dark:text-gray-400 mt-1">
                Gestión de presupuestos y cotizaciones de proveedores
              </p>
            </div>
          </div>
          
          <!-- Botones Crear Presupuesto -->
              
          <div class="flex space-x-2">
            <Link
              v-if="filters.nota_pedido_id"
              :href="route('presupuestos.create', { nota_pedido_id: filters.nota_pedido_id })"
              class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 shadow-md hover:shadow-lg flex items-center space-x-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
              <span>Nuevo Presupuesto</span>
            </Link>
            
            <Link
              :href="route('presupuestos.create')"
              class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 shadow-md hover:shadow-lg flex items-center space-x-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
              <span>Nuevo Presupuesto</span>
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- Filtros de Búsqueda -->
    <section class="max-w-7xl mx-auto px-4 py-6">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Filtros de Búsqueda</h3>
        
        <form @submit.prevent="aplicarFiltros" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Búsqueda General -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Búsqueda General
            </label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Buscar en observaciones..."
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Número de Presupuesto -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Número de Presupuesto
            </label>
            <input
              v-model="filters.numero"
              type="text"
              placeholder="Número de presupuesto"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Ejercicio -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Ejercicio
            </label>
            <input
              v-model="filters.ejercicio"
              type="number"
              placeholder="Año"
              :min="2000"
              :max="new Date().getFullYear()"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Estado -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Estado
            </label>
            <select
              v-model="filters.estado"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Todos los estados</option>
              <option value="borrador">Borrador</option>
              <option value="enviado">Enviado</option>
              <option value="aprobado">Aprobado</option>
              <option value="rechazado">Rechazado</option>
              <option value="desierto">Desierto</option>
              <option value="cerrado">Cerrado</option>
              <option value="adjudicado">Adjudicado</option>
              <option value="adjudicado_parcial">Adjudicado Parcial</option>
              <option value="anulado">Anulado</option>
            </select>
          </div>

          <!-- Proveedor -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Proveedor
            </label>
            <select
              v-model="filters.proveedor_id"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Todos los proveedores</option>
              <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">
                {{ proveedor.razon_social }}
              </option>
            </select>
          </div>

          <!-- Nota de Pedido -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Nota de Pedido
            </label>
            <select
              v-model="filters.nota_pedido_id"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Todas las notas</option>
              <option v-for="nota in notasPedido" :key="nota.id" :value="nota.id">
                {{ nota.numero_nota }}/{{ nota.ejercicio_nota }} - {{ nota.descripcion }}
              </option>
            </select>
          </div>

          <!-- Botones -->
          <div class="flex items-end space-x-2">
            <button
              type="submit"
              class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <span>Buscar</span>
            </button>
            
            <button
              @click="limpiarFiltros"
              type="button"
              class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors"
            >
              Limpiar
            </button>
          </div>
        </form>
      </div>

            <!-- Tabla de Presupuestos -->
      <div class="overflow-hidden bg-white dark:bg-gray-800 rounded-xl shadow-lg">
        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Tipo de Presupuesto
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Número
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Nota de Pedido
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Fecha
              </th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Total
              </th>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Días Restantes
              </th>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Estado
              </th>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="presupuesto in presupuestos.data"
              :key="presupuesto.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150"
            >
              <!-- Tipo de Presupuesto -->
              <td class="px-4 py-4 text-sm">
                <div class="font-medium text-gray-900 dark:text-gray-100">
                  {{ presupuesto.tipo_compra?.descripcion || 'N/A' }}
                </div>
              </td>

              <!-- Número -->
              <td class="px-4 py-4 text-sm">
                <div class="font-bold text-blue-600 dark:text-blue-400">
                  {{ presupuesto.numero_presupuesto }}/{{ presupuesto.ejercicio }}
                </div>
              </td>

              <!-- Nota de Pedido -->
              <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-100">
                {{ presupuesto.nota_pedido?.numero_nota }}/{{ presupuesto.nota_pedido?.ejercicio_nota }}
              </td>

              <!-- Fecha -->
              <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-100">
                {{ formatearFecha(presupuesto.fecha_presupuesto) }}
              </td>

              <!-- Total -->
              <td class="px-4 py-4 text-sm text-right font-medium text-gray-900 dark:text-gray-100">
                ${{ formatearMoneda(presupuesto.total_presupuesto) }}
              </td>

              <!-- Días Restantes -->
              <td class="px-4 py-4 text-center">
                <span
                  v-if="presupuesto.dias_restantes !== null"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="getDiasRestantesClass(presupuesto.dias_restantes)"
                >
                  {{ formatearDiasRestantes(presupuesto.dias_restantes) }}
                </span>
                <span v-else class="text-gray-400 text-xs">N/A</span>
              </td>

              <!-- Estado -->
              <td class="px-4 py-4 text-center">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="getEstadoClass(presupuesto.estado)"
                >
                  {{ getEstadoTexto(presupuesto.estado) }}
                </span>
              </td>

              <!-- Acciones -->
              <td class="px-4 py-4 text-center">
                <div class="flex items-center justify-center space-x-2">
                  <!-- Ver -->
                  <Link
                    :href="route('presupuestos.show', presupuesto.id)"
                    class="inline-flex items-center p-1.5 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-all"
                    title="Ver presupuesto"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </Link>

                  <!-- Editar (solo borradores) -->
                  <Link
                    v-if="presupuesto.estado === 'borrador'"
                    :href="route('presupuestos.edit', presupuesto.id)"
                    class="inline-flex items-center p-1.5 text-orange-600 hover:text-orange-700 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-md transition-all"
                    title="Editar presupuesto"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </Link>

                  <!-- Confirmar (solo borradores) -->
                  <button
                    v-if="presupuesto.estado === 'borrador'"
                    @click="enviarPresupuesto(presupuesto)"
                    class="inline-flex items-center p-1.5 text-green-600 hover:text-green-700 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-md transition-all"
                    title="Confirmar presupuesto"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                  </button>

                  <!-- Ver Ofertas -->
                  <Link
                    v-if="presupuesto.ofertas_count > 0"
                    :href="route('presupuestos.ofertas', presupuesto.id)"
                    class="inline-flex items-center p-1.5 text-purple-600 hover:text-purple-700 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-md transition-all"
                    title="Ver ofertas"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </Link>
                  <button
                    v-else
                    disabled
                    class="inline-flex items-center p-1.5 text-gray-400 cursor-not-allowed rounded-md"
                    title="Sin ofertas"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </button>

                  <!-- Anular -->
                  <button
                    v-if="puedeAnular(presupuesto)"
                    @click="anularPresupuesto(presupuesto)"
                    class="inline-flex items-center p-1.5 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-all"
                    title="Anular presupuesto"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty state -->
            <tr v-if="presupuestos.data && presupuestos.data.length === 0">
              <td colspan="8" class="px-4 py-12 text-center">
                <div class="text-gray-500 dark:text-gray-400">
                  <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                  </svg>
                  <h3 class="mt-2 text-sm font-medium">No hay presupuestos</h3>
                  <p class="mt-1 text-sm">Comienza creando un nuevo presupuesto</p>
                  <div class="mt-6">
                    <Link
                      :href="route('presupuestos.create')"
                      class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                    >
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                      </svg>
                      Nuevo Presupuesto
                    </Link>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-700 dark:text-gray-300">
          <p>
            Mostrando <span class="font-medium">{{ presupuestos.from || 0 }}</span> a <span class="font-medium">{{ presupuestos.to || 0 }}</span> de{' '}
            <span class="font-medium">{{ presupuestos.total || 0 }}</span> presupuestos
          </p>
        </div>

        <nav v-if="presupuestos.links && presupuestos.links.length > 3" class="flex space-x-1">
          <Link 
            v-for="(link, index) in presupuestos.links" 
            :key="index"
            :href="link.url || '#'"
            :class="[
              'px-3 py-2 rounded-md border text-sm font-medium transition-colors duration-150',
              link.active 
                ? 'bg-blue-600 text-white border-blue-600' 
                : link.url 
                  ? 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600' 
                  : 'bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed border-gray-200 dark:border-gray-700'
            ]"
            v-html="link.label"
          ></Link>
        </nav>
      </div>
    </section>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  title: String,
  presupuestos: Object,
  proveedores: Array,
  notasPedido: Array,
  filters: Object
})

const breadcrumbs = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'Presupuestos', href: null }
]

const filters = ref({ ...props.filters })

watch(filters, (newFilters) => {
  router.get(route('presupuestos.index'), newFilters, {
    preserveState: true,
    replace: true
  })
}, { deep: true })

// Computed
const hayFiltros = computed(() => {
  return Object.values(filters.value).some(valor => valor !== '')
})

// Métodos
const aplicarFiltros = () => {
  router.get(route('presupuestos.index'), filters.value, {
    preserveState: true,
    replace: true
  })
}

const limpiarFiltros = () => {
  filters.value = {
    search: '',
    numero: '',
    ejercicio: '',
    estado: '',
    proveedor_id: '',
    nota_pedido_id: ''
  }
  aplicarFiltros()
}

const formatearFecha = (fecha) => {
  if (!fecha) return ''
  return new Date(fecha).toLocaleDateString('es-AR')
}

const formatearMoneda = (monto) => {
  if (!monto) return '0.00'
  return parseFloat(monto || 0).toLocaleString('es-AR', { 
    minimumFractionDigits: 2, 
    maximumFractionDigits: 2 
  })
}

const getEstadoTexto = (estado) => {
  const estados = {
    borrador: 'Borrador',
    enviado: 'Enviado',
    aprobado: 'Aprobado',
    rechazado: 'Rechazado',
    desierto: 'Desierto',
    cerrado: 'Cerrado',
    adjudicado: 'Adjudicado',
    adjudicado_parcial: 'Adjudicado Parcial',
    anulado: 'Anulado'
  }
  return estados[estado] || estado
}

const getEstadoClass = (estado) => {
  const clases = {
    borrador: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    enviado: 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100',
    aprobado: 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100',
    rechazado: 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100',
    desierto: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100',
    cerrado: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    adjudicado: 'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100',
    adjudicado_parcial: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-100',
    anulado: 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100'
  }
  return clases[estado] || 'bg-gray-100 text-gray-800'
}

const enviarPresupuesto = (presupuesto) => {
  if (confirm('¿Está seguro de confirmar este presupuesto? Una vez confirmado no podrá ser editado.')) {
    router.post(route('presupuestos.enviar', presupuesto.id), {}, {
      preserveScroll: true,
      onSuccess: () => {
        // Recargar la página para actualizar el estado
        router.reload({ only: ['presupuestos'] })
      }
    })
  }
}

const puedeAnular = (presupuesto) => {
  return (presupuesto.estado === 'borrador' || presupuesto.estado === 'aprobado') &&
         presupuesto.estado !== 'adjudicado' &&
         presupuesto.estado !== 'adjudicado_parcial'
}

const anularPresupuesto = (presupuesto) => {
  if (confirm(`¿Está seguro de anular el presupuesto ${presupuesto.numero_presupuesto}/${presupuesto.ejercicio}? Esta acción liberará los insumos asociados y no se podrá deshacer.`)) {
    router.post(route('presupuestos.anular', presupuesto.id), {}, {
      preserveScroll: true,
      onSuccess: () => {
        // Recargar la página para actualizar el estado
        router.reload({ only: ['presupuestos'] })
      }
    })
  }
}

const formatearDiasRestantes = (dias) => {
  if (dias === null || dias === undefined) return 'N/A'

  if (dias < 0) {
    return `Vencido (${Math.abs(dias)} días)`
  } else if (dias === 0) {
    return 'Vence hoy'
  } else if (dias === 1) {
    return '1 día'
  } else {
    return `${dias} días`
  }
}

const getDiasRestantesClass = (dias) => {
  if (dias === null || dias === undefined) {
    return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
  }

  if (dias < 0) {
    // Vencido - rojo
    return 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100'
  } else if (dias === 0) {
    // Vence hoy - naranja oscuro
    return 'bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-100'
  } else if (dias <= 7) {
    // Quedan 7 días o menos - amarillo
    return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100'
  } else if (dias <= 15) {
    // Quedan entre 8 y 15 días - azul claro
    return 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100'
  } else {
    // Más de 15 días - verde
    return 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
  }
}
</script>