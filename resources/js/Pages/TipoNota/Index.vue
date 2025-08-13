<!-- resources/js/Pages/TipoNota/Index.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import TipoNotaModal from '@/Components/TipoNotaModal.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';
import axios from 'axios';

const props = defineProps({
  items: Object, // paginado
  filters: Object,
});

const search = ref(props.filters?.search || '');

// Buscador sobre la **página actual**
const filteredPage = computed(() =>
  props.items.data.filter(item =>
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

// Función para normalizar el estado y mostrar correctamente
const normalizeEstado = (estado) => {
  return estado === 'Habilitado' ? 'Habilitado' : 'No habilitado';
};

// Determinar si el estado es habilitado para controles visuales
const isHabilitado = (estado) => {
  return estado === 'Habilitado';
};

// Toggle Estado con SweetAlert2
const confirmToggleEstado = async (item) => {
  // Determinar el estado actual de forma correcta
  const estaHabilitado = item.estado === 'Habilitado';
  // Usar valores de texto para la base de datos según la definición del enum en PostgreSQL
  const nuevoEstado = estaHabilitado ? 'No habilitado' : 'Habilitado';

  // Mostrar información de depuración en la consola
  console.log('Estado actual:', item.estado);
  console.log('Está habilitado:', estaHabilitado);
  console.log('Nuevo estado a enviar:', nuevoEstado);

  const result = await Swal.fire({
    title: '¿Confirmar cambio de estado?',
    text: `¿Deseas ${nuevoEstado === 'No habilitado' ? 'deshabilitar' : 'habilitar'} el tipo de nota "${item.descripcion}"?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: nuevoEstado === 'No habilitado' ? '#ef4444' : '#22c55e',
    cancelButtonColor: '#6b7280',
    confirmButtonText: nuevoEstado === 'No habilitado' ? 'Sí, deshabilitar' : 'Sí, habilitar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  });

  if (result.isConfirmed) {
    try {
      // Usamos axios directamente para enviar la solicitud PATCH
      const response = await axios.patch(`/tipos-nota/${item.id}/toggle-estado`, {
        estado: nuevoEstado
      });

      console.log('Respuesta:', response.data);

      if (response.data.success) {
        // Actualizamos el estado localmente con el valor devuelto por el servidor
        item.estado = response.data.tipoNota.estado;

        Swal.fire({
          title: '¡Éxito!',
          text: response.data.message,
          icon: 'success',
          timer: 2000,
          showConfirmButton: false
        });
      } else {
        throw new Error(response.data.message || 'Error al cambiar el estado');
      }
    } catch (error) {
      console.error('Error al cambiar estado:', error);

      // En caso de error, recargamos la página para mantener la sincronización
      Swal.fire({
        title: 'Error',
        text: error.response?.data?.message || error.message || 'No se pudo cambiar el estado. Recargando...',
        icon: 'error',
        timer: 2000,
        showConfirmButton: false,
        willClose: () => {
          window.location.reload();
        }
      });
    }
  }
};

// Confirmación para eliminar
const confirmDelete = async (item) => {
  const result = await Swal.fire({
    title: '¿Estás seguro?',
    text: `¿Deseas eliminar el tipo de nota "${item.descripcion}"? Esta acción no se puede deshacer.`,
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
      .delete(`/tipos-nota/${item.id}`, {
        onSuccess: () => {
          Swal.fire({
            title: '¡Eliminado!',
            text: 'El tipo de nota ha sido eliminado correctamente.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          });
        },
        onError: () => {
          Swal.fire({
            title: 'Error',
            text: 'No se pudo eliminar el tipo de nota. Inténtalo nuevamente.',
            icon: 'error',
            confirmButtonText: 'Entendido'
          });
        }
      });
  }
};

// Exportación con confirmación
const confirmExport = async (format) => {
  const formatText = format === 'pdf' ? 'PDF' : 'Excel';

  const result = await Swal.fire({
    title: `Exportar a ${formatText}`,
    text: `¿Deseas exportar ${props.items.total} tipos de nota a ${formatText}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3b82f6',
    cancelButtonColor: '#6b7280',
    confirmButtonText: `Sí, exportar a ${formatText}`,
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  });

  if (result.isConfirmed) {
    // Mostrar loading
    Swal.fire({
      title: 'Generando archivo...',
      text: 'Por favor espera mientras se genera el archivo.',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    try {
      // Crear un enlace temporal para descargar
      const downloadUrl = `/tipos-nota/export/${format}?search=${search.value}`;
      const link = document.createElement('a');
      link.href = downloadUrl;
      link.download = `tipos_nota_${new Date().toISOString().split('T')[0]}.${format === 'pdf' ? 'pdf' : 'xlsx'}`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

      // Mostrar éxito
      Swal.fire({
        title: '¡Éxito!',
        text: `Archivo ${formatText} generado correctamente`,
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
      });
    } catch (error) {
      Swal.fire({
        title: 'Error',
        text: `No se pudo generar el archivo ${formatText}. Inténtalo nuevamente.`,
        icon: 'error',
        confirmButtonText: 'Entendido'
      });
    }
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
  <AppLayout title="Tipos de Nota de Pedido">
    <!-- Header Section -->
    <section class="bg-gray-100 dark:bg-gray-900 pt-4 pb-6">
      <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tipos de Nota de Pedido</h1>
      </div>
    </section>

    <!-- Modal Component -->
    <TipoNotaModal
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
              @input="$inertia.get(route('tipos-nota.index', { search: $event.target.value }))"
              placeholder="Buscar tipo de nota..."
              class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
            />
          </label>
        </div>

        <!-- Botones de acción -->
        <div class="flex items-center space-x-3">
          <!-- Botones de exportación -->
          <div class="flex items-center space-x-2">
            <button
              @click="confirmExport('pdf')"
              class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 shadow-md hover:shadow-lg flex items-center space-x-2"
              title="Exportar a PDF"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              <span>PDF</span>
            </button>

            <button
              @click="confirmExport('excel')"
              class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 shadow-md hover:shadow-lg flex items-center space-x-2"
              title="Exportar a Excel"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              <span>Excel</span>
            </button>
          </div>

          <!-- Separador -->
          <div class="w-px h-8 bg-gray-300 dark:bg-gray-600"></div>

          <!-- Botón Nuevo -->
          <button
            @click="openCreateModal"
            class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200 shadow-md hover:shadow-lg flex items-center space-x-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <span>Nuevo Tipo</span>
          </button>
        </div>
      </div>

      <!-- Tabla -->
      <div class="overflow-hidden bg-white dark:bg-gray-800 rounded-xl shadow-lg">
        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700/50">
          <tr>
            <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Descripción
            </th>
            <th class="px-6 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Estado
            </th>
            <th class="px-6 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Acciones
            </th>
          </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr
            v-for="item in filteredPage"
            :key="item.id"
            class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150"
          >
            <!-- Descripción -->
            <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">
              <div class="font-medium">{{ item.descripcion }}</div>
            </td>

            <!-- Estado con Toggle -->
            <td class="px-6 py-3 text-center">
              <label class="inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  :checked="isHabilitado(item.estado)"
                  class="sr-only peer"
                  @change="confirmToggleEstado(item)"
                />
                <div
                  class="relative w-12 h-6 rounded-full transition-colors duration-200 ease-in-out after:content-[''] after:absolute after:top-0.5 after:w-5 after:h-5 after:bg-white after:border after:border-gray-300 after:rounded-full after:transition-all after:duration-200 after:ease-in-out"
                  :class="[
                      isHabilitado(item.estado)
                        ? 'bg-green-500'
                        : 'bg-red-500',
                      isHabilitado(item.estado)
                        ? 'after:left-[1.5rem]'
                        : 'after:left-0.5'
                    ]"
                ></div>
              </label>
              <div class="mt-1 text-xs font-medium" :class="isHabilitado(item.estado) ? 'text-green-600' : 'text-red-600'">
                {{ isHabilitado(item.estado) ? 'Habilitado' : 'No habilitado' }}
              </div>
            </td>

            <!-- Acciones -->
            <td class="px-6 py-3 text-center">
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
          <tr v-if="filteredPage.length === 0">
            <td colspan="3" class="px-6 py-9 text-center">
              <div class="text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium">No hay tipos de nota</h3>
                <p class="mt-1 text-sm">{{ search ? 'No se encontraron resultados para tu búsqueda' : 'Comienza creando un nuevo tipo de nota de pedido' }}</p>
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

      <!-- Paginación moderna -->
      <div class="mt-6 flex items-center justify-between text-sm">
        <!-- Info de página -->
        <p class="text-gray-600 dark:text-gray-300">
          Mostrando
          <span class="font-medium">{{ items.from }} - {{ items.to }}</span>
          de
          <span class="font-medium">{{ items.total }}</span>
          tipos de nota
        </p>

        <!-- Links de paginación -->
        <nav class="flex space-x-1">
          <Link
            v-for="(link, index) in items.links"
            :key="index"
            :href="link.url ?? '#'"
            :class="[
              'px-3 py-2 rounded-md border text-sm font-medium transition-colors duration-150',
              link.active
                ? 'bg-orange-600 text-white border-orange-600'
                : link.url
                  ? 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600'
                  : 'bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed border-gray-200 dark:border-gray-700'
            ]"
            v-html="link.label"
          />
        </nav>
      </div>

      <!-- Resumen de filtros (si hay búsqueda) -->
      <div v-if="search" class="mt-4 text-sm text-gray-500 dark:text-gray-400">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
          Filtrado por: "{{ search }}"
        </span>
      </div>
    </main>
  </AppLayout>
</template>
