<!-- resources/js/Components/MemoModal.vue -->
<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, watch, nextTick } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import Swal from 'sweetalert2';

const props = defineProps({
  item: {
    type: Object,
    default: null
  },
  mode: {
    type: String,
    default: 'create'
  }
});

const emit = defineEmits(['close', 'saved']);

const isEditing = computed(() => props.mode === 'edit');

const form = useForm({
  descripcion: props.item?.descripcion || '',
  estado: props.item?.estado || 'Habilitado'
});

// Limpiar errores cuando se cambian los valores
watch(() => form.descripcion, () => form.clearErrors('descripcion'));

const submit = () => {
  if (isEditing.value) {
    form.put(`/nomencladores/memos/${props.item.id}`, {
      onSuccess: () => {
        emit('saved', 'Memo actualizado correctamente');
        Swal.fire({
          title: '¡Éxito!',
          text: 'Memo actualizado correctamente',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
          toast: true,
          position: 'top-end'
        });
      },
      onError: () => {
        Swal.fire({
          title: 'Error',
          text: 'Hubo un problema al actualizar el memo',
          icon: 'error',
          confirmButtonColor: '#ef4444'
        });
      }
    });
  } else {
    form.post('/nomencladores/memos', {
      onSuccess: () => {
        emit('saved', 'Memo creado correctamente');
        Swal.fire({
          title: '¡Éxito!',
          text: 'Memo creado correctamente',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
          toast: true,
          position: 'top-end'
        });
      },
      onError: () => {
        Swal.fire({
          title: 'Error',
          text: 'Hubo un problema al crear el memo',
          icon: 'error',
          confirmButtonColor: '#ef4444'
        });
      }
    });
  }
};

const closeModal = () => {
  if (form.isDirty && !form.processing) {
    Swal.fire({
      title: '¿Descartar cambios?',
      text: 'Los cambios no guardados se perderán',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Sí, descartar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        form.reset();
        emit('close');
      }
    });
  } else {
    form.reset();
    emit('close');
  }
};

// Auto-focus en el textarea
nextTick(() => {
  document.querySelector('#descripcion')?.focus();
});
</script>

<template>
  <!-- Overlay -->
  <div
    class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    @click.self="closeModal"
  >
    <!-- Modal -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
          {{ isEditing ? 'Editar Memo' : 'Nuevo Memo' }}
        </h3>
        <button
          @click="closeModal"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
        >
          <XMarkIcon class="w-6 h-6" />
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="p-6 space-y-5">
        <!-- Descripción (multi-línea) -->
        <div class="space-y-1">
          <label for="descripcion" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
            Descripción <span class="text-red-500">*</span>
          </label>
          <textarea
            id="descripcion"
            v-model="form.descripcion"
            placeholder="Ingrese la descripción del memo (puede tener múltiples líneas)"
            rows="4"
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all resize-y"
            :class="{ 'border-red-500 dark:border-red-500': form.errors.descripcion }"
            required
          ></textarea>
          <p v-if="form.errors.descripcion" class="text-sm text-red-600 dark:text-red-400">
            {{ form.errors.descripcion }}
          </p>
        </div>

        <!-- Estado -->
        <div class="space-y-1">
          <label for="estado" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
            Estado
          </label>
          <select
            id="estado"
            v-model="form.estado"
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all"
            :class="{ 'border-red-500 dark:border-red-500': form.errors.estado }"
          >
            <option value="Habilitado">Habilitado</option>
            <option value="No habilitado">No habilitado</option>
          </select>
          <p v-if="form.errors.estado" class="text-sm text-red-600 dark:text-red-400">
            {{ form.errors.estado }}
          </p>
        </div>

        <!-- Botones -->
        <div class="flex space-x-3 pt-4">
          <button
            type="button"
            @click="closeModal"
            class="flex-1 px-4 py-3 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-xl transition-colors"
            :disabled="form.processing"
          >
            Cancelar
          </button>
          <button
            type="submit"
            class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="form.processing"
          >
            <span v-if="form.processing">
              {{ isEditing ? 'Actualizando...' : 'Creando...' }}
            </span>
            <span v-else>
              {{ isEditing ? 'Actualizar' : 'Crear' }}
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>