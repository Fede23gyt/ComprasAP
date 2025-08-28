<template>
  <AuthenticatedLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
          <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
              Gestión de Usuarios
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Administra los usuarios del sistema y sus permisos
            </p>
          </div>
          <div class="mt-4 flex md:mt-0 md:ml-4">
            <Link
              :href="route('admin.users.create')"
              class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
              Nuevo Usuario
            </Link>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <UsersIcon class="h-6 w-6 text-gray-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                      Total Usuarios
                    </dt>
                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                      {{ stats.total }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <CheckCircleIcon class="h-6 w-6 text-green-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                      Usuarios Activos
                    </dt>
                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                      {{ stats.active }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <XCircleIcon class="h-6 w-6 text-red-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                      Usuarios Inactivos
                    </dt>
                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                      {{ stats.inactive }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <BuildingOfficeIcon class="h-6 w-6 text-blue-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                      Con Oficinas
                    </dt>
                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                      {{ stats.with_oficinas }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Search -->
            <div>
              <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Buscar
              </label>
              <input
                v-model="filters.search"
                type="text"
                id="search"
                placeholder="Nombre, email o DNI..."
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                @input="debouncedSearch"
              />
            </div>

            <!-- Role Filter -->
            <div>
              <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Rol
              </label>
              <select
                v-model="filters.role"
                id="role"
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                @change="applyFilters"
              >
                <option value="">Todos los roles</option>
                <option v-for="role in roles" :key="role.id" :value="role.id">
                  {{ role.display_name }}
                </option>
              </select>
            </div>

            <!-- Status Filter -->
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Estado
              </label>
              <select
                v-model="filters.status"
                id="status"
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                @change="applyFilters"
              >
                <option value="">Todos</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
              </select>
            </div>

            <!-- Office Filter -->
            <div>
              <label for="oficina" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Oficina
              </label>
              <select
                v-model="filters.oficina"
                id="oficina"
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                @change="applyFilters"
              >
                <option value="">Todas las oficinas</option>
                <option v-for="oficina in oficinas" :key="oficina.id" :value="oficina.id">
                  {{ oficina.nombre }}
                </option>
              </select>
            </div>
          </div>

          <!-- Clear Filters -->
          <div class="mt-4 flex justify-end">
            <button
              @click="clearFilters"
              class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <XMarkIcon class="-ml-0.5 mr-2 h-4 w-4" />
              Limpiar Filtros
            </button>
          </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
          <div class="px-4 py-5 sm:p-6">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Usuario
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Rol
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Oficinas
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Estado
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Fecha Registro
                    </th>
                    <th class="relative px-6 py-3">
                      <span class="sr-only">Acciones</span>
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                          <div class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                              {{ getInitials(user.name) }}
                            </span>
                          </div>
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ user.name }}
                          </div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ user.email }}
                          </div>
                          <div v-if="user.dni" class="text-xs text-gray-400">
                            DNI: {{ user.dni }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                            :class="getRoleBadgeClasses(user.role.name)">
                        {{ user.role.display_name }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div v-if="user.oficinas.length > 0" class="text-sm text-gray-900 dark:text-gray-100">
                        <div v-for="oficina in user.oficinas.slice(0, 2)" :key="oficina.id" class="mb-1">
                          <span class="inline-flex items-center px-2 py-1 rounded-md text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                            {{ oficina.nombre }}
                            <CheckIcon v-if="oficina.pivot.es_principal" class="ml-1 h-3 w-3 text-blue-600" />
                          </span>
                        </div>
                        <span v-if="user.oficinas.length > 2" class="text-xs text-gray-500">
                          +{{ user.oficinas.length - 2 }} más
                        </span>
                      </div>
                      <div v-else class="text-sm text-gray-500 dark:text-gray-400">
                        Sin oficinas asignadas
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <button
                        @click="toggleUserStatus(user)"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer transition-colors"
                        :class="user.is_active 
                          ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 hover:bg-green-200 dark:hover:bg-green-800' 
                          : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 hover:bg-red-200 dark:hover:bg-red-800'"
                      >
                        {{ user.is_active ? 'Activo' : 'Inactivo' }}
                      </button>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      {{ formatDate(user.created_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <div class="flex items-center space-x-2">
                        <Link
                          :href="route('admin.users.show', user.id)"
                          class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300"
                          title="Ver detalles"
                        >
                          <EyeIcon class="h-5 w-5" />
                        </Link>
                        <Link
                          :href="route('admin.users.edit', user.id)"
                          class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300"
                          title="Editar"
                        >
                          <PencilIcon class="h-5 w-5" />
                        </Link>
                        <button
                          @click="confirmDeleteUser(user)"
                          class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                          title="Eliminar"
                        >
                          <TrashIcon class="h-5 w-5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Empty State -->
            <div v-if="users.data.length === 0" class="text-center py-12">
              <UsersIcon class="mx-auto h-12 w-12 text-gray-400" />
              <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No hay usuarios</h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                No se encontraron usuarios con los filtros aplicados.
              </p>
            </div>

            <!-- Pagination -->
            <div v-if="users.data.length > 0" class="mt-6">
              <nav class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-6">
                <div class="flex-1 flex justify-between sm:hidden">
                  <Link
                    v-if="users.prev_page_url"
                    :href="users.prev_page_url"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                  >
                    Anterior
                  </Link>
                  <Link
                    v-if="users.next_page_url"
                    :href="users.next_page_url"
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                  >
                    Siguiente
                  </Link>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                  <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                      Mostrando
                      <span class="font-medium">{{ users.from }}</span>
                      a
                      <span class="font-medium">{{ users.to }}</span>
                      de
                      <span class="font-medium">{{ users.total }}</span>
                      usuarios
                    </p>
                  </div>
                  <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                      <!-- Pagination links aquí -->
                      <template v-for="(link, index) in users.links" :key="index">
                        <Link
                          v-if="link.url"
                          :href="link.url"
                          v-html="link.label"
                          class="relative inline-flex items-center px-2 py-2 border text-sm font-medium"
                          :class="link.active 
                            ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' 
                            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                        />
                        <span
                          v-else
                          v-html="link.label"
                          class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-300"
                        />
                      </template>
                    </nav>
                  </div>
                </div>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useConfirm } from '@/Composables/useConfirm'
import { 
  UsersIcon, 
  PlusIcon, 
  CheckCircleIcon, 
  XCircleIcon, 
  BuildingOfficeIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  XMarkIcon,
  CheckIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  users: Object,
  filters: Object,
  roles: Array,
  oficinas: Array,
  stats: Object
})

const { confirm } = useConfirm()

// Reactive filters
const filters = reactive({
  search: props.filters.search || '',
  role: props.filters.role || '',
  status: props.filters.status || '',
  oficina: props.filters.oficina || ''
})

// Debounced search
let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 300)
}

const applyFilters = () => {
  router.get(route('admin.users.index'), filters, {
    preserveState: true,
    preserveScroll: true
  })
}

const clearFilters = () => {
  Object.keys(filters).forEach(key => {
    filters[key] = ''
  })
  applyFilters()
}

const getInitials = (name) => {
  return name.split(' ').map(word => word[0]).join('').toUpperCase().slice(0, 2)
}

const getRoleBadgeClasses = (roleName) => {
  const classes = {
    administrador: 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
    secretario: 'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200',
    director: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
    operador: 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
    invitado: 'bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200'
  }
  return classes[roleName] || 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const toggleUserStatus = async (user) => {
  const action = user.is_active ? 'desactivar' : 'activar'
  
  if (await confirm(`¿Estás seguro de que quieres ${action} al usuario "${user.name}"?`)) {
    router.patch(route('admin.users.toggle-status', user.id), {}, {
      preserveState: true,
      preserveScroll: true
    })
  }
}

const confirmDeleteUser = async (user) => {
  if (await confirm(
    `¿Estás seguro de que quieres eliminar al usuario "${user.name}"?`,
    'Esta acción no se puede deshacer.'
  )) {
    router.delete(route('admin.users.destroy', user.id), {
      preserveState: true,
      preserveScroll: true
    })
  }
}
</script>