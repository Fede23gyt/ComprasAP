<template>
  <AuthenticatedLayout>
    <Head :title="`Orden de Compra: ${orden.numero_orden}`" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
              <div>
                <h2 class="text-2xl font-bold">Orden de Compra: {{ orden.numero_orden }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  Creada el {{ formatDate(orden.created_at) }}
                </p>
              </div>
              <div class="flex space-x-2">
                <Link 
                  :href="route('ordenes-compra.index')" 
                  class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md"
                >
                  ← Volver
                </Link>
                <button 
                  v-if="orden.estado === 'borrador'"
                  @click="enviarAprobacion"
                  class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md"
                >
                  Enviar para Aprobación
                </button>
                <button 
                  v-if="orden.estado === 'pendiente' && canAprobar"
                  @click="showAprobarModal = true"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md"
                >
                  Aprobar
                </button>
                <button 
                  v-if="orden.estado === 'pendiente' && canAprobar"
                  @click="showRechazarModal = true"
                  class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md"
                >
                  Rechazar
                </button>
                <button 
                  v-if="orden.estado === 'aprobada'"
                  @click="enviarProveedor"
                  class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md"
                >
                  Enviar al Proveedor
                </button>
                <button 
                  v-if="['enviada', 'recibida'].includes(orden.estado)"
                  @click="showRecepcionModal = true"
                  class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-md"
                >
                  Registrar Recepción
                </button>
                <button 
                  v-if="['recibida', 'completada'].includes(orden.estado) && !orden.numero_factura"
                  @click="showFacturaModal = true"
                  class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md"
                >
                  Registrar Factura
                </button>
                <button 
                  v-if="orden.numero_factura && orden.estado_pago !== 'completado'"
                  @click="showPagoModal = true"
                  class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-md"
                >
                  Registrar Pago
                </button>
                <button 
                  v-if="!['completada', 'anulada'].includes(orden.estado)"
                  @click="showAnularModal = true"
                  class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md"
                >
                  Anular
                </button>
              </div>
            </div>

            <!-- Status Badges -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</h3>
                <span 
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium mt-1"
                  :class="getEstadoColor(orden.estado)"
                >
                  {{ getEstadoTexto(orden.estado) }}
                </span>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado de Pago</h3>
                <span 
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium mt-1"
                  :class="getEstadoPagoColor(orden.estado_pago)"
                >
                  {{ getEstadoPagoTexto(orden.estado_pago) }}
                </span>
              </div>
              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Monto Total</h3>
                <p class="text-lg font-bold text-gray-900 dark:text-gray-100 mt-1">
                  {{ formatCurrency(orden.monto_total) }}
                </p>
              </div>
            </div>

            <!-- Order Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-3">Información de la Orden</h3>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Fecha de Orden:</span>
                    <span class="font-medium">{{ formatDate(orden.fecha_orden) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Entrega Estimada:</span>
                    <span class="font-medium">{{ formatDate(orden.fecha_entrega_estimada) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Forma de Pago:</span>
                    <span class="font-medium">{{ orden.forma_pago }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Plazo de Entrega:</span>
                    <span class="font-medium">{{ orden.plazo_entrega }} días</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Lugar de Entrega:</span>
                    <span class="font-medium">{{ orden.lugar_entrega }}</span>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-3">Información de Adjudicación</h3>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Número:</span>
                    <span class="font-medium">{{ orden.adjudicacion?.numero_adjudicacion }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Proveedor:</span>
                    <span class="font-medium">{{ orden.adjudicacion?.proveedor?.razon_social }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Oficina:</span>
                    <span class="font-medium">{{ orden.adjudicacion?.presupuesto?.oficina?.nombre }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Generada por:</span>
                    <span class="font-medium">{{ orden.usuario_generador?.name }}</span>
                  </div>
                  <div v-if="orden.usuario_aprobador" class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Aprobada por:</span>
                    <span class="font-medium">{{ orden.usuario_aprobador?.name }}</span>
                  </div>
                  <div v-if="orden.fecha_aprobacion" class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Fecha Aprobación:</span>
                    <span class="font-medium">{{ formatDate(orden.fecha_aprobacion) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
              <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4 text-center">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Items Totales</h3>
                <p class="text-2xl font-bold text-blue-900 dark:text-blue-200">
                  {{ estadisticas.total_items }}
                </p>
              </div>
              <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4 text-center">
                <h3 class="text-sm font-medium text-green-800 dark:text-green-300">Recepción</h3>
                <p class="text-2xl font-bold text-green-900 dark:text-green-200">
                  {{ Math.round(estadisticas.porcentaje_recepcion) }}%
                </p>
              </div>
              <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-lg p-4 text-center">
                <h3 class="text-sm font-medium text-purple-800 dark:text-purple-300">Pago</h3>
                <p class="text-2xl font-bold text-purple-900 dark:text-purple-200">
                  {{ Math.round(estadisticas.porcentaje_pago) }}%
                </p>
              </div>
              <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg p-4 text-center">
                <h3 class="text-sm font-medium text-orange-800 dark:text-orange-300">Días Restantes</h3>
                <p class="text-2xl font-bold text-orange-900 dark:text-orange-200">
                  {{ estadisticas.dias_entrega_restantes ?? 'N/A' }}
                </p>
              </div>
            </div>

            <!-- Order Details -->
            <div class="mb-6">
              <h3 class="text-lg font-semibold mb-4">Detalles de la Orden</h3>
              
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Renglón
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Código
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Descripción
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Cantidad
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Precio Unitario
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Subtotal
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Recibido
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Pendiente
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(detalle, index) in orden.detalles" :key="detalle.id">
                      <td class="px-4 py-2 text-sm">
                        {{ index + 1 }}
                      </td>
                      <td class="px-4 py-2 text-sm">
                        {{ detalle.insumo?.codigo }}
                      </td>
                      <td class="px-4 py-2 text-sm">
                        <div>{{ detalle.insumo?.descripcion }}</div>
                        <div v-if="detalle.descripcion_adicional" class="text-xs text-gray-500 dark:text-gray-400">
                          {{ detalle.descripcion_adicional }}
                        </div>
                      </td>
                      <td class="px-4 py-2 text-sm text-right">
                        {{ detalle.cantidad }}
                      </td>
                      <td class="px-4 py-2 text-sm text-right">
                        {{ formatCurrency(detalle.precio_unitario) }}
                      </td>
                      <td class="px-4 py-2 text-sm text-right font-medium">
                        {{ formatCurrency(detalle.subtotal) }}
                      </td>
                      <td class="px-4 py-2 text-sm text-right">
                        <span :class="{ 'text-green-600 dark:text-green-400': detalle.cantidad_recibida > 0 }">
                          {{ detalle.cantidad_recibida || 0 }}
                        </span>
                      </td>
                      <td class="px-4 py-2 text-sm text-right">
                        <span :class="{ 'text-red-600 dark:text-red-400': (detalle.cantidad - detalle.cantidad_recibida) > 0 }">
                          {{ detalle.cantidad - detalle.cantidad_recibida }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                      <td colspan="5" class="px-4 py-2 text-sm font-medium text-right">
                        Total:
                      </td>
                      <td class="px-4 py-2 text-sm font-medium text-right">
                        {{ formatCurrency(orden.monto_total) }}
                      </td>
                      <td colspan="2"></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>

            <!-- Payment Information -->
            <div v-if="orden.numero_factura" class="mb-6">
              <h3 class="text-lg font-semibold mb-4">Información de Facturación</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                  <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400">Número de Factura</h4>
                  <p class="text-lg font-bold">{{ orden.numero_factura }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                  <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400">Monto Facturado</h4>
                  <p class="text-lg font-bold">{{ formatCurrency(orden.monto_facturado) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                  <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400">Fecha de Factura</h4>
                  <p class="text-lg">{{ formatDate(orden.fecha_factura) }}</p>
                </div>
              </div>

              <div v-if="orden.fecha_pago" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                  <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400">Fecha de Pago</h4>
                  <p class="text-lg">{{ formatDate(orden.fecha_pago) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                  <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400">Monto Pendiente</h4>
                  <p class="text-lg font-bold">{{ formatCurrency(orden.monto_pendiente) }}</p>
                </div>
              </div>
            </div>

            <!-- Reception History -->
            <div v-if="orden.recepciones && orden.recepciones.length > 0" class="mb-6">
              <h3 class="text-lg font-semibold mb-4">Historial de Recepciones</h3>
              
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Fecha
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Número de Remito
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Cantidad Total Recibida
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Observaciones
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="recepcion in orden.recepciones" :key="recepcion.id">
                      <td class="px-4 py-2 text-sm">
                        {{ formatDateTime(recepcion.created_at) }}
                      </td>
                      <td class="px-4 py-2 text-sm">
                        {{ recepcion.numero_remito }}
                      </td>
                      <td class="px-4 py-2 text-sm text-right">
                        {{ recepcion.cantidad_total }}
                      </td>
                      <td class="px-4 py-2 text-sm">
                        {{ recepcion.observaciones }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Observaciones -->
            <div v-if="orden.observaciones" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
              <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300 mb-2">Observaciones</h3>
              <p class="text-sm text-yellow-700 dark:text-yellow-200">{{ orden.observaciones }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <Modal :show="showAprobarModal" @close="showAprobarModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
          Aprobar Orden de Compra
        </h2>
        
        <form @submit.prevent="aprobarOrden">
          <div class="mb-4">
            <InputLabel for="observaciones_aprobacion" value="Observaciones (opcional)" />
            <TextArea
              id="observaciones_aprobacion"
              v-model="aprobacionForm.observaciones"
              class="mt-1 block w-full"
              rows="3"
            />
          </div>
          
          <div class="flex justify-end space-x-3">
            <SecondaryButton @click="showAprobarModal = false">Cancelar</SecondaryButton>
            <PrimaryButton type="submit">Aprobar</PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <Modal :show="showRechazarModal" @close="showRechazarModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
          Rechazar Orden de Compra
        </h2>
        
        <form @submit.prevent="rechazarOrden">
          <div class="mb-4">
            <InputLabel for="observaciones_rechazo" value="Motivo del rechazo" />
            <TextArea
              id="observaciones_rechazo"
              v-model="rechazoForm.observaciones"
              class="mt-1 block w-full"
              rows="3"
              required
            />
            <InputError class="mt-2" :message="rechazoForm.errors.observaciones" />
          </div>
          
          <div class="flex justify-end space-x-3">
            <SecondaryButton @click="showRechazarModal = false">Cancelar</SecondaryButton>
            <DangerButton type="submit">Rechazar</DangerButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- More modals would be implemented here for reception, invoice, payment, and cancellation -->
    
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  orden: Object,
  estadisticas: Object
})

const showAprobarModal = ref(false)
const showRechazarModal = ref(false)
const showRecepcionModal = ref(false)
const showFacturaModal = ref(false)
const showPagoModal = ref(false)
const showAnularModal = ref(false)

const aprobacionForm = useForm({
  observaciones: ''
})

const rechazoForm = useForm({
  observaciones: ''
})

const canAprobar = computed(() => {
  const user = window.auth.user
  const roleName = user.role?.name || user.role
  return ['administrador', 'supervisor'].includes(roleName)
})

function getEstadoTexto(estado) {
  const estados = {
    'borrador': 'Borrador',
    'pendiente': 'Pendiente Aprobación',
    'aprobada': 'Aprobada',
    'rechazada': 'Rechazada',
    'enviada': 'Enviada al Proveedor',
    'recibida': 'Recibida Parcialmente',
    'completada': 'Completada',
    'anulada': 'Anulada'
  }
  return estados[estado] || estado
}

function getEstadoColor(estado) {
  const colors = {
    'borrador': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
    'pendiente': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-200',
    'aprobada': 'bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-200',
    'rechazada': 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-200',
    'enviada': 'bg-purple-100 text-purple-800 dark:bg-purple-700 dark:text-purple-200',
    'recibida': 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-200',
    'completada': 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-200',
    'anulada': 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-200'
  }
  return colors[estado] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
}

function getEstadoPagoTexto(estadoPago) {
  const estados = {
    'pendiente': 'Pendiente',
    'parcial': 'Pago Parcial',
    'completado': 'Pagado',
    'anulado': 'Anulado'
  }
  return estados[estadoPago] || estadoPago
}

function getEstadoPagoColor(estadoPago) {
  const colors = {
    'pendiente': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-200',
    'parcial': 'bg-orange-100 text-orange-800 dark:bg-orange-700 dark:text-orange-200',
    'completado': 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-200',
    'anulado': 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-200'
  }
  return colors[estadoPago] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('es-ES')
}

function formatDateTime(dateTime) {
  return new Date(dateTime).toLocaleString('es-ES')
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS'
  }).format(amount)
}

function enviarAprobacion() {
  if (confirm('¿Está seguro de enviar esta orden para aprobación?')) {
    router.post(route('ordenes-compra.enviar-aprobacion', props.orden.id), {
      onSuccess: () => router.reload()
    })
  }
}

function aprobarOrden() {
  aprobacionForm.post(route('ordenes-compra.aprobar', props.orden.id), {
    onSuccess: () => {
      showAprobarModal.value = false
      router.reload()
    }
  })
}

function rechazarOrden() {
  rechazoForm.post(route('ordenes-compra.rechazar', props.orden.id), {
    onSuccess: () => {
      showRechazarModal.value = false
      router.reload()
    }
  })
}

function enviarProveedor() {
  if (confirm('¿Está seguro de enviar esta orden al proveedor?')) {
    router.post(route('ordenes-compra.enviar-proveedor', props.orden.id), {
      onSuccess: () => router.reload()
    })
  }
}
</script>