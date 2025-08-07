<!-- resources/js/Pages/TiposCompras/Index.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import TipoCompraModal from '@/Components/TipoCompraModal.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
  items: Array
});

const search = ref('');

// Buscador
const filteredItems = computed(() =>
  props.items.filter(item =>
    item.descripcion.toLowerCase().includes(search.value.toLowerCase())
  )
);

// Modal States
const showModal = ref(false);
const modalItem = ref(null);
const modalMode = ref('create'); // 'create', 'edit'

const openModal = (item = null, mode = 'create') => {
  modalItem.value = item;
  modalMode.value = mode;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  modalItem.value = null;
  modalMode.value = 'create';
};

const openCreateModal = () => {
  openModal(null, 'create');
};

const openEditModal = (item) => {
  openModal(item, 'edit');
};

// Toggle Estado con SweetAlert2
const confirmToggleEstado = async (item) => {
  const nuevoEstado = item.estado === 'Habilitado' ? 'Deshabilitado' : 'Habilitado';

  const result = await Swal.fire({
    title: '¿Confirmar cambio de estado?',
    text: `¿Deseas ${nuevoEstado === 'Deshabilitado' ? 'deshabilitar' : 'habilitar'} el tipo de compra "${item.descripcion}"?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: nuevoEstado === 'Deshabilitado' ? '#ef4444' : '#22c55e',
    cancelButtonColor: '#6b7280',
    confirmButtonText: nuevoEstado === 'Deshabilitado' ? 'Sí, deshabilitar' : 'Sí, habilitar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  });

  if (result.isConfirmed) {
    useForm({ estado: nuevoEstado })
      .patch(`/tipos-compras/${item.id}/toggle-estado`, {
        onSuccess: () => {
          Swal.fire({
            title: '¡Éxito!',
            text: `Estado cambiado a ${nuevoEstado} correctamente`,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          });
        },
        onError: () => {
          Swal.fire({
            title: 'Error',
            text: 'No se pudo cambiar el estado. Inténtalo nuevamente.',
            icon: 'error',
            confirmButtonText: 'Entendido'
          });
        }
      });
  }
};

// Confirmación para eliminar
const confirmDelete = async (item) => {
  const result = await Swal.fire({
    title: '¿Estás seguro?',
    text: `¿Deseas eliminar el tipo de compra "${item.descripcion}"? Esta acción no se puede deshacer.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  });

  if (result.isConfirmed) {
    useForm({})
      .delete(`/tipos-compras/${item.id}`, {
        onSuccess: () => {
          Swal.fire({
            title: '¡Eliminado!',
            text: 'El tipo de compra ha sido eliminado correctamente.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          });
        },
        onError: () => {
          Swal.fire({
            title: 'Error',
            text: 'No se pudo eliminar el tipo de compra. Inténtalo nuevamente.',
            icon: 'error',
            confirmButtonText: 'Entendido'
          });
        }
      });
  }
};

// Callback cuando se guarda desde el modal
const onModalSaved = (message = 'Operación realizada correctamente') => {
  closeModal();
  Swal.fire({
    title: '¡Éxito!',
    text: message,
    icon: 'success',
    timer: 2000,
    showConfirmButton: false
  });
};
</script>

<template>
  <AppLayout title="Tipos de Compra">
    <!-- Header Section -->
    <section class="bg-gray-100 dark:bg-gray-900 pt-4 pb-6">
      <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tipos de Compra</h1>
      </div>
    </section>

    <!-- Modal Component -->
    <TipoCompraModal
      v-if="showModal"
      :item="modalItem"
      :mode="modalMode"
      @close="closeModal"
      @saved="onModalSaved"
    />

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-8">

      <!-- Search and Create Button -->
      <div class="flex items-center justify-between mb-6 gap-4">
        <!-- Buscador -->
        <div class="flex-1 max-w-md">
          <label class="flex items-center space-x-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Buscar:</span>
            <input
              v-model="search"
              placeholder="Buscar tipo de compra..."
              class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
            />
          </label>
        </div>

        <!-- Botón Nuevo -->
        <button
          @click="openCreateModal"
          class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200 shadow-md hover:shadow-lg"
        >
          ➕ Nuevo Tipo
        </button>
      </div>

      <!-- Tabla -->
      <div class="overflow-hidden bg-white dark:bg-gray-800 rounded-xl shadow-lg">
        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Descripción
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Estado
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Acciones
            </th>
          </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr
            v-for="item in filteredItems"
            :key="item.id"
            class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150"
          >
            <!-- Descripción -->
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
              <div class="font-medium">{{ item.descripcion }}</div>
            </td>

            <!-- Estado con Toggle -->
            <td class="px-6 py-4 text-center">
              <label class="inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  :checked="item.estado === 'Habilitado'"
                  class="sr-only peer"
                  @change="confirmToggleEstado(item)"
                />
                <div
                  class="relative w-12 h-6 rounded-full transition-colors duration-200 ease-in-out after:content-[''] after:absolute after:top-0.5 after:w-5 after:h-5 after:bg-white after:border after:border-gray-300 after:rounded-full after:transition-all after:duration-200 after:ease-in-out"
                  :class="[
                      item.estado === 'Habilitado'
                        ? 'bg-green-500'
                        : 'bg-red-500',
                      item.estado === 'Habilitado'
                        ? 'after:left-[1.5rem]'
                        : 'after:left-0.5'
                    ]"
                ></div>
              </label>

            </td>

            <!-- Acciones -->
            <td class="px-6 py-4 text-center">
              <div class="flex items-center justify-center space-x-3">
                <!-- Editar -->
                <button
                  @click="openEditModal(item)"
                  class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-150"
                  title="Editar"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </button>

                <!-- Eliminar -->
                <button
                  @click="confirmDelete(item)"
                  class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors duration-150"
                  title="Eliminar"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
            </td>
          </tr>

          <!-- Empty state -->
          <tr v-if="filteredItems.length === 0">
            <td colspan="3" class="px-6 py-12 text-center">
              <div class="text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium">No hay tipos de compra</h3>
                <p class="mt-1 text-sm">{{ search ? 'No se encontraron resultados para tu búsqueda' : 'Comienza creando un nuevo tipo de compra' }}</p>
                <div class="mt-6" v-if="!search">
                  <button
                    @click="openCreateModal"
                    class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200"
                  >
                    ➕ Crear primer tipo
                  </button>
                </div>
              </div>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <!-- Info adicional -->
      <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
        Mostrando {{ filteredItems.length }} de {{ items.length }} tipos de compra
      </div>
    </main>
  </AppLayout>
</template>
