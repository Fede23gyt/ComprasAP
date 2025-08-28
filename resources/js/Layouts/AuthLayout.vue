<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Main Content -->
    <main class="flex-1">
      <slot />
    </main>
    
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
import { usePage } from '@inertiajs/vue3'
import NotificationToast from '@/Components/Layout/NotificationToast.vue'

const page = usePage()

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