<template>
  <AuthenticatedLayout>
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <h1 class="text-xl font-bold mb-6">Información de Usuario - Debug</h1>
          
          <div class="space-y-4">
            <div>
              <h2 class="font-semibold text-lg mb-2">Datos del Usuario:</h2>
              <pre class="bg-gray-100 dark:bg-gray-700 p-4 rounded text-sm overflow-x-auto">{{ JSON.stringify(user, null, 2) }}</pre>
            </div>
            
            <div>
              <h2 class="font-semibold text-lg mb-2">Análisis de Roles:</h2>
              <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded">
                <p><strong>Tiene user.roles:</strong> {{ !!user?.roles }}</p>
                <p><strong>Cantidad roles Spatie:</strong> {{ user?.roles?.length || 0 }}</p>
                <p><strong>Primer rol Spatie:</strong> {{ user?.roles?.[0]?.name || 'N/A' }}</p>
                <p><strong>Tiene user.role:</strong> {{ !!user?.role }}</p>
                <p><strong>Tipo de user.role:</strong> {{ typeof user?.role }}</p>
                <p><strong>Rol tradicional:</strong> {{ typeof user?.role === 'object' ? user?.role?.name : user?.role }}</p>
                <p><strong>Role ID:</strong> {{ user?.role_id || 'N/A' }}</p>
              </div>
            </div>
            
            <div>
              <h2 class="font-semibold text-lg mb-2">Test de Acceso:</h2>
              <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded">
                <p><strong>canAccessConfig:</strong> {{ canAccessConfig }}</p>
                <p><strong>Rol detectado:</strong> {{ detectedRole }}</p>
                <p><strong>Es administrador/secretario/director:</strong> {{ isAdminRole }}</p>
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
import { usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)

const detectedRole = computed(() => {
  if (!user.value) return null;
  
  // 1. Roles de Spatie Permission
  if (user.value.roles && user.value.roles.length > 0) {
    return user.value.roles[0].name;
  }
  // 2. Rol tradicional como objeto
  else if (user.value.role && typeof user.value.role === 'object') {
    return user.value.role.name;
  }
  // 3. Rol tradicional como string directo
  else if (user.value.role && typeof user.value.role === 'string') {
    return user.value.role;
  }
  
  return null;
})

const isAdminRole = computed(() => {
  const roleName = detectedRole.value;
  return roleName && ['administrador', 'secretario', 'director'].includes(roleName);
})

const canAccessConfig = computed(() => {
  return !!user.value && isAdminRole.value;
})
</script>