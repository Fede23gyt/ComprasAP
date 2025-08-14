<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
  CogIcon,
  DocumentTextIcon,
  CurrencyDollarIcon,
  ShoppingCartIcon,
  ChartBarIcon,
  BookOpenIcon,
  ArrowRightOnRectangleIcon,
  ChevronDownIcon,
  ChevronRightIcon,
  BuildingOfficeIcon,
  UserGroupIcon,
  TagIcon,
  ClipboardDocumentListIcon,
  EyeIcon,
  CheckCircleIcon,
  MagnifyingGlassIcon
} from '@heroicons/vue/24/outline';

const open = ref(false);
const expandedMenus = ref({});

// Obtener datos del usuario actual de la página
const page = usePage();
const user = computed(() => page.props.auth?.user);

function toggle() {
  open.value = !open.value;
}

function close() {
  open.value = false;
}

function toggleSubmenu(key) {
  expandedMenus.value[key] = !expandedMenus.value[key];
}

// Verificar si el usuario tiene rol de admin o supervisor
const canAccessConfig = computed(() => {
  if (!user.value?.role) return false;
  return user.value.role.canManageConfig || ['administrador', 'secretario', 'director'].includes(user.value.role.name);
});

const canAccessFullFeatures = computed(() => {
  if (!user.value?.role) return false;
  return ['administrador', 'secretario', 'director'].includes(user.value.role.name);
});

const menu = computed(() => [
  // Solo mostrar configuración a admins, secretarios y directores
  ...(canAccessConfig.value ? [{
    key: 'configuracion',
    categoria: 'Configuración',
    icon: CogIcon,
    isSubmenu: true,
    items: [
      { label: 'Usuarios', route: '/admin/usuarios', icon: UserGroupIcon },
      { label: 'Roles', route: '/admin/roles', icon: TagIcon },
      { label: 'Tipos de Compra', route: '/tipos-compra', icon: ShoppingCartIcon },
      { label: 'Tipos de Nota', route: '/tipos-nota', icon: DocumentTextIcon },
    ],
  }] : []),
  {
    key: 'notas-pedido',
    categoria: 'Nota de Pedido',
    icon: ClipboardDocumentListIcon,
    isSubmenu: true,
    items: [
      { label: 'Notas de Pedidos', route: '/notas-pedido', icon: DocumentTextIcon },
      { label: 'Consultas', route: '/notas-pedido/consultas', icon: MagnifyingGlassIcon },
      // Solo mostrar confirmar a usuarios que pueden autorizar
      ...(canAccessFullFeatures.value || user.value?.role?.name === 'operador' ? [
        { label: 'Confirmar Nota de Pedido', route: '/notas-pedido/confirmar', icon: CheckCircleIcon }
      ] : []),
    ],
  },
  // Solo mostrar presupuestos a roles superiores
  ...(canAccessFullFeatures.value ? [{
    key: 'presupuestos',
    categoria: 'Presupuestos',
    icon: CurrencyDollarIcon,
    route: '/presupuestos',
  }] : []),
  // Solo mostrar órdenes de compra a roles superiores
  ...(canAccessFullFeatures.value ? [{
    key: 'orden-compra',
    categoria: 'Orden de Compra',
    icon: ShoppingCartIcon,
    route: '/ordenes-compra',
  }] : []),
  {
    key: 'reportes',
    categoria: 'Reportes',
    icon: ChartBarIcon,
    route: '/reportes',
  },
  {
    key: 'nomencladores',
    categoria: 'Nomencladores',
    icon: BookOpenIcon,
    isSubmenu: true,
    items: [
      { label: 'Insumos', route: '/nomencladores/insumos', icon: TagIcon, readonly: true },
      { label: 'Tipos de Nota', route: '/nomencladores/tipos-nota', icon: DocumentTextIcon, readonly: true },
      { label: 'Tipos de Compra', route: '/nomencladores/tipos-compra', icon: ShoppingCartIcon, readonly: true },
    ],
  },
  {
    key: 'salir',
    categoria: 'Salir del Sistema',
    icon: ArrowRightOnRectangleIcon,
    action: 'logout',
    class: 'text-red-400 hover:text-red-300 hover:bg-red-900/20',
  },
]);<script setup>
  import { ref, computed } from 'vue';
  import { Link, usePage } from '@inertiajs/vue3';
  import {
  CogIcon,
  DocumentTextIcon,
  CurrencyDollarIcon,
  ShoppingCartIcon,
  ChartBarIcon,
  BookOpenIcon,
  ArrowRightOnRectangleIcon,
  ChevronDownIcon,
  ChevronRightIcon,
  BuildingOfficeIcon,
  UserGroupIcon,
  TagIcon,
  ClipboardDocumentListIcon,
  EyeIcon,
  CheckCircleIcon,
  MagnifyingGlassIcon
} from '@heroicons/vue/24/outline';

  const open = ref(false);
  const expandedMenus = ref({});

  // Obtener datos del usuario actual de la página
  const page = usePage();
  const user = computed(() => page.props.auth?.user);

  function toggle() {
  open.value = !open.value;
}

  function close() {
  open.value = false;
}

  function toggleSubmenu(key) {
  expandedMenus.value[key] = !expandedMenus.value[key];
}

  // Verificar si el usuario tiene rol de admin o supervisor
  const canAccessConfig = computed(() => {
  const userRoles = user.value?.roles || [];
  return userRoles.some(role => ['admin', 'supervisor', 'administrador'].includes(role.name?.toLowerCase()));
});

  const menu = computed(() => [
  // Solo mostrar configuración a admins y supervisores
  ...(canAccessConfig.value ? [{
  key: 'configuracion',
  categoria: 'Configuración',
  icon: CogIcon,
  isSubmenu: true,
  items: [
{ label: 'Usuarios', route: '/admin/usuarios', icon: UserGroupIcon },
{ label: 'Roles', route: '/admin/roles', icon: TagIcon },
{ label: 'Tipos de Compra', route: '/admin/tipos-compra', icon: ShoppingCartIcon },
{ label: 'Tipos de Nota', route: '/admin/tipos-nota', icon: DocumentTextIcon },
  ],
}] : []),
  {
    key: 'notas-pedido',
    categoria: 'Nota de Pedido',
    icon: ClipboardDocumentListIcon,
    isSubmenu: true,
    items: [
  { label: 'Notas de Pedidos', route: '/notas-pedido', icon: DocumentTextIcon },
  { label: 'Consultas', route: '/notas-pedido/consultas', icon: MagnifyingGlassIcon },
  { label: 'Confirmar Nota de Pedido', route: '/notas-pedido/confirmar', icon: CheckCircleIcon },
    ],
  },
  {
    key: 'presupuestos',
    categoria: 'Presupuestos',
    icon: CurrencyDollarIcon,
    route: '/presupuestos',
  },
  {
    key: 'orden-compra',
    categoria: 'Orden de Compra',
    icon: ShoppingCartIcon,
    route: '/ordenes-compra',
  },
  {
    key: 'reportes',
    categoria: 'Reportes',
    icon: ChartBarIcon,
    route: '/reportes',
  },
  {
    key: 'nomencladores',
    categoria: 'Nomencladores',
    icon: BookOpenIcon,
    isSubmenu: true,
    items: [
  { label: 'Insumos', route: '/nomencladores/insumos', icon: TagIcon, readonly: true },
  { label: 'Tipos de Nota', route: '/nomencladores/tipos-nota', icon: DocumentTextIcon, readonly: true },
  { label: 'Tipos de Compra', route: '/nomencladores/tipos-compra', icon: ShoppingCartIcon, readonly: true },
    ],
  },
  {
    key: 'salir',
    categoria: 'Salir del Sistema',
    icon: ArrowRightOnRectangleIcon,
    action: 'logout',
    class: 'text-red-400 hover:text-red-300 hover:bg-red-900/20',
  },
  ]);

  function handleMenuClick(item) {
  if (item.action === 'logout') {
  // Confirmar antes de cerrar sesión
  if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
  // Usar el método de logout de Laravel
  document.getElementById('logout-form').submit();
}
}
}

  function isActiveRoute(route) {
  if (!route) return false;
  return page.url.startsWith(route);
}

  function hasActiveChild(items) {
  if (!items) return false;
  return items.some(item => isActiveRoute(item.route));
}
</script>

<template>
  <!-- Botón hamburguesa (móvil) -->
  <button
    @click="toggle"
    class="fixed top-4 left-4 z-50 md:hidden bg-orange-500 text-white p-2 rounded-lg shadow-lg"
    aria-label="Abrir menú"
  >
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
  </button>

  <!-- Sidebar -->
  <aside
    :class="[
            open ? 'translate-x-0' : '-translate-x-full',
            'fixed inset-y-0 left-0 w-72 bg-gray-800 dark:bg-gray-900 text-white shadow-xl z-40 md:translate-x-0 md:relative md:shadow-none transition-transform duration-300 overflow-y-auto'
        ]"
  >
    <!-- Header del sidebar -->
    <div class="p-4 border-b border-gray-700">
      <div class="flex justify-between items-center md:justify-center">
        <!-- Logo -->
        <div class="flex items-center space-x-3">
          <img src="/images/logo.png" class="h-10 w-auto" alt="Logo" />
          <div class="hidden md:block">
            <h2 class="text-lg font-bold text-orange-400">Compras AP</h2>
            <p class="text-xs text-gray-400">Mun. Salta</p>
          </div>
        </div>

        <!-- Botón cerrar móvil -->
        <button @click="close" class="text-white md:hidden">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Información del usuario -->
    <div v-if="user" class="p-4 border-b border-gray-700 bg-gray-750">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center">
                    <span class="text-white font-semibold text-sm">
                        {{ user.name?.charAt(0).toUpperCase() }}
                    </span>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-white truncate">{{ user.name }}</p>
          <p class="text-xs text-gray-400 truncate">{{ user.email }}</p>
          <div class="flex items-center space-x-2 mt-1">
            <!-- Rol del usuario -->
            <span v-if="user.role" class="text-xs bg-orange-600 text-white px-2 py-0.5 rounded-full">
                            {{ user.role.display_name }}
                        </span>
            <!-- Oficina principal -->
            <span v-if="user.oficina_principal" class="text-xs bg-blue-600 text-white px-2 py-0.5 rounded-full">
                            {{ user.oficina_principal.abreviacion }}
                        </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Menú de navegación -->
    <nav class="flex-1 p-4 space-y-2">
      <div v-for="section in menu" :key="section.key">
        <!-- Elemento con submenu -->
        <div v-if="section.isSubmenu">
          <button
            @click="toggleSubmenu(section.key)"
            class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors group"
            :class="[
                            hasActiveChild(section.items)
                                ? 'bg-orange-600 text-white'
                                : 'text-gray-300 hover:bg-gray-700 hover:text-white',
                            section.class
                        ]"
          >
            <div class="flex items-center space-x-3">
              <component :is="section.icon" class="w-5 h-5" />
              <span>{{ section.categoria }}</span>
            </div>
            <ChevronDownIcon
              :class="[
                                'w-4 h-4 transition-transform',
                                expandedMenus[section.key] ? 'rotate-180' : ''
                            ]"
            />
          </button>

          <!-- Submenu items -->
          <div
            v-if="expandedMenus[section.key]"
            class="mt-1 ml-8 space-y-1 border-l-2 border-gray-600 pl-4"
          >
            <Link
              v-for="item in section.items"
              :key="item.route"
              :href="item.route"
              @click="close"
              class="flex items-center space-x-2 px-3 py-2 rounded-md text-sm transition-colors group"
              :class="[
                                isActiveRoute(item.route)
                                    ? 'bg-orange-500 text-white font-medium'
                                    : 'text-gray-400 hover:bg-gray-700 hover:text-white',
                                item.readonly ? 'opacity-75' : ''
                            ]"
            >
              <component :is="item.icon" class="w-4 h-4" />
              <span>{{ item.label }}</span>
              <EyeIcon v-if="item.readonly" class="w-3 h-3 ml-auto text-gray-500" title="Solo consulta" />
            </Link>
          </div>
        </div>

        <!-- Elemento simple -->
        <div v-else>
          <Link
            v-if="section.route"
            :href="section.route"
            @click="close"
            class="flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors group"
            :class="[
                            isActiveRoute(section.route)
                                ? 'bg-orange-600 text-white'
                                : 'text-gray-300 hover:bg-gray-700 hover:text-white',
                            section.class
                        ]"
          >
            <component :is="section.icon" class="w-5 h-5" />
            <span>{{ section.categoria }}</span>
          </Link>

          <button
            v-else
            @click="handleMenuClick(section)"
            class="w-full flex items-center space-x-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors group"
            :class="[
                            'text-gray-300 hover:bg-gray-700 hover:text-white',
                            section.class
                        ]"
          >
            <component :is="section.icon" class="w-5 h-5" />
            <span>{{ section.categoria }}</span>
          </button>
        </div>
      </div>
    </nav>

    <!-- Footer del sidebar -->
    <div class="p-4 border-t border-gray-700">
      <div class="text-xs text-gray-500 text-center">
        <p>Compras y Contrataciones</p>
        <p>Municipalidad de Salta</p>
        <p class="mt-1">v1.0.0</p>
      </div>
    </div>
  </aside>

  <!-- Overlay para cerrar al tocar fuera (solo móvil) -->
  <div
    v-if="open"
    @click="close"
    class="fixed inset-0 bg-black/50 z-30 md:hidden backdrop-blur-sm"
  ></div>

  <!-- Formulario oculto para logout -->
  <form id="logout-form" action="/logout" method="POST" style="display: none;">
    <input type="hidden" name="_token" :value="$page.props.csrf_token">
  </form>
</template>
