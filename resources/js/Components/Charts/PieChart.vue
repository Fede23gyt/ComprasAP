<template>
  <div class="chart-container">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted } from 'vue'
import {
  Chart,
  PieController,
  ArcElement,
  CategoryScale,
  LinearScale,
  Title,
  Tooltip,
  Legend
} from 'chart.js'

// Registrar componentes de Chart.js
Chart.register(PieController, ArcElement, CategoryScale, LinearScale, Title, Tooltip, Legend)

const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  options: {
    type: Object,
    default: () => ({})
  },
  title: {
    type: String,
    default: ''
  }
})

const chartCanvas = ref(null)
const chartInstance = ref(null)

const defaultOptions = {
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
    title: {
      display: true,
      text: props.title,
      color: '#374151',
      font: {
        size: 16,
        weight: 'bold'
      }
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      titleColor: '#FFFFFF',
      bodyColor: '#FFFFFF',
      borderColor: 'rgba(255, 255, 255, 0.1)',
      borderWidth: 1,
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
}

const initChart = () => {
  if (!chartCanvas.value) return

  // Destruir instancia anterior si existe
  if (chartInstance.value) {
    chartInstance.value.destroy()
  }

  const ctx = chartCanvas.value.getContext('2d')
  
  chartInstance.value = new Chart(ctx, {
    type: 'pie',
    data: props.data,
    options: {
      ...defaultOptions,
      ...props.options
    }
  })
}

// Observar cambios en los datos
watch(() => props.data, (newData) => {
  if (chartInstance.value) {
    chartInstance.value.data = newData
    chartInstance.value.update()
  }
}, { deep: true })

// Observar cambios en las opciones
watch(() => props.options, (newOptions) => {
  if (chartInstance.value) {
    chartInstance.value.options = {
      ...defaultOptions,
      ...newOptions
    }
    chartInstance.value.update()
  }
}, { deep: true })

onMounted(() => {
  initChart()
})

onUnmounted(() => {
  if (chartInstance.value) {
    chartInstance.value.destroy()
  }
})
</script>

<style scoped>
.chart-container {
  position: relative;
  height: 300px;
  width: 100%;
}

@media (min-width: 768px) {
  .chart-container {
    height: 350px;
  }
}

@media (min-width: 1024px) {
  .chart-container {
    height: 400px;
  }
}
</style>