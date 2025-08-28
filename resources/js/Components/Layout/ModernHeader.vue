<template>
  <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
    <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
      <!-- Left side: Menu button + Search -->
      <div class="flex items-center space-x-4">
        <!-- Mobile menu button -->
        <button
          @click="$emit('toggle-sidebar')"
          class="lg:hidden rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-700"
        >
          <Bars3Icon class="h-6 w-6" />
        </button>

        <!-- Global Search -->
        <div class="relative hidden sm:block w-96">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
          </div>
          <input
            v-model="searchQuery"
            @keyup.enter="performSearch"
            type="text"
            placeholder="Buscar usuarios, notas, insumos..."
            class="block w-full rounded-md border-0 bg-gray-50 py-1.5 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600 dark:placeholder:text-gray-400 sm:text-sm sm:leading-6"
          />
        </div>
      </div>

      <!-- Right side: Quick actions + User menu -->
      <div class="flex items-center space-x-4">
        <!-- Quick Actions -->
        <div class="hidden md:flex items-center space-x-2">
          <!-- New Note Button -->
          <Link
            :href="route('notas-pedido.create')"
            class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
          >
            <PlusIcon class="-ml-0.5 mr-1.5 h-4 w-4" />
            Nueva Nota
          </Link>
        </div>

        <!-- Notifications -->
        <div class="relative">
          <button
            @click="showNotifications = !showNotifications"
            class="relative rounded-full p-1 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:hover:text-gray-300"
          >
            <BellIcon class="h-6 w-6" />
            <span v-if="unreadCount > 0" class="absolute -top-0.5 -right-0.5 h-4 w-4 rounded-full bg-red-500 text-xs font-medium text-white flex items-center justify-center">
              {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
          </button>

          <!-- Notifications Dropdown -->
          <div
            v-if="showNotifications"
            v-click-outside="() => showNotifications = false"
            class="absolute right-0 z-10 mt-2 w-80 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-800 dark:ring-gray-700"
          >
            <div class="p-4">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Notificaciones</h3>
                <button
                  v-if="unreadCount > 0"
                  @click="markAllAsRead"
                  class="text-xs text-blue-600 hover:text-blue-500 dark:text-blue-400"
                >
                  Marcar todas como leídas
                </button>
              </div>
              
              <div class="space-y-2 max-h-64 overflow-y-auto">
                <div
                  v-for="notification in notifications"
                  :key="notification.id"
                  class="p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                  :class="{ 'bg-blue-50 dark:bg-blue-900/20': !notification.read }"
                  @click="handleNotificationClick(notification)"
                >
                  <div class="flex">
                    <div class="flex-shrink-0">
                      <component :is="getNotificationIcon(notification.type)" class="h-5 w-5 text-blue-500" />
                    </div>
                    <div class="ml-3 flex-1">
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
                </div>
              </div>
              
              <div v-if="notifications.length === 0" class="text-center py-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">No hay notificaciones</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Dark Mode Toggle -->
        <DarkToggle />

        <!-- Profile Dropdown -->
        <div class="relative">
          <button
            @click="showProfileMenu = !showProfileMenu"
            class="flex max-w-xs items-center rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-gray-800"
          >
            <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-center">
              <span class="text-xs font-medium text-white">
                {{ getInitials(user?.name) }}
              </span>
            </div>
          </button>

          <!-- Profile Menu Dropdown -->
          <div
            v-if="showProfileMenu"
            v-click-outside="() => showProfileMenu = false"
            class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-800 dark:ring-gray-700"
          >
            <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700">
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ user?.name }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ user?.role?.display_name }}</p>
            </div>
            
            <Link
              :href="route('profile.edit')"
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
            >
              <UserIcon class="inline-block w-4 h-4 mr-2" />
              Mi Perfil
            </Link>
            
            <Link
              :href="route('dashboard')"
              class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
            >
              <ChartBarIcon class="inline-block w-4 h-4 mr-2" />
              Dashboard
            </Link>
            
            <div class="border-t border-gray-200 dark:border-gray-700">
              <button
                @click="logout"
                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
              >
                <ArrowRightOnRectangleIcon class="inline-block w-4 h-4 mr-2" />
                Cerrar Sesión
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Search (shown when needed) -->
    <div v-if="showMobileSearch" class="border-t border-gray-200 px-4 py-2 sm:hidden dark:border-gray-700">
      <div class="relative">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
          <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
        </div>
        <input
          v-model="searchQuery"
          @keyup.enter="performSearch"
          type="text"
          placeholder="Buscar..."
          class="block w-full rounded-md border-0 bg-gray-50 py-1.5 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 dark:bg-gray-700 dark:text-white dark:ring-gray-600 sm:text-sm sm:leading-6"
        />
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import {
  Bars3Icon,
  MagnifyingGlassIcon,
  BellIcon,
  PlusIcon,
  UserIcon,
  ChartBarIcon,
  ArrowRightOnRectangleIcon,
  ClipboardDocumentListIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/outline'
import DarkToggle from '@/Components/DarkToggle.vue'

const props = defineProps({
  notifications: {
    type: Array,
    default: () => []
  }
})

defineEmits(['toggle-sidebar'])

const page = usePage()
const user = computed(() => page.props.auth?.user)

// Component state
const showNotifications = ref(false)
const showProfileMenu = ref(false)
const showMobileSearch = ref(false)
const searchQuery = ref('')

// Computed properties
const unreadCount = computed(() => {
  return props.notifications.filter(n => !n.read).length
})

// Methods
const getInitials = (name) => {
  if (!name) return 'U'
  return name.split(' ').map(word => word[0]).join('').toUpperCase().slice(0, 2)
}

const getNotificationIcon = (type) => {
  const icons = {
    success: CheckCircleIcon,
    warning: ExclamationTriangleIcon,
    info: InformationCircleIcon,
    default: ClipboardDocumentListIcon
  }
  return icons[type] || icons.default
}

const formatTime = (timestamp) => {
  return new Date(timestamp).toLocaleString('es-ES', {
    hour: '2-digit',
    minute: '2-digit',
    day: '2-digit',
    month: '2-digit'
  })
}

const performSearch = () => {
  if (searchQuery.value.trim()) {
    router.get('/search', { q: searchQuery.value })
  }
}

const handleNotificationClick = (notification) => {
  // Mark as read and navigate if has URL
  if (notification.url) {
    router.visit(notification.url)
  }
  showNotifications.value = false
}

const markAllAsRead = () => {
  // Implementation for marking all notifications as read
  console.log('Marking all as read...')
}

const logout = () => {
  if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
    router.post(route('logout'))
  }
}

// Click outside directive (you might need to implement this as a custom directive)
const clickOutside = {
  beforeMount: (el, binding) => {
    el.clickOutsideEvent = event => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value()
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted: el => {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
}
</script>