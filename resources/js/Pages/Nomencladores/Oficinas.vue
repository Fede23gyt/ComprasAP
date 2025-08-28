<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- Header -->
    <section class="bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-900 dark:to-gray-800 py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center space-x-3">
          <div class="p-2 bg-orange-100 dark:bg-orange-900/20 rounded-xl">
            <BuildingOfficeIcon class="w-8 h-8 text-orange-600 dark:text-orange-400" />
          </div>
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              {{ title }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
              Consulta las oficinas disponibles en el sistema organizadas jerárquicamente
            </p>
          </div>
        </div>
      </div>
    </section>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Controles superiores -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
          <!-- Buscador -->
          <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
            </div>
            <input
              ref="searchInput"
              v-model="search"
              type="text"
              placeholder="Buscar oficinas... (Ctrl+K)"
              class="block w-full pl-10 pr-10 py-3 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent dark:bg-gray-700 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 transition-all"
            />
            <button
              v-if="search"
              @click="clearSearch"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>

          <!-- Botón nueva oficina -->
          <button
            @click="openModal()"
            class="inline-flex items-center px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all transform hover:scale-105"
          >
            <PlusIcon class="w-5 h-5 mr-2" />
            Nueva Oficina
          </button>
        </div>

        <!-- Stats -->
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 p-4 rounded-xl">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
              {{ filteredTree.length }}
            </div>
            <div class="text-sm text-blue-600/80 dark:text-blue-400/80">
              Total Oficinas
            </div>
          </div>
          <div class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 p-4 rounded-xl">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">
              {{ filteredTree.filter(o => o.estado === 'Habilitada').length }}
            </div>
            <div class="text-sm text-green-600/80 dark:text-green-400/80">
              Habilitadas
            </div>
          </div>
          <div class="bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 p-4 rounded-xl">
            <div class="text-2xl font-bold text-red-600 dark:text-red-400">
              {{ filteredTree.filter(o => o.estado !== 'Habilitada').length }}
            </div>
            <div class="text-sm text-red-600/80 dark:text-red-400/80">
              No habilitadas
            </div>
          </div>
        </div>
      </div>

      <!-- Tabla -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
              <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                Código
              </th>
              <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                Nombre / Jerarquía
              </th>
              <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                Abreviación
              </th>
              <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                Estado
              </th>
              <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                Acciones
              </th>
            </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="oficina in filteredTree"
              :key="oficina.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
            >
              <!-- Código -->
              <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-md text-gray-900 dark:text-gray-100">
                    {{ oficina.codigo_oficina }}
                  </span>
              </td>

              <!-- Nombre con jerarquía -->
              <td class="px-6 py-4">
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
              <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-600 dark:text-gray-400 font-medium">
                    {{ oficina.abreviacion }}
                  </span>
              </td>

              <!-- Estado -->
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <label class="inline-flex items-center cursor-pointer">
                  <input
                    type="checkbox"
                    :checked="oficina.estado === 'Habilitada'"
                    class="sr-only peer"
                    @change="confirmToggle(oficina)"
                  />
                  <div class="relative w-12 h-6 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:border after:border-gray-300 transition-all"
                       :class="oficina.estado === 'Habilitada' ? 'bg-green-500 peer-checked:bg-green-600' : 'bg-red-400'"
                  ></div>
                </label>
              </td>

              <!-- Acciones -->
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <div class="flex items-center justify-center space-x-2">
                  <!-- Ver -->
                  <button
                    @click="openModal(oficina, 'view')"
                    class="inline-flex items-center p-2 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all"
                    title="Ver oficina"
                  >
                    <EyeIcon class="w-4 h-4" />
                  </button>
                  
                  <!-- Editar -->
                  <button
                    @click="openModal(oficina, 'edit')"
                    class="inline-flex items-center p-2 text-orange-600 hover:text-orange-700 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-lg transition-all"
                    title="Editar oficina"
                  >
                    <PencilIcon class="w-4 h-4" />
                  </button>

                  <!-- Eliminar -->
                  <button
                    @click="confirmDelete(oficina)"
                    class="inline-flex items-center p-2 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all"
                    title="Eliminar oficina"
                  >
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>

        <!-- Estado vacío -->
        <div
          v-if="filteredTree.length === 0"
          class="text-center py-12"
        >
          <tr>
            <td colspan="5" class="px-6 py-12 text-center">
              <BuildingOfficeIcon class="mx-auto h-12 w-12 text-gray-400" />
              <h3 class="mt-4 text-sm font-semibold text-gray-900 dark:text-white">
                {{ search ? 'No se encontraron oficinas' : 'No hay oficinas registradas' }}
              </h3>
              <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ search ? 'Intenta con otros términos de búsqueda' : 'Comienza creando una nueva oficina' }}
              </p>
              <button
                v-if="!search"
                @click="openModal()"
                class="mt-4 inline-flex items-center px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg transition-colors"
              >
                <PlusIcon class="w-4 h-4 mr-2" />
                Nueva Oficina
              </button>
            </td>
          </tr>
        </div>
      </div>
    </main>

    <!-- Modal -->
    <OficinaFormModal
      v-if="showModal"
      v-bind="modalProps"
      @close="closeModal"
      @saved="handleSaved"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue'
import { BuildingOfficeIcon, MagnifyingGlassIcon, PlusIcon, EyeIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import OficinaFormModal from '@/Components/OficinaFormModal.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  title: String,
  oficinas: {
    type: Array,
    default: () => []
  }
})

// Estado de búsqueda
const search = ref('')
const searchInput = ref(null)

const breadcrumbs = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'Nomencladores', href: null },
  { name: 'Oficinas', href: null }
]

// Función para aplanar el árbol manteniendo la jerarquía
const flatTree = (nodes, level = 0) => {
  const result = []
  if (!nodes || !Array.isArray(nodes)) return result
  
  nodes.forEach(node => {
    result.push({ ...node, level })
    if (node.children?.length) {
      result.push(...flatTree(node.children, level + 1))
    }
  })
  return result
}

// Oficinas filtradas con estructura de árbol
const filteredTree = computed(() => {
  const term = search.value.toLowerCase().trim()
  const tree = flatTree(props.oficinas)

  if (!term) return tree

  return tree.filter(oficina =>
    oficina.nombre?.toLowerCase().includes(term) ||
    oficina.codigo_oficina?.toLowerCase().includes(term) ||
    oficina.abreviacion?.toLowerCase().includes(term)
  )
})

// Limpiar búsqueda
const clearSearch = () => {
  search.value = ''
  nextTick(() => searchInput.value?.focus())
}

// Focus en búsqueda con atajo de teclado
const handleKeydown = (event) => {
  if (event.ctrlKey && event.key === 'k') {
    event.preventDefault()
    searchInput.value?.focus()
  }
}

// Montar event listener para atajos
onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})

// Estado del modal
const showModal = ref(false)
const modalProps = ref({ oficina: null, padres: [] })

// Abrir modal para crear, editar o ver
const openModal = (oficina = null, mode = 'create') => {
  // Para nomencladores, necesitamos cargar la lista de oficinas padres
  // Por ahora usaremos las oficinas existentes filtradas
  const padresDisponibles = flatTree(props.oficinas).filter(o => o.id !== oficina?.id)
  
  modalProps.value = {
    oficina,
    mode,
    padres: padresDisponibles
  }
  showModal.value = true
}

// Cerrar modal
const closeModal = () => {
  showModal.value = false
  modalProps.value = { oficina: null, padres: [] }
}

// Manejar cuando se guarda una oficina
const handleSaved = () => {
  closeModal()
  // Recargar la página para mostrar los cambios
  setTimeout(() => {
    window.location.reload()
  }, 100)
}

// Toggle del estado de la oficina con SweetAlert
const confirmToggle = (oficina) => {
  const accion = oficina.estado === 'Habilitada' ? 'inhabilitar' : 'habilitar'
  const nuevoEstado = oficina.estado === 'Habilitada' ? 'No habilitada' : 'Habilitada'

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
          })
        },
        onError: () => {
          Swal.fire({
            title: 'Error',
            text: 'Hubo un problema al cambiar el estado de la oficina',
            icon: 'error',
            confirmButtonColor: '#ef4444'
          })
        }
      })
    }
  })
}

// Confirmación para eliminar
const confirmDelete = async (oficina) => {
  const result = await Swal.fire({
    title: '¿Estás seguro?',
    text: `¿Deseas eliminar la oficina "${oficina.nombre}"? Esta acción no se puede deshacer.`,
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
      .delete(`/nomencladores/oficinas/${oficina.id}`, {
        onSuccess: () => {
          Swal.fire({
            title: '¡Eliminado!',
            text: 'La oficina ha sido eliminada correctamente.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          })
        },
        onError: () => {
          Swal.fire({
            title: 'Error',
            text: 'No se pudo eliminar la oficina. Inténtalo nuevamente.',
            icon: 'error',
            confirmButtonText: 'Entendido'
          })
        }
      })
  }
}
</script>