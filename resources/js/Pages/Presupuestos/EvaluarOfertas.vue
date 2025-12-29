<template>
  <AppLayout>
    <Head :title="`Evaluar Ofertas - ${presupuesto.numero_presupuesto}`" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Cabecera -->
            <div class="flex justify-between items-start mb-6">
              <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                  Evaluación de Ofertas
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                  Presupuesto: {{ presupuesto.numero_presupuesto }}/{{ presupuesto.ejercicio }}
                  <span v-if="presupuesto.nota_pedido"> - Nota: {{ presupuesto.nota_pedido.numero_nota }}</span>
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-500">
                  <span v-if="presupuesto.nota_pedido && presupuesto.nota_pedido.oficina">{{ presupuesto.nota_pedido.oficina.nombre }} • </span>
                  <span v-if="presupuesto.tipo_compra">{{ presupuesto.tipo_compra.nombre }}</span>
                </p>
              </div>

              <div class="flex space-x-2">
                <Link
                  :href="route('presupuestos.show', presupuesto.id)"
                  class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Ver Presupuesto
                </Link>
              </div>
            </div>

            <!-- Información del presupuesto -->
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-6">
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                <div>
                  <span class="font-semibold text-gray-900 dark:text-gray-100">Total Presupuesto:</span>
                  <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                    ${{ Number(presupuesto.monto_total).toLocaleString('es-AR', { minimumFractionDigits: 2 }) }}
                  </div>
                </div>
                <div>
                  <span class="font-semibold text-gray-900 dark:text-gray-100">Ofertas Recibidas:</span>
                  <div class="text-lg font-bold text-green-600 dark:text-green-400">
                    {{ Object.keys(ofertasPorRenglon).length > 0 ? Object.values(ofertasPorRenglon).map(r => r.ofertas.length).reduce((a,b) => a+b, 0) : 0 }}
                  </div>
                </div>
                <div>
                  <span class="font-semibold text-gray-900 dark:text-gray-100">Renglones:</span>
                  <div class="text-lg font-bold text-purple-600 dark:text-purple-400">
                    {{ Object.keys(ofertasPorRenglon).length }}
                  </div>
                </div>
                <div>
                  <span class="font-semibold text-gray-900 dark:text-gray-100">Evaluadas:</span>
                  <div class="text-lg font-bold text-yellow-600 dark:text-yellow-400">
                    {{ Object.values(ofertasPorRenglon).filter(r => r.ofertas.some(o => o.detalles.some(d => d.seleccionado))).length }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Evaluación por renglón -->
            <div v-if="Object.keys(ofertasPorRenglon).length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
              <p class="text-lg">No se encontraron ofertas para este presupuesto.</p>
              <p class="text-sm mt-2">Las ofertas deben estar asociadas a los detalles del presupuesto para aparecer aquí.</p>
            </div>

            <form @submit.prevent="guardarSelecciones" v-else>
              <div v-for="(renglon, renglonKey) in ofertasPorRenglon" :key="renglonKey" class="mb-8">
                <!-- Cabecera del renglón -->
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-t-lg border-b border-blue-200 dark:border-blue-700">
                  <div class="flex justify-between items-start">
                    <div>
                      <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100">
                        Renglón {{ renglonKey }}
                      </h3>
                      <p class="text-sm text-blue-700 dark:text-blue-300">
                        {{ renglon.presupuesto_detalle.insumo?.descripcion || 'Sin descripción' }}
                      </p>
                      <div class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                        Cantidad: {{ Number(renglon.presupuesto_detalle.cantidad).toLocaleString('es-AR') }} {{ renglon.presupuesto_detalle.unidad_medida || '' }}
                        | Precio Ref: ${{ Number(renglon.presupuesto_detalle.precio_unitario).toLocaleString('es-AR', { minimumFractionDigits: 2 }) }}
                        | Subtotal Ref: ${{ Number(renglon.presupuesto_detalle.total_renglon || 0).toLocaleString('es-AR', { minimumFractionDigits: 2 }) }}
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="text-sm font-semibold text-blue-900 dark:text-blue-100">
                        {{ renglon.ofertas.length }} Oferta{{ renglon.ofertas.length !== 1 ? 's' : '' }}
                      </div>
                      <div v-if="renglon.ofertas.length > 0" class="text-xs text-green-600 dark:text-green-400">
                        Mejor precio: ${{ Math.min(...renglon.ofertas.map(o => Number(o.detalles.find(d => d.det_presupuesto_id === renglon.presupuesto_detalle.id)?.precio_unitario || Infinity))).toLocaleString('es-AR', { minimumFractionDigits: 2 }) }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Ofertas para este renglón -->
                <div class="bg-white dark:bg-gray-800 border border-blue-200 dark:border-blue-700 rounded-b-lg">
                  <div v-if="renglon.ofertas.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
                    <p>No hay ofertas para este renglón.</p>
                  </div>

                  <div v-else class="overflow-x-auto">
                    <table class="min-w-full">
                      <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Seleccionar</th>
                          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Proveedor</th>
                          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Descripción</th>
                          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cantidad</th>
                          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Precio Unit.</th>
                          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Subtotal</th>
                          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">% vs Ref</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        <tr
                          v-for="oferta in renglon.ofertas"
                          :key="oferta.id"
                          :class="getRowClass(oferta, renglon.presupuesto_detalle.id, renglon.ofertas)"
                          class="hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                          <td class="px-4 py-3">
                            <input
                              type="radio"
                              :name="`renglon_${renglonKey}`"
                              :value="oferta.detalles.find(d => d.det_presupuesto_id === renglon.presupuesto_detalle.id)?.id"
                              v-model="selecciones[renglonKey]"
                              class="text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600"
                            />
                          </td>
                          <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                              {{ oferta.proveedor.razon_social }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                              {{ oferta.proveedor.cuit }}
                            </div>
                          </td>
                          <td class="px-4 py-3">
                            <div class="text-sm text-gray-900 dark:text-gray-100">
                              {{ renglon.presupuesto_detalle.insumo?.descripcion || 'Sin descripción' }}
                              <div v-if="oferta.detalles.find(d => d.det_presupuesto_id === renglon.presupuesto_detalle.id)?.marca" class="text-xs text-gray-500 mt-1">
                                Marca: {{ oferta.detalles.find(d => d.det_presupuesto_id === renglon.presupuesto_detalle.id)?.marca }}
                              </div>
                            </div>
                          </td>
                          <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                            {{ Number(oferta.detalles.find(d => d.det_presupuesto_id === renglon.presupuesto_detalle.id)?.cantidad || 0).toLocaleString('es-AR') }}
                          </td>
                          <td class="px-4 py-3">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                              ${{ Number(oferta.detalles.find(d => d.det_presupuesto_id === renglon.presupuesto_detalle.id)?.precio_unitario || 0).toLocaleString('es-AR', { minimumFractionDigits: 2 }) }}
                            </div>
                          </td>
                          <td class="px-4 py-3">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                              ${{ Number(oferta.detalles.find(d => d.det_presupuesto_id === renglon.presupuesto_detalle.id)?.subtotal || 0).toLocaleString('es-AR', { minimumFractionDigits: 2 }) }}
                            </div>
                          </td>
                          <td class="px-4 py-3">
                            <div :class="getPriceComparisonClass(oferta, renglon.presupuesto_detalle)" class="text-sm font-medium">
                              {{ getPriceComparison(oferta, renglon.presupuesto_detalle) }}%
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- Motivo de selección -->
                  <div v-if="selecciones[renglonKey]" class="p-4 bg-gray-50 dark:bg-gray-700 border-t">
                    <label :for="`motivo_${renglonKey}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Motivo de selección (opcional)
                    </label>
                    <textarea
                      :id="`motivo_${renglonKey}`"
                      v-model="motivos[renglonKey]"
                      rows="2"
                      class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      placeholder="Explique brevemente por qué seleccionó esta oferta..."
                    ></textarea>
                  </div>
                </div>
              </div>

              <!-- Acciones -->
              <div class="flex justify-between items-center pt-6 border-t border-gray-200 dark:border-gray-600">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                  {{ Object.keys(selecciones).length }} de {{ Object.keys(ofertasPorRenglon).length }} renglones evaluados
                </div>

                <div class="flex space-x-4">
                  <SecondaryButton @click="limpiarSelecciones">
                    Limpiar Selecciones
                  </SecondaryButton>

                  <PrimaryButton type="submit" :disabled="Object.keys(selecciones).length === 0">
                    Guardar Evaluación
                  </PrimaryButton>

                  <button
                    type="button"
                    @click="generarOrdenesCompra"
                    :disabled="Object.keys(selecciones).length === 0"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50"
                  >
                    Generar Órdenes de Compra
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps({
  presupuesto: Object,
  ofertasPorRenglon: Object
})

const selecciones = ref({})
const motivos = ref({})

// Obtener clase de fila según el precio
const getRowClass = (oferta, detPresupuestoId, todasLasOfertas) => {
  const detalleOferta = oferta.detalles.find(d => d.det_presupuesto_id === detPresupuestoId)
  if (!detalleOferta) return ''

  const precios = todasLasOfertas.map(o => {
    const det = o.detalles.find(d => d.det_presupuesto_id === detPresupuestoId)
    return det ? Number(det.precio_unitario) : Infinity
  }).filter(p => p !== Infinity)

  if (precios.length === 0) return ''

  const precioMin = Math.min(...precios)
  const precioOferta = Number(detalleOferta.precio_unitario)

  if (precioOferta === precioMin) {
    return 'bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500'
  }

  return ''
}

// Obtener comparación de precio vs referencia
const getPriceComparison = (oferta, detallePresupuesto) => {
  const detalleOferta = oferta.detalles.find(d => d.det_presupuesto_id === detallePresupuesto.id)
  if (!detalleOferta) return '0'

  const precioOferta = Number(detalleOferta.precio_unitario)
  const precioReferencia = Number(detallePresupuesto.precio_unitario)

  const diferencia = ((precioOferta - precioReferencia) / precioReferencia) * 100
  return diferencia.toFixed(1)
}

// Obtener clase CSS para comparación de precio
const getPriceComparisonClass = (oferta, detallePresupuesto) => {
  const comparison = Number(getPriceComparison(oferta, detallePresupuesto))

  if (comparison < 0) {
    return 'text-green-600 dark:text-green-400'
  } else if (comparison > 0) {
    return 'text-red-600 dark:text-red-400'
  } else {
    return 'text-gray-600 dark:text-gray-400'
  }
}

// Limpiar todas las selecciones
const limpiarSelecciones = () => {
  selecciones.value = {}
  motivos.value = {}
}

// Guardar evaluación
const guardarSelecciones = () => {
  const data = {
    selecciones: selecciones.value,
    motivos: motivos.value
  }

  router.post(route('presupuestos.seleccionar-ofertas', props.presupuesto.id), data, {
    onSuccess: () => {
      // La respuesta se manejará automáticamente por Inertia
    },
    onError: (errors) => {
      console.error('Error al guardar selecciones:', errors)
    }
  })
}

// Generar órdenes de compra
const generarOrdenesCompra = () => {
  if (Object.keys(selecciones.value).length === 0) {
    alert('Debe seleccionar al menos una oferta para generar órdenes de compra.')
    return
  }

  if (!confirm('¿Está seguro de generar las órdenes de compra? Esta acción no se puede deshacer.')) {
    return
  }

  router.post(route('presupuestos.generar-ordenes-compra', props.presupuesto.id), {
    selecciones: selecciones.value,
    motivos: motivos.value
  }, {
    onSuccess: () => {
      // La respuesta se manejará automáticamente por Inertia
    },
    onError: (errors) => {
      console.error('Error al generar órdenes:', errors)
    }
  })
}
</script>