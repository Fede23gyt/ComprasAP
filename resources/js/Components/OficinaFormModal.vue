<!-- resources/js/Components/OficinaFormModal.vue -->
<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, watch, nextTick } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import Swal from 'sweetalert2';

const props = defineProps({
  oficina: {
    type: Object,
    default: null
  },
  padres: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['close', 'saved']);

const isEditing = computed(() => !!props.oficina);

const form = useForm({
  nombre: props.oficina?.nombre || '',
  codigo_oficina: props.oficina?.codigo_oficina || '',
  abreviacion: props.oficina?.abreviacion || '',
  parent_id: props.oficina?.parent_id || '',
  estado: props.oficina?.estado || 'Habilitada'
});

// Limpiar errores cuando se cambian los valores
watch(() => form.nombre, () => form.clearErrors('nombre'));
watch(() => form.codigo_oficina, () => form.clearErrors('codigo_oficina'));
watch(() => form.abreviacion, () => form.clearErrors('abreviacion'));

const submit = () => {
  if (isEditing.value) {
    form.put(`/nomencladores/oficinas/${props.oficina.id}`, {
      onSuccess: () => {
        emit('saved');
        Swal.fire({
          title: '¡Éxito!',
          text: 'Oficina actualizada correctamente',
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
          text: 'Hubo un problema al actualizar la oficina',
          icon: 'error',
          confirmButtonColor: '#ef4444'
        });
      }
    });
  } else {
    form.post('/nomencladores/oficinas', {
      onSuccess: () => {
        emit('saved');
        Swal.fire({
          title: '¡Éxito!',
          text: 'Oficina creada correctamente',
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
          text: 'Hubo un problema al crear la oficina',
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

// Auto-focus en el primer input
nextTick(() => {
  document.querySelector('#nombre')?.focus();
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
          {{ isEditing ? 'Editar Oficina' : 'Nueva Oficina' }}
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
        <!-- Nombre -->
        <div class="space-y-1">
          <label for="nombre" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
            Nombre <span class="text-red-500">*</span>
          </label>
          <input
            id="nombre"
            v-model="form.nombre"
            type="text"
            placeholder="Ingrese el nombre de la oficina"
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all"
            :class="{ 'border-red-500 dark:border-red-500': form.errors.nombre }"
            required
          />
          <p v-if="form.errors.nombre" class="text-sm text-red-600 dark:text-red-400">
            {{ form.errors.nombre }}
          </p>
        </div>

        <!-- Código Oficina -->
        <div class="space-y-1">
          <label for="codigo_oficina" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
            Código de Oficina <span class="text-red-500">*</span>
          </label>
          <input
            id="codigo_oficina"
            v-model="form.codigo_oficina"
            type="text"
            placeholder="Ej: OF-001"
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent dark:bg-gray-700 dark:text-white font-mono transition-all"
            :class="{ 'border-red-500 dark:border-red-500': form.errors.codigo_oficina }"
            required
          />
          <p v-if="form.errors.codigo_oficina" class="text-sm text-red-600 dark:text-red-400">
            {{ form.errors.codigo_oficina }}
          </p>
        </div>

        <!-- Abreviación -->
        <div class="space-y-1">
          <label for="abreviacion" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
            Abreviación <span class="text-red-500">*</span>
          </label>
          <input
            id="abreviacion"
            v-model="form.abreviacion"
            type="text"
            placeholder="Ej: OF1"
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all"
            :class="{ 'border-red-500 dark:border-red-500': form.errors.abreviacion }"
            required
          />
          <p v-if="form.errors.abreviacion" class="text-sm text-red-600 dark:text-red-400">
            {{ form.errors.abreviacion }}
          </p>
        </div>

        <!-- Oficina Padre -->
        <div class="space-y-1">
          <label for="parent_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
            Oficina Padre (Opcional)
          </label>
          <select
            id="parent_id"
            v-model="form.parent_id"
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all"
            :class="{ 'border-red-500 dark:border-red-500': form.errors.parent_id }"
          >
            <option value="">Sin oficina padre</option>
            <option
              v-for="padre in padres"
              :key="padre.id"
              :value="padre.id"
            >
              {{ padre.nombre }}
            </option>
          </select>
          <p v-if="form.errors.parent_id" class="text-sm text-red-600 dark:text-red-400">
            {{ form.errors.parent_id }}
          </p>
        </div>

        <!-- Estado -->
        <div class="space-y-1">
          <label for="estado" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
            Estado <span class="text-red-500">*</span>
          </label>
          <select
            id="estado"
            v-model="form.estado"
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all"
            :class="{ 'border-red-500 dark:border-red-500': form.errors.estado }"
            required
          >
            <option value="Habilitada">Habilitada</option>
            <option value="No habilitada">No habilitada</option>
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
            class="flex-1 px-4 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
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
