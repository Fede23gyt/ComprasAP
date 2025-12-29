<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- Header Section -->
    <section class="bg-gray-100 dark:bg-gray-900 pt-4 pb-6">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-xl">
              <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
            </div>
            <div>
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
              <p class="text-gray-600 dark:text-gray-400 mt-1">
                Crear una nueva solicitud de insumos
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

    <!-- Formulario -->
    <section class="max-w-7xl mx-auto px-4 py-8">
      <form @submit.prevent="guardarNota" class="space-y-8">
        
        <!-- Información Básica -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Información Básica</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Ejercicio y Número (solo informativo) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Ejercicio/Número
              </label>
              <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100">
                {{ ejercicioActual }}/{{ proximoNumero }}
              </div>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Se asignará automáticamente</p>
            </div>

            <!-- Tipo de Nota -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Tipo de Nota <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.tipo_nota_id"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
          </div>

          <!-- Descripción -->
          <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Descripción <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="form.descripcion"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
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
              :key="index"
              class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700"
            >
              <div class="flex items-start justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                  Renglón {{ index + 1 }}
                </h3>
                <button
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
        <div class="flex items-center justify-end space-x-4">
          <Link
            :href="route('notas-pedido.index')"
            class="px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 font-medium transition-colors"
          >
            Cancelar
          </Link>
          
          <button
            type="submit"
            :disabled="form.processing || form.detalles.length === 0"
            class="px-6 py-3 bg-blue-500 hover:bg-blue-600 disabled:bg-blue-300 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-colors shadow-md hover:shadow-lg flex items-center space-x-2"
          >
            <svg v-if="form.processing" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ form.processing ? 'Guardando...' : 'Crear Nota de Pedido' }}</span>
          </button>
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
  tiposNota: {
    type: Array,
    default: () => []
  },
  oficinas: {
    type: Array,
    default: () => []
  },
  ejercicioActual: {
    type: [String, Number],
    default: new Date().getFullYear()
  }
})

const breadcrumbs = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'Notas de Pedido', href: '/notas-pedido' },
  { name: 'Nueva Nota', href: null }
]

// Estado del formulario
const form = useForm({
  tipo_nota_id: '',
  oficina_id: '',
  descripcion: '',
  detalles: []
})

// Estado del modal de comentario
const mostrarModalComentario = ref(false)
const comentarioTemporal = ref('')
const indiceComentario = ref(-1)

// Computed
const proximoNumero = computed(() => {
  // En una implementación real, esto vendría del backend
  return 'XXXX'
})

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
    insumo_id: null,
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

const formatearMoneda = (monto) => {
  return parseFloat(monto || 0).toLocaleString('es-AR', { 
    minimumFractionDigits: 2, 
    maximumFractionDigits: 2 
  })
}

const guardarNota = () => {
  if (form.detalles.length === 0) {
    Swal.fire({
      title: 'Faltan insumos',
      text: 'Debe agregar al menos un insumo a la nota de pedido.',
      icon: 'warning'
    })
    return
  }

  // Verificar que todos los detalles tengan insumo seleccionado
  const detallesSinInsumo = form.detalles.findIndex(detalle => !detalle.insumo_id)
  if (detallesSinInsumo !== -1) {
    Swal.fire({
      title: 'Insumo requerido',
      text: `Debe seleccionar un insumo para el renglón ${detallesSinInsumo + 1}.`,
      icon: 'warning'
    })
    return
  }

  form.post(route('notas-pedido.store'), {
    onSuccess: () => {
      Swal.fire({
        title: '¡Éxito!',
        text: 'La nota de pedido ha sido creada correctamente.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
      })
    },
    onError: () => {
      Swal.fire({
        title: 'Error',
        text: 'Ocurrió un error al guardar la nota. Verifica los datos e intenta nuevamente.',
        icon: 'error'
      })
    }
  })
}

// Agregar el primer renglón por defecto
if (form.detalles.length === 0) {
  agregarRenglon()
}
</script>