<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Welcome Header -->
        <div class="mb-8">
          <div class="sm:flex sm:items-center sm:justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Bienvenido, {{ user.name }}
              </h1>
              <p class="mt-2 text-gray-600 dark:text-gray-400">
                Panel de trabajo para operadores - Gestiona tus notas de pedido
              </p>
            </div>
            <div class="mt-4 sm:mt-0">
              <Link
                :href="route('notas-pedido.create')"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
                Nueva Nota de Pedido
              </Link>
            </div>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
          <StatsCard
            title="Mis Notas"
            :value="stats.my_notes"
            icon="ClipboardDocumentListIcon"
            icon-color="text-blue-500"
            :action-text="'Ver todas'"
            :action-url="route('notas-pedido.index')"
          />
          
          <StatsCard
            title="En Proceso"
            :value="stats.processing_notes"
            icon="ClockIcon"
            icon-color="text-yellow-500"
            subtitle="Esperando aprobación"
          />
          
          <StatsCard
            title="Aprobadas"
            :value="stats.approved_notes"
            icon="CheckCircleIcon"
            icon-color="text-green-500"
            :change="stats.approval_rate"
            change-unit="%"
          />
          
          <StatsCard
            title="Este Mes"
            :value="stats.monthly_notes"
            icon="CalendarIcon"
            icon-color="text-purple-500"
            :change="stats.monthly_growth"
          />
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <!-- My Recent Notes -->
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                  Mis Notas Recientes
                </h3>
                <Link
                  :href="route('notas-pedido.index')"
                  class="text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400"
                >
                  Ver todas →
                </Link>
              </div>
            </div>
            <div class="p-6">
              <div class="space-y-4">
                <div
                  v-for="note in recentNotes"
                  :key="note.id"
                  class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors cursor-pointer"
                  @click="$inertia.visit(route('notas-pedido.show', note.id))"
                >
                  <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                      <div class="h-10 w-10 rounded-lg flex items-center justify-center"
                           :class="getStatusBadgeClasses(note.status)">
                        <component :is="getStatusIcon(note.status)" class="h-5 w-5" />
                      </div>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-900 dark:text-white">
                        Nota #{{ note.numero }}
                      </p>
                      <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ note.oficina_destino.nombre }}
                      </p>
                      <p class="text-xs text-gray-400">
                        {{ formatDate(note.created_at) }}
                      </p>
                    </div>
                  </div>
                  <div class="text-right">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="getStatusBadgeClasses(note.status)">
                      {{ getStatusText(note.status) }}
                    </span>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                      {{ note.items_count }} item{{ note.items_count !== 1 ? 's' : '' }}
                    </p>
                  </div>
                </div>
              </div>
              
              <div v-if="recentNotes.length === 0" class="text-center py-8">
                <ClipboardDocumentListIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay notas recientes</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                  Comienza creando tu primera nota de pedido
                </p>
                <div class="mt-6">
                  <Link
                    :href="route('notas-pedido.create')"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                  >
                    <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
                    Nueva Nota
                  </Link>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions & Tools -->
          <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                  Acciones Rápidas
                </h3>
              </div>
              <div class="p-6 grid grid-cols-1 gap-4">
                <Link
                  :href="route('notas-pedido.create')"
                  class="flex items-center p-4 bg-blue-50 dark:bg-blue-900/50 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/70 transition-colors"
                >
                  <div class="flex-shrink-0">
                    <PlusIcon class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-blue-900 dark:text-blue-200">
                      Crear Nueva Nota
                    </p>
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                      Solicita nuevos insumos para tu oficina
                    </p>
                  </div>
                </Link>

                <Link
                  :href="route('notas-pedido.consultas')"
                  class="flex items-center p-4 bg-green-50 dark:bg-green-900/50 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/70 transition-colors"
                >
                  <div class="flex-shrink-0">
                    <MagnifyingGlassIcon class="h-6 w-6 text-green-600 dark:text-green-400" />
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-green-900 dark:text-green-200">
                      Consultar Estado
                    </p>
                    <p class="text-sm text-green-700 dark:text-green-300">
                      Revisa el estado de tus solicitudes
                    </p>
                  </div>
                </Link>

                <Link
                  :href="route('nomencladores.insumos.index')"
                  class="flex items-center p-4 bg-purple-50 dark:bg-purple-900/50 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/70 transition-colors"
                >
                  <div class="flex-shrink-0">
                    <BookOpenIcon class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-purple-900 dark:text-purple-200">
                      Catálogo de Insumos
                    </p>
                    <p class="text-sm text-purple-700 dark:text-purple-300">
                      Explora los insumos disponibles
                    </p>
                  </div>
                </Link>
              </div>
            </div>

            <!-- Notification Center -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                  Notificaciones
                </h3>
              </div>
              <div class="p-6">
                <div class="space-y-3">
                  <div v-for="notification in notifications" :key="notification.id" 
                       class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex-shrink-0">
                      <component :is="getNotificationIcon(notification.type)" 
                                 class="h-5 w-5"
                                 :class="getNotificationIconClasses(notification.type)" />
                    </div>
                    <div class="flex-1">
                      <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ notification.title }}
                      </p>
                      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ notification.message }}
                      </p>
                      <p class="text-xs text-gray-400 mt-1">
                        {{ formatTime(notification.created_at) }}
                      </p>
                    </div>
                  </div>
                  
                  <div v-if="notifications.length === 0" class="text-center py-4">
                    <BellIcon class="mx-auto h-8 w-8 text-gray-400" />
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                      No hay notificaciones nuevas
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tips and Help -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <LightBulbIcon class="h-8 w-8 text-blue-600 dark:text-blue-400" />
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Consejos para optimizar tu trabajo
              </h3>
              <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-400">
                <div class="flex items-start space-x-2">
                  <CheckIcon class="h-4 w-4 text-green-500 mt-0.5" />
                  <span>Revisa el catálogo antes de crear una nota</span>
                </div>
                <div class="flex items-start space-x-2">
                  <CheckIcon class="h-4 w-4 text-green-500 mt-0.5" />
                  <span>Incluye justificación clara en tus solicitudes</span>
                </div>
                <div class="flex items-start space-x-2">
                  <CheckIcon class="h-4 w-4 text-green-500 mt-0.5" />
                  <span>Consulta el estado regularmente</span>
                </div>
                <div class="flex items-start space-x-2">
                  <CheckIcon class="h-4 w-4 text-green-500 mt-0.5" />
                  <span>Contacta soporte si tienes dudas</span>
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
  PlusIcon,
  ClipboardDocumentListIcon,
  ClockIcon,
  CheckCircleIcon,
  CalendarIcon,
  MagnifyingGlassIcon,
  BookOpenIcon,
  BellIcon,
  LightBulbIcon,
  CheckIcon,
  XCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  user: Object,
  stats: Object,
  recentNotes: Array,
  notifications: Array
})

const breadcrumbs = [
  { name: 'Dashboard', href: route('dashboard') }
]

const getStatusIcon = (status) => {
  const icons = {
    draft: ClockIcon,
    pending: ClockIcon,
    approved: CheckCircleIcon,
    rejected: XCircleIcon
  }
  return icons[status] || ClockIcon
}

const getStatusBadgeClasses = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    approved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
  }
  return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

const getStatusText = (status) => {
  const texts = {
    draft: 'Borrador',
    pending: 'Pendiente',
    approved: 'Aprobada',
    rejected: 'Rechazada'
  }
  return texts[status] || 'Desconocido'
}

const getNotificationIcon = (type) => {
  const icons = {
    success: CheckCircleIcon,
    warning: ExclamationTriangleIcon,
    error: XCircleIcon,
    info: InformationCircleIcon
  }
  return icons[type] || InformationCircleIcon
}

const getNotificationIconClasses = (type) => {
  const classes = {
    success: 'text-green-500',
    warning: 'text-yellow-500',
    error: 'text-red-500',
    info: 'text-blue-500'
  }
  return classes[type] || 'text-blue-500'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
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