<template>
  <div class="bg-white p-8 print:p-0">
    <Head :title="title" />

    <!-- Encabezado de impresión -->
    <div class="text-center mb-8 border-b-2 border-gray-300 pb-4 print:border-b print:pb-2">
      <div class="flex justify-center items-center mb-4">
        <img src="/images/logo.png" class="h-16 w-auto" alt="Logo Municipalidad de Salta" />
      </div>
      <h1 class="text-2xl font-bold text-gray-900">MUNICIPALIDAD DE SALTA</h1>
      <h2 class="text-xl font-semibold text-gray-700">SECRETARÍA DE ADMINISTRACIÓN</h2>
      <h3 class="text-lg text-gray-600">PRESUPUESTO DE COMPRA</h3>
    </div>

    <!-- Información del presupuesto -->
    <div class="grid grid-cols-2 gap-6 mb-6">
      <div>
        <p><strong>Número:</strong> {{ presupuesto.numero_presupuesto }}/{{ presupuesto.ejercicio }}</p>
        <p><strong>Fecha:</strong> {{ formatDate(presupuesto.fecha_presupuesto) }}</p>
        <p><strong>Estado:</strong> {{ getEstadoTexto(presupuesto.estado) }}</p>
      </div>
      <div>
        <p><strong>Proveedor:</strong> {{ presupuesto.proveedor?.razon_social }}</p>
        <p><strong>CUIT:</strong> {{ presupuesto.proveedor?.cuit }}</p>
        <p><strong>Total:</strong> ${{ formatNumber(presupuesto.total_presupuesto) }}</p>
      </div>
    </div>

    <!-- Información de la nota de pedido -->
    <div class="mb-6 p-4 border border-gray-300 rounded">
      <h3 class="text-lg font-semibold mb-2">NOTA DE PEDIDO</h3>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <p><strong>Número:</strong> {{ presupuesto.nota_pedido?.numero_nota }}/{{ presupuesto.nota_pedido?.ejercicio_nota }}</p>
          <p><strong>Descripción:</strong> {{ presupuesto.nota_pedido?.descripcion }}</p>
        </div>
        <div>
          <p><strong>Oficina:</strong> {{ presupuesto.nota_pedido?.oficina?.nombre }}</p>
          <p><strong>Tipo:</strong> {{ presupuesto.nota_pedido?.tipo_nota?.nombre }}</p>
        </div>
      </div>
    </div>

    <!-- Condiciones comerciales -->
    <div class="mb-6 p-4 border border-gray-300 rounded">
      <h3 class="text-lg font-semibold mb-2">CONDICIONES COMERCIALES</h3>
      <div class="grid grid-cols-3 gap-4">
        <div>
          <p><strong>Plazo de entrega:</strong> {{ presupuesto.plazo_entrega ? presupuesto.plazo_entrega + ' días' : 'No especificado' }}</p>
        </div>
        <div>
          <p><strong>Forma de pago:</strong> {{ presupuesto.forma_pago || 'No especificada' }}</p>
        </div>
        <div>
          <p><strong>Validez de oferta:</strong> {{ presupuesto.validez_oferta ? presupuesto.validez_oferta + ' días' : 'No especificada' }}</p>
        </div>
      </div>
    </div>

    <!-- Detalles del presupuesto -->
    <div class="mb-8">
      <h3 class="text-lg font-semibold mb-4">DETALLES DEL PRESUPUESTO</h3>
      
      <table class="min-w-full border border-gray-300">
        <thead>
          <tr class="bg-gray-100">
            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-medium">Renglón</th>
            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-medium">Insumo</th>
            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-medium">Cantidad</th>
            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-medium">Precio Unitario</th>
            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-medium">Total</th>
            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-medium">Unidad</th>
            <th class="border border-gray-300 px-3 py-2 text-left text-sm font-medium">Marca/Modelo</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="detalle in presupuesto.detalles" :key="detalle.id">
            <td class="border border-gray-300 px-3 py-2 text-sm">{{ detalle.renglon }}</td>
            <td class="border border-gray-300 px-3 py-2 text-sm">
              <div>
                <div class="font-medium">{{ detalle.insumo?.descripcion }}</div>
                <div class="text-xs text-gray-600">{{ detalle.insumo?.codigo }}</div>
              </div>
            </td>
            <td class="border border-gray-300 px-3 py-2 text-sm text-right">{{ formatNumber(detalle.cantidad) }}</td>
            <td class="border border-gray-300 px-3 py-2 text-sm text-right">${{ formatNumber(detalle.precio_unitario) }}</td>
            <td class="border border-gray-300 px-3 py-2 text-sm text-right">${{ formatNumber(detalle.total_renglon) }}</td>
            <td class="border border-gray-300 px-3 py-2 text-sm">{{ detalle.unidad_medida || detalle.insumo?.unidad_medida }}</td>
            <td class="border border-gray-300 px-3 py-2 text-sm">
              <div v-if="detalle.marca || detalle.modelo">
                <div>{{ detalle.marca }}</div>
                <div class="text-xs text-gray-600">{{ detalle.modelo }}</div>
              </div>
              <span v-else class="text-gray-400">-</span>
            </td>
          </tr>
        </tbody>
        <tfoot class="bg-gray-50">
          <tr>
            <td colspan="4" class="border border-gray-300 px-3 py-2 text-right text-sm font-medium">
              TOTAL GENERAL:
            </td>
            <td class="border border-gray-300 px-3 py-2 text-sm font-bold text-right">
              ${{ formatNumber(presupuesto.total_presupuesto) }}
            </td>
            <td colspan="2"></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- Información adicional -->
    <div v-if="presupuesto.observaciones" class="mb-6 p-4 border border-gray-300 rounded">
      <h3 class="text-lg font-semibold mb-2">OBSERVACIONES</h3>
      <p class="text-sm">{{ presupuesto.observaciones }}</p>
    </div>

    <!-- Especificaciones técnicas -->
    <div v-if="hasDetallesConInfoAdicional" class="mb-6">
      <h3 class="text-lg font-semibold mb-2">INFORMACIÓN ADICIONAL</h3>
      
      <div v-for="detalle in presupuesto.detalles" :key="'info-' + detalle.id" 
           v-if="detalle.especificaciones_tecnicas || detalle.observaciones"
           class="mb-3 p-3 border border-gray-300 rounded">
        <h4 class="font-medium text-sm mb-1">
          Renglón {{ detalle.renglon }} - {{ detalle.insumo?.descripcion }}
        </h4>
        
        <div v-if="detalle.especificaciones_tecnicas" class="mb-1">
          <p class="text-xs font-medium">Especificaciones Técnicas:</p>
          <p class="text-xs">{{ detalle.especificaciones_tecnicas }}</p>
        </div>
        
        <div v-if="detalle.observaciones">
          <p class="text-xs font-medium">Observaciones:</p>
          <p class="text-xs">{{ detalle.observaciones }}</p>
        </div>
      </div>
    </div>

    <!-- Firmas -->
    <div class="mt-12 grid grid-cols-2 gap-8">
      <div class="text-center">
        <div class="border-t-2 border-gray-400 pt-2 mx-auto" style="width: 200px;"></div>
        <p class="text-sm mt-2">Firma del Proveedor</p>
        <p class="text-xs text-gray-600">{{ presupuesto.proveedor?.razon_social }}</p>
      </div>
      
      <div class="text-center">
        <div class="border-t-2 border-gray-400 pt-2 mx-auto" style="width: 200px;"></div>
        <p class="text-sm mt-2">Firma del Responsable</p>
        <p class="text-xs text-gray-600">{{ presupuesto.usuario?.name }}</p>
        <p class="text-xs text-gray-600">Municipalidad de Salta</p>
      </div>
    </div>

    <!-- Botones de acción (solo en pantalla) -->
    <div class="mt-8 flex justify-center space-x-4 print:hidden">
      <button
        @click="window.print()"
        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
      >
        Imprimir
      </button>
      
      <Link
        :href="route('presupuestos.show', presupuesto.id)"
        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
      >
        Volver
      </Link>
    </div>

    <!-- Información de impresión -->
    <div class="mt-8 text-center text-xs text-gray-500 print:hidden">
      <p>Este documento se generó el {{ formatDateTime(new Date()) }}</p>
      <p>Presione el botón "Imprimir" o use Ctrl+P para imprimir</p>
    </div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
  title: String,
  presupuesto: Object
})

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('es-AR')
}

const formatDateTime = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleString('es-AR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
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

const hasDetallesConInfoAdicional = () => {
  return props.presupuesto.detalles?.some(detalle => 
    detalle.especificaciones_tecnicas || detalle.observaciones
  )
}

// Auto-print cuando se carga la página
import { onMounted } from 'vue'

onMounted(() => {
  // Esperar un momento para que todo se cargue antes de imprimir
  setTimeout(() => {
    if (!window.location.search.includes('noauto')) {
      window.print()
    }
  }, 500)
})
</script>

<style>
@media print {
  @page {
    margin: 1cm;
    size: portrait;
  }
  
  body {
    font-size: 12pt;
    line-height: 1.4;
  }
  
  .print\:hidden {
    display: none !important;
  }
  
  .print\:p-0 {
    padding: 0 !important;
  }
  
  .print\:border-b {
    border-bottom: 1px solid #ccc !important;
  }
  
  .print\:pb-2 {
    padding-bottom: 0.5rem !important;
  }
}
</style>