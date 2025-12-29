<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
  tipos: Array,
});

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

  form.post(route('nomencladores.proveedores.store'));
};
</script>

<template>
  <AppLayout title="Nuevo Proveedor">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Nuevo Proveedor
        </h2>
        <Link
          :href="route('nomencladores.proveedores.index')"
          class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:bg-gray-500 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
          Volver
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
          <form @submit.prevent="submit">
            <div class="p-6 lg:p-8">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- CUIT -->
                <div class="md:col-span-1">
                  <label for="cuit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
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
                  <label for="nombre_proveedor" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
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
                  <label for="razon_social" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
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
                  <label for="tipo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
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
                  <label for="estado" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
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
                  <label for="domicilio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Domicilio <span class="text-red-500">*</span>
                  </label>
                  <textarea
                    id="domicilio"
                    v-model="form.domicilio"
                    rows="3"
                    required
                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    :class="{ 'border-red-500': form.errors.domicilio }"
                  ></textarea>
                  <div v-if="form.errors.domicilio" class="mt-1 text-sm text-red-600">{{ form.errors.domicilio }}</div>
                </div>

                <!-- Teléfono Fijo -->
                <div class="md:col-span-1">
                  <label for="telefono_fijo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
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
                  <label for="telefono_celular" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
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
                  <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
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
            <div class="bg-gray-50 dark:bg-gray-900 px-6 py-4 flex justify-end space-x-4">
              <Link
                :href="route('nomencladores.proveedores.index')"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:bg-gray-500 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
              >
                Cancelar
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 disabled:opacity-50"
              >
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ form.processing ? 'Creando...' : 'Crear Proveedor' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>