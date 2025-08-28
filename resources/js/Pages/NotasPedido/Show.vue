<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- Header Section -->
    <section class="bg-gray-100 dark:bg-gray-900 pt-4 pb-6">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-xl">
              <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </div>
            <div>
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
              <p class="text-gray-600 dark:text-gray-400 mt-1">
                Detalles de la solicitud de insumos
              </p>
            </div>
          </div>
          
          <!-- Botones de acción -->
          <div class="flex items-center space-x-3">
            <!-- Editar (solo si está abierta) -->
            <Link
              v-if="nota.estado === 0"
              :href="route('notas-pedido.edit', nota.id)"
              class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 shadow-md hover:shadow-lg flex items-center space-x-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              <span>Editar</span>
            </Link>

            <!-- Confirmar (solo si está abierta) -->
            <button
              v-if="nota.estado === 0"
              @click="confirmarNota"
              class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 shadow-md hover:shadow-lg flex items-center space-x-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              <span>Confirmar</span>
            </button>

            <!-- Volver -->
            <Link 
              :href="route('notas-pedido.index')"
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
      <!-- Estado y datos principales -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-start justify-between mb-6">
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
              Nota {{ nota.numero_nota }}/{{ nota.ejercicio_nota }}
            </h2>
            <div class="flex items-center space-x-4">
              <span 
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                :class="getEstadoClasses(nota.estado)"
              >
                {{ getEstadoTexto(nota.estado) }}
              </span>
              <span class="text-sm text-gray-500 dark:text-gray-400">
                Creada el {{ formatearFecha(nota.fecha_nota) }}
              </span>
            </div>
          </div>
          
          <!-- Total -->
          <div class="text-right">
            <div class="text-sm text-gray-500 dark:text-gray-400">Total</div>
            <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
              ${{ formatearMoneda(nota.total_nota) }}
            </div>
          </div>
        </div>

        <!-- Grid de información -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Oficina -->
          <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
            <div class="flex items-center space-x-3">
              <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
              </div>
              <div>
                <div class="text-sm font-medium text-gray-900 dark:text-white">Oficina Solicitante</div>
                <div class="text-sm text-gray-600 dark:text-gray-300">{{ nota.oficina.nombre }}</div>
              </div>
            </div>
          </div>

          <!-- Tipo de Nota -->
          <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
            <div class="flex items-center space-x-3">
              <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
              </div>
              <div>
                <div class="text-sm font-medium text-gray-900 dark:text-white">Tipo de Nota</div>
                <div class="text-sm text-gray-600 dark:text-gray-300">{{ nota.tipo_nota.descripcion }}</div>
              </div>
            </div>
          </div>

          <!-- Usuario Solicitante -->
          <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
            <div class="flex items-center space-x-3">
              <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
              </div>
              <div>
                <div class="text-sm font-medium text-gray-900 dark:text-white">Solicitado por</div>
                <div class="text-sm text-gray-600 dark:text-gray-300">{{ nota.usuario.name }}</div>
              </div>
            </div>
          </div>

          <!-- Expediente (si existe) -->
          <div v-if="nota.expediente" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
            <div class="flex items-center space-x-3">
              <div class="p-2 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </div>
              <div>
                <div class="text-sm font-medium text-gray-900 dark:text-white">Expediente</div>
                <div class="text-sm text-gray-600 dark:text-gray-300">{{ nota.expediente }}</div>
              </div>
            </div>
          </div>

          <!-- Información de confirmación (si está confirmada) -->
          <div v-if="nota.estado === 1 && nota.confirmado_por" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
            <div class="flex items-center space-x-3">
              <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div>
                <div class="text-sm font-medium text-gray-900 dark:text-white">Confirmada por</div>
                <div class="text-sm text-gray-600 dark:text-gray-300">{{ nota.confirmado_por.name }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ formatearFecha(nota.fecha_confirmacion) }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Descripción -->
        <div class="mt-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Descripción</h3>
          <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ nota.descripcion }}</p>
          </div>
        </div>
      </div>

      <!-- Detalle de Insumos -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Detalle de Insumos</h2>
        
        <!-- Tabla de insumos -->
        <div class="overflow-x-auto">
          <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  #
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Insumo
                </th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Cantidad
                </th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Precio Unit.
                </th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Total
                </th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Detalles
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr
                v-for="detalle in nota.detalles"
                :key="detalle.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150"
              >
                <!-- Renglón -->
                <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-100 font-medium">
                  {{ detalle.renglon }}
                </td>

                <!-- Información del insumo -->
                <td class="px-4 py-4">
                  <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ detalle.insumo.codigo }} - {{ detalle.insumo.descripcion }}
                  </div>
                  <div v-if="detalle.insumo.clasificacion_economica" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ detalle.insumo.clasificacion }} - {{ detalle.insumo.clasificacion_economica.descripcion }}
                  </div>
                  <div v-if="detalle.insumo.unidad_solicitud" class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                    Unidad: {{ detalle.insumo.unidad_solicitud }}
                  </div>
                </td>

                <!-- Cantidad -->
                <td class="px-4 py-4 text-sm text-right text-gray-900 dark:text-gray-100 font-medium">
                  {{ formatearNumero(detalle.cantidad) }}
                </td>

                <!-- Precio unitario -->
                <td class="px-4 py-4 text-sm text-right text-gray-900 dark:text-gray-100 font-medium">
                  ${{ formatearMoneda(detalle.precio) }}
                </td>

                <!-- Total del renglón -->
                <td class="px-4 py-4 text-sm text-right text-gray-900 dark:text-gray-100 font-bold">
                  ${{ formatearMoneda(detalle.total_renglon) }}
                </td>

                <!-- Comentario -->
                <td class="px-4 py-4 text-center">
                  <button
                    v-if="detalle.comentario"
                    @click="mostrarComentario(detalle)"
                    class="inline-flex items-center p-1.5 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-all"
                    title="Ver comentario"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10m0 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v2m0 0v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                  </button>
                  <span v-else class="text-gray-400 dark:text-gray-500">-</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Total final -->
        <div class="mt-6 flex justify-end">
          <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6">
            <div class="text-right">
              <div class="text-sm text-gray-600 dark:text-gray-400">Total de la nota</div>
              <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                ${{ formatearMoneda(nota.total_nota) }}
              </div>
              <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                {{ nota.detalles.length }} insumo{{ nota.detalles.length !== 1 ? 's' : '' }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Modal para mostrar comentario -->
    <div
      v-if="comentarioModal.mostrar"
      class="fixed inset-0 z-50 overflow-y-auto"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div
          class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          aria-hidden="true"
          @click="cerrarComentarioModal"
        ></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/20 sm:mx-0 sm:h-10 sm:w-10">
              <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10m0 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v2m0 0v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
              </svg>
            </div>
            
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
              <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                Comentario del Insumo
              </h3>
              <div class="mt-2">
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                  {{ comentarioModal.insumo }}
                </p>
                <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                  <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                    {{ comentarioModal.comentario }}
                  </p>
                </div>
              </div>
            </div>

            <button
              @click="cerrarComentarioModal"
              type="button"
              class="ml-auto -mx-1.5 -my-1.5 bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 inline-flex h-8 w-8"
            >
              <span class="sr-only">Cerrar</span>
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
              </svg>
            </button>
          </div>

          <div class="mt-5 sm:mt-6">
            <button
              @click="cerrarComentarioModal"
              type="button"
              class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm"
            >
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  title: String,
  nota: {
    type: Object,
    required: true
  }
})

const breadcrumbs = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'Notas de Pedido', href: '/notas-pedido' },
  { name: `Nota ${props.nota.numero_nota}/${props.nota.ejercicio_nota}`, href: null }
]

// Estado para el modal de comentario
const comentarioModal = ref({
  mostrar: false,
  insumo: '',
  comentario: ''
})

// Métodos
const formatearFecha = (fecha) => {
  return new Date(fecha).toLocaleDateString('es-AR')
}

const formatearMoneda = (monto) => {
  return parseFloat(monto || 0).toLocaleString('es-AR', { 
    minimumFractionDigits: 2, 
    maximumFractionDigits: 2 
  })
}

const formatearNumero = (numero) => {
  return parseFloat(numero || 0).toLocaleString('es-AR', { 
    minimumFractionDigits: 0, 
    maximumFractionDigits: 4 
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

const mostrarComentario = (detalle) => {
  comentarioModal.value = {
    mostrar: true,
    insumo: `${detalle.insumo.codigo} - ${detalle.insumo.descripcion}`,
    comentario: detalle.comentario
  }
}

const cerrarComentarioModal = () => {
  comentarioModal.value = {
    mostrar: false,
    insumo: '',
    comentario: ''
  }
}

const confirmarNota = async () => {
  const result = await Swal.fire({
    title: '¿Confirmar Nota de Pedido?',
    text: `¿Estás seguro de que deseas confirmar la nota ${props.nota.numero_nota}/${props.nota.ejercicio_nota}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#10b981',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Sí, confirmar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  })

  if (result.isConfirmed) {
    router.patch(route('notas-pedido.confirmar', props.nota.id), {}, {
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
</script>