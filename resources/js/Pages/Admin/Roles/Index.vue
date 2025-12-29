<template>
  <AppLayout title="Gestión de Roles">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
          <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
              Gestión de Roles
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Administra los roles del sistema y sus permisos
            </p>
          </div>
          <div class="mt-4 flex md:mt-0 md:ml-4">
            <Link
              :href="route('admin.roles.create')"
              class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
              Nuevo Rol
            </Link>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <UserGroupIcon class="h-6 w-6 text-gray-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                      Total Roles
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
                      Roles Activos
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
                  <ShieldCheckIcon class="h-6 w-6 text-blue-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                      Roles del Sistema
                    </dt>
                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                      {{ stats.system_roles }}
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
                  <UsersIcon class="h-6 w-6 text-purple-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                      Con Usuarios
                    </dt>
                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                      {{ stats.with_users }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <!-- Search -->
            <div class="sm:col-span-2">
              <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Buscar roles
              </label>
              <input
                v-model="filters.search"
                type="text"
                id="search"
                placeholder="Nombre, código o descripción..."
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                @input="debouncedSearch"
              />
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

        <!-- Roles Grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div v-for="role in roles.data" :key="role.id" 
               class="bg-white dark:bg-gray-800 shadow rounded-lg hover:shadow-lg transition-shadow duration-200">
            <div class="p-6">
              <!-- Header del role -->
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-lg flex items-center justify-center"
                         :class="getRoleIconClasses(role.name)">
                      <ShieldCheckIcon class="h-6 w-6" />
                    </div>
                  </div>
                  <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                      {{ role.display_name }}
                    </h3>
                    <p class="text-sm font-mono text-gray-500 dark:text-gray-400">
                      {{ role.name }}
                    </p>
                  </div>
                </div>
                
                <!-- Status badge -->
                <button
                  @click="toggleRoleStatus(role)"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer transition-colors"
                  :class="role.is_active 
                    ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 hover:bg-green-200 dark:hover:bg-green-800' 
                    : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 hover:bg-red-200 dark:hover:bg-red-800'"
                >
                  {{ role.is_active ? 'Activo' : 'Inactivo' }}
                </button>
              </div>

              <!-- Description -->
              <p v-if="role.description" class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                {{ role.description }}
              </p>

              <!-- Stats -->
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                  <UsersIcon class="h-4 w-4 text-gray-400 mr-1" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ role.users_count }} usuario{{ role.users_count !== 1 ? 's' : '' }}
                  </span>
                </div>
                <div class="flex items-center">
                  <KeyIcon class="h-4 w-4 text-gray-400 mr-1" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ getPermissionsCount(role.permissions) }} permiso{{ getPermissionsCount(role.permissions) !== 1 ? 's' : '' }}
                  </span>
                </div>
              </div>

              <!-- System Role Badge -->
              <div v-if="isSystemRole(role.name)" class="mb-4">
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                  <CogIcon class="h-3 w-3 mr-1" />
                  Rol del Sistema
                </span>
              </div>

              <!-- Actions -->
              <div class="flex space-x-2">
                <Link
                  :href="route('admin.roles.show', role.id)"
                  class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                  title="Ver detalles"
                >
                  <EyeIcon class="h-4 w-4 mr-1" />
                  Ver
                </Link>
                <Link
                  :href="route('admin.roles.edit', role.id)"
                  class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                  title="Editar"
                >
                  <PencilIcon class="h-4 w-4 mr-1" />
                  Editar
                </Link>
                <button
                  v-if="!isSystemRole(role.name) && role.users_count === 0"
                  @click="confirmDeleteRole(role)"
                  class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                  title="Eliminar"
                >
                  <TrashIcon class="h-4 w-4" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="roles.data.length === 0" class="text-center py-12">
          <UserGroupIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No hay roles</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            No se encontraron roles con los filtros aplicados.
          </p>
        </div>

        <!-- Pagination -->
        <div v-if="roles.data.length > 0" class="mt-8">
          <nav class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-6">
            <div class="flex-1 flex justify-between sm:hidden">
              <Link
                v-if="roles.prev_page_url"
                :href="roles.prev_page_url"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Anterior
              </Link>
              <Link
                v-if="roles.next_page_url"
                :href="roles.next_page_url"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Siguiente
              </Link>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                  Mostrando
                  <span class="font-medium">{{ roles.from }}</span>
                  a
                  <span class="font-medium">{{ roles.to }}</span>
                  de
                  <span class="font-medium">{{ roles.total }}</span>
                  roles
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                  <template v-for="(link, index) in roles.links" :key="index">
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
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useConfirm } from '@/Composables/useConfirm'
import { 
  UserGroupIcon,
  PlusIcon,
  CheckCircleIcon,
  ShieldCheckIcon,
  UsersIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  XMarkIcon,
  KeyIcon,
  CogIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  roles: Object,
  filters: Object,
  stats: Object
})

const { confirm } = useConfirm()

// Reactive filters
const filters = reactive({
  search: props.filters.search || '',
  status: props.filters.status || ''
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
  router.get(route('admin.roles.index'), filters, {
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

const getRoleIconClasses = (roleName) => {
  const classes = {
    administrador: 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400',
    secretario: 'bg-orange-100 dark:bg-orange-900 text-orange-600 dark:text-orange-400',
    director: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-400',
    operador: 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400',
    invitado: 'bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400'
  }
  return classes[roleName] || 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400'
}

const isSystemRole = (roleName) => {
  return ['administrador', 'secretario', 'director', 'operador', 'invitado'].includes(roleName)
}

// Contar permisos correctamente (puede ser string JSON o array)
const getPermissionsCount = (permissions) => {
  if (!permissions) return 0
  if (Array.isArray(permissions)) return permissions.length
  if (typeof permissions === 'string') {
    try {
      const parsed = JSON.parse(permissions)
      return Array.isArray(parsed) ? parsed.length : 0
    } catch {
      return 0
    }
  }
  return 0
}

const toggleRoleStatus = async (role) => {
  const action = role.is_active ? 'desactivar' : 'activar'
  
  if (await confirm(`¿Estás seguro de que quieres ${action} el rol "${role.display_name}"?`)) {
    router.patch(route('admin.roles.toggle-status', role.id), {}, {
      preserveState: true,
      preserveScroll: true
    })
  }
}

const confirmDeleteRole = async (role) => {
  if (await confirm(
    `¿Estás seguro de que quieres eliminar el rol "${role.display_name}"?`,
    'Esta acción no se puede deshacer.'
  )) {
    router.delete(route('admin.roles.destroy', role.id), {
      preserveState: true,
      preserveScroll: true
    })
  }
}
</script>