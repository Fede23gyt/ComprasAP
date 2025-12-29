<!-- resources/js/Pages/Memo/Index.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import MemoModal from '@/Components/MemoModal.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';
import axios from 'axios';

const props = defineProps({
  items: Object,
});

const search = ref('');

// Buscador
const filteredItems = computed(() =>
  props.items.filter(item =>
    item.nombre.toLowerCase().includes(search.value.toLowerCase()) ||
    item.descripcion.toLowerCase().includes(search.value.toLowerCase())
  )
);

// Modal States
const showModal = ref(false);
const modalItem = ref(null);
const modalMode = ref('create');

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

// Confirmación para eliminar
const confirmDelete = async (item) => {
  const result = await Swal.fire({
    title: '¿Estás seguro?',
    text: `¿Deseas eliminar el memo "${item.descripcion}"? Esta acción no se puede deshacer.`,
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
      .delete(`/nomencladores/memos/${item.id}`, {
        onSuccess: () => {
          Swal.fire({
            title: '¡Eliminado!',
            text: 'El memo ha sido eliminado correctamente.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          });
        },
        onError: () => {
          Swal.fire({
            title: 'Error',
            text: 'No se pudo eliminar el memo. Inténtalo nuevamente.',
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
  <AppLayout title="Memos">
    <!-- Page Content -->
    <div class="p-8">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Memos</h1>
      </div>

      <!-- Search Section with Button -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="relative flex-1 max-w-md">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
          </div>
          <input 
            v-model="search"
            type="text" 
            placeholder="Buscar memos..."
            class="pl-10 pr-4 py-2 w-full border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
          >
        </div>
        <button 
          class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
          @click="openCreateModal"
        >
          <i class="fas fa-plus mr-2"></i> Nuevo Memo
        </button>
      </div>

      <!-- Table -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Listado de Memos</h2>
        </div>
        <div class="p-6">
          <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  ID
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Nombre
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Estado
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr 
                v-for="item in filteredItems" 
                :key="item.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
              >
                <!-- ID -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ item.id }}
                  </div>
                </td>
                
                <!-- Nombre -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">
                    {{ item.nombre }}
                  </div>
                </td>
                
                <!-- Estado -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="item.estado === 'Habilitado' 
                      ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                      : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
                  >
                    {{ item.estado }}
                  </span>
                </td>
                
                <!-- Acciones -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <!-- Ver detalle -->
                    <Link 
                      :href="route('nomencladores.memos.show', item.id)"
                      class="p-2 text-green-600 hover:text-green-700 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors"
                      title="Ver detalle"
                    >
                      <i class="fas fa-eye"></i>
                    </Link>
                    
                    <!-- Editar -->
                    <button 
                      class="p-2 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                      @click="openEditModal(item)"
                      title="Editar"
                    >
                      <i class="fas fa-edit"></i>
                    </button>
                    
                    <!-- Eliminar -->
                    <button 
                      class="p-2 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                      @click="confirmDelete(item)"
                      title="Eliminar"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Empty state -->
              <tr v-if="filteredItems.length === 0">
                <td colspan="4" class="px-6 py-12 text-center">
                  <div class="text-gray-500 dark:text-gray-400">
                    <i class="fas fa-sticky-note text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium mb-2">
                      {{ search ? 'No se encontraron resultados' : 'No hay memos' }}
                    </h3>
                    <p class="mb-4">
                      {{ search ? 'Intenta con otros términos de búsqueda' : 'Comienza creando un nuevo memo' }}
                    </p>
                    <button 
                      v-if="!search"
                      class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                      @click="openCreateModal"
                    >
                      <i class="fas fa-plus mr-2"></i> Crear primer memo
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Info adicional -->
      <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
        Mostrando {{ filteredItems.length }} de {{ items.length }} memos
      </div>
    </div>

    <!-- Modal Component -->
    <MemoModal
      v-if="showModal"
      :item="modalItem"
      :mode="modalMode"
      @close="closeModal"
      @saved="onModalSaved"
    />
  </AppLayout>
</template>