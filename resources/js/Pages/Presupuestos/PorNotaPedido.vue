<template>
  <AppLayout>
    <Head :title="title" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
              <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                  {{ title }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                  Oficina: {{ notaPedido.oficina?.nombre }} - 
                  Tipo: {{ notaPedido.tipoNota?.nombre }}
                </p>
              </div>
              
              <div class="flex space-x-2">
                <Link
                  :href="route('presupuestos.create', { nota_pedido_id: notaPedido.id })"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Nuevo Presupuesto
                </Link>
                
                <Link
                  :href="route('presupuestos.comparar', notaPedido.id)"
                  class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Comparar Presupuestos
                </Link>
                
                <Link
                  :href="route('notas-pedido.show', notaPedido.id)"
                  class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Volver a Nota
                </Link>
              </div>
            </div>

            <!-- Información de la nota de pedido -->
            <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Descripción:</p>
                  <p class="text-blue-700 dark:text-blue-300">{{ notaPedido.descripcion }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Expediente:</p>
                  <p class="text-blue-700 dark:text-blue-300">{{ notaPedido.expediente || 'Sin expediente' }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Total Estimado:</p>
                  <p class="text-blue-700 dark:text-blue-300">${{ formatNumber(notaPedido.total_estimado) }}</p>
                </div>
              </div>
            </div>

            <!-- Filtros -->
            <div class="mb-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
              <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <InputLabel for="search" value="Buscar proveedor" />
                  <TextInput
                    id="search"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="filters.search"
                    placeholder="Nombre o razón social"
                  />
                </div>

                <div>
                  <InputLabel for="estado" value="Estado" />
                  <select
                    id="estado"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    v-model="filters.estado"
                  >
                    <option value="">Todos los estados</option>
                    <option value="borrador">Borrador</option>
                    <option value="enviado">Enviado</option>
                    <option value="aprobado">Aprobado</option>
                    <option value="rechazado">Rechazado</option>
                  </select>
                </div>

                <div class="flex items-end space-x-2">
                  <PrimaryButton type="submit">
                    Aplicar Filtros
                  </PrimaryButton>
                  
                  <button
                    type="button"
                    @click="resetFilters"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                  >
                    Limpiar
                  </button>
                </div>
              </form>
            </div>

            <!-- Tabla de presupuestos -->
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Número
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Proveedor
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Fecha
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Total
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Estado
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Creado por
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Acciones
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="presupuesto in presupuestos.data" :key="presupuesto.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                      {{ presupuesto.numero_presupuesto }}/{{ presupuesto.ejercicio }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                      {{ presupuesto.proveedor?.razon_social }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                      {{ formatDate(presupuesto.fecha_presupuesto) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                      ${{ formatNumber(presupuesto.total_presupuesto) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span 
                        :class="[
                          'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                          getEstadoClass(presupuesto.estado)
                        ]"
                      >
                        {{ getEstadoTexto(presupuesto.estado) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                      {{ presupuesto.usuario?.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex space-x-2">
                        <Link
                          :href="route('presupuestos.show', presupuesto.id)"
                          class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                          Ver
                        </Link>
                        
                        <Link
                          v-if="presupuesto.estado === 'borrador'"
                          :href="route('presupuestos.edit', presupuesto.id)"
                          class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                        >
                          Editar
                        </Link>
                        
                        <button
                          v-if="presupuesto.estado === 'borrador'"
                          @click="enviarPresupuesto(presupuesto)"
                          class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                        >
                          Enviar
                        </button>
                        
                        <button
                          v-if="presupuesto.estado === 'enviado'"
                          @click="aprobarPresupuesto(presupuesto)"
                          class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                          Aprobar
                        </button>
                        
                        <button
                          v-if="presupuesto.estado === 'enviado'"
                          @click="rechazarPresupuesto(presupuesto)"
                          class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                        >
                          Rechazar
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Paginación -->
            <div class="mt-6">
              <Pagination :links="presupuestos.links" />
            </div>

            <!-- Resumen -->
            <div v-if="presupuestos.data.length > 0" class="mt-6 bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
              <h3 class="text-lg font-medium text-green-800 dark:text-green-200 mb-2">
                Resumen de Presupuestos
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                  <p class="text-sm font-medium text-green-700 dark:text-green-300">Total Presupuestos:</p>
                  <p class="text-lg font-semibold text-green-800 dark:text-green-200">{{ presupuestos.total }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-green-700 dark:text-green-300">Borradores:</p>
                  <p class="text-lg font-semibold text-green-800 dark:text-green-200">
                    {{ contarPorEstado('borrador') }}
                  </p>
                </div>
                <div>
                  <p class="text-sm font-medium text-green-700 dark:text-green-300">Enviados:</p>
                  <p class="text-lg font-semibold text-green-800 dark:text-green-200">
                    {{ contarPorEstado('enviado') }}
                  </p>
                </div>
                <div>
                  <p class="text-sm font-medium text-green-700 dark:text-green-300">Aprobados:</p>
                  <p class="text-lg font-semibold text-green-800 dark:text-green-200">
                    {{ contarPorEstado('aprobado') }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Sin presupuestos -->
            <div v-else class="text-center py-12">
              <div class="text-gray-400 dark:text-gray-500">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No hay presupuestos</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                  Comience creando el primer presupuesto para esta nota de pedido.
                </p>
                <div class="mt-6">
                  <Link
                    :href="route('presupuestos.create', { nota_pedido_id: notaPedido.id })"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                  >
                    Crear Primer Presupuesto
                  </Link>
                </div>
              </div>
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
import { ref, watch } from 'vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
  title: String,
  presupuestos: Object,
  notaPedido: Object,
  filters: Object
})

const filters = ref({ ...props.filters })

watch(filters, (newFilters) => {
  router.get(route('presupuestos.por-nota-pedido', props.notaPedido.id), newFilters, {
    preserveState: true,
    replace: true
  })
}, { deep: true })

const applyFilters = () => {
  router.get(route('presupuestos.por-nota-pedido', props.notaPedido.id), filters.value, {
    preserveState: true,
    replace: true
  })
}

const resetFilters = () => {
  filters.value = {
    search: '',
    estado: ''
  }
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('es-AR')
}

const formatNumber = (number) => {
  if (!number) return '0.00'
  return new Intl.NumberFormat('es-AR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(number)
}

const getEstadoTexto = (estado) => {
  const estados = {
    borrador: 'Borrador',
    enviado: 'Enviado',
    aprobado: 'Aprobado',
    rechazado: 'Rechazado'
  }
  return estados[estado] || estado
}

const getEstadoClass = (estado) => {
  const clases = {
    borrador: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    enviado: 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100',
    aprobado: 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100',
    rechazado: 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100'
  }
  return clases[estado] || 'bg-gray-100 text-gray-800'
}

const contarPorEstado = (estado) => {
  return props.presupuestos.data.filter(p => p.estado === estado).length
}

const enviarPresupuesto = (presupuesto) => {
  if (confirm('¿Está seguro de enviar este presupuesto? Una vez enviado no podrá ser editado.')) {
    router.post(route('presupuestos.enviar', presupuesto.id), {}, {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ['presupuestos'] })
      }
    })
  }
}

const aprobarPresupuesto = (presupuesto) => {
  const observaciones = prompt('Ingrese observaciones de aprobación (opcional):')
  if (observaciones !== null) {
    router.post(route('presupuestos.aprobar', presupuesto.id), {
      observaciones: observaciones
    }, {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ['presupuestos'] })
      }
    })
  }
}

const rechazarPresupuesto = (presupuesto) => {
  const motivo = prompt('Ingrese el motivo del rechazo (requerido):')
  if (motivo && motivo.trim()) {
    router.post(route('presupuestos.rechazar', presupuesto.id), {
      motivo: motivo
    }, {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ['presupuestos'] })
      }
    })
  }
}
</script>