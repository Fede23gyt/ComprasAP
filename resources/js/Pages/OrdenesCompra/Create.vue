<template>
  <AuthenticatedLayout>
    <Head title="Crear Orden de Compra" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-bold">Crear Orden de Compra</h2>
              <Link 
                :href="route('ordenes-compra.index')" 
                class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
              >
                ← Volver
              </Link>
            </div>

            <!-- Adjudicación Info -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-6">
              <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-300 mb-2">
                Adjudicación: {{ adjudicacion.numero_adjudicacion }}
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="font-medium">Proveedor:</span>
                  {{ adjudicacion.proveedor?.razon_social }}
                </div>
                <div>
                  <span class="font-medium">Monto Adjudicado:</span>
                  {{ formatCurrency(adjudicacion.monto_adjudicado) }}
                </div>
                <div>
                  <span class="font-medium">Fecha:</span>
                  {{ formatDate(adjudicacion.fecha_adjudicacion) }}
                </div>
                <div>
                  <span class="font-medium">Estado:</span>
                  <span class="capitalize">{{ adjudicacion.estado }}</span>
                </div>
              </div>
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
                          Precio Unitario
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
                      <tr v-for="(detalle, index) in form.detalles" :key="detalle.adjudicacion_detalle_id">
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
              <div class="flex justify-end">
                <PrimaryButton :disabled="form.processing">
                  {{ form.processing ? 'Creando...' : 'Crear Orden de Compra' }}
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
  adjudicacion: Object,
  formas_pago: Object
})

const minDate = new Date().toISOString().split('T')[0]

const form = useForm({
  adjudicacion_id: props.adjudicacion.id,
  fecha_entrega_estimada: '',
  forma_pago: '',
  plazo_entrega: 30,
  lugar_entrega: '',
  condiciones_pago: '',
  garantias: '',
  observaciones: '',
  detalles: props.adjudicacion.detalles.map(detalle => ({
    adjudicacion_detalle_id: detalle.id,
    insumo_codigo: detalle.insumo?.codigo,
    insumo_descripcion: detalle.insumo?.descripcion,
    cantidad_adjudicada: detalle.cantidad_adjudicada,
    precio_adjudicado: detalle.precio_unitario_adjudicado,
    cantidad: detalle.cantidad_adjudicada,
    precio_unitario: detalle.precio_unitario_adjudicado,
    subtotal: detalle.cantidad_adjudicada * detalle.precio_unitario_adjudicado,
    descripcion_adicional: ''
  }))
})

const total = computed(() => {
  return form.detalles.reduce((sum, detalle) => sum + (detalle.subtotal || 0), 0)
})

function calcularSubtotal(detalle) {
  detalle.subtotal = (parseFloat(detalle.cantidad) || 0) * (parseFloat(detalle.precio_unitario) || 0)
}

function submit() {
  form.post(route('ordenes-compra.store'))
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
</script>