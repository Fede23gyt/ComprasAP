<!-- resources/js/Components/TipoNotaModal.vue -->
<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import axios from 'axios'; // Agregamos axios para peticiones AJAX

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

// Form state
const form = useForm({
  descripcion: '',
  estado: 'Habilitado' // Por defecto habilitado
});

// Inicializar form con los datos del item si estamos en modo edit
watch(() => props.item, () => {
  if (props.item) {
    form.descripcion = props.item.descripcion || '';
    // Asegurarse que el estado se envíe según los valores permitidos en la BD
    form.estado = props.item.estado === 'Habilitado' ? 'Habilitado' : 'No habilitado';
  } else {
    form.reset();
    form.estado = 'Habilitado'; // Valor por defecto para nuevos registros
  }
}, { immediate: true });

// Form submit handler
const submit = () => {
  // Debug para verificar los valores que se están enviando
  console.log('Enviando datos:', form.data());

  if (props.mode === 'create') {
    // Para creación, usamos axios directamente
    axios.post('/tipos-nota', form.data())
      .then(response => {
        console.log('Respuesta exitosa:', response.data);
        emit('saved', 'Tipo de nota creado correctamente');
        // El evento saved se encargará de cerrar el modal y recargar la página
      })
      .catch(error => {
        console.error('Errores en la creación:', error.response?.data);
        // Mostrar un mensaje de error
        Swal.fire({
          title: 'Error',
          text: error.response?.data?.message || 'No se pudo crear el tipo de nota. Revisa los errores e inténtalo nuevamente.',
          icon: 'error',
          confirmButtonText: 'Entendido'
        });
      });
  } else if (props.mode === 'edit') {
    // Para edición, usamos axios directamente
    axios.patch(`/tipos-nota/${props.item.id}`, form.data())
      .then(response => {
        console.log('Respuesta exitosa:', response.data);
        emit('saved', 'Tipo de nota actualizado correctamente');
        // El evento saved se encargará de cerrar el modal y recargar la página
      })
      .catch(error => {
        console.error('Errores en la actualización:', error.response?.data);
        // Mostrar un mensaje de error
        Swal.fire({
          title: 'Error',
          text: error.response?.data?.message || 'No se pudo actualizar el tipo de nota. Revisa los errores e inténtalo nuevamente.',
          icon: 'error',
          confirmButtonText: 'Entendido'
        });
      });
  }
};
</script>

<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
      <!-- Header -->
      <div class="bg-orange-500 text-white px-6 py-4">
        <h3 class="text-lg font-medium">
          {{ mode === 'create' ? 'Nuevo Tipo de Nota' : 'Editar Tipo de Nota' }}
        </h3>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="p-6 space-y-4">
        <!-- Descripción -->
        <div>
          <label for="descripcion" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Descripción
          </label>
          <input
            id="descripcion"
            v-model="form.descripcion"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
            placeholder="Nombre del tipo de nota..."
            required
            autofocus
          />
          <p v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.descripcion }}
          </p>
        </div>

        <!-- Estado -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Estado
          </label>
          <div class="flex items-center space-x-3">
            <label class="flex items-center space-x-2 cursor-pointer">
              <input
                v-model="form.estado"
                type="radio"
                value="Habilitado"
                name="estado"
                class="h-4 w-4 text-orange-500 focus:ring-orange-500 border-gray-300 dark:border-gray-600"
              />
              <span class="text-sm text-gray-700 dark:text-gray-300">Habilitado</span>
            </label>
            <label class="flex items-center space-x-2 cursor-pointer">
              <input
                v-model="form.estado"
                type="radio"
                value="No habilitado"
                name="estado"
                class="h-4 w-4 text-orange-500 focus:ring-orange-500 border-gray-300 dark:border-gray-600"
              />
              <span class="text-sm text-gray-700 dark:text-gray-300">No habilitado</span>
            </label>
          </div>
          <p v-if="form.errors.estado" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.estado }}
          </p>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end space-x-3 pt-4">
          <button
            type="button"
            @click="emit('close')"
            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg disabled:opacity-70 disabled:cursor-not-allowed"
          >
            {{ form.processing ? 'Guardando...' : (mode === 'create' ? 'Crear' : 'Actualizar') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
