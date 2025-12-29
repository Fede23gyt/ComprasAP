<template>
  <AppLayout :title="`Editar Usuario: ${user.name}`">
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
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
              <li>
                <div class="flex items-center">
                  <ChevronRightIcon class="w-6 h-6 text-gray-400" />
                  <span class="ml-1 text-gray-500 dark:text-gray-400">Editar</span>
                </div>
              </li>
            </ol>
          </nav>
          
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            Editar Usuario: {{ user.name }}
          </h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Modifique los datos del usuario y ajuste sus permisos según sea necesario.
          </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Información Personal -->
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                Información Personal
              </h3>
              
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Nombre -->
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nombre Completo *
                  </label>
                  <input
                    v-model="form.name"
                    type="text"
                    id="name"
                    required
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    :class="{ 'border-red-300': errors.name }"
                  />
                  <p v-if="errors.name" class="mt-2 text-sm text-red-600 dark:text-red-400">
                    {{ errors.name }}
                  </p>
                </div>

                <!-- Email -->
                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Correo Electrónico *
                  </label>
                  <input
                    v-model="form.email"
                    type="email"
                    id="email"
                    required
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    :class="{ 'border-red-300': errors.email }"
                  />
                  <p v-if="errors.email" class="mt-2 text-sm text-red-600 dark:text-red-400">
                    {{ errors.email }}
                  </p>
                </div>

                <!-- DNI -->
                <div>
                  <label for="dni" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    DNI
                  </label>
                  <input
                    v-model="form.dni"
                    type="text"
                    id="dni"
                    maxlength="20"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    :class="{ 'border-red-300': errors.dni }"
                  />
                  <p v-if="errors.dni" class="mt-2 text-sm text-red-600 dark:text-red-400">
                    {{ errors.dni }}
                  </p>
                </div>

                <!-- Teléfono -->
                <div>
                  <label for="telefono" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Teléfono
                  </label>
                  <input
                    v-model="form.telefono"
                    type="text"
                    id="telefono"
                    maxlength="20"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    :class="{ 'border-red-300': errors.telefono }"
                  />
                  <p v-if="errors.telefono" class="mt-2 text-sm text-red-600 dark:text-red-400">
                    {{ errors.telefono }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Cambiar Contraseña -->
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                  Cambiar Contraseña
                </h3>
                <label class="flex items-center">
                  <input
                    v-model="changePassword"
                    type="checkbox"
                    class="form-checkbox h-4 w-4 text-blue-600"
                  />
                  <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    Cambiar contraseña
                  </span>
                </label>
              </div>
              
              <div v-if="changePassword" class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Nueva Contraseña -->
                <div>
                  <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nueva Contraseña
                  </label>
                  <input
                    v-model="form.password"
                    type="password"
                    id="password"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    :class="{ 'border-red-300': errors.password }"
                  />
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Mínimo 8 caracteres. Dejar vacío para mantener la contraseña actual.
                  </p>
                  <p v-if="errors.password" class="mt-2 text-sm text-red-600 dark:text-red-400">
                    {{ errors.password }}
                  </p>
                </div>

                <!-- Confirmar Nueva Contraseña -->
                <div>
                  <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Confirmar Nueva Contraseña
                  </label>
                  <input
                    v-model="form.password_confirmation"
                    type="password"
                    id="password_confirmation"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Rol y Estado -->
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                Rol y Permisos
              </h3>
              
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Rol -->
                <div>
                  <label for="role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Rol *
                  </label>
                  <select
                    v-model="form.role_id"
                    id="role_id"
                    required
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    :class="{ 'border-red-300': errors.role_id }"
                  >
                    <option value="">Seleccione un rol</option>
                    <option v-for="role in roles" :key="role.id" :value="role.id">
                      {{ role.display_name }}
                    </option>
                  </select>
                  <p v-if="selectedRole" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Rol: {{ selectedRole.name }} - {{ selectedRole.display_name }}
                  </p>
                  <p v-if="errors.role_id" class="mt-2 text-sm text-red-600 dark:text-red-400">
                    {{ errors.role_id }}
                  </p>
                </div>

                <!-- Estado -->
                <div>
                  <label for="is_active" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Estado
                  </label>
                  <div class="mt-1">
                    <label class="inline-flex items-center">
                      <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out"
                      />
                      <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        Usuario activo
                      </span>
                    </label>
                  </div>
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Los usuarios inactivos no pueden acceder al sistema
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Oficinas -->
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                Oficinas Asignadas
              </h3>
              
              <div class="space-y-4">
                <!-- Búsqueda de oficinas -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Buscar Oficinas
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                    </div>
                    <input
                      v-model="searchTerm"
                      type="text"
                      placeholder="Buscar por nombre o código..."
                      class="pl-10 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    />
                    <button
                      v-if="searchTerm"
                      @click="searchTerm = ''"
                      class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                    >
                      <XMarkIcon class="h-4 w-4" />
                    </button>
                  </div>
                </div>

                <!-- Oficinas filtradas -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Oficinas Disponibles
                  </label>
                  <div class="grid grid-cols-1 gap-2 max-h-64 overflow-y-auto border border-gray-300 dark:border-gray-600 rounded-md p-3">
                    <div v-if="filteredOficinas.length === 0" class="text-center py-4 text-sm text-gray-500">
                      No se encontraron oficinas
                    </div>
                    <label 
                      v-for="oficina in filteredOficinas" 
                      :key="oficina.id" 
                      class="flex items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors"
                    >
                      <input
                        v-model="form.oficinas"
                        type="checkbox"
                        :value="oficina.id"
                        class="form-checkbox h-4 w-4 text-blue-600"
                      />
                      <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                        <span class="font-mono text-xs bg-gray-100 dark:bg-gray-600 px-1 py-0.5 rounded">
                          {{ oficina.codigo_oficina }}
                        </span>
                        - {{ oficina.nombre }}
                      </span>
                    </label>
                  </div>
                </div>

                <!-- Oficinas seleccionadas -->
                <div v-if="form.oficinas.length > 0">
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Oficinas Seleccionadas ({{ form.oficinas.length }})
                  </label>
                  <div class="space-y-2">
                    <div 
                      v-for="oficinaId in form.oficinas" 
                      :key="oficinaId"
                      class="flex items-center justify-between p-2 bg-blue-50 dark:bg-blue-900/20 rounded-md"
                    >
                      <span class="text-sm text-gray-700 dark:text-gray-300">
                        {{ getOfficinaName(oficinaId) }}
                      </span>
                      <button
                        @click="removeOficina(oficinaId)"
                        class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
                      >
                        <XMarkIcon class="h-4 w-4" />
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Oficina principal -->
                <div v-if="form.oficinas.length > 0">
                  <label for="oficina_principal" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Oficina Principal
                  </label>
                  <select
                    v-model="form.oficina_principal"
                    id="oficina_principal"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  >
                    <option value="">Sin oficina principal</option>
                    <option 
                      v-for="oficinaId in form.oficinas" 
                      :key="oficinaId" 
                      :value="oficinaId"
                    >
                      {{ getOfficinaName(oficinaId) }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- Observaciones -->
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <label for="observaciones" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Observaciones
              </label>
              <textarea
                v-model="form.observaciones"
                id="observaciones"
                rows="3"
                maxlength="500"
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                :class="{ 'border-red-300': errors.observaciones }"
                placeholder="Notas adicionales sobre el usuario..."
              />
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ form.observaciones?.length || 0 }}/500 caracteres
              </p>
              <p v-if="errors.observaciones" class="mt-2 text-sm text-red-600 dark:text-red-400">
                {{ errors.observaciones }}
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3">
            <Link
              :href="route('admin.users.index')"
              class="bg-white dark:bg-gray-700 py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Cancelar
            </Link>
            <button
              type="submit"
              :disabled="processing"
              class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="processing" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Actualizando...
              </span>
              <span v-else>Actualizar Usuario</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ChevronRightIcon, MagnifyingGlassIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  user: Object,
  roles: Array,
  oficinas: Array,
  userOficinas: Array
})

const changePassword = ref(false)
const searchTerm = ref('')

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  dni: props.user.dni,
  telefono: props.user.telefono,
  password: '',
  password_confirmation: '',
  role_id: props.user.role_id,
  is_active: props.user.is_active,
  oficinas: props.userOficinas.map(o => o.id),
  oficina_principal: props.userOficinas.find(o => o.es_principal)?.id || '',
  observaciones: props.user.observaciones || ''
})

const { errors, processing } = form

const selectedRole = computed(() => {
  return props.roles.find(role => role.id === form.role_id)
})

const getOfficinaName = (oficinaId) => {
  const oficina = props.oficinas.find(o => o.id === oficinaId)
  return oficina ? `${oficina.codigo_oficina} - ${oficina.nombre}` : ''
}

// Filtrar oficinas según término de búsqueda
const filteredOficinas = computed(() => {
  if (!searchTerm.value) {
    return props.oficinas
  }
  const term = searchTerm.value.toLowerCase()
  return props.oficinas.filter(oficina => 
    oficina.nombre.toLowerCase().includes(term) ||
    oficina.codigo_oficina.toLowerCase().includes(term)
  )
})

// Remover una oficina de la selección
const removeOficina = (oficinaId) => {
  form.oficinas = form.oficinas.filter(id => id !== oficinaId)
  // Si la oficina removida era la principal, limpiar la selección
  if (form.oficina_principal === oficinaId) {
    form.oficina_principal = ''
  }
}

const submit = () => {
  // If not changing password, remove password fields
  if (!changePassword.value) {
    form.password = ''
    form.password_confirmation = ''
  }

  form.put(route('admin.users.update', props.user.id), {
    onSuccess: () => {
      // La redirección se maneja en el backend
    },
  })
}
</script>