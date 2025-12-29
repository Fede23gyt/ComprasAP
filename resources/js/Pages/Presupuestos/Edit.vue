<template>
  <AppLayout>
    <Head :title="title" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-6">
              {{ title }}
            </h2>

            <!-- Información de la nota de pedido -->
            <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
              <h3 class="text-lg font-medium text-blue-800 dark:text-blue-200 mb-2">
                Nota de Pedido: {{ presupuesto.nota_pedido?.numero_nota }}/{{ presupuesto.nota_pedido?.ejercicio_nota }}
              </h3>
              <p class="text-blue-700 dark:text-blue-300">
                {{ presupuesto.nota_pedido?.descripcion }} - {{ presupuesto.nota_pedido?.oficina?.nombre }}
              </p>
              <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                Estado: {{ getEstadoTexto(presupuesto.estado) }}
              </p>
            </div>

            <form @submit.prevent="submit">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Proveedor -->
                <div>
                  <InputLabel for="proveedor_id" value="Proveedor *" />
                  <select
                    id="proveedor_id"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    v-model="form.proveedor_id"
                    required
                  >
                    <option value="">Seleccionar proveedor</option>
                    <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">
                      {{ proveedor.razon_social }} - {{ proveedor.cuit }}
                    </option>
                  </select>
                  <InputError class="mt-2" :message="form.errors.proveedor_id" />
                </div>

                <!-- Plazo de entrega -->
                <div>
                  <InputLabel for="plazo_entrega" value="Plazo de entrega (días)" />
                  <TextInput
                    id="plazo_entrega"
                    type="number"
                    class="mt-1 block w-full"
                    v-model="form.plazo_entrega"
                    min="1"
                    placeholder="Días hábiles"
                  />
                  <InputError class="mt-2" :message="form.errors.plazo_entrega" />
                </div>

                <!-- Forma de pago -->
                <div>
                  <InputLabel for="forma_pago" value="Forma de pago" />
                  <TextInput
                    id="forma_pago"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.forma_pago"
                    placeholder="Ej: Contado, 30 días, etc."
                  />
                  <InputError class="mt-2" :message="form.errors.forma_pago" />
                </div>

                <!-- Validez de oferta -->
                <div>
                  <InputLabel for="validez_oferta" value="Validez de oferta (días)" />
                  <TextInput
                    id="validez_oferta"
                    type="number"
                    class="mt-1 block w-full"
                    v-model="form.validez_oferta"
                    min="1"
                    placeholder="Días hábiles"
                  />
                  <InputError class="mt-2" :message="form.errors.validez_oferta" />
                </div>

                <!-- Observaciones -->
                <div class="md:col-span-2">
                  <InputLabel for="observaciones" value="Observaciones" />
                  <textarea
                    id="observaciones"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    v-model="form.observaciones"
                    rows="3"
                    placeholder="Observaciones adicionales..."
                  ></textarea>
                  <InputError class="mt-2" :message="form.errors.observaciones" />
                </div>
              </div>

              <!-- Detalles del presupuesto -->
              <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                  Detalles del Presupuesto
                </h3>
                
                <div v-for="(detalle, index) in form.detalles" :key="index" class="mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <!-- Insumo (solo lectura) -->
                    <div>
                      <InputLabel :for="'insumo_' + index" value="Insumo" />
                      <TextInput
                        :id="'insumo_' + index"
                        type="text"
                        class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                        :value="getInsumoDescripcion(detalle.insumo_id)"
                        disabled
                      />
                    </div>

                    <!-- Precio Unitario -->
                    <div>
                      <InputLabel :for="'precio_unitario_' + index" value="Precio Unitario *" />
                      <TextInput
                        :id="'precio_unitario_' + index"
                        type="number"
                        class="mt-1 block w-full"
                        v-model="detalle.precio_unitario"
                        step="0.01"
                        min="0"
                        required
                        @input="calcularTotalRenglon(index)"
                      />
                      <InputError class="mt-2" :message="getDetalleError('precio_unitario', index)" />
                    </div>

                    <!-- Cantidad (solo lectura) -->
                    <div>
                      <InputLabel :for="'cantidad_' + index" value="Cantidad" />
                      <TextInput
                        :id="'cantidad_' + index"
                        type="number"
                        class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                        :value="getCantidadOriginal(detalle.insumo_id)"
                        disabled
                      />
                    </div>

                    <!-- Total Renglón (calculado) -->
                    <div>
                      <InputLabel :for="'total_' + index" value="Total Renglón" />
                      <TextInput
                        :id="'total_' + index"
                        type="text"
                        class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                        :value="'$' + formatNumber(detalle.precio_unitario * getCantidadOriginal(detalle.insumo_id))"
                        disabled
                      />
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Marca -->
                    <div>
                      <InputLabel :for="'marca_' + index" value="Marca" />
                      <TextInput
                        :id="'marca_' + index"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="detalle.marca"
                        placeholder="Marca del producto"
                      />
                      <InputError class="mt-2" :message="getDetalleError('marca', index)" />
                    </div>

                    <!-- Modelo -->
                    <div>
                      <InputLabel :for="'modelo_' + index" value="Modelo" />
                      <TextInput
                        :id="'modelo_' + index"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="detalle.modelo"
                        placeholder="Modelo del producto"
                      />
                      <InputError class="mt-2" :message="getDetalleError('modelo', index)" />
                    </div>

                    <!-- Especificaciones Técnicas -->
                    <div class="md:col-span-2">
                      <InputLabel :for="'especificaciones_' + index" value="Especificaciones Técnicas" />
                      <textarea
                        :id="'especificaciones_' + index"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                        v-model="detalle.especificaciones_tecnicas"
                        rows="2"
                        placeholder="Especificaciones técnicas del producto..."
                      ></textarea>
                      <InputError class="mt-2" :message="getDetalleError('especificaciones_tecnicas', index)" />
                    </div>

                    <!-- Observaciones -->
                    <div class="md:col-span-2">
                      <InputLabel :for="'observaciones_detalle_' + index" value="Observaciones" />
                      <textarea
                        :id="'observaciones_detalle_' + index"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                        v-model="detalle.observaciones"
                        rows="2"
                        placeholder="Observaciones específicas del renglón..."
                      ></textarea>
                      <InputError class="mt-2" :message="getDetalleError('observaciones', index)" />
                    </div>
                  </div>

                  <!-- Campos ocultos -->
                  <input type="hidden" v-model="detalle.insumo_id" />
                  <input type="hidden" v-model="detalle.renglon" />
                  <input type="hidden" v-model="detalle.cantidad" />
                  <input type="hidden" v-model="detalle.unidad_medida" />
                  <input type="hidden" v-model="detalle.id" />
                </div>
              </div>

              <!-- Botones de acción -->
              <div class="flex justify-end space-x-4">
                <Link
                  :href="route('presupuestos.show', presupuesto.id)"
                  class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Cancelar
                </Link>
                
                <PrimaryButton :disabled="form.processing">
                  {{ form.processing ? 'Actualizando...' : 'Actualizar Presupuesto' }}
                </PrimaryButton>
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
import { Head, Link, useForm } from '@inertiajs/vue3'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

const props = defineProps({
  title: String,
  presupuesto: Object,
  proveedores: Array
})

// Preparar los detalles del formulario
const detallesForm = props.presupuesto.detalles.map(detalle => ({
  id: detalle.id,
  insumo_id: detalle.insumo_id,
  renglon: detalle.renglon,
  cantidad: detalle.cantidad,
  precio_unitario: detalle.precio_unitario,
  unidad_medida: detalle.unidad_medida,
  especificaciones_tecnicas: detalle.especificaciones_tecnicas,
  observaciones: detalle.observaciones,
  marca: detalle.marca,
  modelo: detalle.modelo
}))

const form = useForm({
  proveedor_id: props.presupuesto.proveedor_id,
  plazo_entrega: props.presupuesto.plazo_entrega,
  forma_pago: props.presupuesto.forma_pago,
  validez_oferta: props.presupuesto.validez_oferta,
  observaciones: props.presupuesto.observaciones,
  detalles: detallesForm
})

const submit = () => {
  form.put(route('presupuestos.update', props.presupuesto.id))
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

const getInsumoDescripcion = (insumoId) => {
  const detalle = props.presupuesto.detalles.find(d => d.insumo_id === insumoId)
  return detalle?.insumo?.descripcion || ''
}

const getCantidadOriginal = (insumoId) => {
  const detalle = props.presupuesto.detalles.find(d => d.insumo_id === insumoId)
  return detalle?.cantidad || 0
}

const calcularTotalRenglon = (index) => {
  // El total se calcula automáticamente en el backend
  const detalle = form.detalles[index]
  const cantidadOriginal = getCantidadOriginal(detalle.insumo_id)
  return (detalle.precio_unitario || 0) * cantidadOriginal
}

const getDetalleError = (field, index) => {
  const errorKey = `detalles.${index}.${field}`
  return form.errors[errorKey]
}
</script>