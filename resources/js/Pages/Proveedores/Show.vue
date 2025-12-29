<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  proveedor: Object,
});

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
  <AppLayout title="Ver Proveedor">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Información del Proveedor
        </h2>
        <div class="flex space-x-2">
          <Link
            :href="route('nomencladores.proveedores.edit', proveedor.id)"
            class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-500 focus:bg-yellow-500 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Editar
          </Link>
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
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">

          <!-- Header con estado -->
          <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <h1 class="text-2xl font-medium text-gray-900 dark:text-white">
                  {{ proveedor.nombre_proveedor }}
                </h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">
                  {{ proveedor.razon_social }}
                </p>
              </div>
              <span :class="[
                'inline-flex px-3 py-2 text-sm font-semibold rounded-full',
                proveedor.estado
                  ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                  : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
              ]">
                {{ proveedor.estado ? 'Activo' : 'Inactivo' }}
              </span>
            </div>
          </div>

          <!-- Información principal -->
          <div class="p-6 lg:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

              <!-- Datos fiscales -->
              <div class="space-y-6">
                <div>
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                    Datos Fiscales
                  </h3>

                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        CUIT
                      </label>
                      <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                        {{ formatCuit(proveedor.cuit) }}
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tipo IVA
                      </label>
                      <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ proveedor.tipo }}
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Razón Social
                      </label>
                      <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ proveedor.razon_social }}
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nombre del Proveedor
                      </label>
                      <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ proveedor.nombre_proveedor }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Información de contacto -->
              <div class="space-y-6">
                <div>
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">
                    Información de Contacto
                  </h3>

                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Domicilio
                      </label>
                      <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ proveedor.domicilio || 'No especificado' }}
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Teléfono Fijo
                      </label>
                      <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ proveedor.telefono_fijo || 'No especificado' }}
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Teléfono Celular
                      </label>
                      <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ proveedor.telefono_celular || 'No especificado' }}
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Email
                      </label>
                      <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        <a
                          v-if="proveedor.email"
                          :href="`mailto:${proveedor.email}`"
                          class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                        >
                          {{ proveedor.email }}
                        </a>
                        <span v-else>No especificado</span>
                      </p>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- Metadatos -->
          <div class="bg-gray-50 dark:bg-gray-900 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-400">
              <div>
                <span class="font-medium">Creado:</span>
                {{ new Date(proveedor.created_at).toLocaleDateString('es-AR', {
                  year: 'numeric',
                  month: 'long',
                  day: 'numeric',
                  hour: '2-digit',
                  minute: '2-digit'
                }) }}
              </div>
              <div>
                <span class="font-medium">Última actualización:</span>
                {{ new Date(proveedor.updated_at).toLocaleDateString('es-AR', {
                  year: 'numeric',
                  month: 'long',
                  day: 'numeric',
                  hour: '2-digit',
                  minute: '2-digit'
                }) }}
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AppLayout>
</template>