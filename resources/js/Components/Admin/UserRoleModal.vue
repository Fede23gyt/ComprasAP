<template>
  <Modal :show="show" @close="$emit('close')" max-width="md">
    <div class="px-6 py-4">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
          Cambiar Rol de Usuario
        </h3>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600"
        >
          <XMarkIcon class="h-6 w-6" />
        </button>
      </div>

      <div class="mb-6">
        <div class="flex items-center mb-4">
          <div class="flex-shrink-0 h-12 w-12">
            <div class="h-12 w-12 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ getInitials(user.name) }}
              </span>
            </div>
          </div>
          <div class="ml-4">
            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
              {{ user.name }}
            </h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              {{ user.email }}
            </p>
          </div>
        </div>

        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
            Rol actual:
          </p>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                :class="getRoleBadgeClasses(user.role.name)">
            {{ user.role.display_name }}
          </span>
        </div>
      </div>

      <form @submit.prevent="submit">
        <div class="mb-6">
          <label for="role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Nuevo Rol
          </label>
          <select
            v-model="form.role_id"
            id="role_id"
            required
            class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            :class="{ 'border-red-300': form.errors.role_id }"
          >
            <option value="">Seleccione un rol</option>
            <option v-for="role in roles" :key="role.id" :value="role.id">
              {{ role.display_name }}
            </option>
          </select>
          <p v-if="form.errors.role_id" class="mt-2 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.role_id }}
          </p>
        </div>

        <div class="flex justify-end space-x-3">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="form.processing || !form.role_id"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="form.processing">Actualizando...</span>
            <span v-else>Cambiar Rol</span>
          </button>
        </div>
      </form>
    </div>
  </Modal>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  show: Boolean,
  user: Object,
  roles: Array
})

const emit = defineEmits(['close', 'updated'])

const form = useForm({
  role_id: props.user?.role_id || ''
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

const submit = () => {
  form.patch(route('admin.users.update', props.user.id), {
    onSuccess: () => {
      emit('updated')
      emit('close')
      form.reset()
    },
  })
}
</script>