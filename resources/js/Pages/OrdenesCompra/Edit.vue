<template>
  <AuthenticatedLayout>
    <Head :title="`Editar Orden: ${orden.numero_orden}`" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-bold">Editar Orden: {{ orden.numero_orden }}</h2>
              <Link 
                :href="route('ordenes-compra.show', orden.id)" 
                class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
              >
                ← Cancelar
              </Link>
            </div>

            <!-- Status -->
            <div class="mb-6">
              <span 
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium"
                :class="getEstadoColor(orden.estado)"
              >
                {{ getEstadoTexto(orden.estado) }}
              </span>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Fecha de entrega estimada -->
                <div>
                  <InputLabel for="fecha_entrega_estimada" value="Fecha de Entrega Estimada" />
                  <TextInput
                    id="fecha_entrega_estimada"
                    v-model="form.fecha_entrega_estimada"
                    type="date"
                    class="mt-1 block w-full"
                    :min="minDate"
                    required
                  />
                  <InputError class="mt-2" :message="form.errors.fecha_entrega_estimada" />
                </div>

                <!-- Forma de pago -->
                <div>
                  <InputLabel for="forma_pago" value="Forma de Pago" />
                  <select
                    id="forma_pago"
                    v-model="form.forma_pago"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    required
                  >
                    <option value="">Seleccionar forma de pago</option>
                    <option v-for="(label, value) in formas_pago" :key="value" :value="value">
                      {{ label }}
                    </option>
                  </select>
                  <InputError class="mt-2" :message="form.errors.forma_pago" />
                </div>

                <!-- Plazo de entrega -->
                <div>
                  <InputLabel for="plazo_entrega" value="Plazo de Entrega (días)" />
                  <TextInput
                    id="plazo_entrega"
                    v-model="form.plazo_entrega"
                    type="number"
                    class="mt-1 block w-full"
                    min="1"
                    required
                  />
                  <InputError class="mt-2" :message="form.errors.plazo_entrega" />
                </div>

                <!-- Lugar de entrega -->
                <div>
                  <InputLabel for="lugar_entrega" value="Lugar de Entrega" />
                  <TextInput
                    id="lugar_entrega"
                    v-model="form.lugar_entrega"
                    type="text"
                    class="mt-1 block w-full"
                    required
                  />
                  <InputError class="mt-2" :message="form.errors.lugar_entrega" />
                </div>

                <!-- Condiciones de pago -->
                <div class="md:col-span-2">
                  <InputLabel for="condiciones_pago" value="Condiciones de Pago" />
                  <TextArea
                    id="condiciones_pago"
                    v-model="form.condiciones_pago"
                    class="mt-1 block w-full"
                    rows="3"
                    required
                  />
                  <InputError class="mt-2" :message="form.errors.condiciones_pago" />
                </div>

                <!-- Garantías -->
                <div class="md:col-span-2">
                  <InputLabel for="garantias" value="Garantías" />
                  <TextArea
                    id="garantias"
                    v-model="form.garantias"
                    class="mt-1 block w-full"
                    rows="2"
                  />
                  <InputError class="mt-2" :message="form.errors.garantias" />
                </div>

                <!-- Observaciones -->
                <div class="md:col-span-2">
                  <InputLabel for="observaciones" value="Observaciones" />
                  <TextArea
                    id="observaciones"
                    v-model="form.observaciones"
                    class="mt-1 block w-full"
                    rows="2"
                  />
                  <InputError class="mt-2" :message="form.errors.observaciones" />
                </div>
              </div>

              <!-- Detalles de la orden -->
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
                          Cantidad Adjudicada
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                          Precio Adjudicado
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                          Cantidad a Ordenar
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                          Precio Unitario
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                          Subtotal
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                          Descripción Adicional
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="(detalle, index) in form.detalles" :key="detalle.id">
                        <td class="px-4 py-2 text-sm">
                          {{ index + 1 }}
                        </td>
                        <td class="px-4 py-2 text-sm">
                          {{ detalle.insumo_codigo }}
                        </td>
                        <td class="px-4 py-2 text-sm">
                          {{ detalle.insumo_descripcion }}
                        </td>
                        <td class="px-4 py-2 text-sm text-right">
                          {{ detalle.cantidad_adjudicada }}
                        </td>
                        <td class="px-4 py-2 text-sm text-right">
                          {{ formatCurrency(detalle.precio_adjudicado) }}
                        </td>
                        <td class="px-4 py-2">
                          <TextInput
                            v-model="detalle.cantidad"
                            type="number"
                            class="w-20 text-right"
                            min="0.01"
                            step="0.01"
                            :max="detalle.cantidad_adjudicada"
                            required
                            @input="calcularSubtotal(detalle)"
                          />
                        </td>
                        <td class="px-4 py-2">
                          <TextInput
                            v-model="detalle.precio_unitario"
                            type="number"
                            class="w-24 text-right"
                            min="0"
                            step="0.01"
                            required
                            @input="calcularSubtotal(detalle)"
                          />
                        </td>
                        <td class="px-4 py-2 text-sm text-right font-medium">
                          {{ formatCurrency(detalle.subtotal || 0) }}
                        </td>
                        <td class="px-4 py-2">
                          <TextInput
                            v-model="detalle.descripcion_adicional"
                            type="text"
                            class="w-32"
                            placeholder="Opcional"
                          />
                        </td>
                      </tr>
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                        <td colspan="7" class="px-4 py-2 text-sm font-medium text-right">
                          Total:
                        </td>
                        <td class="px-4 py-2 text-sm font-medium text-right">
                          {{ formatCurrency(total) }}
                        </td>
                        <td></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>

              <!-- Submit -->
              <div class="flex justify-end space-x-3">
                <Link 
                  :href="route('ordenes-compra.show', orden.id)" 
                  class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md"
                >
                  Cancelar
                </Link>
                <PrimaryButton :disabled="form.processing">
                  {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
  orden: Object,
  formas_pago: Object
})

const minDate = new Date().toISOString().split('T')[0]

const form = useForm({
  fecha_entrega_estimada: props.orden.fecha_entrega_estimada,
  forma_pago: props.orden.forma_pago,
  plazo_entrega: props.orden.plazo_entrega,
  lugar_entrega: props.orden.lugar_entrega,
  condiciones_pago: props.orden.condiciones_pago,
  garantias: props.orden.garantias,
  observaciones: props.orden.observaciones,
  detalles: props.orden.detalles.map(detalle => ({
    id: detalle.id,
    adjudicacion_detalle_id: detalle.adjudicacion_detalle_id,
    insumo_codigo: detalle.insumo?.codigo,
    insumo_descripcion: detalle.insumo?.descripcion,
    cantidad_adjudicada: detalle.adjudicacion_detalle?.cantidad_adjudicada,
    precio_adjudicado: detalle.adjudicacion_detalle?.precio_unitario_adjudicado,
    cantidad: detalle.cantidad,
    precio_unitario: detalle.precio_unitario,
    subtotal: detalle.subtotal,
    descripcion_adicional: detalle.descripcion_adicional
  }))
})

const total = computed(() => {
  return form.detalles.reduce((sum, detalle) => sum + (detalle.subtotal || 0), 0)
})

function calcularSubtotal(detalle) {
  detalle.subtotal = (parseFloat(detalle.cantidad) || 0) * (parseFloat(detalle.precio_unitario) || 0)
}

function submit() {
  form.put(route('ordenes-compra.update', props.orden.id))
}

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

function formatCurrency(amount) {
  return new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS'
  }).format(amount)
}
</script>