<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- Header Section -->
    <section class="bg-gray-100 dark:bg-gray-900 pt-4 pb-6">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center space-x-3">
          <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-xl">
            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
          </div>
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ title }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
              Consulta los insumos disponibles en el sistema
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">

      <!-- Search and Export Options -->
      <div class="flex items-center justify-between mb-6 gap-4">
        <!-- Buscador -->
        <div class="flex-1 max-w-md">
          <label class="flex items-center space-x-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Buscar:</span>
            <input
              v-model="search"
              @input="handleSearch"
              placeholder="Buscar por código o descripción..."
              class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </label>
        </div>

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
      </div>

      <!-- Tabla -->
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
            v-for="item in insumos.data"
            :key="item.id"
            class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150"
          >
            <!-- Código -->
            <td class="px-4 py-2 text-sm font-mono text-gray-900 dark:text-gray-100">
              {{ item.codigo }}
            </td>

            <!-- Descripción -->
            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
              <div class="font-medium">{{ item.descripcion }}</div>
              <div class="text-xs text-gray-500 dark:text-gray-400" v-if="item.unidad_solicitud">
                Unidad: {{ item.unidad_solicitud }}
              </div>
            </td>

            <!-- Clasificación Económica (Código primero, descripción después, una sola línea) -->
            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
              <div v-if="item.clasificacion_economica">
                <span class="font-mono text-blue-600 dark:text-blue-400">{{ item.clasificacion }}</span>
                <span class="text-gray-500 dark:text-gray-400"> - </span>
                <span>{{ item.clasificacion_economica.descripcion }}</span>
              </div>
              <div v-else-if="item.clasificacion" class="font-mono text-blue-600 dark:text-blue-400">
                {{ item.clasificacion }}
              </div>
              <div v-else class="text-gray-400 dark:text-gray-500 italic">
                Sin clasificación
              </div>
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

            <!-- Estados (Iconos alineados) -->
            <td class="px-4 py-2">
              <div class="flex items-center justify-center space-x-2">
                <!-- Registrable -->
                <div class="flex items-center" :title="item.registrable ? 'Registrable' : 'No registrable'">
                  <div class="w-3 h-3 rounded-full flex items-center justify-center"
                       :class="item.registrable ? 'bg-green-500' : 'bg-red-500'">
                    <svg v-if="item.registrable" class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <svg v-else class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <span class="ml-1 text-xs">R</span>
                </div>

                <!-- Inventariable -->
                <div v-if="item.inventariable" class="flex items-center" title="Inventariable">
                  <div class="w-3 h-3 rounded-full bg-blue-500 flex items-center justify-center">
                    <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <span class="ml-1 text-xs">I</span>
                </div>

                <!-- Rendición tribunal -->
                <div v-if="item.rend_tribunal" class="flex items-center" title="Rendición Tribunal">
                  <div class="w-3 h-3 rounded-full bg-purple-500 flex items-center justify-center">
                    <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <span class="ml-1 text-xs">T</span>
                </div>
              </div>
            </td>

            <!-- Acciones -->
            <td class="px-4 py-2 text-center">
              <div class="flex items-center justify-center space-x-1">
                <!-- Ver -->
                <button
                  @click="openModal(item, 'view')"
                  class="inline-flex items-center p-1.5 text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-all"
                  title="Ver insumo"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                </button>

                <!-- Editar -->
                <button
                  @click="openModal(item, 'edit')"
                  class="inline-flex items-center p-1.5 text-orange-600 hover:text-orange-700 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-md transition-all"
                  title="Editar insumo"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </button>
              </div>
            </td>
          </tr>

          <!-- Empty state -->
          <tr v-if="insumos.data && insumos.data.length === 0">
            <td colspan="6" class="px-4 py-12 text-center">
              <div class="text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <h3 class="mt-2 text-sm font-medium">No hay insumos</h3>
                <p class="mt-1 text-sm">{{ filters.search ? 'No se encontraron resultados para tu búsqueda' : 'No hay insumos registrados en el sistema' }}</p>
              </div>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-700 dark:text-gray-300">
          <p>
            Mostrando <span class="font-medium">{{ insumos.from || 0 }}</span> a <span class="font-medium">{{ insumos.to || 0 }}</span> de
            <span class="font-medium">{{ insumos.total || 0 }}</span> insumos
          </p>
        </div>

        <nav v-if="insumos.links && insumos.links.length > 3" class="flex space-x-1">
          <Link
            v-for="(link, index) in insumos.links"
            :key="index"
            :href="link.url || '#'"
            :class="[
              'px-3 py-2 rounded-md border text-sm font-medium transition-colors duration-150',
              link.active
                ? 'bg-blue-600 text-white border-blue-600'
                : link.url
                  ? 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600'
                  : 'bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed border-gray-200 dark:border-gray-700'
            ]"
            v-html="link.label"
          ></Link>
        </nav>
      </div>

      <!-- Resumen de filtros (si hay búsqueda) -->
      <div v-if="filters.search" class="mt-4 text-sm text-gray-500 dark:text-gray-400">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
          Filtrado por: "{{ filters.search }}"
        </span>
      </div>
    </main>

    <!-- Modal -->
    <InsumoViewModal
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
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import InsumoViewModal from '@/Components/InsumoViewModal.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  title: String,
  insumos: {
    type: Object,
    default: () => ({ data: [], links: [], total: 0, from: 0, to: 0 })
  },
  filters: {
    type: Object,
    default: () => ({ search: '' })
  }
})

const search = ref(props.filters.search || '')

const breadcrumbs = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'Nomencladores', href: null },
  { name: 'Insumos', href: null }
]

// Debounce para la búsqueda
let searchTimeout = null

// Manejar búsqueda con navegación
const handleSearch = () => {
  // Limpiar timeout anterior
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  
  // Ejecutar búsqueda después de 500ms de inactividad
  searchTimeout = setTimeout(() => {
    router.get('/nomencladores/insumos', { search: search.value }, {
      preserveState: true,
      replace: true,
      only: ['insumos', 'filters']
    })
  }, 500)
}

// Estado del modal
const showModal = ref(false)
const selectedItem = ref(null)
const modalMode = ref('view')

// Funciones del modal
const openModal = async (item, mode) => {
  modalMode.value = mode

  if (mode === 'edit') {
    try {
      const response = await axios.get(`/nomencladores/insumos/${item.id}/edit`)
      selectedItem.value = response.data.insumo
      showModal.value = true
    } catch (error) {
      Swal.fire({
        title: 'Error',
        text: 'No se pudo cargar el insumo para editar',
        icon: 'error',
        confirmButtonColor: '#ef4444'
      })
    }
  } else {
    selectedItem.value = item
    showModal.value = true
  }
}

const closeModal = () => {
  showModal.value = false
  selectedItem.value = null
  modalMode.value = 'view'
}

const handleSaved = (message) => {
  closeModal()

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

// Exportación con confirmación SweetAlert
const confirmExport = async (format) => {
  const formatText = format === 'pdf' ? 'PDF' : 'Excel'

  const result = await Swal.fire({
    title: `Exportar a ${formatText}`,
    text: `¿Deseas exportar ${props.insumos.total || 0} insumos a ${formatText}?`,
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
      const downloadUrl = `/nomencladores/insumos/export/${format}?search=${search.value}`
      const link = document.createElement('a')
      link.href = downloadUrl
      link.download = `nomenclador_insumos_${new Date().toISOString().split('T')[0]}.${format === 'pdf' ? 'pdf' : 'xlsx'}`
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
