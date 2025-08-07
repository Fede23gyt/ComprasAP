<!-- resources/js/Components/TipoCompraModal.vue -->
<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
  item: {
    type: Object,
    default: null
  },
  mode: {
    type: String,
    default: 'create', // 'create' | 'edit'
    validator: (value) => ['create', 'edit'].includes(value)
  }
});

const emit = defineEmits(['close', 'saved']);

// Títulos dinámicos
const modalTitle = computed(() => {
  return props.mode === 'create' ? 'Nuevo Tipo de Compra' : 'Editar Tipo de Compra';
});

const submitButtonText = computed(() => {
  return props.mode === 'create' ? 'Crear Tipo' : 'Actualizar Tipo';
});

// Form setup
const form = useForm({
  descripcion: '',
  estado: 'Habilitado'
});

// Inicializar formulario cuando cambie el item o el modo
watch(() => [props.item, props.mode], () => {
  if (props.mode === 'edit' && props.item) {
    form.descripcion = props.item.descripcion || '';
    form.estado = props.item.estado || 'Habilitado';
  } else {
    // Modo create
    form.descripcion = '';
    form.estado = 'Habilitado';
  }

  // Limpiar errores
  form.clearErrors();
}, { immediate: true });

// Referencia al primer input para focus
const descripcionInput = ref(null);

// Focus al primer input cuando se abre el modal
watch(() => props.mode, () => {
  setTimeout(() => {
    if (descripcionInput.value) {
      descripcionInput.value.focus();
    }
  }, 100);
}, { immediate: true });

const closeModal = () => {
  emit('close');
};

// Confirmación antes de cerrar si hay cambios
const confirmClose = async () => {
  if (form.isDirty) {
    const result = await Swal.fire({
      title: '¿Cerrar sin guardar?',
      text: 'Tienes cambios sin guardar. ¿Estás seguro de que quieres cerrar?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Sí, cerrar',
      cancelButtonText: 'Continuar editando',
      reverseButtons: true
    });

    if (result.isConfirmed) {
      closeModal();
    }
  } else {
    closeModal();
  }
};

const submitForm = async () => {
  // Validación básica en frontend
  if (!form.descripcion.trim()) {
    await Swal.fire({
      title: 'Campo requerido',
      text: 'La descripción es obligatoria',
      icon: 'error',
      confirmButtonText: 'Entendido'
    });
    descripcionInput.value?.focus();
    return;
  }

  // Confirmación antes de guardar
  const action = props.mode === 'create' ? 'crear' : 'actualizar';
  const result = await Swal.fire({
    title: `¿Confirmar ${action}?`,
    text: `¿Estás seguro de que quieres ${action} este tipo de compra?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#22c55e',
    cancelButtonColor: '#6b7280',
    confirmButtonText: `Sí, ${action}`,
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  });

  if (!result.isConfirmed) return;

  // Submit del formulario
  if (props.mode === 'create') {
    form.post('/tipos-compras', {
      onSuccess: () => {
        emit('saved', 'Tipo de compra creado correctamente');
      },
      onError: (errors) => {
        handleFormErrors(errors);
      }
    });
  } else {
    form.patch(`/tipos-compras/${props.item.id}`, {
      onSuccess: () => {
        emit('saved', 'Tipo de compra actualizado correctamente');
      },
      onError: (errors) => {
        handleFormErrors(errors);
      }
    });
  }
};

const handleFormErrors = (errors) => {
  const errorMessages = Object.values(errors).flat();
  Swal.fire({
    title: 'Error de validación',
    html: `<ul style="text-align: left;">${errorMessages.map(error => `<li>${error}</li>`).join('')}</ul>`,
    icon: 'error',
    confirmButtonText: 'Entendido'
  });
};

// Cerrar modal con ESC
const handleKeydown = (event) => {
  if (event.key === 'Escape') {
    confirmClose();
  }
};

// Event listener para ESC
document.addEventListener('keydown', handleKeydown);

// Cleanup al desmontar
import { onUnmounted } from 'vue';
onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
  <!-- Modal Backdrop -->
  <div
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
    @click.self="confirmClose"
  >
    <!-- Modal Content -->
    <div
      class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto"
      @click.stop
    >
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
          {{ modalTitle }}
        </h2>
        <button
          @click="confirmClose"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-150"
          type="button"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="submitForm" class="p-6 space-y-6">
        <!-- Descripción -->
        <div>
          <label
            for="descripcion"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
          >
            Descripción <span class="text-red-500">*</span>
          </label>
          <input
            ref="descripcionInput"
            id="descripcion"
            v-model="form.descripcion"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-150"
            :class="{ 'border-red-500': form.errors.descripcion }"
            placeholder="Ingresa la descripción del tipo de compra"
            maxlength="255"
            required
          />
          <div v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.descripcion }}
          </div>
          <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            {{ form.descripcion.length }}/255 caracteres
          </div>
        </div>

        <!-- Estado -->
        <div>
          <label
            for="estado"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
          >
            Estado
          </label>
          <select
            id="estado"
            v-model="form.estado"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors duration-150"
            :class="{ 'border-red-500': form.errors.estado }"
          >
            <option value="Habilitado">Habilitado</option>
            <option value="Deshabilitado">Deshabilitado</option>
          </select>
          <div v-if="form.errors.estado" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.estado }}
          </div>
        </div>

        <!-- Vista previa del estado -->
        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600 dark:text-gray-300">Vista previa:</span>
            <div class="flex items-center space-x-2">
              <div
                class="w-3 h-3 rounded-full"
                :class="form.estado === 'Habilitado' ? 'bg-green-500' : 'bg-red-500'"
              ></div>
              <span
                class="text-sm font-medium"
                :class="form.estado === 'Habilitado' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
              >
                {{ form.estado }}
              </span>
            </div>
          </div>
        </div>
      </form>

      <!-- Footer -->
      <div class="flex items-center justify-end space-x-3 p-6 border-t border-gray-200 dark:border-gray-700">
        <button
          @click="confirmClose"
          type="button"
          class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-150"
          :disabled="form.processing"
        >
          Cancelar
        </button>
        <button
          @click="submitForm"
          type="button"
          class="px-4 py-2 text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="form.processing"
        >
          <span v-if="form.processing" class="flex items-center">
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Procesando...
          </span>
          <span v-else>{{ submitButtonText }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Animación de entrada del modal */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

/* Scroll personalizado para el modal */
.max-h-\[90vh\]::-webkit-scrollbar {
  width: 6px;
}

.max-h-\[90vh\]::-webkit-scrollbar-track {
  background: transparent;
}

.max-h-\[90vh\]::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.5);
  border-radius: 3px;
}

.max-h-\[90vh\]::-webkit-scrollbar-thumb:hover {
  background-color: rgba(156, 163, 175, 0.7);
}
</style>
