<template>
  <AuthenticatedLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            Panel de Reportes Administrativos
          </h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Monitorea el estado del sistema y analiza las métricas clave de usuarios y roles
          </p>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <UsersIcon class="h-6 w-6 text-blue-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                      Total Usuarios
                    </dt>
                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                      {{ stats.total_users }}
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
                      {{ stats.active_users }}
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
                  <UserGroupIcon class="h-6 w-6 text-purple-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                      Total Roles
                    </dt>
                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                      {{ stats.total_roles }}
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
                  <BuildingOfficeIcon class="h-6 w-6 text-orange-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                      Oficinas Activas
                    </dt>
                    <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                      {{ stats.active_offices }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <!-- Users by Role Chart -->
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                Distribución de Usuarios por Rol
              </h3>
              <div class="space-y-3">
                <div v-for="roleData in usersByRole" :key="roleData.role" class="flex items-center">
                  <div class="w-4 h-4 rounded-full mr-3" :style="`background-color: ${roleData.color}`"></div>
                  <div class="flex-1 flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                      {{ roleData.role }}
                    </span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                      {{ roleData.count }} usuario{{ roleData.count !== 1 ? 's' : '' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- User Activity -->
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                Actividad de Usuarios
              </h3>
              <div class="space-y-4">
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600 dark:text-gray-400">Registros últimos 30 días</span>
                  <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ usersActivity.recent_registrations }}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600 dark:text-gray-400">Activos último mes</span>
                  <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ usersActivity.active_last_month }}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600 dark:text-gray-400">Tasa de crecimiento</span>
                  <span class="text-sm font-medium" 
                        :class="usersActivity.growth_rate >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ usersActivity.growth_rate >= 0 ? '+' : '' }}{{ usersActivity.growth_rate }}%
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Detailed Stats Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <!-- Office Stats -->
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                Estadísticas de Oficinas
              </h3>
              <dl class="space-y-3">
                <div class="flex justify-between">
                  <dt class="text-sm text-gray-600 dark:text-gray-400">Total de oficinas</dt>
                  <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ officeStats.total_offices }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-gray-600 dark:text-gray-400">Oficinas raíz</dt>
                  <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ officeStats.root_offices }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-gray-600 dark:text-gray-400">Oficinas hoja</dt>
                  <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ officeStats.leaf_offices }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-gray-600 dark:text-gray-400">Promedio usuarios/oficina</dt>
                  <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ officeStats.avg_users_per_office }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- User Distribution -->
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                Distribución de Usuarios
              </h3>
              <dl class="space-y-3">
                <div class="flex justify-between">
                  <dt class="text-sm text-gray-600 dark:text-gray-400">Con oficinas asignadas</dt>
                  <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ stats.users_with_offices }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-gray-600 dark:text-gray-400">Sin oficinas</dt>
                  <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ stats.users_without_offices }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-gray-600 dark:text-gray-400">Usuarios inactivos</dt>
                  <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ stats.inactive_users }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-sm text-gray-600 dark:text-gray-400">Roles activos</dt>
                  <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ stats.active_roles }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                Acciones Rápidas
              </h3>
              <div class="space-y-3">
                <Link
                  :href="route('admin.reports.users')"
                  class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <DocumentChartBarIcon class="-ml-1 mr-2 h-4 w-4" />
                  Reporte de Usuarios
                </Link>
                <Link
                  :href="route('admin.reports.roles')"
                  class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <UserGroupIcon class="-ml-1 mr-2 h-4 w-4" />
                  Reporte de Roles
                </Link>
                <Link
                  :href="route('admin.reports.offices')"
                  class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <BuildingOfficeIcon class="-ml-1 mr-2 h-4 w-4" />
                  Reporte de Oficinas
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-6">
              Actividad Reciente
            </h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Recent Users -->
              <div>
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">
                  Usuarios Recientes
                </h4>
                <div class="space-y-3">
                  <div v-for="user in recentActivity.recent_users" :key="user.id" 
                       class="flex items-center justify-between">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-8 w-8">
                        <div class="h-8 w-8 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                          <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                            {{ getInitials(user.name) }}
                          </span>
                        </div>
                      </div>
                      <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                          {{ user.name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                          {{ user.email }}
                        </p>
                      </div>
                    </div>
                    <div class="text-right">
                      <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ formatRelativeTime(user.created_at) }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Recent Role Changes -->
              <div>
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">
                  Cambios de Rol Recientes
                </h4>
                <div class="space-y-3">
                  <div v-for="user in recentActivity.recent_role_changes" :key="`role-${user.id}`" 
                       class="flex items-center justify-between">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-8 w-8">
                        <div class="h-8 w-8 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                          <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                            {{ getInitials(user.name) }}
                          </span>
                        </div>
                      </div>
                      <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                          {{ user.name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                          Rol: {{ user.role?.display_name }}
                        </p>
                      </div>
                    </div>
                    <div class="text-right">
                      <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ formatRelativeTime(user.updated_at) }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { 
  UsersIcon,
  CheckCircleIcon,
  UserGroupIcon,
  BuildingOfficeIcon,
  DocumentChartBarIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  stats: Object,
  usersByRole: Array,
  usersActivity: Object,
  officeStats: Object,
  recentActivity: Object
})

const getInitials = (name) => {
  return name.split(' ').map(word => word[0]).join('').toUpperCase().slice(0, 2)
}

const formatRelativeTime = (date) => {
  const now = new Date()
  const diffTime = Math.abs(now - new Date(date))
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays === 1) {
    return 'hace 1 día'
  } else if (diffDays < 7) {
    return `hace ${diffDays} días`
  } else if (diffDays < 30) {
    const weeks = Math.ceil(diffDays / 7)
    return `hace ${weeks} semana${weeks > 1 ? 's' : ''}`
  } else {
    const months = Math.ceil(diffDays / 30)
    return `hace ${months} mes${months > 1 ? 'es' : ''}`
  }
}
</script>