<template>
  <AppLayout>
    <Head :title="title" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                {{ title }}
              </h2>

              <div class="flex space-x-2">
                <Link
                  :href="route('presupuestos.index')"
                  class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Ver Presupuestos
                </Link>

                <Link
                  :href="route('ofertas.create')"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                  Nueva Oferta
                </Link>
              </div>
            </div>

            <!-- Estadísticas -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
              <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-700">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Total</h3>
                <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ estadisticas.total }}</p>
              </div>

              <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-700">
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Pendientes</h3>
                <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ estadisticas.pendientes }}</p>
              </div>

              <div class="bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-lg border border-indigo-200 dark:border-indigo-700">
                <h3 class="text-sm font-medium text-indigo-800 dark:text-indigo-200">Evaluando</h3>
                <p class="text-2xl font-bold text-indigo-900 dark:text-indigo-100">{{ estadisticas.evaluando }}</p>
              </div>

              <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-700">
                <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Aprobadas</h3>
                <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ estadisticas.aprobadas }}</p>
              </div>

              <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border border-red-200 dark:border-red-700">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Rechazadas</h3>
                <p class="text-2xl font-bold text-red-900 dark:text-red-100">{{ estadisticas.rechazadas }}</p>
              </div>

              <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-700">
                <h3 class="text-sm font-medium text-purple-800 dark:text-purple-200">Adjudicadas</h3>
                <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">{{ estadisticas.adjudicadas }}</p>
              </div>

              <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg border border-orange-200 dark:border-orange-700">
                <h3 class="text-sm font-medium text-orange-800 dark:text-orange-200">No Adjudicadas</h3>
                <p class="text-2xl font-bold text-orange-900 dark:text-orange-100">{{ estadisticas.no_adjudicadas || 0 }}</p>
              </div>
            </div>

            <!-- Filtros -->
            <div class="mb-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
              <form @submit.prevent="aplicarFiltros" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                  <InputLabel for="busqueda" value="Buscar" />
                  <TextInput
                    id="busqueda"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.busqueda"
                    placeholder="N° oferta, proveedor..."
                  />
                </div>

                <div>
                  <InputLabel for="estado" value="Estado" />
                  <select
                    id="estado"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    v-model="form.estado"
                  >
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="evaluando">En evaluación</option>
                    <option value="aprobada">Aprobada</option>
                    <option value="rechazada">Rechazada</option>
                    <option value="adjudicada">Adjudicada</option>
                    <option value="no_adjudicada">No Adjudicada</option>
                  </select>
                </div>

                <div>
                  <InputLabel for="proveedor" value="Proveedor" />
                  <TextInput
                    id="proveedor"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.proveedor"
                    placeholder="Nombre del proveedor"
                  />
                </div>

                <div>
                  <InputLabel for="presupuesto_id" value="Presupuesto" />
                  <select
                    id="presupuesto_id"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    v-model="form.presupuesto_id"
                  >
                    <option value="">Todos</option>
                    <option v-for="presupuesto in presupuestos" :key="presupuesto.id" :value="presupuesto.id">
                      {{ presupuesto.numero_presupuesto }}/{{ presupuesto.ejercicio }}
                    </option>
                  </select>
                </div>

                <div class="flex items-end space-x-2">
                  <PrimaryButton type="submit">
                    Filtrar
                  </PrimaryButton>

                  <button
                    type="button"
                    @click="limpiarFiltros"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                  >
                    Limpiar
                  </button>
                </div>
              </form>
            </div>

            <!-- Tabla de ofertas -->
            <div v-if="ofertas.data.length > 0" class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      N° Oferta
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Presupuesto
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Proveedor
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Fecha
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Monto
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Estado
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Acciones
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="oferta in ofertas.data" :key="oferta.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                      {{ oferta.numero_oferta }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                      <div v-if="oferta.presupuesto">
                        <div class="font-medium">{{ oferta.presupuesto.numero_presupuesto }}/{{ oferta.presupuesto.ejercicio }}</div>
                        <div v-if="oferta.presupuesto.nota_pedido" class="text-xs text-gray-500 dark:text-gray-400">
                          NP: {{ oferta.presupuesto.nota_pedido.numero_nota }}
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                      <div v-if="oferta.proveedor">
                        <div class="font-medium">{{ oferta.proveedor.razon_social }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ oferta.proveedor.cuit }}</div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                      {{ formatearFecha(oferta.fecha_oferta) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100">
                      <div class="font-semibold">${{ formatearMoneda(oferta.monto_total) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getEstadoClass(oferta.estado_efectivo || oferta.estado)">
                        {{ getEstadoTexto(oferta.estado_efectivo || oferta.estado) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                      <div class="flex justify-center space-x-2">
                        <Link
                          :href="route('ofertas.show', oferta.id)"
                          class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                          title="Ver detalle"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                          </svg>
                        </Link>

                        <Link
                          v-if="puedeEditar(oferta)"
                          :href="route('ofertas.edit', oferta.id)"
                          class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                          title="Editar"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </Link>

                        <button
                          v-if="puedeEditar(oferta)"
                          @click="confirmarEliminar(oferta)"
                          class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                          title="Eliminar"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Paginación -->
              <div v-if="ofertas.last_page > 1" class="mt-6">
                <Pagination :links="ofertas.links" />
              </div>
            </div>

            <!-- Sin datos -->
            <div v-else class="text-center py-12">
              <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                No hay ofertas registradas
              </h3>
              <p class="text-gray-600 dark:text-gray-400 mb-4">
                {{ filtros.busqueda || filtros.estado || filtros.proveedor || filtros.presupuesto_id ? 'No se encontraron ofertas con los filtros aplicados.' : 'Comience creando una nueva oferta para un presupuesto aprobado.' }}
              </p>
              <Link
                :href="route('ofertas.create')"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Crear Primera Oferta
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
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import Pagination from '@/Components/Pagination.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  title: String,
  ofertas: Object,
  estadisticas: Object,
  filtros: Object,
  presupuestos: Array,
  proveedores: Array
})

const form = ref({
  busqueda: props.filtros?.busqueda || '',
  estado: props.filtros?.estado || '',
  proveedor: props.filtros?.proveedor || '',
  presupuesto_id: props.filtros?.presupuesto_id || ''
})

const aplicarFiltros = () => {
  router.get(route('ofertas.index'), form.value, {
    preserveState: true,
    preserveScroll: true
  })
}

const limpiarFiltros = () => {
  form.value = {
    busqueda: '',
    estado: '',
    proveedor: '',
    presupuesto_id: ''
  }
  router.get(route('ofertas.index'), {}, {
    preserveState: true,
    preserveScroll: true
  })
}

const formatearFecha = (fecha) => {
  if (!fecha) return '-'
  return new Date(fecha).toLocaleDateString('es-AR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const formatearMoneda = (monto) => {
  if (!monto) return '0.00'
  return parseFloat(monto).toLocaleString('es-AR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

const getEstadoTexto = (estado) => {
  const estados = {
    'pendiente': 'Pendiente',
    'evaluando': 'En Evaluación',
    'aprobada': 'Aprobada',
    'rechazada': 'Rechazada',
    'adjudicada': 'Adjudicada',
    'no_adjudicada': 'No Adjudicada'
  }
  return estados[estado] || estado
}

const getEstadoClass = (estado) => {
  const clases = {
    'pendiente': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100',
    'evaluando': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-100',
    'aprobada': 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100',
    'rechazada': 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100',
    'adjudicada': 'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100',
    'no_adjudicada': 'bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-100'
  }
  return clases[estado] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100'
}

// Verificar si la oferta puede ser editada o eliminada
// Usa el atributo puede_modificar del backend que considera estado del presupuesto
const puedeEditar = (oferta) => {
  // Si viene del backend, usar ese valor
  if (oferta.puede_modificar !== undefined) {
    return oferta.puede_modificar
  }
  // Fallback al cálculo local
  return oferta.estado === 'pendiente' || oferta.estado === 'evaluando'
}

const confirmarEliminar = (oferta) => {
  Swal.fire({
    title: '¿Eliminar oferta?',
    text: `Se eliminará la oferta ${oferta.numero_oferta}. Esta acción no se puede deshacer.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('ofertas.destroy', oferta.id), {
        preserveScroll: true,
        onSuccess: () => {
          Swal.fire('Eliminada', 'La oferta ha sido eliminada exitosamente.', 'success')
        },
        onError: () => {
          Swal.fire('Error', 'No se pudo eliminar la oferta.', 'error')
        }
      })
    }
  })
}
</script>
