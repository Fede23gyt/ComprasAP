<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Sidebar -->
    <ModernSidebar 
      :is-open="sidebarOpen" 
      @close="sidebarOpen = false" 
      @toggle="sidebarOpen = !sidebarOpen"
    />
    
    <!-- Main Content -->
    <div class="lg:pl-72">
      <!-- Header -->
      <ModernHeader 
        @toggle-sidebar="sidebarOpen = !sidebarOpen"
        :notifications="notifications"
      />
      
      <!-- Breadcrumbs -->
      <nav v-if="breadcrumbs && breadcrumbs.length" class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="px-4 sm:px-6 lg:px-8">
          <div class="flex items-center space-x-2 py-3">
            <HomeIcon class="h-4 w-4 text-gray-400" />
            <template v-for="(crumb, index) in breadcrumbs" :key="index">
              <ChevronRightIcon class="h-4 w-4 text-gray-400" />
              <Link 
                v-if="crumb.href && index !== breadcrumbs.length - 1"
                :href="crumb.href"
                class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
              >
                {{ crumb.name }}
              </Link>
              <span 
                v-else
                class="text-sm font-medium text-gray-900 dark:text-gray-100"
              >
                {{ crumb.name }}
              </span>
            </template>
          </div>
        </div>
      </nav>
      
      <!-- Page Content -->
      <main class="flex-1">
        <slot />
      </main>
      
      <!-- Footer -->
      <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto">
        <div class="px-4 py-6 sm:px-6 lg:px-8">
          <div class="flex flex-col sm:flex-row justify-between items-center text-sm text-gray-500 dark:text-gray-400">
            <div class="flex items-center space-x-4">
              <span>&copy; 2025 Municipalidad de Salta</span>
              <span>•</span>
              <span>Sistema de Compras v2.0</span>
            </div>
            <div class="mt-2 sm:mt-0 flex items-center space-x-4">
              <a href="#" class="hover:text-gray-700 dark:hover:text-gray-300">Soporte</a>
              <span>•</span>
              <a href="#" class="hover:text-gray-700 dark:hover:text-gray-300">Documentación</a>
            </div>
          </div>
        </div>
      </footer>
    </div>
    
    <!-- Notifications Toast -->
    <NotificationToast 
      v-if="showNotification"
      :message="notificationMessage"
      :type="notificationType"
      @close="showNotification = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { HomeIcon, ChevronRightIcon } from '@heroicons/vue/20/solid'
import ModernSidebar from '@/Components/Layout/ModernSidebar.vue'
import ModernHeader from '@/Components/Layout/ModernHeader.vue'
import NotificationToast from '@/Components/Layout/NotificationToast.vue'

const props = defineProps({
  breadcrumbs: Array,
  notifications: {
    type: Array,
    default: () => []
  }
})

const page = usePage()
const sidebarOpen = ref(false)

// Notification system
const showNotification = ref(false)
const notificationMessage = ref('')
const notificationType = ref('info')

// Handle flash messages
const flashMessage = computed(() => page.props.flash)

onMounted(() => {
  // Show flash messages as notifications
  if (flashMessage.value?.success) {
    showNotificationToast(flashMessage.value.success, 'success')
  } else if (flashMessage.value?.error) {
    showNotificationToast(flashMessage.value.error, 'error')
  } else if (flashMessage.value?.warning) {
    showNotificationToast(flashMessage.value.warning, 'warning')
  }
})

const showNotificationToast = (message, type = 'info') => {
  notificationMessage.value = message
  notificationType.value = type
  showNotification.value = true
  
  // Auto-hide after 5 seconds
  setTimeout(() => {
    showNotification.value = false
  }, 5000)
}

// Expose method to child components
defineExpose({
  showNotificationToast
})
</script>
