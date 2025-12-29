<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
  show: Boolean,
  item: {
    type: Object,
    default: null
  },
  mode: {
    type: String,
    default: 'create'
  },
  tipos: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['close', 'saved']);

// Form state
const form = useForm({
  cuit: '',
  nombre_proveedor: '',
  razon_social: '',
  tipo: '',
  domicilio: '',
  telefono_fijo: '',
  telefono_celular: '',
  email: '',
  estado: true
});

// Formatear CUIT mientras se escribe
const formatCuit = (value) => {
  const clean = value.replace(/[^0-9]/g, '');
  if (clean.length <= 11) {
    if (clean.length >= 10) {
      return `${clean.slice(0, 2)}-${clean.slice(2, 10)}-${clean.slice(10)}`;
    } else if (clean.length >= 2) {
      return `${clean.slice(0, 2)}-${clean.slice(2)}`;
    }
    return clean;
  }
  return value;
};

const onCuitInput = (event) => {
  const formatted = formatCuit(event.target.value);
  form.cuit = formatted;
  event.target.value = formatted;
};

// Validar CUIT
const validarCuit = (cuit) => {
  const clean = cuit.replace(/[^0-9]/g, '');
  if (clean.length !== 11) return false;

  const prefijo = clean.slice(0, 2);
  if (!['20', '23', '24', '27', '30', '33', '34'].includes(prefijo)) return false;

  const coeficientes = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
  let suma = 0;

  for (let i = 0; i < 10; i++) {
    suma += parseInt(clean[i]) * coeficientes[i];
  }

  const resto = suma % 11;
  let digitoVerificador = 11 - resto;

  if (digitoVerificador === 11) digitoVerificador = 0;
  else if (digitoVerificador === 10) digitoVerificador = 9;

  return digitoVerificador === parseInt(clean[10]);
};

// Inicializar form con los datos del item si estamos en modo edit
watch(() => props.item, () => {
  if (props.item) {
    form.cuit = props.item.cuit || '';
    form.nombre_proveedor = props.item.nombre_proveedor || '';
    form.razon_social = props.item.razon_social || '';
    form.tipo = props.item.tipo || '';
    form.domicilio = props.item.domicilio || '';
    form.telefono_fijo = props.item.telefono_fijo || '';
    form.telefono_celular = props.item.telefono_celular || '';
    form.email = props.item.email || '';
    form.estado = props.item.estado !== undefined ? props.item.estado : true;
  } else {
    form.reset();
    form.estado = true;
  }
}, { immediate: true });

// Cerrar modal
const close = () => {
  emit('close');
  form.reset();
  form.clearErrors();
};

// Form submit handler
const submit = () => {
  // Validar CUIT antes de enviar
  if (!validarCuit(form.cuit)) {
    Swal.fire({
      title: 'CUIT inválido',
      text: 'El CUIT ingresado no es válido. Verifique el formato y dígito verificador.',
      icon: 'error',
      confirmButtonText: 'Entendido'
    });
    return;
  }

  const url = props.mode === 'create'
    ? '/nomencladores/proveedores'
    : `/nomencladores/proveedores/${props.item.id}`;

  const method = props.mode === 'create' ? 'post' : 'patch';

  form[method](url, {
    onSuccess: () => {
      close();
      const message = props.mode === 'create'
        ? 'Proveedor creado correctamente'
        : 'Proveedor actualizado correctamente';

      Swal.fire({
        title: '¡Éxito!',
        text: message,
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
      });
    },
    onError: (errors) => {
      console.error('Errores:', errors);
      const errorMessages = Object.values(errors).flat();
      Swal.fire({
        title: 'Error',
        html: `<ul style="text-align: left;">${errorMessages.map(error => `<li>${error}</li>`).join('')}</ul>`,
        icon: 'error',
        confirmButtonText: 'Entendido'
      });
    }
  });
};
</script>

<template>
  <!-- Modal Backdrop -->
  <div v-show="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="close"></div>

      <!-- Modal Panel -->
      <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
              {{ mode === 'create' ? 'Nuevo Proveedor' : 'Editar Proveedor' }}
            </h3>
            <button @click="close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit">
          <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

              <!-- CUIT -->
              <div class="md:col-span-1">
                <label for="cuit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  CUIT <span class="text-red-500">*</span>
                </label>
                <input
                  id="cuit"
                  v-model="form.cuit"
                  @input="onCuitInput"
                  type="text"
                  placeholder="99-99999999-9"
                  maxlength="13"
                  required
                  class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                  :class="{ 'border-red-500': form.errors.cuit }"
                />
                <div v-if="form.errors.cuit" class="mt-1 text-sm text-red-600">{{ form.errors.cuit }}</div>
              </div>

              <!-- Nombre Proveedor -->
              <div class="md:col-span-1">
                <label for="nombre_proveedor" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Nombre Proveedor <span class="text-red-500">*</span>
                </label>
                <input
                  id="nombre_proveedor"
                  v-model="form.nombre_proveedor"
                  type="text"
                  required
                  class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                  :class="{ 'border-red-500': form.errors.nombre_proveedor }"
                />
                <div v-if="form.errors.nombre_proveedor" class="mt-1 text-sm text-red-600">{{ form.errors.nombre_proveedor }}</div>
              </div>

              <!-- Razón Social -->
              <div class="md:col-span-2">
                <label for="razon_social" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Razón Social <span class="text-red-500">*</span>
                </label>
                <input
                  id="razon_social"
                  v-model="form.razon_social"
                  type="text"
                  required
                  class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                  :class="{ 'border-red-500': form.errors.razon_social }"
                />
                <div v-if="form.errors.razon_social" class="mt-1 text-sm text-red-600">{{ form.errors.razon_social }}</div>
              </div>

              <!-- Tipo IVA -->
              <div class="md:col-span-1">
                <label for="tipo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Tipo IVA <span class="text-red-500">*</span>
                </label>
                <select
                  id="tipo"
                  v-model="form.tipo"
                  required
                  class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                  :class="{ 'border-red-500': form.errors.tipo }"
                >
                  <option value="">Seleccione un tipo</option>
                  <option v-for="tipo in tipos" :key="tipo" :value="tipo">{{ tipo }}</option>
                </select>
                <div v-if="form.errors.tipo" class="mt-1 text-sm text-red-600">{{ form.errors.tipo }}</div>
              </div>

              <!-- Estado -->
              <div class="md:col-span-1">
                <label for="estado" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Estado
                </label>
                <select
                  id="estado"
                  v-model="form.estado"
                  class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                >
                  <option :value="true">Activo</option>
                  <option :value="false">Inactivo</option>
                </select>
              </div>

              <!-- Domicilio -->
              <div class="md:col-span-2">
                <label for="domicilio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Domicilio <span class="text-red-500">*</span>
                </label>
                <textarea
                  id="domicilio"
                  v-model="form.domicilio"
                  rows="2"
                  required
                  class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                  :class="{ 'border-red-500': form.errors.domicilio }"
                ></textarea>
                <div v-if="form.errors.domicilio" class="mt-1 text-sm text-red-600">{{ form.errors.domicilio }}</div>
              </div>

              <!-- Teléfono Fijo -->
              <div class="md:col-span-1">
                <label for="telefono_fijo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Teléfono Fijo
                </label>
                <input
                  id="telefono_fijo"
                  v-model="form.telefono_fijo"
                  type="text"
                  class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                  :class="{ 'border-red-500': form.errors.telefono_fijo }"
                />
                <div v-if="form.errors.telefono_fijo" class="mt-1 text-sm text-red-600">{{ form.errors.telefono_fijo }}</div>
              </div>

              <!-- Teléfono Celular -->
              <div class="md:col-span-1">
                <label for="telefono_celular" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Teléfono Celular
                </label>
                <input
                  id="telefono_celular"
                  v-model="form.telefono_celular"
                  type="text"
                  class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                  :class="{ 'border-red-500': form.errors.telefono_celular }"
                />
                <div v-if="form.errors.telefono_celular" class="mt-1 text-sm text-red-600">{{ form.errors.telefono_celular }}</div>
              </div>

              <!-- Email -->
              <div class="md:col-span-2">
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Email
                </label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                  :class="{ 'border-red-500': form.errors.email }"
                />
                <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
              </div>

            </div>
          </div>

          <!-- Footer -->
          <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
            >
              <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ form.processing ? 'Guardando...' : (mode === 'create' ? 'Crear' : 'Actualizar') }}
            </button>
            <button
              type="button"
              @click="close"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>