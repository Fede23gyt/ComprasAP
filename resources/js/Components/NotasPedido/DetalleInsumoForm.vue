<template>
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
    <!-- Selector de Insumo (6 columnas) -->
    <div class="lg:col-span-6">
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Insumo <span class="text-red-500">*</span>
      </label>
      
      <!-- Input de búsqueda -->
      <div class="relative">
        <input
          v-model="busquedaInsumo"
          @input="buscarInsumos"
          @focus="mostrarResultados = true"
          type="text"
          placeholder="Buscar insumo por código o descripción..."
          :disabled="props.soloLectura"
          class="w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed"
          :class="{ 'border-red-500': errorInsumo }"
        />
        
        <!-- Icono de búsqueda -->
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
          <svg v-if="cargandoInsumos" class="animate-spin h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg v-else class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>

        <!-- Dropdown de resultados -->
        <div
          v-if="mostrarResultados && (insumosEncontrados.length > 0 || busquedaInsumo.length > 2)"
          class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto"
        >
          <div v-if="insumosEncontrados.length === 0 && busquedaInsumo.length > 2" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
            No se encontraron insumos
          </div>
          
          <button
            v-for="insumo in insumosEncontrados"
            :key="insumo.id"
            @click="seleccionarInsumo(insumo)"
            type="button"
            class="w-full px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-600 border-b border-gray-100 dark:border-gray-600 last:border-b-0"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                  {{ insumo.codigo }} - {{ insumo.descripcion }}
                </div>
                <div v-if="insumo.clasificacion_economica" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  {{ insumo.clasificacion }} - {{ insumo.clasificacion_economica.descripcion }}
                </div>
                <div v-if="insumo.unidad_solicitud" class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                  Unidad: {{ insumo.unidad_solicitud }}
                </div>
              </div>
              <div v-if="insumo.precio_insumo && insumo.precio_insumo > 0" class="ml-4 text-right">
                <div class="text-sm font-medium text-green-600 dark:text-green-400">
                  ${{ formatearMoneda(insumo.precio_insumo) }}
                </div>
                <div v-if="insumo.precio_testigo" class="text-xs text-yellow-600 dark:text-yellow-400">
                  Testigo
                </div>
              </div>
            </div>
          </button>
        </div>
      </div>

      <!-- Información del insumo seleccionado -->
      <div v-if="insumoSeleccionado" class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <div class="text-sm font-medium text-blue-900 dark:text-blue-100">
              {{ insumoSeleccionado.codigo }} - {{ insumoSeleccionado.descripcion }}
            </div>
            <div v-if="insumoSeleccionado.clasificacion_economica" class="text-xs text-blue-700 dark:text-blue-300 mt-1">
              {{ insumoSeleccionado.clasificacion }} - {{ insumoSeleccionado.clasificacion_economica.descripcion }}
            </div>
            <div v-if="insumoSeleccionado.unidad_solicitud" class="text-xs text-blue-600 dark:text-blue-400 mt-1">
              Unidad: {{ insumoSeleccionado.unidad_solicitud }}
            </div>
          </div>
          <button
            @click="limpiarSeleccion"
            type="button"
            class="ml-2 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
            title="Cambiar insumo"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Error de insumo -->
      <div v-if="errorInsumo" class="mt-1 text-sm text-red-600 dark:text-red-400">
        {{ errorInsumo }}
      </div>
    </div>

    <!-- Cantidad (2 columnas) -->
    <div class="lg:col-span-2">
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Cantidad <span class="text-red-500">*</span>
      </label>
      <input
        v-model="cantidad"
        @input="actualizarDetalle"
        type="number"
        step="0.0001"
        min="0.0001"
        :disabled="props.soloLectura"
        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed"
        :class="{ 'border-red-500': errorCantidad }"
        placeholder="1"
      />
      <div v-if="errorCantidad" class="mt-1 text-sm text-red-600 dark:text-red-400">
        {{ errorCantidad }}
      </div>
    </div>

    <!-- Precio (2 columnas) -->
    <div class="lg:col-span-2">
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Precio Unit. <span class="text-red-500">*</span>
      </label>
      <div class="relative">
        <span class="absolute left-3 top-2 text-gray-500 dark:text-gray-400">$</span>
        <input
          v-model="precio"
          @input="actualizarDetalle"
          type="number"
          step="0.01"
          min="0"
          :disabled="props.soloLectura"
          class="w-full pl-8 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed"
          :class="{ 'border-red-500': errorPrecio }"
          placeholder="0.00"
        />
      </div>
      
      <!-- Botón para usar precio testigo -->
      <button
        v-if="insumoSeleccionado && insumoSeleccionado.precio_insumo && insumoSeleccionado.precio_insumo > 0 && !props.soloLectura"
        @click="usarPrecioTestigo"
        type="button"
        class="mt-1 text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 underline"
      >
        Usar precio testigo (${{ formatearMoneda(insumoSeleccionado.precio_insumo) }})
      </button>
      
      <!-- Diferencia con precio testigo -->
      <div v-if="mostrarDiferenciaPrecio" class="mt-1 text-xs" :class="claseDiferenciaPrecio">
        {{ textoDiferenciaPrecio }}
      </div>

      <div v-if="errorPrecio" class="mt-1 text-sm text-red-600 dark:text-red-400">
        {{ errorPrecio }}
      </div>
    </div>

    <!-- Total y Acciones (2 columnas) -->
    <div class="lg:col-span-2">
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Total
      </label>
      <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-gray-100 font-medium">
        ${{ formatearMoneda(totalRenglon) }}
      </div>
      
      <!-- Botón para comentario -->
      <button
        v-if="!props.soloLectura"
        @click="$emit('abrir-comentario', index)"
        type="button"
        class="mt-2 w-full text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 border border-blue-300 dark:border-blue-600 rounded px-2 py-1 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors flex items-center justify-center space-x-1"
      >
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10m0 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v2m0 0v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
        </svg>
        <span>{{ detalle.comentario ? 'Editar comentario' : 'Agregar comentario' }}</span>
      </button>
      
      <!-- Mostrar comentario en modo solo lectura -->
      <div v-else-if="props.soloLectura && detalle.comentario" class="mt-2 p-2 bg-gray-50 dark:bg-gray-600 rounded text-xs text-gray-700 dark:text-gray-300">
        <div class="font-medium text-gray-900 dark:text-white mb-1">Comentario:</div>
        {{ detalle.comentario }}
      </div>
      
      <!-- Indicador de comentario -->
      <div v-if="detalle.comentario" class="mt-1 text-xs text-green-600 dark:text-green-400 flex items-center">
        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Comentario agregado
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  detalle: {
    type: Object,
    required: true
  },
  index: {
    type: Number,
    required: true
  },
  errors: {
    type: Object,
    default: () => ({})
  },
  soloLectura: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['actualizar', 'abrir-comentario'])

// Estado local
const busquedaInsumo = ref('')
const insumosEncontrados = ref([])
const mostrarResultados = ref(false)
const cargandoInsumos = ref(false)
const cantidad = ref(props.detalle.cantidad || 1)
const precio = ref(props.detalle.precio || 0)

// Insumo seleccionado
const insumoSeleccionado = computed(() => props.detalle.insumo)

// Errores específicos para este renglón
const errorInsumo = computed(() => props.errors[`detalles.${props.index}.insumo_id`])
const errorCantidad = computed(() => props.errors[`detalles.${props.index}.cantidad`])
const errorPrecio = computed(() => props.errors[`detalles.${props.index}.precio`])

// Total del renglón
const totalRenglon = computed(() => {
  const cant = parseFloat(cantidad.value) || 0
  const prec = parseFloat(precio.value) || 0
  return cant * prec
})

// Diferencia con precio testigo
const mostrarDiferenciaPrecio = computed(() => {
  return insumoSeleccionado.value && 
         insumoSeleccionado.value.precio_insumo && 
         insumoSeleccionado.value.precio_insumo > 0 && 
         precio.value > 0
})

const diferenciaPorcentaje = computed(() => {
  if (!mostrarDiferenciaPrecio.value) return 0
  
  const precioTestigo = parseFloat(insumoSeleccionado.value.precio_insumo)
  const precioActual = parseFloat(precio.value)
  
  return ((precioActual - precioTestigo) / precioTestigo) * 100
})

const textoDiferenciaPrecio = computed(() => {
  const diff = diferenciaPorcentaje.value
  if (Math.abs(diff) < 0.1) return 'Igual al precio testigo'
  
  const signo = diff > 0 ? '+' : ''
  return `${signo}${diff.toFixed(1)}% vs precio testigo`
})

const claseDiferenciaPrecio = computed(() => {
  const diff = diferenciaPorcentaje.value
  if (Math.abs(diff) < 0.1) return 'text-green-600 dark:text-green-400'
  if (diff > 0) return 'text-red-600 dark:text-red-400'
  return 'text-blue-600 dark:text-blue-400'
})

// Timeout para búsqueda
let timeoutBusqueda = null

// Watchers
watch([cantidad, precio], () => {
  actualizarDetalle()
})

watch(() => props.detalle, (nuevoDetalle) => {
  cantidad.value = nuevoDetalle.cantidad || 1
  precio.value = nuevoDetalle.precio || 0
  
  if (nuevoDetalle.insumo) {
    busquedaInsumo.value = `${nuevoDetalle.insumo.codigo} - ${nuevoDetalle.insumo.descripcion}`
    mostrarResultados.value = false
  }
}, { immediate: true, deep: true })

// Métodos
const buscarInsumos = () => {
  // No buscar si está en modo solo lectura
  if (props.soloLectura) return
  
  // Limpiar timeout anterior
  if (timeoutBusqueda) {
    clearTimeout(timeoutBusqueda)
  }

  // Si el input está vacío, limpiar resultados
  if (busquedaInsumo.value.length < 2) {
    insumosEncontrados.value = []
    return
  }

  // Buscar después de 300ms de inactividad
  timeoutBusqueda = setTimeout(async () => {
    try {
      cargandoInsumos.value = true
      const response = await axios.get('/notas-pedido/api/buscar-insumos', {
        params: { search: busquedaInsumo.value }
      })
      insumosEncontrados.value = response.data
    } catch (error) {
      console.error('Error al buscar insumos:', error)
      insumosEncontrados.value = []
    } finally {
      cargandoInsumos.value = false
    }
  }, 300)
}

const seleccionarInsumo = (insumo) => {
  busquedaInsumo.value = `${insumo.codigo} - ${insumo.descripcion}`
  mostrarResultados.value = false
  
  // Si el insumo tiene precio testigo, usarlo por defecto
  if (insumo.precio_insumo && insumo.precio_insumo > 0) {
    precio.value = insumo.precio_insumo
  }
  
  actualizarDetalle({
    insumo_id: insumo.id,
    insumo: insumo
  })
}

const limpiarSeleccion = () => {
  busquedaInsumo.value = ''
  mostrarResultados.value = true
  insumosEncontrados.value = []
  
  actualizarDetalle({
    insumo_id: '',
    insumo: null
  })
}

const usarPrecioTestigo = () => {
  if (insumoSeleccionado.value && insumoSeleccionado.value.precio_insumo) {
    precio.value = insumoSeleccionado.value.precio_insumo
    actualizarDetalle()
  }
}

const actualizarDetalle = (campos = {}) => {
  const detalleActualizado = {
    cantidad: cantidad.value,
    precio: precio.value,
    ...campos
  }
  
  emit('actualizar', props.index, detalleActualizado)
}

const formatearMoneda = (monto) => {
  return parseFloat(monto || 0).toLocaleString('es-AR', { 
    minimumFractionDigits: 2, 
    maximumFractionDigits: 2 
  })
}

// Cerrar dropdown al hacer click fuera
const cerrarDropdown = (event) => {
  if (!event.target.closest('.relative')) {
    mostrarResultados.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', cerrarDropdown)
})

onUnmounted(() => {
  document.removeEventListener('click', cerrarDropdown)
  if (timeoutBusqueda) {
    clearTimeout(timeoutBusqueda)
  }
})
</script>