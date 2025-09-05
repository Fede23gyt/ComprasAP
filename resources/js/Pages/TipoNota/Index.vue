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
      const response = await axios.patch(`/nomencladores/tipos-nota/${item.id}/toggle`);

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
      .delete(`/nomencladores/tipos-nota/${item.id}`, {
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
      const downloadUrl = `/nomencladores/tipos-nota/export/${format}?search=${search.value}`;
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
    <!-- Page Content usando el diseño original -->
    <div class="content">
      <div class="page-title">
        <h1 class="text-gray-900 dark:text-white">Tipos de Nota de Pedido</h1>
      </div>

      <!-- Modal Component -->
      <TipoNotaModal
        v-if="showModal"
        :item="modalItem"
        :mode="modalMode"
        @close="closeModal"
        @saved="onModalSaved"
      />

      <!-- Search Section with Button -->
      <div class="search-section">
        <div class="search-input">
          <i class="fas fa-search text-gray-400"></i>
          <input 
            type="text" 
            placeholder="Buscar tipo de nota..."
            v-model="search"
            @input="$inertia.get(route('nomencladores.tipos-nota.index', { search: $event.target.value }))"
            class="search-field"
          >
        </div>
        <button 
          class="btn btn-primary"
          @click="openCreateModal"
        >
          <i class="fas fa-plus"></i> Nuevo Tipo
        </button>
      </div>

      <!-- Table -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">Listado de Tipos de Nota</h2>
          <div style="display: flex; gap: 1rem;">
            <button class="btn btn-outline" @click="confirmExport('pdf')">
              <i class="fas fa-file-pdf"></i> Exportar PDF
            </button>
            <button class="btn btn-outline" @click="confirmExport('excel')">
              <i class="fas fa-file-excel"></i> Exportar Excel
            </button>
          </div>
        </div>
        <div class="card-body">
          <table class="table">
            <thead>
              <tr>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="item in filteredPage" 
                :key="item.id"
              >
                <td>{{ item.descripcion }}</td>
                <td>
                  <span 
                    class="status"
                    :class="{
                      'status-approved': item.estado === 'Habilitado',
                      'status-rejected': item.estado === 'No habilitado'
                    }"
                  >
                    <i 
                      class="fas"
                      :class="{
                        'fa-check': item.estado === 'Habilitado',
                        'fa-times': item.estado === 'No habilitado'
                      }"
                    ></i>
                    {{ item.estado }}
                  </span>
                </td>
                <td>
                  <div style="display: flex; gap: 0.5rem;">
                    <button 
                      class="btn btn-outline"
                      @click="openEditModal(item)"
                      title="Editar"
                    >
                      <i class="fas fa-edit"></i>
                    </button>
                    <button 
                      class="btn"
                      :class="item.estado === 'Habilitado' ? 'btn-danger' : 'btn-success'"
                      @click="confirmToggleEstado(item)"
                      :title="item.estado === 'Habilitado' ? 'Deshabilitar' : 'Habilitar'"
                    >
                      <i 
                        class="fas"
                        :class="item.estado === 'Habilitado' ? 'fa-times' : 'fa-check'"
                      ></i>
                    </button>
                    <button 
                      class="btn btn-danger"
                      @click="confirmDelete(item)"
                      title="Eliminar"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Empty state -->
              <tr v-if="filteredPage.length === 0">
                <td colspan="3" style="text-align: center; padding: 2rem;">
                  <div style="color: #64748b;">
                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                    <h3 style="margin-bottom: 0.5rem;">No hay tipos de nota</h3>
                    <p style="margin-bottom: 1rem;">
                      {{ search ? 'No se encontraron resultados para tu búsqueda' : 'Comienza creando un nuevo tipo de nota' }}
                    </p>
                    <button 
                      v-if="!search"
                      class="btn btn-primary"
                      @click="openCreateModal"
                    >
                      <i class="fas fa-plus"></i> Crear primer tipo
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Info adicional -->
      <div style="margin-top: 1rem; font-size: 0.875rem; color: #64748b;">
        Mostrando {{ filteredPage.length }} de {{ items.total }} tipos de nota
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Estilos específicos para esta página, usando el diseño original */
:root {
  --primary: #2563eb;
  --primary-dark: #1d4ed8;
  --secondary: #64748b;
  --success: #10b981;
  --warning: #f59e0b;
  --danger: #ef4444;
  --light: #f8fafc;
  --dark: #1e293b;
  --gray: #94a3b8;
}

/* Content */
.content {
  padding: 2rem;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.card {
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
  overflow: hidden;
}

.dark .card {
  background: #374151;
}

.card-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.dark .card-header {
  border-bottom-color: #4b5563;
}

.card-title {
  font-weight: 600;
  font-size: 1.125rem;
  color: #1f2937;
}

.dark .card-title {
  color: #f3f4f6;
}

.card-body {
  padding: 1.5rem;
}

/* Search Section */
.search-section {
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.search-input {
  display: flex;
  align-items: center;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  padding: 0.5rem 1rem;
  flex: 1;
  max-width: 400px;
}

.dark .search-input {
  background: #374151;
  border-color: #4b5563;
}

.search-field {
  border: none;
  background: transparent;
  outline: none;
  margin-left: 0.5rem;
  width: 100%;
  color: #1f2937;
}

.dark .search-field {
  color: #f3f4f6;
}

.search-field::placeholder {
  color: #9ca3af;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  border: none;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
}

.btn-primary {
  background-color: var(--primary);
  color: white;
}

.btn-primary:hover {
  background-color: var(--primary-dark);
}

.btn-success {
  background-color: var(--success);
  color: white;
}

.btn-success:hover {
  opacity: 0.9;
}

.btn-danger {
  background-color: var(--danger);
  color: white;
}

.btn-danger:hover {
  opacity: 0.9;
}

.btn-outline {
  background-color: transparent;
  border: 1px solid var(--gray);
  color: var(--dark);
}

.btn-outline:hover {
  background-color: var(--light);
}

/* Tables */
.table {
  width: 100%;
  border-collapse: collapse;
}

.table th, .table td {
  padding: 0.75rem 1rem;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
}

.table th {
  font-weight: 500;
  color: var(--secondary);
  background-color: #f8fafc;
}

.dark .table th {
  background-color: #4b5563;
  color: #d1d5db;
  border-bottom-color: #6b7280;
}

.dark .table td {
  color: #f3f4f6;
  border-bottom-color: #4b5563;
}

.table tr:hover {
  background-color: #f8fafc;
}

.dark .table tr:hover {
  background-color: #4b5563;
}

.status {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.875rem;
  font-weight: 500;
}

.status-approved {
  background-color: #d1fae5;
  color: #065f46;
}

.status-rejected {
  background-color: #fee2e2;
  color: #b91c1c;
}

/* Responsive */
@media (max-width: 1024px) {
  .page-title {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }
}

@media (max-width: 768px) {
  .content {
    padding: 1rem;
  }
  
  .card-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }
  
  .search-input {
    max-width: 100%;
  }
  
  .search-section {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }
}
</style>
