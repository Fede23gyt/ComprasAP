<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  proveedores: Object,
  filters: Object,
  tipos: Array,
});

const search = ref(props.filters?.search || '');
const tipoFilter = ref(props.filters?.tipo || '');
const estadoFilter = ref(props.filters?.estado ?? '');

const applyFilters = () => {
  router.get('/nomencladores/proveedores', {
    search: search.value,
    tipo: tipoFilter.value,
    estado: estadoFilter.value,
  }, {
    preserveState: true,
    replace: true,
  });
};

const clearFilters = () => {
  search.value = '';
  tipoFilter.value = '';
  estadoFilter.value = '';
  router.get('/nomencladores/proveedores');
};

const confirmDelete = (proveedor) => {
  if (confirm(`¿Eliminar el proveedor "${proveedor.nombre_proveedor}"?`)) {
    router.delete(`/nomencladores/proveedores/${proveedor.id}`);
  }
};

const formatCuit = (cuit) => {
  if (!cuit) return '';
  const clean = cuit.replace(/[^0-9]/g, '');
  if (clean.length === 11) {
    return `${clean.slice(0, 2)}-${clean.slice(2, 10)}-${clean.slice(10)}`;
  }
  return cuit;
};
</script>

<template>
  <AppLayout title="Gestión de Proveedores">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Gestión de Proveedores
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">

          <!-- Header -->
          <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
              <div>
                <h1 class="text-2xl font-medium text-gray-900 dark:text-white">
                  Proveedores
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                  Total: {{ proveedores?.total || 0 }} proveedores
                </p>
              </div>
              <Link
                href="/nomencladores/proveedores/create"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                Nuevo Proveedor
              </Link>
            </div>
          </div>

          <!-- Filtros -->
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Buscar
                </label>
                <input
                  v-model="search"
                  @input="applyFilters"
                  type="text"
                  placeholder="Nombre, razón social o CUIT..."
                  class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Tipo IVA
                </label>
                <select
                  v-model="tipoFilter"
                  @change="applyFilters"
                  class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                >
                  <option value="">Todos los tipos</option>
                  <option v-for="tipo in tipos || []" :key="tipo" :value="tipo">{{ tipo }}</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Estado
                </label>
                <select
                  v-model="estadoFilter"
                  @change="applyFilters"
                  class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                >
                  <option value="">Todos</option>
                  <option value="1">Activos</option>
                  <option value="0">Inactivos</option>
                </select>
              </div>

              <div class="flex items-end">
                <button
                  @click="clearFilters"
                  class="w-full px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Limpiar
                </button>
              </div>
            </div>
          </div>

          <!-- Debug info -->
          <div class="p-4 bg-yellow-50 border-b">
            <p class="text-sm text-gray-600">
              Debug - Proveedores: {{ proveedores ? 'Existe' : 'null' }},
              Data: {{ proveedores?.data ? proveedores.data.length + ' items' : 'no data' }}
            </p>
          </div>

          <!-- Tabla Simple -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    CUIT
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Nombre / Razón Social
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Teléfono
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Estado
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <!-- Si no hay proveedores -->
                <tr v-if="!proveedores || !proveedores.data || proveedores.data.length === 0">
                  <td colspan="5" class="px-6 py-12 text-center">
                    <div class="text-gray-500 dark:text-gray-400">
                      <p class="text-lg font-medium">No hay proveedores disponibles</p>
                      <p class="text-sm">Los proveedores aparecerán aquí cuando estén disponibles.</p>
                    </div>
                  </td>
                </tr>
                <!-- Filas de datos -->
                <tr v-for="proveedor in proveedores?.data || []" :key="proveedor.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ formatCuit(proveedor.cuit) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                      {{ proveedor.nombre_proveedor }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      {{ proveedor.razon_social }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                    <div v-if="proveedor.telefono_fijo">{{ proveedor.telefono_fijo }}</div>
                    <div v-if="proveedor.telefono_celular">{{ proveedor.telefono_celular }}</div>
                    <div v-if="!proveedor.telefono_fijo && !proveedor.telefono_celular" class="text-gray-400">-</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="[
                      'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                      proveedor.estado
                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                    ]">
                      {{ proveedor.estado ? 'Activo' : 'Inactivo' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                    <Link
                      :href="`/nomencladores/proveedores/${proveedor.id}`"
                      class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                    >
                      Ver
                    </Link>
                    <Link
                      :href="`/nomencladores/proveedores/${proveedor.id}/edit`"
                      class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
                    >
                      Editar
                    </Link>
                    <button
                      @click="confirmDelete(proveedor)"
                      class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                    >
                      Eliminar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginación -->
          <div v-if="proveedores?.links && proveedores.links.length > 3" class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700 dark:text-gray-300">
                Mostrando {{ proveedores.from || 0 }} a {{ proveedores.to || 0 }} de {{ proveedores.total || 0 }} resultados
              </div>
              <div class="flex space-x-1">
                <Link
                  v-for="link in proveedores.links || []"
                  :key="link.label"
                  :href="link.url"
                  v-html="link.label"
                  :class="[
                    'px-3 py-2 text-sm border rounded-md',
                    link.active
                      ? 'bg-indigo-600 text-white border-indigo-600'
                      : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
                  ]"
                />
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>