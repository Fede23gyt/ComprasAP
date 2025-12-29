<template>
  <AppLayout>
    <Head :title="title" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
              <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                  {{ title }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                  Oficina: {{ notaPedido.oficina?.nombre }} - 
                  Descripción: {{ notaPedido.descripcion }}
                </p>
              </div>
              
              <div class="flex space-x-2">
                <Link
                  :href="route('presupuestos.por-nota-pedido', notaPedido.id)"
                  class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Volver a Presupuestos
                </Link>
                
                <Link
                  :href="route('notas-pedido.show', notaPedido.id)"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Ver Nota Completa
                </Link>
              </div>
            </div>

            <!-- Información de la nota de pedido -->
            <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                  <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Número:</p>
                  <p class="text-blue-700 dark:text-blue-300">{{ notaPedido.numero_nota }}/{{ notaPedido.ejercicio_nota }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Expediente:</p>
                  <p class="text-blue-700 dark:text-blue-300">{{ notaPedido.expediente || 'Sin expediente' }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Total Estimado:</p>
                  <p class="text-blue-700 dark:text-blue-300">${{ formatNumber(notaPedido.total_estimado) }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Presupuestos:</p>
                  <p class="text-blue-700 dark:text-blue-300">{{ comparacion.length }}</p>
                </div>
              </div>
            </div>

            <!-- Resumen de presupuestos -->
            <div class="mb-6">
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Resumen de Presupuestos
              </h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div v-for="(item, index) in comparacion" :key="index" 
                     class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                  <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ item.proveedor.razon_social }}
                  </h4>
                  <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    ${{ formatNumber(item.total) }}
                  </p>
                  <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ getEstadoTexto(item.presupuesto.estado) }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                    {{ formatDate(item.presupuesto.fecha_presupuesto) }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Tabla comparativa -->
            <div class="mb-6">
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Comparativa por Renglón
              </h3>
              
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Renglón
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Insumo
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Cantidad
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Unidad
                      </th>
                      <th v-for="(item, index) in comparacion" :key="index" 
                          class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        {{ item.proveedor.razon_social }}
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Mejor Oferta
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="renglon in renglones" :key="renglon">
                      <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ renglon }}
                      </td>
                      <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                        {{ getInsumoDescripcion(renglon) }}
                      </td>
                      <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                        {{ formatNumber(getCantidad(renglon)) }}
                      </td>
                      <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                        {{ getUnidadMedida(renglon) }}
                      </td>
                      
                      <td v-for="(item, index) in comparacion" :key="index" 
                          class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                        <div v-if="getDetalle(item, renglon)">
                          <div class="font-semibold">
                            ${{ formatNumber(getDetalle(item, renglon).precio_unitario) }}
                          </div>
                          <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ getDetalle(item, renglon).marca }}
                          </div>
                          <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ getDetalle(item, renglon).modelo }}
                          </div>
                        </div>
                        <span v-else class="text-gray-400">-</span>
                      </td>
                      
                      <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                        <span v-if="getMejorOferta(renglon)" 
                              :class="getMejorOfertaClass(getMejorOferta(renglon).index)">
                          {{ getMejorOferta(renglon).proveedor }}
                          <br>
                          <span class="text-xs">
                            ${{ formatNumber(getMejorOferta(renglon).precio) }}
                          </span>
                        </span>
                        <span v-else class="text-gray-400">-</span>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                      <td colspan="4" class="px-4 py-2 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                        Total General:
                      </td>
                      <td v-for="(item, index) in comparacion" :key="index" 
                          class="px-4 py-2 text-sm font-bold text-gray-900 dark:text-gray-100">
                        ${{ formatNumber(item.total) }}
                      </td>
                      <td class="px-4 py-2 text-sm font-bold text-green-600 dark:text-green-400">
                        ${{ formatNumber(getMejorTotal()) }}
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>

            <!-- Análisis comparativo -->
            <div class="mb-6">
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Análisis Comparativo
              </h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div v-for="(item, index) in comparacion" :key="index" 
                     class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                  <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                    {{ item.proveedor.razon_social }}
                  </h4>
                  
                  <div class="space-y-1 text-sm">
                    <p><span class="font-medium">Total:</span> ${{ formatNumber(item.total) }}</p>
                    <p><span class="font-medium">Estado:</span> {{ getEstadoTexto(item.presupuesto.estado) }}</p>
                    <p><span class="font-medium">Fecha:</span> {{ formatDate(item.presupuesto.fecha_presupuesto) }}</p>
                    
                    <div v-if="item.presupuesto.plazo_entrega" class="mt-2">
                      <p class="font-medium">Condiciones:</p>
                      <p class="text-xs">Entrega: {{ item.presupuesto.plazo_entrega }} días</p>
                      <p v-if="item.presupuesto.forma_pago" class="text-xs">
                        Pago: {{ item.presupuesto.forma_pago }}
                      </p>
                      <p v-if="item.presupuesto.validez_oferta" class="text-xs">
                        Validez: {{ item.presupuesto.validez_oferta }} días
                      </p>
                    </div>
                  </div>
                  
                  <div class="mt-3">
                    <Link
                      :href="route('presupuestos.show', item.presupuesto.id)"
                      class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm"
                    >
                      Ver detalle completo
                    </Link>
                  </div>
                </div>
              </div>
            </div>

            <!-- Recomendación -->
            <div v-if="comparacion.length > 1" class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
              <h3 class="text-lg font-medium text-green-800 dark:text-green-200 mb-2">
                Recomendación
              </h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <p class="text-sm text-green-700 dark:text-green-300">
                    <span class="font-medium">Mejor oferta económica:</span> 
                    {{ getMejorOfertaEconomica().proveedor }}
                  </p>
                  <p class="text-green-800 dark:text-green-200 font-semibold">
                    ${{ formatNumber(getMejorOfertaEconomica().total) }}
                  </p>
                  <p class="text-xs text-green-600 dark:text-green-400">
                    Ahorro: ${{ formatNumber(getAhorro()) }} 
                    ({{ getPorcentajeAhorro() }}%)
                  </p>
                </div>
                
                <div>
                  <p class="text-sm text-green-700 dark:text-green-300">
                    <span class="font-medium">Condiciones recomendadas:</span>
                  </p>
                  <p class="text-xs text-green-600 dark:text-green-400">
                    Verifique las condiciones de entrega, pago y garantías antes de adjudicar.
                  </p>
                </div>
              </div>
            </div>

            <!-- Sin datos para comparar -->
            <div v-else class="text-center py-12">
              <div class="text-gray-400 dark:text-gray-500">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                  No hay suficientes datos para comparar
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                  Se necesitan al menos 2 presupuestos en estado "Enviado" o "Aprobado" para realizar la comparación.
                </p>
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
import { computed } from 'vue'

const props = defineProps({
  title: String,
  comparacion: Array,
  notaPedido: Object
})

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('es-AR')
}

const formatNumber = (number) => {
  if (!number) return '0.00'
  return new Intl.NumberFormat('es-AR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(number)
}

const getEstadoTexto = (estado) => {
  const estados = {
    borrador: 'Borrador',
    enviado: 'Enviado',
    aprobado: 'Aprobado',
    rechazado: 'Rechazado'
  }
  return estados[estado] || estado
}

// Obtener todos los renglones únicos
const renglones = computed(() => {
  const renglonesSet = new Set()
  props.comparacion.forEach(item => {
    Object.keys(item.detalles).forEach(renglon => {
      renglonesSet.add(parseInt(renglon))
    })
  })
  return Array.from(renglonesSet).sort((a, b) => a - b)
})

const getInsumoDescripcion = (renglon) => {
  for (const item of props.comparacion) {
    const detalle = item.detalles[renglon]?.[0]
    if (detalle) {
      return detalle.insumo?.descripcion || 'Insumo no especificado'
    }
  }
  return 'Insumo no encontrado'
}

const getCantidad = (renglon) => {
  for (const item of props.comparacion) {
    const detalle = item.detalles[renglon]?.[0]
    if (detalle) {
      return detalle.cantidad
    }
  }
  return 0
}

const getUnidadMedida = (renglon) => {
  for (const item of props.comparacion) {
    const detalle = item.detalles[renglon]?.[0]
    if (detalle) {
      return detalle.unidad_medida || detalle.insumo?.unidad_medida || 'u'
    }
  }
  return 'u'
}

const getDetalle = (item, renglon) => {
  return item.detalles[renglon]?.[0]
}

const getMejorOferta = (renglon) => {
  const ofertas = []
  props.comparacion.forEach((item, index) => {
    const detalle = getDetalle(item, renglon)
    if (detalle && detalle.precio_unitario > 0) {
      ofertas.push({
        index: index,
        proveedor: item.proveedor.razon_social,
        precio: detalle.precio_unitario,
        detalle: detalle
      })
    }
  })
  
  if (ofertas.length === 0) return null
  
  // Ordenar por precio (menor primero)
  ofertas.sort((a, b) => a.precio - b.precio)
  return ofertas[0]
}

const getMejorOfertaClass = (index) => {
  const colors = [
    'text-green-600 dark:text-green-400',
    'text-blue-600 dark:text-blue-400', 
    'text-purple-600 dark:text-purple-400',
    'text-orange-600 dark:text-orange-400'
  ]
  return colors[index % colors.length]
}

const getMejorTotal = () => {
  if (props.comparacion.length === 0) return 0
  
  const totals = props.comparacion.map(item => item.total)
  return Math.min(...totals.filter(total => total > 0))
}

const getMejorOfertaEconomica = () => {
  if (props.comparacion.length === 0) {
    return { proveedor: 'No disponible', total: 0 }
  }
  
  const ofertas = props.comparacion
    .filter(item => item.total > 0)
    .map(item => ({
      proveedor: item.proveedor.razon_social,
      total: item.total,
      item: item
    }))
    .sort((a, b) => a.total - b.total)
  
  return ofertas[0] || { proveedor: 'No disponible', total: 0 }
}

const getAhorro = () => {
  if (props.comparacion.length < 2) return 0
  
  const totals = props.comparacion
    .filter(item => item.total > 0)
    .map(item => item.total)
    .sort((a, b) => a - b)
  
  if (totals.length < 2) return 0
  
  return totals[1] - totals[0]
}

const getPorcentajeAhorro = () => {
  const ahorro = getAhorro()
  const totals = props.comparacion
    .filter(item => item.total > 0)
    .map(item => item.total)
    .sort((a, b) => a - b)
  
  if (totals.length < 2 || totals[1] === 0) return 0
  
  return ((ahorro / totals[1]) * 100).toFixed(1)
}
</script>