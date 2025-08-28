<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- Header Section -->
    <section class="bg-gray-100 dark:bg-gray-900 pt-4 pb-6">
      <div class="max-w-4xl mx-auto px-4">
        <div class="flex items-center space-x-3">
          <div class="p-2 bg-orange-100 dark:bg-orange-900/20 rounded-xl">
            <DocumentTextIcon class="w-8 h-8 text-orange-600 dark:text-orange-400" />
          </div>
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
              Consulta los tipos de nota disponibles en el sistema
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-8">

      <!-- Search and Export Button -->
      <div class="flex items-center justify-between mb-6 gap-4">
        <!-- Buscador -->
        <div class="flex-1 max-w-md">
          <label class="flex items-center space-x-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Buscar:</span>
            <input
              v-model="search"
              placeholder="Buscar tipo de nota..."
              class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
            />
          </label>
        </div>

        <!-- Botones de acción -->
        <div class="flex items-center space-x-2">
          <!-- Botón nuevo tipo de nota -->
          <button
            @click="openModal()"
            class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 shadow-md hover:shadow-lg flex items-center space-x-2 transform hover:scale-105"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <span>Nuevo Tipo</span>
          </button>
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
              Fecha Creación
            </th>
            <th class="px-6 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              Acciones
            </th>
          </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr
            v-for="item in filteredTiposNota"
            :key="item.id"
            class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150"
          >
            <!-- Descripción -->
            <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">
              <div class="font-medium">{{ item.descripcion }}</div>
            </td>

            <!-- Estado -->
            <td class="px-6 py-3 text-center">
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
            <td class="px-6 py-3 text-center text-sm text-gray-500 dark:text-gray-400">
              {{ formatDate(item.created_at) }}
            </td>

            <!-- Acciones -->
            <td class="px-6 py-3 text-center">
              <div class="flex items-center justify-center space-x-2">
                <!-- Ver -->
                <button
                  @click="openModal(item, 'view')"
                  class="inline-flex items-center p-2 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all"
                  title="Ver tipo de nota"
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
                  title="Editar tipo de nota"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </button>

                <!-- Eliminar -->
                <button
                  @click="confirmDelete(item)"
                  class="inline-flex items-center p-2 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all"
                  title="Eliminar tipo de nota"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
            </td>
          </tr>

          <!-- Empty state -->
          <tr v-if="filteredTiposNota.length === 0">
            <td colspan="4" class="px-6 py-9 text-center">
              <div class="text-gray-500 dark:text-gray-400">
                <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" />
                <h3 class="mt-2 text-sm font-medium">No hay tipos de nota</h3>
                <p class="mt-1 text-sm">{{ search ? 'No se encontraron resultados para tu búsqueda' : 'No hay tipos de nota registrados en el sistema' }}</p>
              </div>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <!-- Info adicional -->
      <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
        Mostrando {{ filteredTiposNota.length }} de {{ tiposNota.length }} tipos de nota
      </div>

      <!-- Resumen de filtros (si hay búsqueda) -->
      <div v-if="search" class="mt-4 text-sm text-gray-500 dark:text-gray-400">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
          Filtrado por: "{{ search }}"
        </span>
      </div>
    </main>

    <!-- Modal -->
    <TipoNotaModal
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
import { DocumentTextIcon } from '@heroicons/vue/24/outline'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import TipoNotaModal from '@/Components/TipoNotaModal.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  title: String,
  tiposNota: {
    type: Array,
    default: () => []
  }
})

const search = ref('')

const breadcrumbs = [
  { name: 'Inicio', href: '/dashboard' },
  { name: 'Nomencladores', href: null },
  { name: 'Tipos de Nota', href: null }
]

const filteredTiposNota = computed(() => {
  if (!search.value) return props.tiposNota

  return props.tiposNota.filter(tipo =>
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
    title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} tipo de nota?`,
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
      useForm({}).patch(`/nomencladores/tipos-nota/${item.id}/toggle`, {
        onSuccess: () => {
          Swal.fire({
            title: '¡Éxito!',
            text: `Tipo de nota ${accion}do correctamente`,
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
            text: 'Hubo un problema al cambiar el estado del tipo de nota',
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
    text: `¿Deseas eliminar el tipo de nota "${item.descripcion}"? Esta acción no se puede deshacer.`,
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
      .delete(`/nomencladores/tipos-nota/${item.id}`, {
        onSuccess: () => {
          Swal.fire({
            title: '¡Eliminado!',
            text: 'El tipo de nota ha sido eliminado correctamente.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          })
        },
        onError: () => {
          Swal.fire({
            title: 'Error',
            text: 'No se pudo eliminar el tipo de nota. Inténtalo nuevamente.',
            icon: 'error',
            confirmButtonText: 'Entendido'
          })
        }
      })
  }
}

// Exportación con confirmación
const confirmExport = async (format) => {
  const formatText = format === 'pdf' ? 'PDF' : 'Excel'

  const result = await Swal.fire({
    title: `Exportar a ${formatText}`,
    text: `¿Deseas exportar ${props.tiposNota.length} tipos de nota a ${formatText}?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3b82f6',
    cancelButtonColor: '#6b7280',
    confirmButtonText: `Sí, exportar a ${formatText}`,
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  })

  if (result.isConfirmed) {
    // Mostrar loading
    Swal.fire({
      title: 'Generando archivo...',
      text: 'Por favor espera mientras se genera el archivo.',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading()
      }
    })

    try {
      // Crear un enlace temporal para descargar
      const downloadUrl = `/nomencladores/tipos-nota/export/${format}?search=${search.value}`
      const link = document.createElement('a')
      link.href = downloadUrl
      link.download = `nomenclador_tipos_nota_${new Date().toISOString().split('T')[0]}.${format === 'pdf' ? 'pdf' : 'xlsx'}`
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)

      // Mostrar éxito
      Swal.fire({
        title: '¡Éxito!',
        text: `Archivo ${formatText} generado correctamente`,
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
      })
    } catch (error) {
      Swal.fire({
        title: 'Error',
        text: `No se pudo generar el archivo ${formatText}. Inténtalo nuevamente.`,
        icon: 'error',
        confirmButtonText: 'Entendido'
      })
    }
  }
}
</script>
