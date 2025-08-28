<template>
  <AuthenticatedLayout>
    <div class="py-6">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
              <li class="inline-flex items-center">
                <Link :href="route('admin.users.index')" class="text-gray-700 dark:text-gray-300 hover:text-blue-600">
                  Usuarios
                </Link>
              </li>
              <li>
                <div class="flex items-center">
                  <ChevronRightIcon class="w-6 h-6 text-gray-400" />
                  <span class="ml-1 text-gray-500 dark:text-gray-400">{{ user.name }}</span>
                </div>
              </li>
            </ol>
          </nav>
          
          <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-16 w-16">
                  <div class="h-16 w-16 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                    <span class="text-xl font-medium text-gray-700 dark:text-gray-300">
                      {{ getInitials(user.name) }}
                    </span>
                  </div>
                </div>
                <div class="ml-6">
                  <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ user.name }}
                  </h1>
                  <div class="flex items-center mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="getRoleBadgeClasses(user.role.name)">
                      {{ user.role.display_name }}
                    </span>
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="user.is_active 
                            ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' 
                            : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'">
                      {{ user.is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <div class="mt-4 flex space-x-3 md:mt-0">
              <Link
                :href="route('admin.users.edit', user.id)"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <PencilIcon class="-ml-1 mr-2 h-5 w-5" />
                Editar
              </Link>
              <button
                @click="toggleUserStatus"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                {{ user.is_active ? 'Desactivar' : 'Activar' }}
              </button>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Información Principal -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Información Personal -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
              <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                  Información Personal
                </h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                      Nombre Completo
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                      {{ user.name }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                      Correo Electrónico
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                      <a :href="`mailto:${user.email}`" class="text-blue-600 hover:text-blue-500">
                        {{ user.email }}
                      </a>
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                      DNI
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                      {{ user.dni || 'No especificado' }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                      Teléfono
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                      {{ user.telefono || 'No especificado' }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                      Fecha de Registro
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                      {{ formatDate(user.created_at) }}
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                      Última Actualización
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                      {{ formatDate(user.updated_at) }}
                    </dd>
                  </div>
                  <div v-if="user.observaciones" class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                      Observaciones
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                      {{ user.observaciones }}
                    </dd>
                  </div>
                </dl>
              </div>
            </div>

            <!-- Oficinas Asignadas -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
              <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                  Oficinas Asignadas
                </h3>
                
                <div v-if="user.oficinas.length > 0" class="space-y-3">
                  <div v-for="oficina in user.oficinas" :key="oficina.id" 
                       class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex items-center">
                      <BuildingOfficeIcon class="h-5 w-5 text-gray-400 mr-3" />
                      <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                          {{ oficina.nombre }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                          {{ oficina.codigo_oficina }}
                        </p>
                      </div>
                    </div>
                    <div class="flex items-center space-x-2">
                      <span v-if="oficina.pivot.es_principal" 
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                        <CheckCircleIcon class="h-3 w-3 mr-1" />
                        Principal
                      </span>
                      <span v-if="oficina.pivot.puede_autorizar" 
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                        <ShieldCheckIcon class="h-3 w-3 mr-1" />
                        Puede Autorizar
                      </span>
                    </div>
                  </div>
                </div>
                
                <div v-else class="text-center py-6">
                  <BuildingOfficeIcon class="mx-auto h-12 w-12 text-gray-400" />
                  <h4 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                    Sin oficinas asignadas
                  </h4>
                  <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Este usuario no tiene oficinas asignadas.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Panel Lateral -->
          <div class="space-y-6">
            <!-- Información del Rol -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
              <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                  Información del Rol
                </h3>
                <div class="space-y-3">
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                      Rol Actual
                    </dt>
                    <dd class="mt-1">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium"
                            :class="getRoleBadgeClasses(user.role.name)">
                        {{ user.role.display_name }}
                      </span>
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                      Código del Rol
                    </dt>
                    <dd class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100">
                      {{ user.role.name }}
                    </dd>
                  </div>
                  <div v-if="user.role.description">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                      Descripción
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                      {{ user.role.description }}
                    </dd>
                  </div>
                </div>
              </div>
            </div>

            <!-- Resumen de Permisos -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
              <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                  Capacidades
                </h3>
                <div class="space-y-2">
                  <div class="flex items-center">
                    <CheckIcon v-if="user.role.name === 'administrador'" class="h-4 w-4 text-green-500 mr-2" />
                    <XMarkIcon v-else class="h-4 w-4 text-gray-400 mr-2" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Administrador del sistema</span>
                  </div>
                  <div class="flex items-center">
                    <CheckIcon v-if="['administrador', 'secretario', 'director'].includes(user.role.name)" 
                               class="h-4 w-4 text-green-500 mr-2" />
                    <XMarkIcon v-else class="h-4 w-4 text-gray-400 mr-2" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Supervisor</span>
                  </div>
                  <div class="flex items-center">
                    <CheckIcon v-if="oficinasPuedeAutorizar.length > 0" class="h-4 w-4 text-green-500 mr-2" />
                    <XMarkIcon v-else class="h-4 w-4 text-gray-400 mr-2" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Puede autorizar notas</span>
                  </div>
                  <div class="flex items-center">
                    <CheckIcon v-if="user.oficinas.length > 0" class="h-4 w-4 text-green-500 mr-2" />
                    <XMarkIcon v-else class="h-4 w-4 text-gray-400 mr-2" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Tiene oficinas asignadas</span>
                  </div>
                  <div class="flex items-center">
                    <CheckIcon v-if="oficinaPrincipal" class="h-4 w-4 text-green-500 mr-2" />
                    <XMarkIcon v-else class="h-4 w-4 text-gray-400 mr-2" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Tiene oficina principal</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
              <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                  Acciones
                </h3>
                <div class="space-y-3">
                  <button
                    @click="toggleUserStatus"
                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                  >
                    {{ user.is_active ? 'Desactivar Usuario' : 'Activar Usuario' }}
                  </button>
                  
                  <Link
                    :href="route('admin.users.edit', user.id)"
                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                  >
                    <PencilIcon class="-ml-1 mr-2 h-4 w-4" />
                    Editar Usuario
                  </Link>
                  
                  <button
                    @click="confirmDeleteUser"
                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                  >
                    <TrashIcon class="-ml-1 mr-2 h-4 w-4" />
                    Eliminar Usuario
                  </button>
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
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useConfirm } from '@/Composables/useConfirm'
import { 
  ChevronRightIcon,
  PencilIcon,
  TrashIcon,
  BuildingOfficeIcon,
  CheckCircleIcon,
  ShieldCheckIcon,
  CheckIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  user: Object,
  oficinaPrincipal: Object,
  oficinasAutoriza: Array
})

const { confirm } = useConfirm()

const oficinasPuedeAutorizar = computed(() => {
  return props.user.oficinas.filter(oficina => oficina.pivot.puede_autorizar)
})

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
  return new Date(date).toLocaleString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const toggleUserStatus = async () => {
  const action = props.user.is_active ? 'desactivar' : 'activar'
  
  if (await confirm(`¿Estás seguro de que quieres ${action} al usuario "${props.user.name}"?`)) {
    router.patch(route('admin.users.toggle-status', props.user.id), {}, {
      preserveState: true,
      preserveScroll: true
    })
  }
}

const confirmDeleteUser = async () => {
  if (await confirm(
    `¿Estás seguro de que quieres eliminar al usuario "${props.user.name}"?`,
    'Esta acción no se puede deshacer.'
  )) {
    router.delete(route('admin.users.destroy', props.user.id))
  }
}
</script>