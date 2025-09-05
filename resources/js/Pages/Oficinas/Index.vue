<!-- resources/js/Pages/Oficinas/Index.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import OficinaFormModal from '@/Components/OficinaFormModal.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
  oficinas: Array,
  padres: Array,
});

// Estado de búsqueda
const search = ref('');
const searchInput = ref(null);

// Función para aplanar el árbol manteniendo la jerarquía
const flatTree = (nodes, level = 0) => {
  const result = [];
  nodes.forEach(node => {
    result.push({ ...node, level });
    if (node.children?.length) {
      result.push(...flatTree(node.children, level + 1));
    }
  });
  return result;
};

// Oficinas filtradas
const filteredTree = computed(() => {
  const term = search.value.toLowerCase().trim();
  const tree = flatTree(props.oficinas);

  if (!term) return tree;

  return tree.filter(oficina =>
    oficina.nombre.toLowerCase().includes(term) ||
    oficina.codigo_oficina.toLowerCase().includes(term) ||
    oficina.abreviacion.toLowerCase().includes(term)
  );
});

// Estado del modal
const showModal = ref(false);
const modalProps = ref({ oficina: null, padres: [] });

// Abrir modal para crear o editar
const openModal = (oficina = null) => {
  modalProps.value = {
    oficina,
    padres: props.padres.filter(padre => padre.id !== oficina?.id)
  };
  showModal.value = true;
};

// Cerrar modal
const closeModal = () => {
  showModal.value = false;
  modalProps.value = { oficina: null, padres: [] };
};

// Manejar cuando se guarda una oficina
const handleSaved = () => {
  closeModal();
  // Usar router.reload() en lugar de window.location.reload()
  setTimeout(() => {
    window.location.reload();
  }, 100);
};

// Toggle del estado de la oficina con SweetAlert
const confirmToggle = (oficina) => {
  const accion = oficina.estado === 'Habilitada' ? 'inhabilitar' : 'habilitar';
  const nuevoEstado = oficina.estado === 'Habilitada' ? 'No habilitada' : 'Habilitada';

  Swal.fire({
    title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} oficina?`,
    text: `¿Estás seguro de ${accion} "${oficina.nombre}"?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: oficina.estado === 'Habilitada' ? '#ef4444' : '#10b981',
    cancelButtonColor: '#6b7280',
    confirmButtonText: `Sí, ${accion}`,
    cancelButtonText: 'Cancelar',
    backdrop: true
  }).then((result) => {
    if (result.isConfirmed) {
      useForm({}).patch(`/nomencladores/oficinas/${oficina.id}/toggle`, {
        onSuccess: () => {
          Swal.fire({
            title: '¡Éxito!',
            text: `Oficina ${accion}da correctamente`,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
          });
        },
        onError: () => {
          Swal.fire({
            title: 'Error',
            text: 'Hubo un problema al cambiar el estado de la oficina',
            icon: 'error',
            confirmButtonColor: '#ef4444'
          });
        }
      });
    }
  });
};

// Limpiar búsqueda
const clearSearch = () => {
  search.value = '';
  nextTick(() => searchInput.value?.focus());
};

// Focus en búsqueda con atajo de teclado
const handleKeydown = (event) => {
  if (event.ctrlKey && event.key === 'k') {
    event.preventDefault();
    searchInput.value?.focus();
  }
};

// Montar event listener para atajos
onMounted(() => {
  document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
  <AppLayout title="Oficinas">
    <!-- Page Content -->
    <div class="content">
      <div class="page-title">
        <h1 class="text-gray-900 dark:text-white">Oficinas</h1>
      </div>



      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-number">{{ filteredTree.length }}</div>
          <div class="stat-label">Total Oficinas</div>
        </div>
        <div class="stat-card stat-success">
          <div class="stat-number">{{ filteredTree.filter(o => o.estado === 'Habilitada').length }}</div>
          <div class="stat-label">Habilitadas</div>
        </div>
        <div class="stat-card stat-danger">
          <div class="stat-number">{{ filteredTree.filter(o => o.estado === 'No habilitada').length }}</div>
          <div class="stat-label">No habilitadas</div>
        </div>
      </div>

      <!-- Search Section with Button -->
      <div class="search-section">
        <div class="search-input">
          <i class="fas fa-search text-gray-400"></i>
          <input
            ref="searchInput"
            v-model="search"
            type="text"
            placeholder="Buscar oficinas..."
            class="search-field"
          >
          <button
            v-if="search"
            @click="clearSearch"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
          >
            <i class="fas fa-times"></i>
          </button>
        </div>
        <button
          class="btn btn-primary"
          @click="openModal()"
        >
          <i class="fas fa-plus"></i> Nueva Oficina
        </button>
      </div>

      <!-- Table -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">Listado de Oficinas</h2>
        </div>
        <div class="card-body">
          <table class="table">
            <thead>
              <tr>
                <th>Código</th>
                <th>Nombre / Jerarquía</th>
                <th>Abreviación</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="oficina in filteredTree"
                :key="oficina.id"
              >
                <!-- Código -->
                <td>
                  <span class="text-sm font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-md text-gray-900 dark:text-gray-100">
                    {{ oficina.codigo_oficina }}
                  </span>
                </td>

                <!-- Nombre con jerarquía -->
                <td>
                  <div class="flex items-center">
                    <!-- Indentación visual -->
                    <div :style="{ width: oficina.level * 24 + 'px' }" class="flex-shrink-0">
                      <div
                        v-if="oficina.level > 0"
                        class="flex items-center h-full"
                      >
                        <!-- Línea de conexión -->
                        <div class="w-full border-l-2 border-gray-300 dark:border-gray-600 border-dashed h-4"></div>
                        <div class="w-4 border-t-2 border-gray-300 dark:border-gray-600 border-dashed"></div>
                      </div>
                    </div>

                    <!-- Contenido -->
                    <div class="ml-2">
                      <div class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ oficina.nombre }}
                      </div>
                      <div v-if="oficina.level > 0" class="text-xs text-gray-500 dark:text-gray-400">
                        Nivel {{ oficina.level + 1 }}
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Abreviación -->
                <td>{{ oficina.abreviacion }}</td>

                <!-- Estado -->
                <td>
                  <span
                    class="status"
                    :class="{
                      'status-approved': oficina.estado === 'Habilitada',
                      'status-rejected': oficina.estado === 'No habilitada'
                    }"
                  >
                    <i
                      class="fas"
                      :class="{
                        'fa-check': oficina.estado === 'Habilitada',
                        'fa-times': oficina.estado === 'No habilitada'
                      }"
                    ></i>
                    {{ oficina.estado }}
                  </span>
                </td>

                <!-- Acciones -->
                <td>
                  <div style="display: flex; gap: 0.5rem;">
                    <button
                      class="btn btn-outline"
                      @click="openModal(oficina)"
                      title="Editar"
                    >
                      <i class="fas fa-edit"></i>
                    </button>
                    <button
                      class="btn"
                      :class="oficina.estado === 'Habilitada' ? 'btn-danger' : 'btn-success'"
                      @click="confirmToggle(oficina)"
                      :title="oficina.estado === 'Habilitada' ? 'Deshabilitar' : 'Habilitar'"
                    >
                      <i
                        class="fas"
                        :class="oficina.estado === 'Habilitada' ? 'fa-times' : 'fa-check'"
                      ></i>
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Empty state -->
              <tr v-if="filteredTree.length === 0">
                <td colspan="5" style="text-align: center; padding: 2rem;">
                  <div style="color: #64748b;">
                    <i class="fas fa-building" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                    <h3 style="margin-bottom: 0.5rem;">No hay oficinas</h3>
                    <p style="margin-bottom: 1rem;">
                      {{ search ? 'No se encontraron resultados para tu búsqueda' : 'Comienza creando una nueva oficina' }}
                    </p>
                    <button
                      v-if="!search"
                      class="btn btn-primary"
                      @click="openModal()"
                    >
                      <i class="fas fa-plus"></i> Crear primera oficina
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
        Mostrando {{ filteredTree.length }} oficinas
      </div>
    </div>

    <!-- Modal -->
    <OficinaFormModal
      v-if="showModal"
      v-bind="modalProps"
      @close="closeModal"
      @saved="handleSaved"
    />
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
  position: relative;
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

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  background: white;
  border-radius: 0.75rem;
  padding: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border-left: 4px solid #2563eb;
}

.dark .stat-card {
  background: #374151;
}

.stat-card.stat-success {
  border-left-color: #10b981;
}

.stat-card.stat-danger {
  border-left-color: #ef4444;
}

.stat-number {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
}

.dark .stat-number {
  color: #f3f4f6;
}

.stat-label {
  font-size: 0.875rem;
  color: #64748b;
  margin-top: 0.25rem;
}

.dark .stat-label {
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

.dark .btn-outline {
  color: #f3f4f6;
  border-color: #6b7280;
}

.dark .btn-outline:hover {
  background-color: #4b5563;
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

.dark .status-approved {
  background-color: #064e3b;
  color: #a7f3d0;
}

.dark .status-rejected {
  background-color: #7f1d1d;
  color: #fca5a5;
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

  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>
