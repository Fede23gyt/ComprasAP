<template>
  <AuthenticatedLayout>
    <Head title="Estadísticas Generales" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
              <div>
                <h2 class="text-2xl font-bold">Estadísticas Generales</h2>
                <p class="text-gray-600 dark:text-gray-400">
                  Dashboard con métricas globales y análisis del sistema
                </p>
              </div>
              <div class="flex space-x-2">
                <select
                  v-model="filtros.periodo"
                  @change="buscar"
                  class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md"
                >
                  <option value="week">Última semana</option>
                  <option value="month">Último mes</option>
                  <option value="quarter">Último trimestre</option>
                  <option value="year">Último año</option>
                  <option value="custom">Personalizado</option>
                </select>
                <button 
                  @click="exportarDashboard"
                  class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md flex items-center"
                >
                  <ArrowDownTrayIcon class="w-4 h-4 mr-1" />
                  Exportar
                </button>
              </div>
            </div>

            <!-- Filtros Personalizados -->
            <div v-if="filtros.periodo === 'custom'" class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-6">
              <h3 class="text-lg font-semibold mb-4">Rango Personalizado</h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Fecha Desde
                  </label>
                  <input
                    v-model="filtros.fechaDesde"
                    type="date"
                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Fecha Hasta
                  </label>
                  <input
                    v-model="filtros.fechaHasta"
                    type="date"
                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md"
                  />
                </div>
                <div class="flex items-end">
                  <button
                    @click="buscar"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md"
                  >
                    Aplicar
                  </button>
                </div>
              </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
              <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                <div class="flex items-center">
                  <DocumentTextIcon class="w-8 h-8 text-blue-600 dark:text-blue-400 mr-3" />
                  <div>
                    <p class="text-sm font-medium text-blue-800 dark:text-blue-300">Notas de Pedido</p>
                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-200">
                      {{ stats.total_notas }}
                    </p>
                  </div>
                </div>
                <p class="text-xs text-blue-700 dark:text-blue-300 mt-2">
                  {{ stats.notas_variacion }}% vs período anterior
                </p>
              </div>

              <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4">
                <div class="flex items-center">
                  <CurrencyDollarIcon class="w-8 h-8 text-green-600 dark:text-green-400 mr-3" />
                  <div>
                    <p class="text-sm font-medium text-green-800 dark:text-green-300">Monto Total</p>
                    <p class="text-2xl font-bold text-green-900 dark:text-green-200">
                      {{ formatCurrency(stats.monto_total) }}
                    </p>
                  </div>
                </div>
                <p class="text-xs text-green-700 dark:text-green-300 mt-2">
                  {{ stats.monto_variacion }}% vs período anterior
                </p>
              </div>

              <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-lg p-4">
                <div class="flex items-center">
                  <ShoppingCartIcon class="w-8 h-8 text-purple-600 dark:text-purple-400 mr-3" />
                  <div>
                    <p class="text-sm font-medium text-purple-800 dark:text-purple-300">Órdenes Generadas</p>
                    <p class="text-2xl font-bold text-purple-900 dark:text-purple-200">
                      {{ stats.total_ordenes }}
                    </p>
                  </div>
                </div>
                <p class="text-xs text-purple-700 dark:text-purple-300 mt-2">
                  {{ stats.ordenes_variacion }}% vs período anterior
                </p>
              </div>

              <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg p-4">
                <div class="flex items-center">
                  <CheckCircleIcon class="w-8 h-8 text-orange-600 dark:text-orange-400 mr-3" />
                  <div>
                    <p class="text-sm font-medium text-orange-800 dark:text-orange-300">Tasa de Finalización</p>
                    <p class="text-2xl font-bold text-orange-900 dark:text-orange-200">
                      {{ stats.tasa_finalizacion }}%
                    </p>
                  </div>
                </div>
                <p class="text-xs text-orange-700 dark:text-orange-300 mt-2">
                  {{ stats.tasa_variacion }}% vs período anterior
                </p>
              </div>
            </div>

            <!-- Grid de Estadísticas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
              <!-- Distribución por Estado -->
              <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Distribución por Estado</h3>
                <PieChart 
                  v-if="chartData.notasPorEstado"
                  :data="chartData.notasPorEstado"
                  :options="chartOptions.pie"
                  title="Distribución de Notas por Estado"
                />
                <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                  <p>Cargando gráfico...</p>
                </div>
              </div>

              <!-- Top Oficinas -->
              <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Top 5 Oficinas</h3>
                <div class="space-y-3">
                  <div 
                    v-for="(oficina, index) in stats.top_oficinas" 
                    :key="oficina.id"
                    class="flex items-center justify-between"
                  >
                    <div class="flex items-center">
                      <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-6">
                        {{ index + 1 }}.
                      </span>
                      <span class="text-sm text-gray-600 dark:text-gray-400 truncate max-w-xs">
                        {{ oficina.oficina?.nombre || 'Sin oficina' }}
                      </span>
                    </div>
                    <div class="text-right">
                      <span class="text-sm font-medium">
                        {{ oficina.total_notas }}
                      </span>
                      <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                        {{ formatCurrency(oficina.monto_total) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Tendencia Mensual -->
              <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Tendencia Mensual</h3>
                <BarChart 
                  v-if="chartData.notasPorMes"
                  :data="chartData.notasPorMes"
                  :options="chartOptions.bar"
                  title="Notas de Pedido por Mes"
                />
                <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                  <p>Cargando gráfico...</p>
                </div>
              </div>

              <!-- Top Insumos -->
              <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Insumos Más Solicitados</h3>
                <div class="space-y-3">
                  <div 
                    v-for="item in stats.top_insumos" 
                    :key="item.codigo"
                    class="flex items-center justify-between"
                  >
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                        {{ item.descripcion }}
                      </p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ item.codigo }}
                      </p>
                    </div>
                    <div class="text-right">
                      <span class="text-sm font-medium">
                        {{ item.total_cantidad }}
                      </span>
                      <span class="text-xs text-gray-500 dark:text-gray-400 block">
                        {{ formatCurrency(item.total_monto) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Métricas Adicionales -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
              <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 text-center">
                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400">Promedio por Nota</h4>
                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                  {{ formatCurrency(stats.promedio_nota) }}
                </p>
              </div>
              <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 text-center">
                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400">Días Promedio Procesamiento</h4>
                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                  {{ stats.dias_promedio }}
                </p>
              </div>
              <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 text-center">
                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400">Eficiencia del Sistema</h4>
                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">
                  {{ stats.eficiencia }}%
                </p>
              </div>
            </div>

            <!-- Información del Período -->
            <div class="text-center">
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Período: {{ formatDate(stats.fecha_desde) }} - {{ formatDate(stats.fecha_hasta) }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, watch, onMounted } from 'vue'
import { 
  DocumentTextIcon, 
  CurrencyDollarIcon, 
  ShoppingCartIcon, 
  CheckCircleIcon, 
  ArrowDownTrayIcon 
} from '@heroicons/vue/24/outline'
import PieChart from '@/Components/Charts/PieChart.vue'
import BarChart from '@/Components/Charts/BarChart.vue'

const props = defineProps({
  stats: Object,
  filtros: Object,
  estados: Object
})

const filtros = ref({ ...props.filtros })
const chartData = ref({})
const chartOptions = ref({
  pie: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'right',
        labels: {
          color: '#6B7280',
          padding: 20,
          font: {
            size: 12
          }
        }
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            const label = context.label || ''
            const value = context.raw || 0
            const total = context.dataset.data.reduce((a, b) => a + b, 0)
            const percentage = ((value / total) * 100).toFixed(1)
            return `${label}: ${value} (${percentage}%)`
          }
        }
      }
    }
  },
  bar: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: false
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          stepSize: 1
        }
      }
    }
  }
})

function buscar() {
  router.get(route('reportes.estadisticas'), filtros.value, {
    preserveState: true,
    preserveScroll: true
  })
}

function exportarDashboard() {
  const params = new URLSearchParams(filtros.value).toString()
  window.open(`/reportes/exportar/dashboard/excel?${params}`)
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS'
  }).format(amount)
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('es-ES')
}

function formatMes(mes) {
  const [ano, mesNum] = mes.split('-')
  const meses = [
    'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
    'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'
  ]
  return `${meses[parseInt(mesNum) - 1]} ${ano}`
}

function getEstadoColor(estado) {
  const colors = {
    'borrador': 'bg-gray-400',
    'pendiente': 'bg-yellow-400',
    'confirmada': 'bg-blue-400',
    'rechazada': 'bg-red-400',
    'en_proceso': 'bg-purple-400',
    'completada': 'bg-green-400'
  }
  return colors[estado] || 'bg-gray-400'
}

function prepareChartData() {
  // Prepare data for pie chart - notas por estado
  if (props.stats.notas_por_estado && props.stats.notas_por_estado.length > 0) {
    const estadoColors = {
      'borrador': '#9CA3AF',
      'pendiente': '#F59E0B',
      'confirmada': '#3B82F6',
      'rechazada': '#EF4444',
      'en_proceso': '#8B5CF6',
      'completada': '#10B981'
    }

    chartData.value.notasPorEstado = {
      labels: props.stats.notas_por_estado.map(item => props.estados[item.estado]),
      datasets: [{
        data: props.stats.notas_por_estado.map(item => item.total),
        backgroundColor: props.stats.notas_por_estado.map(item => estadoColors[item.estado] || '#9CA3AF'),
        borderColor: '#FFFFFF',
        borderWidth: 2
      }]
    }
  }

  // Prepare data for bar chart - notas por mes
  if (props.stats.notas_por_mes && props.stats.notas_por_mes.length > 0) {
    chartData.value.notasPorMes = {
      labels: props.stats.notas_por_mes.map(item => formatMes(item.mes)),
      datasets: [{
        label: 'Notas de Pedido',
        data: props.stats.notas_por_mes.map(item => item.total),
        backgroundColor: '#3B82F6',
        borderColor: '#2563EB',
        borderWidth: 1
      }]
    }
  }
}

// Prepare chart data when component mounts
onMounted(() => {
  prepareChartData()
})

// Watch for stats changes and update charts
watch(() => props.stats, () => {
  prepareChartData()
}, { deep: true })

// Watch for filter changes
watch(filtros, (newVal, oldVal) => {
  if (JSON.stringify(newVal) !== JSON.stringify(oldVal)) {
    buscar()
  }
}, { deep: true })
</script>