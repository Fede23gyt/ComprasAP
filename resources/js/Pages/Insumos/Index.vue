<!-- resources/js/Pages/Insumos/Index.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
// Cambiar el import del modal
import InsumoViewModal from '@/Components/InsumoViewModal.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
  insumos: Object, // paginado
  padres: Array,
});

const search = ref('');

// Buscador sobre la **página actual**
const filteredPage = computed(() =>
  props.insumos.data.filter(i =>
    i.descripcion.toLowerCase().includes(search.value.toLowerCase()) ||
    i.codigo.toLowerCase().includes(search.value.toLowerCase())
  )
);

// Modal States
const showModal = ref(false);
const modalItem = ref(null);
const modalMode = ref('view'); // 'view', 'edit', 'create'

const openModal = (item, mode = 'view') => {
  modalItem.value = item;
  modalMode.value = mode;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  modalItem.value = null;
  modalMode.value = 'view';
};

const openCreateModal = () => {
  openModal(null, 'create');
};

const openEditModal = (item) => {
  openModal(item, 'edit');
};

// Toggle Registrable con SweetAlert2
const confirmToggle = async (item, field) => {
  const nuevoEstado = !item[field];
  const estadoTexto = nuevoEstado ? 'registrable' : 'no registrable';

  const result = await Swal.fire({
    title: '¿Confirmar cambio?',
    text: `¿Deseas marcar "${item.descripcion}" como ${estadoTexto}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: nuevoEstado ? '#22c55e' : '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: `Sí, marcar como ${estadoTexto}`,
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  });

  if (result.isConfirmed) {
    useForm({ [field]: nuevoEstado })
      .patch(`/insumos/${item.id}/toggle-${field}`, {
        onSuccess: () => {
          Swal.fire({
            title: '¡Éxito!',
            text: `Insumo marcado como ${estadoTexto} correctamente`,
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
    text: `¿Deseas eliminar el insumo "${item.descripcion}"? Esta acción no se puede deshacer.`,
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
      .delete(`/insumos/${item.id}`, {
        onSuccess: () => {
          Swal.fire({
            title: '¡Eliminado!',
            text: 'El insumo ha sido eliminado correctamente.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          });
        },
        onError: () => {
          Swal.fire({
            title: 'Error',
            text: 'No se pudo eliminar el insumo. Inténtalo nuevamente.',
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
    text: `¿Deseas exportar ${props.insumos.total} insumos a ${formatText}?`,
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
      const downloadUrl = `/insumos/export/${format}?search=${search.value}`;
      const link = document.createElement('a');
      link.href = downloadUrl;
      link.download = `insumos_${new Date().toISOString().split('T')[0]}.${format === 'pdf' ? 'pdf' : 'xlsx'}`;
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
  <AppLayout>
    <!-- Header Section -->
    <section class="bg-gray-100 dark:bg-gray-900 pt-4 pb-6">
      <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Insumos</h1>
      </div>
    </section>

    <!-- Modal Component -->
    <InsumoViewModal
      v-if="showModal"
      :item="modalItem"
      :mode="modalMode"
      :padres="padres"
      @close="closeModal"
      @saved="onModalSaved"
    />

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">

      <!-- Search, Create Button and Export Options -->
      <div class="flex items-center justify-between mb-6 gap-4">
        <!-- Buscador -->
        <div class="flex-1 max-w-md">
          <label class="flex items-center space-x-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Buscar:</span>
            <input
              v-model="search"
              @input="$inertia.get(route('insumos.index', { search: $event.target.value }))"
              placeholder="Buscar por código o descripción..."
              class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
            class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200 shadow-md hover:shadow-lg flex items-center space-x-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <span>Nuevo Insumo</span>
          </button>
        </div>
      </div>

      <!-- Tabla más compacta -->
      <div class="overflow-hidden bg-white dark:bg-gray-800 rounded-xl shadow-lg">
        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700/50">
          <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Código
            </th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Descripción
            </th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Clasificación Económica
            </th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Presentación
            </th>
            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Precio
            </th>
            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Estados
            </th>
            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
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
            <!-- Código -->
            <td class="px-4 py-2 text-sm font-mono text-gray-900 dark:text-gray-100">
              <button
                @click="openModal(item, 'view')"
                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:underline cursor-pointer transition-colors duration-150"
              >
                {{ item.codigo }}
              </button>
            </td>

            <!-- Descripción -->
            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
              <button
                @click="openModal(item, 'view')"
                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:underline cursor-pointer transition-colors duration-150 text-left"
              >
                <div class="font-medium">{{ item.descripcion }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400" v-if="item.unidad_solicitud">
                  Unidad: {{ item.unidad_solicitud }}
                </div>
              </button>
            </td>

            <!-- Clasificación Económica -->
            <td class="px-4 py-2 text-sm">
              <div v-if="item.clasif_economica" class="space-y-1">
                <div class="font-medium text-gray-900 dark:text-gray-100">
                  {{ item.clasif_economica.descripcion }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                  Código: {{ item.clasificacion }}
                </div>
              </div>
              <div v-else class="space-y-1">
                <div class="text-gray-400 dark:text-gray-500 italic">Sin clasificación</div>
                <div v-if="item.clasificacion" class="text-xs text-gray-500 dark:text-gray-400">
                  Código: {{ item.clasificacion }}
                </div>
              </div>
            </td>

            <!-- Presentación -->
            <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
              <div v-if="item.presentacion">{{ item.presentacion }}</div>
              <div v-else class="text-gray-400 dark:text-gray-500 italic">Sin presentación</div>
            </td>

            <!-- Precio -->
            <td class="px-4 py-2 text-sm text-right">
              <div v-if="item.precio_insumo && item.precio_insumo > 0" class="space-y-1">
                <div class="font-medium text-green-600 dark:text-green-400">
                  ${{ parseFloat(item.precio_insumo).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                </div>
                <div v-if="item.fecha_ultima" class="text-xs text-gray-500 dark:text-gray-400">
                  {{ new Date(item.fecha_ultima).toLocaleDateString('es-AR') }}
                </div>
                <div v-if="item.precio_testigo" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                  Testigo
                </div>
              </div>
              <div v-else class="text-gray-400 dark:text-gray-500 italic">Sin precio</div>
            </td>

            <!-- Estados (Badges compactos) -->
            <td class="px-4 py-2 text-center">
              <div class="flex flex-wrap items-center justify-center gap-1">
                <!-- Registrable -->
                <span
                  class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-gray-500 dark:text-gray-500"
                  :class="item.registrable
                      ? 'bg-green-100 dark:bg-green-900'
                      : 'bg-red-100 dark:bg-red-900'"
                >
                    {{ item.registrable ? 'R' : '✕' }}
                  </span>

                <!-- Inventariable -->
                <span
                  v-if="item.inventariable"
                  class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-gray-900 dark:text-gray-100 bg-blue-100 dark:bg-blue-900"
                  title="Inventariable"
                >
                    I
                  </span>

                <!-- Rinde Tribunal -->
                <span
                  v-if="item.rend_tribunal"
                  class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-gray-900 dark:text-gray-100 bg-purple-100 dark:bg-purple-900"
                  title="Rinde Tribunal"
                >
                    T
                  </span>
              </div>
            </td>

            <!-- Acciones -->
            <td class="px-4 py-2 text-center">
              <div class="flex items-center justify-center space-x-2">
                <!-- Editar -->
                <button
                  @click="openEditModal(item)"
                  class="text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300 transition-colors duration-150"
                  title="Editar"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </button>

                <!-- Eliminar -->
                <button
                  @click="confirmDelete(item)"
                  class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors duration-150"
                  title="Eliminar"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
            </td>
          </tr>

          <!-- Empty state -->
          <tr v-if="filteredPage.length === 0">
            <td colspan="7" class="px-4 py-12 text-center">
              <div class="text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <h3 class="mt-2 text-sm font-medium">No hay insumos</h3>
                <p class="mt-1 text-sm">{{ search ? 'No se encontraron resultados para tu búsqueda' : 'Comienza creando un nuevo insumo' }}</p>
                <div class="mt-6" v-if="!search">
                  <button
                    @click="openCreateModal"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200"
                  >
                    ➕ Crear primer insumo
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
          <span class="font-medium">{{ insumos.from }} - {{ insumos.to }}</span>
          de
          <span class="font-medium">{{ insumos.total }}</span>
          insumos
        </p>

        <!-- Links de paginación -->
        <nav class="flex space-x-1">
          <Link
            v-for="(link, index) in insumos.links"
            :key="index"
            :href="link.url ?? '#'"
            :class="[
              'px-3 py-2 rounded-md border text-sm font-medium transition-colors duration-150',
              link.active
                ? 'bg-blue-600 text-white border-blue-600'
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
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
          Filtrado por: "{{ search }}"
        </span>
      </div>
    </main>
  </AppLayout>
</template>
