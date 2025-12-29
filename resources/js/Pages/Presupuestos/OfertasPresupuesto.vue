<template>
  <AppLayout>
    <Head :title="title" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Cabecera del Presupuesto -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6">
            <div class="flex justify-between items-start mb-4">
              <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                  {{ title }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                  Tipo: {{ presupuesto.tipo_compra?.nombre || 'N/A' }}
                </p>
              </div>

              <Link
                :href="route('presupuestos.index')"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition"
              >
                Volver a Presupuestos
              </Link>
            </div>

            <!-- Datos del Presupuesto -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Monto Presupuestado</h3>
                <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                  ${{ formatearMoneda(estadisticas.monto_presupuestado) }}
                </p>
              </div>

              <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Total Ofertas</h3>
                <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                  {{ estadisticas.total_ofertas }}
                </p>
              </div>

              <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Oferta Más Baja</h3>
                <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">
                  {{ estadisticas.monto_menor_oferta ? '$' + formatearMoneda(estadisticas.monto_menor_oferta) : 'N/A' }}
                </p>
              </div>

              <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-purple-800 dark:text-purple-200">Oferta Más Alta</h3>
                <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">
                  {{ estadisticas.monto_mayor_oferta ? '$' + formatearMoneda(estadisticas.monto_mayor_oferta) : 'N/A' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Lista de Ofertas -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                Ofertas Recibidas
              </h3>

              <Link
                v-if="presupuesto.ofertas && presupuesto.ofertas.length > 0"
                :href="route('presupuestos.evaluar-ofertas', presupuesto.id)"
                class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 transition"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Comparar Ofertas
              </Link>
            </div>

            <div v-if="!presupuesto.ofertas || presupuesto.ofertas.length === 0" class="text-center py-12">
              <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <p class="text-gray-500 dark:text-gray-400">No hay ofertas para este presupuesto</p>
              <Link
                :href="route('ofertas.create')"
                class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition"
              >
                Cargar Nueva Oferta
              </Link>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="oferta in presupuesto.ofertas"
                :key="oferta.id"
                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition"
              >
                <div class="flex justify-between items-start">
                  <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                      <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ oferta.proveedor?.razon_social || 'Proveedor no especificado' }}
                      </h4>
                      <span
                        class="px-2 py-1 text-xs font-semibold rounded-full"
                        :class="{
                          'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300': oferta.estado === 'pendiente',
                          'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': oferta.estado === 'evaluando',
                          'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': oferta.estado === 'aprobada',
                          'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': oferta.estado === 'rechazada',
                          'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300': oferta.estado === 'adjudicada'
                        }"
                      >
                        {{ getEstadoTexto(oferta.estado) }}
                      </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                      <div>
                        <span class="text-gray-600 dark:text-gray-400">Número:</span>
                        <span class="font-medium text-gray-900 dark:text-gray-100 ml-2">
                          {{ oferta.numero_oferta }}
                        </span>
                      </div>
                      <div>
                        <span class="text-gray-600 dark:text-gray-400">Fecha:</span>
                        <span class="font-medium text-gray-900 dark:text-gray-100 ml-2">
                          {{ formatearFecha(oferta.fecha_oferta) }}
                        </span>
                      </div>
                      <div>
                        <span class="text-gray-600 dark:text-gray-400">CUIT:</span>
                        <span class="font-medium text-gray-900 dark:text-gray-100 ml-2">
                          {{ oferta.proveedor?.cuit || 'N/A' }}
                        </span>
                      </div>
                      <div>
                        <span class="text-gray-600 dark:text-gray-400">Plazo Entrega:</span>
                        <span class="font-medium text-gray-900 dark:text-gray-100 ml-2">
                          {{ oferta.plazo_entrega ? oferta.plazo_entrega + ' días' : 'N/A' }}
                        </span>
                      </div>
                      <div>
                        <span class="text-gray-600 dark:text-gray-400">Forma Pago:</span>
                        <span class="font-medium text-gray-900 dark:text-gray-100 ml-2">
                          {{ oferta.forma_pago || 'N/A' }}
                        </span>
                      </div>
                      <div>
                        <span class="text-gray-600 dark:text-gray-400">Renglones:</span>
                        <span class="font-medium text-gray-900 dark:text-gray-100 ml-2">
                          {{ oferta.detalles?.length || 0 }}
                        </span>
                      </div>
                    </div>

                    <div v-if="oferta.observaciones" class="mt-2 text-sm">
                      <span class="text-gray-600 dark:text-gray-400">Observaciones:</span>
                      <p class="text-gray-900 dark:text-gray-100 mt-1">{{ oferta.observaciones }}</p>
                    </div>
                  </div>

                  <div class="ml-4 text-right">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                      ${{ formatearMoneda(oferta.monto_total) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Monto Total</div>

                    <div class="mt-4 flex flex-col gap-2">
                      <Link
                        :href="route('ofertas.show', oferta.id)"
                        class="inline-flex items-center justify-center px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-md hover:bg-blue-700 transition"
                      >
                        Ver Detalle
                      </Link>
                    </div>
                  </div>
                </div>
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

const props = defineProps({
  title: String,
  presupuesto: Object,
  estadisticas: Object
})

const formatearMoneda = (monto) => {
  if (!monto) return '0.00'
  return parseFloat(monto).toLocaleString('es-AR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

const formatearFecha = (fecha) => {
  if (!fecha) return 'N/A'
  return new Date(fecha).toLocaleDateString('es-AR')
}

const getEstadoTexto = (estado) => {
  const estados = {
    'pendiente': 'Pendiente',
    'evaluando': 'En Evaluación',
    'aprobada': 'Aprobada',
    'rechazada': 'Rechazada',
    'adjudicada': 'Adjudicada'
  }
  return estados[estado] || estado
}
</script>
