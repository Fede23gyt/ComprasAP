<template>
  <!-- Sidebar -->
  <aside
    :class="[
      isOpen ? 'translate-x-0' : '-translate-x-full',
      'fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-gray-800 shadow-xl lg:translate-x-0 transition-transform duration-300 ease-in-out'
    ]"
  >
    <!-- Logo & Header -->
    <div class="flex h-16 items-center justify-between px-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center space-x-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-blue-600 to-blue-700">
          <BuildingStorefrontIcon class="h-6 w-6 text-white" />
        </div>
        <div>
          <h1 class="text-lg font-bold text-gray-900 dark:text-white">ComprasAP</h1>
          <p class="text-xs text-gray-500 dark:text-gray-400">Mun. Salta</p>
        </div>
      </div>
      <button
        @click="$emit('close')"
        class="lg:hidden rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-700"
      >
        <XMarkIcon class="h-5 w-5" />
      </button>
    </div>

    <!-- User Info -->
    <div v-if="user" class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-750">
      <div class="flex items-center space-x-3">
        <div class="relative">
          <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-center">
            <span class="text-sm font-medium text-white">
              {{ getInitials(user.name) }}
            </span>
          </div>
          <div class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full bg-green-400 border-2 border-white dark:border-gray-800"></div>
        </div>
        <div class="min-w-0 flex-1">
          <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
          <div class="flex items-center space-x-2 mt-1">
            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="getRoleBadgeClasses(user.role?.name)">
              {{ user.role?.display_name }}
            </span>
            <span v-if="user.oficina_principal" 
                  class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
              {{ user.oficina_principal.abreviacion }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-1">
      <!-- Dashboard -->
      <div class="mb-6">
        <Link :href="route('dashboard')" 
              class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium transition-colors duration-200"
              :class="isCurrentRoute('dashboard') 
                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' 
                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white'">
          <ChartBarIcon class="mr-3 h-5 w-5" />
          Dashboard
        </Link>
      </div>

      <!-- Menu Sections -->
      <div v-for="section in menuSections" :key="section.title" class="mb-6">
        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">
          {{ section.title }}
        </h3>
        <div class="mt-2 space-y-1">
          <template v-for="item in section.items" :key="item.key">
            <!-- Menu item with submenu -->
            <div v-if="item.submenu">
              <button
                @click="toggleSubmenu(item.key)"
                class="group flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm font-medium transition-colors duration-200"
                :class="hasActiveChild(item.submenu) || expandedMenus[item.key]
                  ? 'bg-gray-50 text-gray-900 dark:bg-gray-700 dark:text-white'
                  : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white'"
              >
                <div class="flex items-center">
                  <component :is="item.icon" class="mr-3 h-5 w-5" />
                  {{ item.name }}
                </div>
                <ChevronRightIcon
                  :class="[
                    'h-4 w-4 transition-transform duration-200',
                    expandedMenus[item.key] ? 'rotate-90' : ''
                  ]"
                />
              </button>
              
              <!-- Submenu -->
              <div v-if="expandedMenus[item.key]" class="mt-1 space-y-1">
                <Link
                  v-for="subItem in item.submenu"
                  :key="subItem.route"
                  :href="subItem.route"
                  class="group flex items-center rounded-lg py-2 pl-11 pr-3 text-sm font-medium transition-colors duration-200"
                  :class="isCurrentRoute(subItem.route)
                    ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300'
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white'"
                >
                  <component :is="subItem.icon" class="mr-2 h-4 w-4" />
                  {{ subItem.name }}
                  <span v-if="subItem.badge" 
                        class="ml-auto inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-200">
                    {{ subItem.badge }}
                  </span>
                </Link>
              </div>
            </div>

            <!-- Simple menu item -->
            <Link
              v-else
              :href="item.route"
              class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium transition-colors duration-200"
              :class="isCurrentRoute(item.route)
                ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300'
                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white'"
            >
              <component :is="item.icon" class="mr-3 h-5 w-5" />
              {{ item.name }}
              <span v-if="item.badge" 
                    class="ml-auto inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-200">
                {{ item.badge }}
              </span>
            </Link>
          </template>
        </div>
      </div>

      <!-- Logout -->
      <div class="pt-4 mt-6 border-t border-gray-200 dark:border-gray-700">
        <button
          @click="logout"
          class="group flex w-full items-center rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition-colors duration-200 hover:bg-red-50 hover:text-red-600 dark:text-gray-300 dark:hover:bg-red-900/20 dark:hover:text-red-400"
        >
          <ArrowRightOnRectangleIcon class="mr-3 h-5 w-5" />
          Cerrar Sesión
        </button>
      </div>
    </nav>
  </aside>

  <!-- Mobile overlay -->
  <div
    v-if="isOpen"
    @click="$emit('close')"
    class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
  />
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import {
  BuildingStorefrontIcon,
  XMarkIcon,
  ChartBarIcon,
  ChevronRightIcon,
  ArrowRightOnRectangleIcon,
  // Menu Icons
  CogIcon,
  UserGroupIcon,
  ShieldCheckIcon,
  DocumentTextIcon,
  ClipboardDocumentListIcon,
  MagnifyingGlassIcon,
  CheckCircleIcon,
  CurrencyDollarIcon,
  ShoppingCartIcon,
  PresentationChartLineIcon,
  BookOpenIcon,
  TagIcon,
  BuildingOfficeIcon,
  // New icons
  DocumentIcon,
  DocumentDuplicateIcon,
  ClipboardDocumentCheckIcon,
  BanknotesIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  isOpen: Boolean
})

defineEmits(['close', 'toggle'])

const page = usePage()
const expandedMenus = ref({})

const user = computed(() => page.props.auth?.user)

// Menu configuration based on user role
const menuSections = computed(() => {
  const sections = []
  
  // Admin Section - Only for administrators and supervisors
  if (canAccessAdmin.value) {
    sections.push({
      title: 'Administración',
      items: [
        {
          key: 'admin',
          name: 'Panel Administrativo',
          icon: CogIcon,
          submenu: [
            { name: 'Usuarios', route: route('admin.users.index'), icon: UserGroupIcon },
            { name: 'Roles & Permisos', route: route('admin.roles.index'), icon: ShieldCheckIcon },
            { name: 'Reportes Admin', route: route('admin.reports.index'), icon: PresentationChartLineIcon },
            { name: 'Oficinas', route: route('oficinas.index'), icon: BuildingOfficeIcon },
          ]
        }
      ]
    })
  }
  
  // Operations Section
  sections.push({
    title: 'Operaciones',
    items: [
      {
        key: 'notas-pedido',
        name: 'Notas de Pedido',
        icon: ClipboardDocumentListIcon,
        submenu: [
          { name: 'Mis Notas', route: route('notas-pedido.mis-notas'), icon: DocumentTextIcon },
          { name: 'Todas las Notas', route: route('notas-pedido.index'), icon: MagnifyingGlassIcon },
          ...(canAuthorize.value ? [
            { name: 'Por Confirmar', route: route('notas-pedido.por-confirmar'), icon: CheckCircleIcon, badge: 5 }
          ] : [])
        ]
      }
    ]
  })
  
  // Management Section - For supervisors
  if (canAccessManagement.value) {
    sections.push({
      title: 'Gestión',
      items: [
        {
          key: 'presupuestos',
          name: 'Presupuestos',
          icon: BanknotesIcon,
          submenu: [
            { name: 'Todos los Presupuestos', route: route('presupuestos.index'), icon: BanknotesIcon },
            { name: 'Crear Presupuesto', route: route('presupuestos.create'), icon: BanknotesIcon }
          ]
        },
        {
          key: 'ofertas',
          name: 'Ofertas',
          icon: DocumentDuplicateIcon,
          submenu: [
            { name: 'Todas las Ofertas', route: route('ofertas.index'), icon: DocumentDuplicateIcon },
            { name: 'Crear Oferta', route: route('ofertas.create'), icon: DocumentDuplicateIcon }
          ]
        },
        {
          key: 'ordenes-compra',
          name: 'Órdenes de Compra',
          icon: ShoppingCartIcon,
          submenu: [
            { name: 'Todas las Órdenes', route: route('ordenes-compra.index'), icon: ShoppingCartIcon },
            { name: 'Crear Orden', route: route('ordenes-compra.create'), icon: ShoppingCartIcon }
          ]
        },
        {
          key: 'proveedores',
          name: 'Proveedores',
          icon: UserGroupIcon,
          submenu: [
            { name: 'Todos los Proveedores', route: route('nomencladores.proveedores.index'), icon: UserGroupIcon },
            { name: 'Nuevo Proveedor', route: route('nomencladores.proveedores.create'), icon: UserGroupIcon }
          ]
        }
      ]
    })
  }

  // Evaluation & Adjudication Section - For supervisors
  if (canAccessManagement.value) {
    sections.push({
      title: 'Evaluación y Adjudicación',
      items: [
        {
          key: 'recepcion-ofertas',
          name: 'Recepción de Ofertas',
          icon: DocumentDuplicateIcon,
          submenu: [
            { name: 'Ofertas Recibidas', route: route('ofertas.index'), icon: DocumentDuplicateIcon },
            { name: 'Presupuestos Abiertos', route: route('presupuestos.index'), icon: BanknotesIcon },
            { name: 'Nueva Oferta', route: route('ofertas.create'), icon: DocumentDuplicateIcon }
          ]
        },
        {
          key: 'evaluacion',
          name: 'Evaluación de Ofertas',
          icon: ClipboardDocumentCheckIcon,
          submenu: [
            { name: 'Evaluar Ofertas', route: route('presupuestos.index'), icon: ClipboardDocumentCheckIcon },
            { name: 'Ofertas Evaluadas', route: route('ofertas.index'), icon: CheckCircleIcon },
            { name: 'Comparar Precios', route: route('ofertas.index'), icon: MagnifyingGlassIcon }
          ]
        },
        {
          key: 'adjudicacion',
          name: 'Adjudicación',
          icon: CheckCircleIcon,
          submenu: [
            { name: 'Ofertas Seleccionadas', route: route('ofertas.index'), icon: CheckCircleIcon },
            { name: 'Generar Órdenes', route: route('ordenes-compra.index'), icon: ShoppingCartIcon },
            { name: 'Órdenes Generadas', route: route('ordenes-compra.index'), icon: ShoppingCartIcon }
          ]
        }
      ]
    })
  }

  // Reports Section - Accessible to all authenticated users
  sections.push({
    title: 'Reportes',
    items: [
      {
        key: 'reportes',
        name: 'Reportes',
        icon: PresentationChartLineIcon,
        submenu: [
          { name: 'Dashboard', route: route('reportes.index'), icon: PresentationChartLineIcon },
          ...(canAccessManagement.value ? [
            { name: 'Notas por Oficina', route: route('reportes.notas-por-oficina'), icon: BuildingOfficeIcon },
            { name: 'Proveedores y Ofertas', route: route('reportes.proveedores-ofertas'), icon: UserGroupIcon },
            { name: 'Órdenes por Estado', route: route('reportes.ordenes-por-estado'), icon: ShoppingCartIcon },
            { name: 'Insumos Solicitados', route: route('reportes.insumos-solicitados'), icon: TagIcon }
          ] : [])
        ]
      }
    ]
  })
  
  // Catalog Section
  if (canAccessManagement.value) {
    sections.push({
      title: 'Catálogo',
      items: [
        {
          key: 'nomencladores',
          name: 'Nomencladores',
          icon: BookOpenIcon,
          submenu: [
            { name: 'Insumos', route: route('nomencladores.insumos.index'), icon: TagIcon },
            { name: 'Proveedores', route: route('nomencladores.proveedores.index'), icon: UserGroupIcon },
            { name: 'Tipos de Nota', route: route('nomencladores.tipos-nota.index'), icon: DocumentTextIcon },
            { name: 'Tipos de Compra', route: route('nomencladores.tipos-compra.index'), icon: ShoppingCartIcon },
            { name: 'Memos', route: route('nomencladores.memos.index'), icon: DocumentIcon }
          ]
        }
      ]
    })
  }
  
  return sections
})

// Permission checks
const canAccessAdmin = computed(() => {
  if (!user.value?.role) return false
  return ['administrador', 'secretario', 'director'].includes(user.value.role.name)
})

const canAccessManagement = computed(() => {
  if (!user.value?.role) return false
  return ['administrador', 'secretario', 'director'].includes(user.value.role.name)
})

const canAuthorize = computed(() => {
  if (!user.value?.role) return false
  return ['administrador', 'secretario', 'director', 'operador'].includes(user.value.role.name)
})

const getInitials = (name) => {
  if (!name) return 'U'
  return name.split(' ').map(word => word[0]).join('').toUpperCase().slice(0, 2)
}

const getRoleBadgeClasses = (roleName) => {
  const classes = {
    administrador: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    secretario: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
    director: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    operador: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    invitado: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
  }
  return classes[roleName] || 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
}

const isCurrentRoute = (routeOrPath) => {
  if (!routeOrPath) return false
  if (typeof routeOrPath === 'string') {
    return page.url.startsWith(routeOrPath)
  }
  return route().current(routeOrPath)
}

const hasActiveChild = (submenu) => {
  if (!submenu) return false
  return submenu.some(item => isCurrentRoute(item.route))
}

const toggleSubmenu = (key) => {
  expandedMenus.value[key] = !expandedMenus.value[key]
}

const logout = () => {
  if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
    router.post(route('logout'))
  }
}
</script>