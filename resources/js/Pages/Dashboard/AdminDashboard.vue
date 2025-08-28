<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Welcome Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Panel de Administración
          </h1>
          <p class="mt-2 text-gray-600 dark:text-gray-400">
            Gestiona usuarios, roles y supervisa el sistema completo
          </p>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
          <StatsCard
            title="Total de Usuarios"
            :value="stats.total_users"
            icon="UsersIcon"
            icon-color="text-blue-500"
            :change="stats.users_growth"
            :action-text="'Ver Usuarios'"
            :action-url="route('admin.users.index')"
            :progress="(stats.active_users / stats.total_users) * 100"
            progress-label="Usuarios activos"
          />
          
          <StatsCard
            title="Notas Pendientes"
            :value="stats.pending_notes"
            icon="ClipboardDocumentListIcon"
            icon-color="text-orange-500"
            :change="stats.notes_growth"
            :action-text="'Revisar Notas'"
            :action-url="'/notas-pedido/confirmar'"
          />
          
          <StatsCard
            title="Oficinas Activas"
            :value="stats.active_offices"
            icon="BuildingOfficeIcon"
            icon-color="text-green-500"
            :action-text="'Gestionar'"
            :action-url="route('oficinas.index')"
          />
          
          <StatsCard
            title="Presupuesto Mensual"
            :value="stats.monthly_budget"
            format="currency"
            icon="CurrencyDollarIcon"
            icon-color="text-purple-500"
            :change="stats.budget_variance"
            change-unit="%"
          />
        </div>

        <!-- Main Dashboard Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <!-- System Overview -->
          <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                  Resumen del Sistema
                </h3>
              </div>
              <div class="p-6">
                <!-- Activity Chart Placeholder -->
                <div class="h-64 bg-gray-50 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                  <div class="text-center">
                    <ChartBarIcon class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Gráfico de Actividad</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                      Visualización de notas creadas y procesadas por mes
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions & Recent Activity -->
          <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                  Acciones Rápidas
                </h3>
              </div>
              <div class="p-6 space-y-3">
                <Link
                  :href="route('admin.users.create')"
                  class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <UserPlusIcon class="-ml-1 mr-2 h-4 w-4" />
                  Crear Usuario
                </Link>
                
                <Link
                  :href="route('admin.roles.create')"
                  class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <ShieldCheckIcon class="-ml-1 mr-2 h-4 w-4" />
                  Crear Rol
                </Link>
                
                <Link
                  :href="route('admin.reports.index')"
                  class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <PresentationChartLineIcon class="-ml-1 mr-2 h-4 w-4" />
                  Ver Reportes
                </Link>
              </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                  Actividad Reciente
                </h3>
              </div>
              <div class="p-6">
                <div class="flow-root">
                  <ul class="-mb-8">
                    <li v-for="(activity, index) in recentActivity" :key="activity.id">
                      <div class="relative pb-8" :class="{ 'pb-0': index === recentActivity.length - 1 }">
                        <span
                          v-if="index !== recentActivity.length - 1"
                          class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700"
                        />
                        <div class="relative flex space-x-3">
                          <div>
                            <span class="h-8 w-8 rounded-full flex items-center justify-center"
                                  :class="getActivityIconClasses(activity.type)">
                              <component :is="getActivityIcon(activity.type)" class="h-4 w-4" />
                            </span>
                          </div>
                          <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                            <div>
                              <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ activity.description }}
                              </p>
                              <p class="text-xs text-gray-400 dark:text-gray-500">
                                {{ activity.user }}
                              </p>
                            </div>
                            <div class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                              {{ formatTime(activity.created_at) }}
                            </div>
                          </div>
                        </div>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- System Status Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- User Distribution -->
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Distribución de Usuarios
              </h3>
            </div>
            <div class="p-6">
              <div class="space-y-4">
                <div v-for="role in usersByRole" :key="role.name" class="flex items-center justify-between">
                  <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full mr-3" :style="`background-color: ${role.color}`"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ role.name }}</span>
                  </div>
                  <span class="text-sm font-medium text-gray-900 dark:text-white">{{ role.count }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- System Alerts -->
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Alertas del Sistema
              </h3>
            </div>
            <div class="p-6">
              <div class="space-y-3">
                <div v-for="alert in systemAlerts" :key="alert.id" class="flex items-start">
                  <div class="flex-shrink-0">
                    <ExclamationTriangleIcon
                      v-if="alert.type === 'warning'"
                      class="h-5 w-5 text-yellow-400"
                    />
                    <InformationCircleIcon
                      v-else
                      class="h-5 w-5 text-blue-400"
                    />
                  </div>
                  <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ alert.title }}
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                      {{ alert.description }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Performance Metrics -->
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Métricas del Sistema
              </h3>
            </div>
            <div class="p-6">
              <div class="space-y-4">
                <div>
                  <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Tiempo de respuesta</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ metrics.response_time }}ms</span>
                  </div>
                  <div class="mt-2 w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                    <div class="bg-green-600 h-2 rounded-full" :style="`width: ${Math.min(metrics.response_time / 10, 100)}%`"></div>
                  </div>
                </div>
                
                <div>
                  <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Uptime</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ metrics.uptime }}%</span>
                  </div>
                  <div class="mt-2 w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                    <div class="bg-blue-600 h-2 rounded-full" :style="`width: ${metrics.uptime}%`"></div>
                  </div>
                </div>
                
                <div>
                  <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Uso de memoria</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ metrics.memory_usage }}%</span>
                  </div>
                  <div class="mt-2 w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                    <div class="bg-yellow-600 h-2 rounded-full" :style="`width: ${metrics.memory_usage}%`"></div>
                  </div>
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
import StatsCard from '@/Components/Admin/StatsCard.vue'
import {
  ChartBarIcon,
  UserPlusIcon,
  ShieldCheckIcon,
  PresentationChartLineIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  UsersIcon,
  ClipboardDocumentListIcon,
  BuildingOfficeIcon,
  CurrencyDollarIcon,
  CheckCircleIcon,
  XCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  stats: Object,
  recentActivity: Array,
  usersByRole: Array,
  systemAlerts: Array,
  metrics: Object
})

const breadcrumbs = [
  { name: 'Dashboard', href: route('dashboard') }
]

const getActivityIcon = (type) => {
  const icons = {
    user_created: UsersIcon,
    note_approved: CheckCircleIcon,
    note_rejected: XCircleIcon,
    role_assigned: ShieldCheckIcon
  }
  return icons[type] || InformationCircleIcon
}

const getActivityIconClasses = (type) => {
  const classes = {
    user_created: 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400',
    note_approved: 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400',
    note_rejected: 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400',
    role_assigned: 'bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-400'
  }
  return classes[type] || 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
}

const formatTime = (timestamp) => {
  return new Date(timestamp).toLocaleString('es-ES', {
    hour: '2-digit',
    minute: '2-digit',
    day: '2-digit',
    month: '2-digit'
  })
}
</script>