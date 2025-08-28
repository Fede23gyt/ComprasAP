<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  item: {
    type: Object,
    default: null
  },
  mode: {
    type: String,
    default: 'view'
  },
  padres: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'saved'])

const modalTitle = computed(() => {
  switch (props.mode) {
    case 'create': return 'Nuevo Insumo'
    case 'edit': return 'Editar Insumo'
    default: return 'Detalles del Insumo'
  }
})

const isReadOnly = computed(() => props.mode === 'view')

// Inicializar form con datos seguros
const form = useForm({
  codigo: '',
  descripcion: '',
  clasificacion: '',
  registrable: true,
  presentacion: '',
  unidad_solicitud: '',
  precio_insumo: '',
  precio_testigo: false,
  inventariable: false,
  rend_tribunal: false,
  conversion: '',
  fecha_ultima: ''
})

// Watch para actualizar el form cuando cambie el item
watch(() => [props.item, props.mode], () => {
  if (props.item) {
    form.codigo = props.item.codigo || ''
    form.descripcion = props.item.descripcion || ''
    form.clasificacion = props.item.clasificacion || ''
    form.registrable = props.item.registrable !== undefined ? props.item.registrable : true
    form.presentacion = props.item.presentacion || ''
    form.unidad_solicitud = props.item.unidad_solicitud || ''
    form.precio_insumo = props.item.precio_insumo || ''
    form.precio_testigo = props.item.precio_testigo !== undefined ? props.item.precio_testigo : false
    form.inventariable = props.item.inventariable !== undefined ? props.item.inventariable : false
    form.rend_tribunal = props.item.rend_tribunal !== undefined ? props.item.rend_tribunal : false
    form.conversion = props.item.conversion || ''
    form.fecha_ultima = props.item.fecha_ultima || ''
  } else if (props.mode === 'create') {
    // Reset form for create mode
    form.reset()
    form.registrable = true
    form.precio_testigo = false
    form.inventariable = false
    form.rend_tribunal = false
  }
}, { immediate: true })

const closeModal = () => emit('close')

const submitForm = () => {
  if (props.mode === 'create') {
    form.post('/nomencladores/insumos', {
      onSuccess: () => {
        emit('saved', 'Insumo creado correctamente')
      },
      onError: (errors) => {
        console.log('Errores:', errors)
      }
    })
  } else if (props.mode === 'edit' && props.item) {
    form.put(`/nomencladores/insumos/${props.item.id}`, {
      onSuccess: () => {
        emit('saved', 'Insumo actualizado correctamente')
      },
      onError: (errors) => {
        console.log('Errores:', errors)
      }
    })
  }
}

const handleEscape = (e) => {
  if (e.key === 'Escape') closeModal()
}

onMounted(() => {
  document.addEventListener('keydown', handleEscape)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscape)
})

// Computed para contar caracteres de descripción de forma segura
const descripcionLength = computed(() => {
  return form.descripcion ? form.descripcion.length : 0
})
</script>

<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="closeModal">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ modalTitle }}</h2>
        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Body -->
      <div class="p-6">
        <form @submit.prevent="submitForm" class="space-y-6">
          <!-- Primera fila -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Código <span v-if="!isReadOnly" class="text-red-500">*</span>
              </label>
              <input
                v-model="form.codigo"
                type="text"
                :readonly="isReadOnly"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 transition-colors"
                :class="isReadOnly ? 'bg-gray-50 dark:bg-gray-700' : 'bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500'"
                placeholder="Código del insumo"
              />
              <div v-if="form.errors?.codigo" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.codigo }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Clasificación</label>
              <input
                v-model="form.clasificacion"
                type="text"
                :readonly="isReadOnly"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 transition-colors"
                :class="isReadOnly ? 'bg-gray-50 dark:bg-gray-700' : 'bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500'"
                placeholder="Código de clasificación"
              />
              <div v-if="form.errors?.clasificacion" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.clasificacion }}
              </div>
              <!-- Mostrar descripción de la clasificación si existe -->
              <div v-if="item?.clasificacion_economica?.descripcion" class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                {{ item.clasificacion_economica.descripcion }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Presentación</label>
              <input
                v-model="form.presentacion"
                type="text"
                :readonly="isReadOnly"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 transition-colors"
                :class="isReadOnly ? 'bg-gray-50 dark:bg-gray-700' : 'bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500'"
                placeholder="Ej: Caja, Unidad, etc."
              />
            </div>
          </div>

          <!-- Descripción -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Descripción <span v-if="!isReadOnly" class="text-red-500">*</span>
            </label>
            <textarea
              v-model="form.descripcion"
              :readonly="isReadOnly"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg resize-none text-gray-900 dark:text-gray-100 transition-colors"
              :class="isReadOnly ? 'bg-gray-50 dark:bg-gray-700' : 'bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500'"
              placeholder="Descripción del insumo"
              maxlength="500"
            ></textarea>
            <div v-if="form.errors?.descripcion" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.descripcion }}
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              {{ descripcionLength }}/500 caracteres
            </div>
          </div>

          <!-- Segunda fila -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Unidad Solicitud</label>
              <input
                v-model="form.unidad_solicitud"
                type="text"
                :readonly="isReadOnly"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 transition-colors"
                :class="isReadOnly ? 'bg-gray-50 dark:bg-gray-700' : 'bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500'"
                placeholder="kg, m², etc."
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Precio ($)</label>
              <input
                v-model="form.precio_insumo"
                type="number"
                step="0.01"
                min="0"
                :readonly="isReadOnly"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 transition-colors"
                :class="isReadOnly ? 'bg-gray-50 dark:bg-gray-700' : 'bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500'"
                placeholder="0.00"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Conversión</label>
              <input
                v-model="form.conversion"
                type="number"
                step="0.0001"
                min="0"
                :readonly="isReadOnly"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 transition-colors"
                :class="isReadOnly ? 'bg-gray-50 dark:bg-gray-700' : 'bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500'"
                placeholder="0.0000"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha Actualización</label>
              <input
                v-model="form.fecha_ultima"
                type="date"
                :readonly="isReadOnly"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 transition-colors"
                :class="isReadOnly ? 'bg-gray-50 dark:bg-gray-700' : 'bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500'"
              />
            </div>
          </div>

          <!-- Configuraciones -->
          <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Configuraciones</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <input
                  v-model="form.registrable"
                  type="checkbox"
                  :disabled="isReadOnly"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2"
                />
                <span class="text-sm text-gray-900 dark:text-gray-100">Registrable</span>
              </div>

              <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <input
                  v-model="form.precio_testigo"
                  type="checkbox"
                  :disabled="isReadOnly"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2"
                />
                <span class="text-sm text-gray-900 dark:text-gray-100">Precio Testigo</span>
              </div>

              <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <input
                  v-model="form.inventariable"
                  type="checkbox"
                  :disabled="isReadOnly"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2"
                />
                <span class="text-sm text-gray-900 dark:text-gray-100">Inventariable</span>
              </div>

              <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <input
                  v-model="form.rend_tribunal"
                  type="checkbox"
                  :disabled="isReadOnly"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2"
                />
                <span class="text-sm text-gray-900 dark:text-gray-100">Rinde Tribunal</span>
              </div>
            </div>
          </div>

          <!-- Información adicional en modo view -->
          <div v-if="isReadOnly && item" class="border-t border-gray-200 dark:border-gray-600 pt-4">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Información del Sistema</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div>
                <span class="text-gray-500 dark:text-gray-400">Fecha de creación:</span>
                <span class="ml-2 text-gray-900 dark:text-gray-100">
                  {{ item.created_at ? new Date(item.created_at).toLocaleString('es-AR') : 'N/A' }}
                </span>
              </div>
              <div>
                <span class="text-gray-500 dark:text-gray-400">Última modificación:</span>
                <span class="ml-2 text-gray-900 dark:text-gray-100">
                  {{ item.updated_at ? new Date(item.updated_at).toLocaleString('es-AR') : 'N/A' }}
                </span>
              </div>
            </div>
          </div>
        </form>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-end space-x-3 p-6 border-t border-gray-200 dark:border-gray-700">
        <button
          @click="closeModal"
          type="button"
          class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
        >
          {{ isReadOnly ? 'Cerrar' : 'Cancelar' }}
        </button>

        <button
          v-if="!isReadOnly"
          @click="submitForm"
          type="button"
          class="px-4 py-2 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-lg transition-colors disabled:opacity-50"
          :disabled="form.processing"
        >
          <span v-if="form.processing">Procesando...</span>
          <span v-else>{{ mode === 'create' ? 'Crear Insumo' : 'Actualizar Insumo' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>
