<template>
  <AppLayout>
    <Head title="Sistema de Reportes" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
              <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                Sistema de Reportes
              </h2>
              <p class="text-gray-600 dark:text-gray-400 mt-2">
                Acceda a los diferentes reportes del sistema según sus permisos
              </p>
            </div>

            <!-- Estadísticas Generales -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
              <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-6">
                <div class="flex items-center">
                  <div class="flex-1">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300 uppercase tracking-wide">
                      Notas de Pedido
                    </h3>
                    <div class="mt-2">
                      <div class="text-2xl font-bold text-blue-900 dark:text-blue-200">
                        {{ estadisticas.notas_pedido.total }}
                      </div>
                      <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                        {{ estadisticas.notas_pedido.pendientes }} pendientes,
                        {{ estadisticas.notas_pedido.aprobadas }} aprobadas
                      </div>
                    </div>
                  </div>
                  <div class="ml-4 text-blue-400">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </div>
              </div>

              <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-6">
                <div class="flex items-center">
                  <div class="flex-1">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-300 uppercase tracking-wide">
                      Presupuestos
                    </h3>
                    <div class="mt-2">
                      <div class="text-2xl font-bold text-green-900 dark:text-green-200">
                        {{ estadisticas.presupuestos.total }}
                      </div>
                      <div class="text-xs text-green-600 dark:text-green-400 mt-1">
                        {{ estadisticas.presupuestos.aprobados }} aprobados,
                        {{ estadisticas.presupuestos.vencidos }} vencidos
                      </div>
                    </div>
                  </div>
                  <div class="ml-4 text-green-400">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                      <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                </div>
              </div>

              <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-lg p-6">
                <div class="flex items-center">
                  <div class="flex-1">
                    <h3 class="text-sm font-medium text-purple-800 dark:text-purple-300 uppercase tracking-wide">
                      Ofertas
                    </h3>
                    <div class="mt-2">
                      <div class="text-2xl font-bold text-purple-900 dark:text-purple-200">
                        {{ estadisticas.ofertas.total }}
                      </div>
                      <div class="text-xs text-purple-600 dark:text-purple-400 mt-1">
                        {{ estadisticas.ofertas.evaluadas }} evaluadas,
                        {{ estadisticas.ofertas.seleccionadas }} seleccionadas
                      </div>
                    </div>
                  </div>
                  <div class="ml-4 text-purple-400">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                </div>
              </div>

              <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg p-6">
                <div class="flex items-center">
                  <div class="flex-1">
                    <h3 class="text-sm font-medium text-orange-800 dark:text-orange-300 uppercase tracking-wide">
                      Órdenes de Compra
                    </h3>
                    <div class="mt-2">
                      <div class="text-2xl font-bold text-orange-900 dark:text-orange-200">
                        {{ estadisticas.ordenes_compra.total }}
                      </div>
                      <div class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                        {{ estadisticas.ordenes_compra.aprobadas }} aprobadas,
                        {{ estadisticas.ordenes_compra.completadas }} completadas
                      </div>
                    </div>
                  </div>
                  <div class="ml-4 text-orange-400">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                </div>
              </div>
            </div>

            <!-- Gráfico de Tendencias -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-8">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                Tendencias Mensuales (Últimos 6 Meses)
              </h3>
              <div class="overflow-x-auto">
                <table class="min-w-full">
                  <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-600">
                      <th class="px-4 py-2 text-left text-sm font-medium text-gray-600 dark:text-gray-300">Mes</th>
                      <th class="px-4 py-2 text-center text-sm font-medium text-gray-600 dark:text-gray-300">Notas de Pedido</th>
                      <th class="px-4 py-2 text-center text-sm font-medium text-gray-600 dark:text-gray-300">Presupuestos</th>
                      <th class="px-4 py-2 text-center text-sm font-medium text-gray-600 dark:text-gray-300">Órdenes de Compra</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="mes in meses" :key="mes.mes" class="border-b border-gray-100 dark:border-gray-600">
                      <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ mes.nombre }}</td>
                      <td class="px-4 py-3 text-sm text-center text-gray-600 dark:text-gray-300">{{ mes.notas_pedido }}</td>
                      <td class="px-4 py-3 text-sm text-center text-gray-600 dark:text-gray-300">{{ mes.presupuestos }}</td>
                      <td class="px-4 py-3 text-sm text-center text-gray-600 dark:text-gray-300">{{ mes.ordenes_compra }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Reportes Disponibles -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                  Reportes Básicos
                </h3>
                <div class="space-y-3">
                  <div class="text-sm text-gray-600 dark:text-gray-400">
                    Reportes disponibles para su rol y oficinas:
                  </div>

                  <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded p-3">
                    <div class="text-sm font-medium text-blue-900 dark:text-blue-100">
                      Mis Estadísticas Personales
                    </div>
                    <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                      Vista resumida de su actividad en el sistema
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="user && (user.roles.some(r => ['administrador', 'secretario', 'director'].includes(r.name)))"
                   class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                  Reportes Avanzados
                </h3>
                <div class="space-y-3">
                  <Link :href="route('reportes.notas-por-oficina')"
                        class="block bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded p-3 hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                    <div class="text-sm font-medium text-green-900 dark:text-green-100">
                      Notas por Oficina
                    </div>
                    <div class="text-xs text-green-600 dark:text-green-400 mt-1">
                      Análisis de notas de pedido agrupadas por oficina
                    </div>
                  </Link>

                  <Link :href="route('reportes.proveedores-ofertas')"
                        class="block bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded p-3 hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                    <div class="text-sm font-medium text-purple-900 dark:text-purple-100">
                      Proveedores y Ofertas
                    </div>
                    <div class="text-xs text-purple-600 dark:text-purple-400 mt-1">
                      Estadísticas de proveedores y sus ofertas
                    </div>
                  </Link>

                  <Link :href="route('reportes.ordenes-por-estado')"
                        class="block bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded p-3 hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors">
                    <div class="text-sm font-medium text-orange-900 dark:text-orange-100">
                      Órdenes por Estado
                    </div>
                    <div class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                      Análisis de órdenes de compra por su estado actual
                    </div>
                  </Link>

                  <Link :href="route('reportes.insumos-solicitados')"
                        class="block bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-700 rounded p-3 hover:bg-teal-100 dark:hover:bg-teal-900/30 transition-colors">
                    <div class="text-sm font-medium text-teal-900 dark:text-teal-100">
                      Insumos Más Solicitados
                    </div>
                    <div class="text-xs text-teal-600 dark:text-teal-400 mt-1">
                      Ranking de insumos por frecuencia de solicitud
                    </div>
                  </Link>
                </div>
              </div>
            </div>

            <!-- Información Adicional -->
            <div class="mt-8 bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
              <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                Información sobre Reportes
              </h4>
              <div class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                <p>• Los datos se actualizan en tiempo real</p>
                <p>• Los reportes avanzados incluyen opciones de filtrado por fecha y oficina</p>
                <p>• Algunos reportes permiten exportación a CSV para análisis externo</p>
                <p v-if="!user?.isSupervisor">• Sus reportes están limitados a las oficinas asignadas a su usuario</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

defineProps({
  estadisticas: Object,
  meses: Array,
  user: Object
})
</script>