<template>
  <AppLayout :title="`Rol: ${role.display_name}`">
    <div class="py-6">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
              <li class="inline-flex items-center">
                <Link :href="route('admin.roles.index')" class="text-gray-700 dark:text-gray-300 hover:text-blue-600">
                  Roles
                </Link>
              </li>
              <li>
                <div class="flex items-center">
                  <ChevronRightIcon class="w-6 h-6 text-gray-400" />
                  <span class="ml-1 text-gray-500 dark:text-gray-400">{{ role.display_name }}</span>
                </div>
              </li>
            </ol>
          </nav>
          
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="h-12 w-12 rounded-lg flex items-center justify-center" :class="getRoleIconClasses(role.name)">
                <ShieldCheckIcon class="h-7 w-7" />
              </div>
              <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                  {{ role.display_name }}
                </h1>
                <p class="text-sm font-mono text-gray-500 dark:text-gray-400">{{ role.name }}</p>
              </div>
            </div>
            <div class="flex items-center space-x-3">
              <span 
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                :class="role.is_active 
                  ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' 
                  : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'"
              >
                {{ role.is_active ? 'Activo' : 'Inactivo' }}
              </span>
              <span v-if="isSystemRole" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                <CogIcon class="h-4 w-4 mr-1" />
                Rol del Sistema
              </span>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="mb-6 flex space-x-3">
          <Link
            :href="route('admin.roles.index')"
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
          >
            <ArrowLeftIcon class="h-4 w-4 mr-2" />
            Volver
          </Link>
          <Link
            :href="route('admin.roles.edit', role.id)"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
          >
            <PencilIcon class="h-4 w-4 mr-2" />
            Editar
          </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Información del Rol -->
          <div class="lg:col-span-1 space-y-6">
            <!-- Descripción -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Información
              </h3>
              <div class="space-y-4">
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Descripción</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ role.description || 'Sin descripción' }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Creado</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ formatDate(role.created_at) }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Última actualización</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ formatDate(role.updated_at) }}
                  </dd>
                </div>
              </div>
            </div>

            <!-- Estadísticas -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Estadísticas
              </h3>
              <div class="grid grid-cols-2 gap-4">
                <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                  <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ role.users?.length || 0 }}</p>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Usuarios</p>
                </div>
                <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                  <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ (role.permissions || []).length }}</p>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Permisos</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Permisos y Usuarios -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Permisos -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                  Permisos Asignados
                </h3>
              </div>
              <div class="p-6">
                <div v-if="role.permissions && role.permissions.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div
                    v-for="permission in role.permissions"
                    :key="permission"
                    class="flex items-center p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg"
                  >
                    <CheckCircleIcon class="h-5 w-5 text-green-600 dark:text-green-400 mr-3" />
                    <div>
                      <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ permissions[permission] || permission }}
                      </p>
                      <p class="text-xs font-mono text-gray-500 dark:text-gray-400">{{ permission }}</p>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                  <KeyIcon class="mx-auto h-12 w-12 text-gray-400" />
                  <p class="mt-2">Este rol no tiene permisos asignados</p>
                </div>
              </div>
            </div>

            <!-- Usuarios con este Rol -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                  Usuarios con este Rol ({{ role.users?.length || 0 }})
                </h3>
              </div>
              <div class="p-6">
                <div v-if="role.users && role.users.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
                  <div
                    v-for="user in role.users"
                    :key="user.id"
                    class="py-4 flex items-center justify-between"
                  >
                    <div class="flex items-center min-w-0">
                      <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                          {{ getInitials(user.name) }}
                        </span>
                      </div>
                      <div class="ml-4 truncate">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                          {{ user.name }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                          {{ user.email }}
                        </p>
                      </div>
                    </div>
                    <div class="flex items-center space-x-2">
                      <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="user.is_active 
                          ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' 
                          : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'"
                      >
                        {{ user.is_active ? 'Activo' : 'Inactivo' }}
                      </span>
                      <Link
                        :href="route('admin.users.show', user.id)"
                        class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                      >
                        <EyeIcon class="h-5 w-5" />
                      </Link>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                  <UsersIcon class="mx-auto h-12 w-12 text-gray-400" />
                  <p class="mt-2">No hay usuarios con este rol</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { 
  ChevronRightIcon, 
  ShieldCheckIcon, 
  CogIcon, 
  PencilIcon, 
  ArrowLeftIcon,
  CheckCircleIcon,
  KeyIcon,
  UsersIcon,
  EyeIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  role: Object,
  permissions: Object
})

const systemRoles = ['administrador', 'secretario', 'director', 'operador', 'invitado']
const isSystemRole = computed(() => systemRoles.includes(props.role.name))

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

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('es-AR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getInitials = (name) => {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}
</script>
