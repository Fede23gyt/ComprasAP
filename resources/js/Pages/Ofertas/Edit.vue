<template>
  <AppLayout>
    <Head title="Editar Oferta" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
              <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                  Editar Oferta {{ oferta.numero_oferta }}
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mt-2">
                  Modifique los datos de la oferta
                </p>
              </div>

              <Link
                :href="route('ofertas.show', oferta.id)"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                Cancelar
              </Link>
            </div>

            <form @submit.prevent="guardarOferta">
              <!-- Información General -->
              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                  Información General
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <InputLabel for="presupuesto_id" value="Presupuesto *" />
                    <select
                      id="presupuesto_id"
                      v-model="form.presupuesto_id"
                      disabled
                      class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm opacity-50"
                    >
                      <option v-for="presupuesto in presupuestos" :key="presupuesto.id" :value="presupuesto.id">
                        {{ presupuesto.numero_presupuesto }}/{{ presupuesto.ejercicio }}
                      </option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">El presupuesto no se puede cambiar</p>
                  </div>

                  <div>
                    <InputLabel for="proveedor_id" value="Proveedor *" />
                    <select
                      id="proveedor_id"
                      v-model="form.proveedor_id"
                      class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                      required
                    >
                      <option value="">Seleccione un proveedor</option>
                      <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">
                        {{ proveedor.razon_social }} ({{ proveedor.cuit }})
                      </option>
                    </select>
                    <InputError :message="form.errors.proveedor_id" class="mt-2" />
                  </div>

                  <div>
                    <InputLabel for="fecha_oferta" value="Fecha de Oferta *" />
                    <TextInput
                      id="fecha_oferta"
                      type="date"
                      v-model="form.fecha_oferta"
                      class="mt-1 block w-full"
                      required
                    />
                    <InputError :message="form.errors.fecha_oferta" class="mt-2" />
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
                    <InputLabel for="plazo_entrega" value="Plazo de Entrega (días)" />
                    <TextInput
                      id="plazo_entrega"
                      type="number"
                      v-model="form.plazo_entrega"
                      class="mt-1 block w-full"
                      min="1"
                      placeholder="Ej: 15"
                    />
                    <InputError :message="form.errors.plazo_entrega" class="mt-2" />
                  </div>

                  <div>
                    <InputLabel for="forma_pago" value="Forma de Pago" />
                    <TextInput
                      id="forma_pago"
                      type="text"
                      v-model="form.forma_pago"
                      class="mt-1 block w-full"
                      placeholder="Ej: 30 días fecha factura"
                    />
                    <InputError :message="form.errors.forma_pago" class="mt-2" />
                  </div>

                  <div>
                    <InputLabel for="validez_oferta" value="Validez Oferta (días)" />
                    <TextInput
                      id="validez_oferta"
                      type="number"
                      v-model="form.validez_oferta"
                      class="mt-1 block w-full"
                      min="1"
                      placeholder="Ej: 30"
                    />
                    <InputError :message="form.errors.validez_oferta" class="mt-2" />
                  </div>
                </div>
              </div>

              <!-- Detalle de Renglones -->
              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                  Detalle de Renglones
                </h3>

                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-100 dark:bg-gray-800">
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
                          Precio Unit. *
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
                      <tr v-for="(detalle, index) in form.detalles" :key="index">
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                          {{ detalle.renglon }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                          <div class="font-medium">{{ detalle.insumo_nombre }}</div>
                          <div v-if="detalle.caracteristicas_tecnicas" class="text-xs text-gray-500 dark:text-gray-400">
                            {{ detalle.caracteristicas_tecnicas }}
                          </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100">
                          {{ detalle.cantidad }}
                        </td>
                        <td class="px-4 py-3">
                          <input
                            type="number"
                            v-model="detalle.precio_unitario"
                            @input="calcularSubtotal(index)"
                            step="0.01"
                            min="0"
                            class="block w-full text-right border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm"
                            placeholder="0.00"
                            required
                          />
                        </td>
                        <td class="px-4 py-3">
                          <input
                            type="text"
                            v-model="detalle.marca"
                            class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm"
                            placeholder="Marca"
                          />
                        </td>
                        <td class="px-4 py-3">
                          <input
                            type="text"
                            v-model="detalle.modelo"
                            class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm"
                            placeholder="Modelo"
                          />
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
                          ${{ formatearMoneda(totalOferta) }}
                        </td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>

              <!-- Observaciones -->
              <div class="mb-6">
                <InputLabel for="observaciones" value="Observaciones" />
                <textarea
                  id="observaciones"
                  v-model="form.observaciones"
                  rows="3"
                  class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                  placeholder="Observaciones adicionales sobre la oferta..."
                ></textarea>
                <InputError :message="form.errors.observaciones" class="mt-2" />
              </div>

              <!-- Botones de acción -->
              <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-600">
                <Link
                  :href="route('ofertas.show', oferta.id)"
                  class="px-4 py-2 text-sm bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500"
                >
                  Cancelar
                </Link>

                <button
                  type="submit"
                  :disabled="form.processing"
                  class="px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="form.processing">Guardando...</span>
                  <span v-else>Actualizar Oferta</span>
                </button>
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
import { computed } from 'vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  title: String,
  oferta: Object,
  presupuestos: Array,
  proveedores: Array
})

const form = useForm({
  presupuesto_id: props.oferta.presupuesto_id,
  proveedor_id: props.oferta.proveedor_id,
  fecha_oferta: props.oferta.fecha_oferta,
  plazo_entrega: props.oferta.plazo_entrega,
  forma_pago: props.oferta.forma_pago,
  validez_oferta: props.oferta.validez_oferta,
  observaciones: props.oferta.observaciones,
  detalles: props.oferta.detalles.map(detalle => ({
    det_presupuesto_id: detalle.det_presupuesto_id,
    renglon: detalle.renglon,
    insumo_id: detalle.insumo_id,
    insumo_nombre: detalle.insumo?.descripcion || '',
    caracteristicas_tecnicas: detalle.caracteristicas_tecnicas || '',
    cantidad: detalle.cantidad,
    precio_unitario: detalle.precio_unitario,
    marca: detalle.marca || '',
    modelo: detalle.modelo || '',
    garantia: detalle.garantia || '',
    tiempo_entrega: detalle.tiempo_entrega || '',
    observaciones: detalle.observaciones || '',
    subtotal: parseFloat(detalle.cantidad) * parseFloat(detalle.precio_unitario)
  }))
})

const totalOferta = computed(() => {
  return form.detalles.reduce((total, detalle) => {
    return total + (parseFloat(detalle.subtotal) || 0)
  }, 0)
})

const calcularSubtotal = (index) => {
  const detalle = form.detalles[index]
  const cantidad = parseFloat(detalle.cantidad) || 0
  const precioUnitario = parseFloat(detalle.precio_unitario) || 0
  detalle.subtotal = cantidad * precioUnitario
}

const formatearMoneda = (monto) => {
  if (!monto) return '0.00'
  return parseFloat(monto).toLocaleString('es-AR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

const guardarOferta = () => {
  form.put(route('ofertas.update', props.oferta.id), {
    preserveScroll: true,
    onSuccess: () => {
      // La redirección la manejará el controlador
    }
  })
}
</script>
