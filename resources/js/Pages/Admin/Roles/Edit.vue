<template>
  <AppLayout :title="`Editar Rol: ${role.display_name}`">
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
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
              <li>
                <div class="flex items-center">
                  <ChevronRightIcon class="w-6 h-6 text-gray-400" />
                  <span class="ml-1 text-gray-500 dark:text-gray-400">Editar</span>
                </div>
              </li>
            </ol>
          </nav>
          
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                Editar Rol: {{ role.display_name }}
              </h1>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Modifica la configuración y permisos del rol.
              </p>
            </div>
            <span v-if="isSystemRole" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
              <CogIcon class="h-4 w-4 mr-1" />
              Rol del Sistema
            </span>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Información Básica -->
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                Información del Rol
              </h3>
              
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Nombre interno -->
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nombre Interno *
                  </label>
                  <input
                    v-model="form.name"
                    type="text"
                    id="name"
                    required
                    :disabled="isSystemRole"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    :class="{ 'border-red-300': form.errors.name, 'opacity-50 cursor-not-allowed': isSystemRole }"
                  />
                  <p v-if="isSystemRole" class="mt-1 text-xs text-yellow-600 dark:text-yellow-400">
                    No se puede modificar el nombre de un rol del sistema
                  </p>
                  <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Solo letras minúsculas y guiones bajos
                  </p>
                  <p v-if="form.errors.name" class="mt-2 text-sm text-red-600 dark:text-red-400">
                    {{ form.errors.name }}
                  </p>
                </div>

                <!-- Nombre para mostrar -->
                <div>
                  <label for="display_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nombre para Mostrar *
                  </label>
                  <input
                    v-model="form.display_name"
                    type="text"
                    id="display_name"
                    required
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    :class="{ 'border-red-300': form.errors.display_name }"
                  />
                  <p v-if="form.errors.display_name" class="mt-2 text-sm text-red-600 dark:text-red-400">
                    {{ form.errors.display_name }}
                  </p>
                </div>

                <!-- Descripción -->
                <div class="sm:col-span-2">
                  <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Descripción
                  </label>
                  <textarea
                    v-model="form.description"
                    id="description"
                    rows="3"
                    maxlength="500"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="Describe las responsabilidades y alcances de este rol..."
                  />
                  <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ form.description?.length || 0 }}/500 caracteres
                  </p>
                </div>

                <!-- Estado -->
                <div class="sm:col-span-2">
                  <label class="flex items-center">
                    <input
                      v-model="form.is_active"
                      type="checkbox"
                      class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out"
                    />
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                      Rol activo
                    </span>
                  </label>
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Los usuarios con roles inactivos no podrán acceder al sistema
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Permisos -->
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                Permisos del Rol
              </h3>
              <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Selecciona los permisos que tendrán los usuarios con este rol.
              </p>
              
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                  v-for="(description, permission) in availablePermissions"
                  :key="permission"
                  class="relative flex items-start p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                  :class="{ 'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-700': form.permissions.includes(permission) }"
                >
                  <div class="flex items-center h-5">
                    <input
                      :id="permission"
                      v-model="form.permissions"
                      :value="permission"
                      type="checkbox"
                      class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded"
                    />
                  </div>
                  <div class="ml-3 text-sm">
                    <label :for="permission" class="font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                      {{ description }}
                    </label>
                    <p class="text-gray-500 dark:text-gray-400 text-xs font-mono">
                      {{ permission }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="mt-4 flex space-x-4">
                <button
                  type="button"
                  @click="selectAllPermissions"
                  class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
                >
                  Seleccionar todos
                </button>
                <button
                  type="button"
                  @click="clearAllPermissions"
                  class="text-sm text-gray-600 dark:text-gray-400 hover:underline"
                >
                  Limpiar selección
                </button>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3">
            <Link
              :href="route('admin.roles.index')"
              class="bg-white dark:bg-gray-700 py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Cancelar
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="form.processing" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Guardando...
              </span>
              <span v-else>Actualizar Rol</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ChevronRightIcon, CogIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  role: Object,
  availablePermissions: Object
})

const systemRoles = ['administrador', 'secretario', 'director', 'operador', 'invitado']
const isSystemRole = computed(() => systemRoles.includes(props.role.name))

const form = useForm({
  name: props.role.name,
  display_name: props.role.display_name,
  description: props.role.description || '',
  permissions: props.role.permissions || [],
  is_active: props.role.is_active
})

const selectAllPermissions = () => {
  form.permissions = Object.keys(props.availablePermissions)
}

const clearAllPermissions = () => {
  form.permissions = []
}

const submit = () => {
  form.put(route('admin.roles.update', props.role.id), {
    onSuccess: () => {
      // La redirección se maneja en el backend
    },
  })
}
</script>
