<template>
  <div class="space-y-4">
    <!-- Permission Groups -->
    <div v-for="(group, groupKey) in permissionGroups" :key="groupKey" 
         class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
      <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
          {{ group.name }}
        </h4>
        <button
          type="button"
          @click="toggleGroup(groupKey)"
          class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200"
        >
          {{ isGroupSelected(groupKey) ? 'Deseleccionar todo' : 'Seleccionar todo' }}
        </button>
      </div>
      
      <div class="grid grid-cols-1 gap-3">
        <label 
          v-for="permission in group.permissions" 
          :key="permission"
          class="flex items-center"
        >
          <input
            :value="permission"
            v-model="selectedPermissions"
            type="checkbox"
            class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out"
          />
          <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
            {{ getPermissionName(permission) }}
          </span>
        </label>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  availablePermissions: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:modelValue'])

const selectedPermissions = computed({
  get: () => props.modelValue || [],
  set: (value) => emit('update:modelValue', value)
})

const permissionGroups = {
  system: {
    name: 'Sistema',
    permissions: ['manage_users', 'manage_roles', 'manage_offices', 'manage_config']
  },
  notes: {
    name: 'Notas de Pedido',
    permissions: ['create_any_nota', 'create_own_notas', 'authorize_notas', 'view_all_notas', 'view_own_notas']
  },
  procurement: {
    name: 'Compras',
    permissions: ['manage_budgets', 'manage_orders', 'view_budgets', 'view_orders']
  },
  reports: {
    name: 'Reportes',
    permissions: ['view_all_reports', 'view_own_reports', 'export_reports']
  },
  catalog: {
    name: 'Catálogo',
    permissions: ['manage_nomenclators', 'view_nomenclators']
  }
}

const getPermissionName = (permission) => {
  return props.availablePermissions[permission] || permission
}

const isGroupSelected = (groupKey) => {
  const groupPermissions = permissionGroups[groupKey].permissions
  return groupPermissions.every(permission => selectedPermissions.value.includes(permission))
}

const toggleGroup = (groupKey) => {
  const groupPermissions = permissionGroups[groupKey].permissions
  const allSelected = isGroupSelected(groupKey)
  
  if (allSelected) {
    // Remove all group permissions
    selectedPermissions.value = selectedPermissions.value.filter(
      permission => !groupPermissions.includes(permission)
    )
  } else {
    // Add all group permissions
    const newPermissions = [...selectedPermissions.value]
    groupPermissions.forEach(permission => {
      if (!newPermissions.includes(permission)) {
        newPermissions.push(permission)
      }
    })
    selectedPermissions.value = newPermissions
  }
}
</script>