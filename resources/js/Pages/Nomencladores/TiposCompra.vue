<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ title }}
          </h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Consulta los tipos de compra disponibles en el sistema
          </p>
        </div>

        <!-- Search and Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0 mb-6">
          <!-- Buscador -->
          <div class="flex-1 max-w-md">
            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Buscar tipo de compra
            </label>
            <input
              id="search"
              v-model="search"
              type="text"
              placeholder="Buscar por descripción..."
              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-primary focus:border-primary"
            />
          </div>

          <!-- Botón nuevo tipo de compra -->
          <button
            @click="openModal()"
            class="inline-flex items-center px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all transform hover:scale-105"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Nuevo Tipo
          </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <ShoppingCartIcon class="h-8 w-8 text-blue-600" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                      Total Tipos
                    </dt>
                    <dd class="text-lg font-medium text-gray-900 dark:text-white">
                      {{ filteredTiposCompra.length }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
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
                Fecha Creación
              </th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                Acciones
              </th>
            </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="item in filteredTiposCompra"
              :key="item.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150"
            >
              <!-- Descripción -->
              <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                <div class="font-medium">{{ item.descripcion }}</div>
              </td>

              <!-- Estado -->
              <td class="px-6 py-4 text-center">
                <label class="inline-flex items-center cursor-pointer">
                  <input
                    type="checkbox"
                    :checked="item.estado === 'Habilitado'"
                    class="sr-only peer"
                    @change="confirmToggle(item)"
                  />
                  <div class="relative w-12 h-6 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:border after:border-gray-300 transition-all"
                       :class="item.estado === 'Habilitado' ? 'bg-green-500 peer-checked:bg-green-600' : 'bg-red-400'"
                  ></div>
                </label>
              </td>

              <!-- Fecha Creación -->
              <td class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                {{ formatDate(item.created_at) }}
              </td>

              <!-- Acciones -->
              <td class="px-6 py-4 text-center">
                <div class="flex items-center justify-center space-x-2">
                  <!-- Ver -->
                  <button
                    @click="openModal(item, 'view')"
                    class="inline-flex items-center p-2 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all"
                    title="Ver tipo de compra"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                  
                  <!-- Editar -->
                  <button
                    @click="openModal(item, 'edit')"
                    class="inline-flex items-center p-2 text-orange-600 hover:text-orange-700 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-lg transition-all"
                    title="Editar tipo de compra"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>

                  <!-- Eliminar -->
                  <button
                    @click="confirmDelete(item)"
                    class="inline-flex items-center p-2 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all"
                    title="Eliminar tipo de compra"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty state -->
            <tr v-if="filteredTiposCompra.length === 0">
              <td colspan="4" class="px-6 py-12 text-center">
                <div class="text-gray-500 dark:text-gray-400">
                  <ShoppingCartIcon class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" />
                  <h3 class="mt-2 text-sm font-medium">No hay tipos de compra</h3>
                  <p class="mt-1 text-sm">{{ search ? 'No se encontraron resultados para tu búsqueda' : 'No hay tipos de compra registrados en el sistema' }}</p>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>

        <!-- Info adicional -->
        <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
          Mostrando {{ filteredTiposCompra.length }} de {{ tiposCompra.length }} tipos de compra
        </div>
      </div>
    </div>

    <!-- Modal -->
    <TipoCompraModal
      v-if="showModal"
      :item="selectedItem"
      :mode="modalMode"
      @close="closeModal"
      @saved="handleSaved"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { ShoppingCartIcon } from '@heroicons/vue/24/outline'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import TipoCompraModal from '@/Components/TipoCompraModal.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  title: String,
  tiposCompra: {
    type: Array,
    default: () => []
  }
})

const search = ref('')

const breadcrumbs = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'Nomencladores', href: null },
  { name: 'Tipos de Compra', href: null }
]

const filteredTiposCompra = computed(() => {
  if (!search.value) return props.tiposCompra
  
  return props.tiposCompra.filter(tipo => 
    tipo.descripcion?.toLowerCase().includes(search.value.toLowerCase())
  )
})

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('es-AR', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  })
}

// Estado del modal
const showModal = ref(false)
const selectedItem = ref(null)
const modalMode = ref('create')

// Funciones del modal
const openModal = (item = null, mode = 'create') => {
  selectedItem.value = item
  modalMode.value = mode
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedItem.value = null
  modalMode.value = 'create'
}

const handleSaved = (message) => {
  closeModal()
  
  // Mostrar mensaje de éxito
  Swal.fire({
    title: '¡Éxito!',
    text: message,
    icon: 'success',
    timer: 2000,
    showConfirmButton: false,
    toast: true,
    position: 'top-end'
  })
  
  // Recargar la página para mostrar los cambios
  setTimeout(() => {
    window.location.reload()
  }, 100)
}

// Toggle del estado con SweetAlert
const confirmToggle = (item) => {
  const accion = item.estado === 'Habilitado' ? 'inhabilitar' : 'habilitar'
  const nuevoEstado = item.estado === 'Habilitado' ? 'No habilitado' : 'Habilitado'

  Swal.fire({
    title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} tipo de compra?`,
    text: `¿Estás seguro de ${accion} "${item.descripcion}"?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: item.estado === 'Habilitado' ? '#ef4444' : '#10b981',
    cancelButtonColor: '#6b7280',
    confirmButtonText: `Sí, ${accion}`,
    cancelButtonText: 'Cancelar',
    backdrop: true
  }).then((result) => {
    if (result.isConfirmed) {
      useForm({}).patch(`/nomencladores/tipos-compra/${item.id}/toggle`, {
        onSuccess: () => {
          Swal.fire({
            title: '¡Éxito!',
            text: `Tipo de compra ${accion}do correctamente`,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
          })
          // Recargar después del éxito
          setTimeout(() => {
            window.location.reload()
          }, 500)
        },
        onError: () => {
          Swal.fire({
            title: 'Error',
            text: 'Hubo un problema al cambiar el estado del tipo de compra',
            icon: 'error',
            confirmButtonColor: '#ef4444'
          })
        }
      })
    }
  })
}

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
  })

  if (result.isConfirmed) {
    useForm({})
      .delete(`/nomencladores/tipos-compra/${item.id}`, {
        onSuccess: () => {
          Swal.fire({
            title: '¡Eliminado!',
            text: 'El tipo de compra ha sido eliminado correctamente.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          })
        },
        onError: () => {
          Swal.fire({
            title: 'Error',
            text: 'No se pudo eliminar el tipo de compra. Inténtalo nuevamente.',
            icon: 'error',
            confirmButtonText: 'Entendido'
          })
        }
      })
  }
}
</script>