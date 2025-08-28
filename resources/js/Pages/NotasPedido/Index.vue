<template>
  <AppLayout :breadcrumbs="breadcrumbs">
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
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
              <p class="text-gray-600 dark:text-gray-400 mt-1">
                Gestión de solicitudes de insumos para las oficinas
              </p>
            </div>
          </div>
          
          <!-- Botón Crear Nueva Nota -->
          <Link 
            :href="route('notas-pedido.create')"
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 shadow-md hover:shadow-lg flex items-center space-x-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span>Nueva Nota</span>
          </Link>
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
              v-model="filtros.search"
              type="text"
              placeholder="Descripción, expediente..."
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Número de Nota -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Número de Nota
            </label>
            <input
              v-model="filtros.numero"
              type="text"
              placeholder="Ej: 123"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Oficina -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Oficina
            </label>
            <select
              v-model="filtros.oficina_id"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Todas las oficinas</option>
              <option v-for="oficina in oficinas" :key="oficina.id" :value="oficina.id">
                {{ oficina.nombre }}
              </option>
            </select>
          </div>

          <!-- Estado -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Estado
            </label>
            <select
              v-model="filtros.estado"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Todos los estados</option>
              <option value="0">Abierta</option>
              <option value="1">Confirmada</option>
              <option value="2">Rechazada</option>
            </select>
          </div>

          <!-- Expediente -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Expediente
            </label>
            <input
              v-model="filtros.expediente"
              type="text"
              placeholder="Número de expediente"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
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

      <!-- Tabla de Notas de Pedido -->
      <div class="overflow-hidden bg-white dark:bg-gray-800 rounded-xl shadow-lg">
        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Año/Nota
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Fecha
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Oficina
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Descripción
              </th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Total
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
              v-for="nota in notas.data"
              :key="nota.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150"
            >
              <!-- Año/Nota -->
              <td class="px-4 py-4 text-sm">
                <div class="font-bold text-blue-600 dark:text-blue-400">
                  {{ nota.numero_nota }}/{{ nota.ejercicio_nota }}
                </div>
                <div v-if="nota.expediente" class="text-xs text-gray-500 dark:text-gray-400">
                  Exp: {{ nota.expediente }}
                </div>
              </td>

              <!-- Fecha -->
              <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-100">
                {{ formatearFecha(nota.fecha_nota) }}
              </td>

              <!-- Oficina -->
              <td class="px-4 py-4 text-sm">
                <div class="font-medium text-gray-900 dark:text-gray-100">
                  {{ nota.oficina.nombre }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                  {{ nota.tipo_nota.descripcion }}
                </div>
              </td>

              <!-- Descripción -->
              <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-100">
                <div class="max-w-xs truncate" :title="nota.descripcion">
                  {{ nota.descripcion }}
                </div>
              </td>

              <!-- Total -->
              <td class="px-4 py-4 text-sm text-right font-medium text-gray-900 dark:text-gray-100">
                ${{ formatearMoneda(nota.total_nota) }}
              </td>

              <!-- Estado -->
              <td class="px-4 py-4 text-center">
                <span 
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="getEstadoClasses(nota.estado)"
                >
                  {{ getEstadoTexto(nota.estado) }}
                </span>
              </td>

              <!-- Acciones -->
              <td class="px-4 py-4 text-center">
                <div class="flex items-center justify-center space-x-2">
                  <!-- Ver -->
                  <Link
                    :href="route('notas-pedido.show', nota.id)"
                    class="inline-flex items-center p-1.5 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-all"
                    title="Ver nota"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </Link>

                  <!-- Editar (solo notas abiertas) -->
                  <Link
                    v-if="nota.estado === 0"
                    :href="route('notas-pedido.edit', nota.id)"
                    class="inline-flex items-center p-1.5 text-orange-600 hover:text-orange-700 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-md transition-all"
                    title="Editar nota"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </Link>

                  <!-- Confirmar (solo notas abiertas) -->
                  <button
                    v-if="nota.estado === 0"
                    @click="confirmarNota(nota)"
                    class="inline-flex items-center p-1.5 text-green-600 hover:text-green-700 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-md transition-all"
                    title="Confirmar nota"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                  </button>

                  <!-- Reabrir (solo para admin/supervisor en notas confirmadas) -->
                  <button
                    v-if="nota.estado === 1 && puedeReabrir"
                    @click="reabrirNota(nota)"
                    class="inline-flex items-center p-1.5 text-purple-600 hover:text-purple-700 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-md transition-all"
                    title="Reabrir nota"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty state -->
            <tr v-if="notas.data && notas.data.length === 0">
              <td colspan="7" class="px-4 py-12 text-center">
                <div class="text-gray-500 dark:text-gray-400">
                  <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  <h3 class="mt-2 text-sm font-medium">No hay notas de pedido</h3>
                  <p class="mt-1 text-sm">{{ hayFiltros ? 'No se encontraron resultados para tu búsqueda' : 'Comienza creando una nueva nota de pedido' }}</p>
                  <div v-if="!hayFiltros" class="mt-6">
                    <Link
                      :href="route('notas-pedido.create')"
                      class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                    >
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                      </svg>
                      Nueva Nota de Pedido
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
            Mostrando <span class="font-medium">{{ notas.from || 0 }}</span> a <span class="font-medium">{{ notas.to || 0 }}</span> de{' '}
            <span class="font-medium">{{ notas.total || 0 }}</span> notas
          </p>
        </div>

        <nav v-if="notas.links && notas.links.length > 3" class="flex space-x-1">
          <Link 
            v-for="(link, index) in notas.links" 
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
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  title: String,
  notas: {
    type: Object,
    default: () => ({ data: [], links: [], total: 0, from: 0, to: 0 })
  },
  oficinas: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const page = usePage()

const breadcrumbs = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'Notas de Pedido', href: null }
]

// Filtros reactivos
const filtros = ref({
  search: props.filters.search || '',
  numero: props.filters.numero || '',
  oficina_id: props.filters.oficina_id || '',
  expediente: props.filters.expediente || '',
  estado: props.filters.estado || ''
})

// Computed
const hayFiltros = computed(() => {
  return Object.values(filtros.value).some(valor => valor !== '')
})

const puedeReabrir = computed(() => {
  const userRole = page.props.auth?.user?.role?.name
  return userRole === 'administrador' || userRole === 'supervisor'
})

// Métodos
const aplicarFiltros = () => {
  router.get(route('notas-pedido.index'), filtros.value, {
    preserveState: true,
    replace: true
  })
}

const limpiarFiltros = () => {
  filtros.value = {
    search: '',
    numero: '',
    oficina_id: '',
    expediente: '',
    estado: ''
  }
  aplicarFiltros()
}

const formatearFecha = (fecha) => {
  return new Date(fecha).toLocaleDateString('es-AR')
}

const formatearMoneda = (monto) => {
  return parseFloat(monto || 0).toLocaleString('es-AR', { 
    minimumFractionDigits: 2, 
    maximumFractionDigits: 2 
  })
}

const getEstadoTexto = (estado) => {
  const estados = {
    0: 'Abierta',
    1: 'Confirmada',
    2: 'Rechazada'
  }
  return estados[estado] || 'Desconocido'
}

const getEstadoClasses = (estado) => {
  const clases = {
    0: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    1: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    2: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
  }
  return clases[estado] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
}

const confirmarNota = async (nota) => {
  const result = await Swal.fire({
    title: '¿Confirmar Nota de Pedido?',
    text: `¿Estás seguro de que deseas confirmar la nota ${nota.numero_nota}/${nota.ejercicio_nota}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Sí, confirmar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  })

  if (result.isConfirmed) {
    router.patch(route('notas-pedido.confirmar', nota.id), {}, {
      onSuccess: () => {
        Swal.fire({
          title: '¡Confirmada!',
          text: 'La nota de pedido ha sido confirmada correctamente.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false
        })
      },
      onError: () => {
        Swal.fire({
          title: 'Error',
          text: 'No se pudo confirmar la nota de pedido.',
          icon: 'error'
        })
      }
    })
  }
}

const reabrirNota = async (nota) => {
  const result = await Swal.fire({
    title: '¿Reabrir Nota de Pedido?',
    text: `¿Estás seguro de que deseas reabrir la nota ${nota.numero_nota}/${nota.ejercicio_nota}?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#8b5cf6',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Sí, reabrir',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  })

  if (result.isConfirmed) {
    router.patch(route('notas-pedido.reabrir', nota.id), {}, {
      onSuccess: () => {
        Swal.fire({
          title: '¡Reabierta!',
          text: 'La nota de pedido ha sido reabierta correctamente.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false
        })
      },
      onError: () => {
        Swal.fire({
          title: 'Error',
          text: 'No se pudo reabrir la nota de pedido.',
          icon: 'error'
        })
      }
    })
  }
}
</script>