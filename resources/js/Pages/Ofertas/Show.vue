<template>
  <AppLayout>
    <Head :title="title" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Header con acciones -->
            <div class="flex justify-between items-start mb-6">
              <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                  Oferta {{ oferta.numero_oferta }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                  Presupuesto: {{ oferta.presupuesto.numero_presupuesto }}/{{ oferta.presupuesto.ejercicio }}
                </p>
              </div>

              <div class="flex space-x-2">
                <Link
                  :href="route('ofertas.index')"
                  class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Volver
                </Link>

                <Link
                  v-if="puedeEditar"
                  :href="route('ofertas.edit', oferta.id)"
                  class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Editar
                </Link>
              </div>
            </div>

            <!-- Estado y metadatos -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Estado</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1" :class="getEstadoClass(oferta.estado_efectivo || oferta.estado)">
                  {{ getEstadoTexto(oferta.estado_efectivo || oferta.estado) }}
                </span>
              </div>

              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Fecha Oferta</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                  {{ formatearFecha(oferta.fecha_oferta) }}
                </p>
              </div>

              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Monto Total</p>
                <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                  ${{ formatearMoneda(oferta.monto_total) }}
                </p>
              </div>

              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Plazo Entrega</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                  {{ oferta.plazo_entrega }} días
                </p>
              </div>
            </div>

            <!-- Información del Proveedor -->
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                Información del Proveedor
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Razón Social</p>
                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ oferta.proveedor.razon_social }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">CUIT</p>
                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ oferta.proveedor.cuit }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Nombre Fantasía</p>
                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ oferta.proveedor.nombre_fantasia || '-' }}</p>
                </div>
              </div>
            </div>

            <!-- Condiciones Comerciales -->
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                Condiciones Comerciales
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Forma de Pago</p>
                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ oferta.forma_pago || '-' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Validez Oferta</p>
                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ oferta.validez_oferta }} días</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400">Plazo de Entrega</p>
                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ oferta.plazo_entrega }} días</p>
                </div>
              </div>
            </div>

            <!-- Detalle de Renglones -->
            <div class="bg-white dark:bg-gray-800 mb-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                Detalle de Renglones Ofertados
              </h3>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Reng.
                      </th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Insumo
                      </th>
                      <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Cantidad
                      </th>
                      <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Precio Unit.
                      </th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Marca
                      </th>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Modelo
                      </th>
                      <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                        Subtotal
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="detalle in oferta.detalles" :key="detalle.id">
                      <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                        {{ detalle.renglon }}
                      </td>
                      <td class="px-4 py-3 text-sm">
                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ detalle.insumo.descripcion }}</div>
                        <div v-if="detalle.caracteristicas_tecnicas" class="text-xs text-gray-500 dark:text-gray-400">
                          {{ detalle.caracteristicas_tecnicas }}
                        </div>
                      </td>
                      <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100">
                        {{ detalle.cantidad }}
                      </td>
                      <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100">
                        ${{ formatearMoneda(detalle.precio_unitario) }}
                      </td>
                      <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                        {{ detalle.marca || '-' }}
                      </td>
                      <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                        {{ detalle.modelo || '-' }}
                      </td>
                      <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 dark:text-gray-100">
                        ${{ formatearMoneda(detalle.subtotal) }}
                      </td>
                    </tr>
                  </tbody>
                  <tfoot class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                      <td colspan="6" class="px-4 py-3 text-right font-bold text-gray-900 dark:text-gray-100">
                        Total Oferta:
                      </td>
                      <td class="px-4 py-3 text-right font-bold text-lg text-blue-600 dark:text-blue-400">
                        ${{ formatearMoneda(oferta.monto_total) }}
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>

            <!-- Observaciones -->
            <div v-if="oferta.observaciones" class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                Observaciones
              </h3>
              <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ oferta.observaciones }}</p>
            </div>

            <!-- Información del Presupuesto -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
              <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2">
                Información del Presupuesto
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                  <span class="text-blue-700 dark:text-blue-300">Número:</span>
                  <span class="ml-2 font-medium text-blue-900 dark:text-blue-100">
                    {{ oferta.presupuesto.numero_presupuesto }}/{{ oferta.presupuesto.ejercicio }}
                  </span>
                </div>
                <div v-if="oferta.presupuesto.nota_pedido">
                  <span class="text-blue-700 dark:text-blue-300">Nota de Pedido:</span>
                  <span class="ml-2 font-medium text-blue-900 dark:text-blue-100">
                    {{ oferta.presupuesto.nota_pedido.numero_nota }}
                  </span>
                </div>
                <div>
                  <span class="text-blue-700 dark:text-blue-300">Estado Presupuesto:</span>
                  <span class="ml-2 font-medium text-blue-900 dark:text-blue-100">
                    {{ oferta.presupuesto.estado }}
                  </span>
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
import { computed } from 'vue'

const props = defineProps({
  title: String,
  oferta: Object
})

// Verificar si la oferta puede ser editada
// Usa el atributo puede_modificar del backend que considera el estado del presupuesto
const puedeEditar = computed(() => {
  if (props.oferta.puede_modificar !== undefined) {
    return props.oferta.puede_modificar
  }
  return props.oferta.estado === 'pendiente' || props.oferta.estado === 'evaluando'
})

const formatearFecha = (fecha) => {
  if (!fecha) return '-'
  return new Date(fecha).toLocaleDateString('es-AR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const formatearMoneda = (monto) => {
  if (!monto) return '0.00'
  return parseFloat(monto).toLocaleString('es-AR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

const getEstadoTexto = (estado) => {
  const estados = {
    'pendiente': 'Pendiente',
    'evaluando': 'En Evaluación',
    'aprobada': 'Aprobada',
    'rechazada': 'Rechazada',
    'adjudicada': 'Adjudicada',
    'no_adjudicada': 'No Adjudicada'
  }
  return estados[estado] || estado
}

const getEstadoClass = (estado) => {
  const clases = {
    'pendiente': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100',
    'evaluando': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-100',
    'aprobada': 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100',
    'rechazada': 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100',
    'adjudicada': 'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100',
    'no_adjudicada': 'bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-100'
  }
  return clases[estado] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100'
}
</script>
