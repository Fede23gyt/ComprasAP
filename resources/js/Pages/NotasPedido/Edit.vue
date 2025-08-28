<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- Header Section -->
    <section class="bg-gray-100 dark:bg-gray-900 pt-4 pb-6">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="p-2 bg-orange-100 dark:bg-orange-900/20 rounded-xl">
              <svg class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </div>
            <div>
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
              <p class="text-gray-600 dark:text-gray-400 mt-1">
                Modificar solicitud de insumos • Estado: {{ getEstadoTexto(nota.estado) }}
              </p>
            </div>
          </div>
          
          <!-- Botón Volver -->
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
    </section>

    <!-- Información de la Nota -->
    <section class="max-w-7xl mx-auto px-4 py-6">
      <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <div class="text-sm font-medium text-blue-900 dark:text-blue-100">Número de Nota</div>
            <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
              {{ nota.numero_nota }}/{{ nota.ejercicio_nota }}
            </div>
          </div>
          <div>
            <div class="text-sm font-medium text-blue-900 dark:text-blue-100">Fecha de Creación</div>
            <div class="text-lg font-semibold text-blue-800 dark:text-blue-200">
              {{ formatearFecha(nota.fecha_nota) }}
            </div>
          </div>
          <div>
            <div class="text-sm font-medium text-blue-900 dark:text-blue-100">Total Actual</div>
            <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
              ${{ formatearMoneda(nota.total_nota) }}
            </div>
          </div>
        </div>
        
        <!-- Advertencia si no se puede editar -->
        <div v-if="nota.estado !== 0" class="mt-4 p-3 bg-yellow-100 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-700 rounded-lg">
          <div class="flex items-center">
            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L5.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
            <span class="text-sm text-yellow-800 dark:text-yellow-200">
              Solo se pueden editar notas en estado "Abierta"
            </span>
          </div>
        </div>
      </div>
    </section>

    <!-- Formulario -->
    <section class="max-w-7xl mx-auto px-4 pb-8">
      <form @submit.prevent="actualizarNota" class="space-y-8">
        
        <!-- Información Básica -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Información Básica</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Tipo de Nota -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Tipo de Nota <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.tipo_nota_id"
                :disabled="nota.estado !== 0"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed"
                :class="{ 'border-red-500': form.errors.tipo_nota_id }"
              >
                <option value="">Seleccionar tipo de nota</option>
                <option v-for="tipo in tiposNota" :key="tipo.id" :value="tipo.id">
                  {{ tipo.descripcion }}
                </option>
              </select>
              <div v-if="form.errors.tipo_nota_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.tipo_nota_id }}
              </div>
            </div>

            <!-- Oficina Solicitante -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Oficina Solicitante <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.oficina_id"
                :disabled="nota.estado !== 0"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed"
                :class="{ 'border-red-500': form.errors.oficina_id }"
              >
                <option value="">Seleccionar oficina</option>
                <option v-for="oficina in oficinas" :key="oficina.id" :value="oficina.id">
                  {{ oficina.nombre }}
                </option>
              </select>
              <div v-if="form.errors.oficina_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.oficina_id }}
              </div>
            </div>

            <!-- Expediente -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Expediente
              </label>
              <input
                v-model="form.expediente"
                :disabled="nota.estado !== 0"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed"
                :class="{ 'border-red-500': form.errors.expediente }"
                placeholder="Número de expediente"
                maxlength="100"
              />
              <div v-if="form.errors.expediente" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.expediente }}
              </div>
            </div>
          </div>

          <!-- Descripción -->
          <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Descripción <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="form.descripcion"
              :disabled="nota.estado !== 0"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed"
              :class="{ 'border-red-500': form.errors.descripcion }"
              placeholder="Describe el motivo de la solicitud de insumos..."
              maxlength="1000"
            ></textarea>
            <div class="flex justify-between mt-1">
              <div v-if="form.errors.descripcion" class="text-sm text-red-600 dark:text-red-400">
                {{ form.errors.descripcion }}
              </div>
              <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ form.descripcion.length }}/1000 caracteres
              </div>
            </div>
          </div>
        </div>

        <!-- Detalle de Insumos -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Detalle de Insumos</h2>
            <button
              v-if="nota.estado === 0"
              @click="agregarRenglon"
              type="button"
              class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 shadow-md hover:shadow-lg flex items-center space-x-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
              <span>Agregar Insumo</span>
            </button>
          </div>

          <!-- Lista de Renglones -->
          <div v-if="form.detalles.length === 0" class="text-center py-12 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Sin insumos</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Comienza agregando insumos a la solicitud</p>
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="(detalle, index) in form.detalles"
              :key="detalle.id || index"
              class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700"
            >
              <div class="flex items-start justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                  Renglón {{ index + 1 }}
                </h3>
                <button
                  v-if="nota.estado === 0"
                  @click="eliminarRenglon(index)"
                  type="button"
                  class="text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 p-1 rounded transition-all"
                  title="Eliminar renglón"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </div>

              <DetalleInsumoForm
                :detalle="detalle"
                :index="index"
                :errors="form.errors"
                :solo-lectura="nota.estado !== 0"
                @actualizar="actualizarDetalle"
                @abrir-comentario="abrirModalComentario"
              />
            </div>
          </div>

          <!-- Total -->
          <div v-if="form.detalles.length > 0" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-600">
            <div class="flex justify-end">
              <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <div class="text-right">
                  <div class="text-sm text-gray-600 dark:text-gray-400">Total estimado</div>
                  <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                    ${{ formatearMoneda(totalEstimado) }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Error de detalles -->
          <div v-if="form.errors.detalles" class="mt-4 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.detalles }}
          </div>
        </div>

        <!-- Botones de Acción -->
        <div v-if="nota.estado === 0" class="flex items-center justify-end space-x-4">
          <Link
            :href="route('notas-pedido.index')"
            class="px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 font-medium transition-colors"
          >
            Cancelar
          </Link>
          
          <button
            type="submit"
            :disabled="form.processing || form.detalles.length === 0"
            class="px-6 py-3 bg-orange-500 hover:bg-orange-600 disabled:bg-orange-300 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-colors shadow-md hover:shadow-lg flex items-center space-x-2"
          >
            <svg v-if="form.processing" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ form.processing ? 'Guardando...' : 'Actualizar Nota de Pedido' }}</span>
          </button>
        </div>

        <!-- Mensaje para notas que no se pueden editar -->
        <div v-else class="text-center py-6">
          <div class="text-gray-500 dark:text-gray-400">
            <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <p class="text-sm font-medium">Esta nota no se puede editar</p>
            <p class="text-sm">Solo las notas en estado "Abierta" pueden ser modificadas</p>
          </div>
        </div>
      </form>
    </section>

    <!-- Modal de Comentario -->
    <ComentarioModal
      v-if="mostrarModalComentario"
      :comentario="comentarioTemporal"
      @guardar="guardarComentario"
      @cerrar="cerrarModalComentario"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import DetalleInsumoForm from '@/Components/NotasPedido/DetalleInsumoForm.vue'
import ComentarioModal from '@/Components/NotasPedido/ComentarioModal.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  title: String,
  nota: {
    type: Object,
    required: true
  },
  tiposNota: {
    type: Array,
    default: () => []
  },
  oficinas: {
    type: Array,
    default: () => []
  }
})

const breadcrumbs = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'Notas de Pedido', href: '/notas-pedido' },
  { name: `Editar ${props.nota.numero_nota}/${props.nota.ejercicio_nota}`, href: null }
]

// Estado del formulario
const form = useForm({
  tipo_nota_id: props.nota.tipo_nota_id,
  oficina_id: props.nota.oficina_id,
  descripcion: props.nota.descripcion,
  expediente: props.nota.expediente || '',
  detalles: props.nota.detalles.map(detalle => ({
    id: detalle.id,
    insumo_id: detalle.insumo_id,
    insumo: detalle.insumo,
    cantidad: detalle.cantidad,
    precio: detalle.precio,
    comentario: detalle.comentario || ''
  }))
})

// Estado del modal de comentario
const mostrarModalComentario = ref(false)
const comentarioTemporal = ref('')
const indiceComentario = ref(-1)

// Computed
const totalEstimado = computed(() => {
  return form.detalles.reduce((total, detalle) => {
    const cantidad = parseFloat(detalle.cantidad) || 0
    const precio = parseFloat(detalle.precio) || 0
    return total + (cantidad * precio)
  }, 0)
})

// Métodos
const agregarRenglon = () => {
  form.detalles.push({
    id: null,
    insumo_id: '',
    insumo: null,
    cantidad: 1,
    precio: 0,
    comentario: ''
  })
}

const eliminarRenglon = async (index) => {
  if (form.detalles.length === 1) {
    Swal.fire({
      title: 'No se puede eliminar',
      text: 'Debe tener al menos un insumo en la nota de pedido.',
      icon: 'warning'
    })
    return
  }

  const result = await Swal.fire({
    title: '¿Eliminar renglón?',
    text: `¿Estás seguro de que deseas eliminar el renglón ${index + 1}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  })

  if (result.isConfirmed) {
    form.detalles.splice(index, 1)
  }
}

const actualizarDetalle = (index, detalle) => {
  form.detalles[index] = { ...form.detalles[index], ...detalle }
}

const abrirModalComentario = (index) => {
  indiceComentario.value = index
  comentarioTemporal.value = form.detalles[index].comentario || ''
  mostrarModalComentario.value = true
}

const guardarComentario = (comentario) => {
  if (indiceComentario.value >= 0) {
    form.detalles[indiceComentario.value].comentario = comentario
  }
  cerrarModalComentario()
}

const cerrarModalComentario = () => {
  mostrarModalComentario.value = false
  comentarioTemporal.value = ''
  indiceComentario.value = -1
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

const actualizarNota = () => {
  if (form.detalles.length === 0) {
    Swal.fire({
      title: 'Faltan insumos',
      text: 'Debe agregar al menos un insumo a la nota de pedido.',
      icon: 'warning'
    })
    return
  }

  form.put(route('notas-pedido.update', props.nota.id), {
    onSuccess: () => {
      Swal.fire({
        title: '¡Éxito!',
        text: 'La nota de pedido ha sido actualizada correctamente.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
      })
    },
    onError: () => {
      Swal.fire({
        title: 'Error',
        text: 'Ocurrió un error al actualizar la nota. Verifica los datos e intenta nuevamente.',
        icon: 'error'
      })
    }
  })
}
</script>