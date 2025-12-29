<template>
  <AppLayout>
    <Head title="Nueva Oferta" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
              <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                  Nueva Oferta
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mt-2">
                  Seleccione un presupuesto y complete los renglones que desea ofertar
                </p>
              </div>

              <Link
                :href="route('ofertas.index')"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                Volver a Ofertas
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
                      @change="cargarPresupuesto"
                      class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                      required
                    >
                      <option value="">Seleccione un presupuesto</option>
                      <option v-for="presupuesto in presupuestos" :key="presupuesto.id" :value="presupuesto.id">
                        {{ presupuesto.numero_presupuesto }}/{{ presupuesto.ejercicio }} - {{ getNombreOficina(presupuesto) }}
                      </option>
                    </select>
                    <InputError :message="form.errors.presupuesto_id" class="mt-2" />
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

                <div v-if="!form.presupuesto_id" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
                  <p class="text-sm text-yellow-700 dark:text-yellow-300">
                    📋 Seleccione primero un presupuesto para cargar los renglones disponibles
                  </p>
                </div>

                <div v-else-if="cargandoPresupuesto" class="text-center py-8">
                  <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                  <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Cargando renglones del presupuesto...</p>
                </div>

                <div v-else-if="renglones.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                  <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                  </svg>
                  <p>Este presupuesto no tiene renglones definidos</p>
                </div>

                <div v-else class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                      <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                          <input
                            type="checkbox"
                            @change="toggleTodosRenglones"
                            :checked="todosRenglonesSeleccionados"
                            class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500"
                          />
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                          Reng.
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                          Insumo
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                          Cant. Solicitada
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                          Precio Presup.
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                          Cant. Ofertada *
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                          Precio Ofertado *
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                          Descripción
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                          Subtotal
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="(renglon, index) in renglones" :key="index" :class="{'bg-gray-50 dark:bg-gray-700/50': renglon.ofertado}">
                        <td class="px-4 py-3">
                          <input
                            type="checkbox"
                            v-model="renglon.ofertado"
                            @change="toggleRenglon(index)"
                            class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500"
                          />
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                          {{ renglon.renglon }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                          <div class="font-medium text-gray-900 dark:text-gray-100">
                            {{ renglon.insumo_codigo || renglon.insumo_id }}
                          </div>
                          <div class="text-xs text-gray-600 dark:text-gray-400">
                            {{ renglon.insumo_nombre }}
                          </div>
                          <button
                            type="button"
                            @click="verDescripcionRenglon(renglon)"
                            class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mt-1"
                          >
                            Ver descripción completa →
                          </button>
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100">
                          {{ renglon.cantidad_solicitada }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100">
                          ${{ formatearMoneda(renglon.precio_presupuesto) }}
                        </td>
                        <td class="px-4 py-3">
                          <input
                            type="number"
                            v-model="renglon.cantidad_ofertada"
                            @input="calcularSubtotal(index)"
                            :disabled="!renglon.ofertado"
                            step="1"
                            min="0"
                            class="block w-full text-right border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            :placeholder="renglon.ofertado ? '0' : '-'"
                          />
                        </td>
                        <td class="px-4 py-3">
                          <input
                            type="number"
                            v-model="renglon.precio_ofertado"
                            @input="calcularSubtotal(index)"
                            :disabled="!renglon.ofertado"
                            step="0.01"
                            min="0"
                            class="block w-full text-right border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            :placeholder="renglon.ofertado ? '0.00' : '-'"
                          />
                        </td>
                        <td class="px-4 py-3 text-center">
                          <button
                            type="button"
                            @click="abrirModalDescripcion(index)"
                            :disabled="!renglon.ofertado"
                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 disabled:opacity-50 disabled:cursor-not-allowed"
                            title="Agregar descripción de lo ofertado"
                          >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                          </button>
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 dark:text-gray-100">
                          {{ renglon.ofertado ? '$' + formatearMoneda(renglon.subtotal) : '-' }}
                        </td>
                      </tr>
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                        <td colspan="8" class="px-4 py-3 text-right font-bold text-gray-900 dark:text-gray-100">
                          Total Oferta:
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-lg text-blue-600 dark:text-blue-400">
                          ${{ formatearMoneda(totalOferta) }}
                        </td>
                      </tr>
                      <tr>
                        <td colspan="9" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">
                          Renglones ofertados: {{ renglonesOfertados }} de {{ renglones.length }}
                        </td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>

              <!-- Observaciones -->
              <div class="mb-6">
                <InputLabel for="observaciones" value="Observaciones Generales" />
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
                  :href="route('ofertas.index')"
                  class="px-4 py-2 text-sm bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500"
                >
                  Cancelar
                </Link>

                <button
                  type="submit"
                  :disabled="form.processing || renglonesOfertados === 0"
                  class="px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="form.processing">Guardando...</span>
                  <span v-else>Guardar Oferta</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Descripción del Renglón Presupuestado -->
    <Modal :show="mostrarModalRenglon" @close="cerrarModalRenglon">
      <div class="p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
          Descripción del Renglón {{ renglonSeleccionado?.renglon }}
        </h2>

        <div v-if="renglonSeleccionado" class="space-y-4">
          <div>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Insumo:</p>
            <p class="text-base text-gray-900 dark:text-gray-100">{{ renglonSeleccionado.insumo_nombre }}</p>
          </div>

          <div>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Código:</p>
            <p class="text-base text-gray-900 dark:text-gray-100">{{ renglonSeleccionado.insumo_codigo || renglonSeleccionado.insumo_id }}</p>
          </div>

          <div>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Cantidad Solicitada:</p>
            <p class="text-base text-gray-900 dark:text-gray-100">{{ renglonSeleccionado.cantidad_solicitada }}</p>
          </div>

          <div>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Precio Presupuestado:</p>
            <p class="text-base text-gray-900 dark:text-gray-100">${{ formatearMoneda(renglonSeleccionado.precio_presupuesto) }}</p>
          </div>

          <div v-if="renglonSeleccionado.especificaciones_tecnicas">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Especificaciones Técnicas:</p>
            <p class="text-base text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ renglonSeleccionado.especificaciones_tecnicas }}</p>
          </div>

          <div v-if="renglonSeleccionado.observaciones_presupuesto">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Observaciones del Presupuesto:</p>
            <p class="text-base text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ renglonSeleccionado.observaciones_presupuesto }}</p>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <button
            @click="cerrarModalRenglon"
            class="px-4 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700"
          >
            Cerrar
          </button>
        </div>
      </div>
    </Modal>

    <!-- Modal Descripción de lo Ofertado -->
    <Modal :show="mostrarModalOferta" @close="cerrarModalOferta">
      <div class="p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
          Descripción de lo Ofertado - Renglón {{ renglones[indiceEdicion]?.renglon }}
        </h2>

        <div class="space-y-4">
          <div>
            <InputLabel for="marca" value="Marca" />
            <TextInput
              id="marca"
              type="text"
              v-model="descripcionOferta.marca"
              class="mt-1 block w-full"
              placeholder="Marca del producto"
            />
          </div>

          <div>
            <InputLabel for="modelo" value="Modelo" />
            <TextInput
              id="modelo"
              type="text"
              v-model="descripcionOferta.modelo"
              class="mt-1 block w-full"
              placeholder="Modelo del producto"
            />
          </div>

          <div>
            <InputLabel for="caracteristicas_tecnicas" value="Características Técnicas" />
            <textarea
              id="caracteristicas_tecnicas"
              v-model="descripcionOferta.caracteristicas_tecnicas"
              rows="3"
              class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
              placeholder="Describa las características técnicas del producto ofertado..."
            ></textarea>
          </div>

          <div>
            <InputLabel for="garantia" value="Garantía" />
            <TextInput
              id="garantia"
              type="text"
              v-model="descripcionOferta.garantia"
              class="mt-1 block w-full"
              placeholder="Ej: 12 meses"
            />
          </div>

          <div>
            <InputLabel for="tiempo_entrega" value="Tiempo de Entrega (días)" />
            <TextInput
              id="tiempo_entrega"
              type="number"
              v-model="descripcionOferta.tiempo_entrega"
              class="mt-1 block w-full"
              min="0"
              placeholder="Días de entrega"
            />
          </div>

          <div>
            <InputLabel for="observaciones_renglon" value="Observaciones del Renglón" />
            <textarea
              id="observaciones_renglon"
              v-model="descripcionOferta.observaciones"
              rows="3"
              class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
              placeholder="Observaciones específicas sobre este renglón..."
            ></textarea>
          </div>
        </div>

        <div class="mt-6 flex justify-end space-x-4">
          <button
            @click="cerrarModalOferta"
            class="px-4 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700"
          >
            Cancelar
          </button>
          <button
            @click="guardarDescripcionOferta"
            class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700"
          >
            Guardar
          </button>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import Modal from '@/Components/Modal.vue'
import axios from 'axios'

const props = defineProps({
  title: String,
  presupuestos: Array,
  proveedores: Array
})

const cargandoPresupuesto = ref(false)
const presupuestoSeleccionado = ref(null)
const renglones = ref([])
const mostrarModalRenglon = ref(false)
const mostrarModalOferta = ref(false)
const renglonSeleccionado = ref(null)
const indiceEdicion = ref(null)

const descripcionOferta = ref({
  marca: '',
  modelo: '',
  caracteristicas_tecnicas: '',
  garantia: '',
  tiempo_entrega: '',
  observaciones: ''
})

const form = useForm({
  presupuesto_id: '',
  proveedor_id: '',
  fecha_oferta: new Date().toISOString().split('T')[0],
  plazo_entrega: '',
  forma_pago: '',
  validez_oferta: '30',
  observaciones: '',
  detalles: []
})

const totalOferta = computed(() => {
  return renglones.value.reduce((total, renglon) => {
    if (renglon.ofertado) {
      return total + (parseFloat(renglon.subtotal) || 0)
    }
    return total
  }, 0)
})

const renglonesOfertados = computed(() => {
  return renglones.value.filter(r => r.ofertado && parseFloat(r.cantidad_ofertada) > 0 && parseFloat(r.precio_ofertado) > 0).length
})

const todosRenglonesSeleccionados = computed(() => {
  return renglones.value.length > 0 && renglones.value.every(r => r.ofertado)
})

const getNombreOficina = (presupuesto) => {
  if (!presupuesto.notas_pedido || presupuesto.notas_pedido.length === 0) return ''
  const nota = presupuesto.notas_pedido[0]
  return nota.oficina ? nota.oficina.nombre : ''
}

const cargarPresupuesto = async () => {
  if (!form.presupuesto_id) {
    renglones.value = []
    return
  }

  cargandoPresupuesto.value = true

  try {
    const response = await axios.get(route('presupuestos.api.detalles', form.presupuesto_id))
    const presupuesto = response.data

    presupuestoSeleccionado.value = presupuesto

    // Cargar los renglones del presupuesto
    renglones.value = presupuesto.detalles.map(detalle => ({
      det_presupuesto_id: detalle.id,
      renglon: detalle.renglon,
      insumo_id: detalle.insumo_id,
      insumo_codigo: detalle.insumo?.codigo || '',
      insumo_nombre: detalle.insumo?.descripcion || '',
      cantidad_solicitada: detalle.cantidad,
      precio_presupuesto: detalle.precio_unitario || 0,
      especificaciones_tecnicas: detalle.especificaciones_tecnicas || '',
      observaciones_presupuesto: detalle.observaciones || '',
      // Datos de la oferta
      ofertado: false,
      cantidad_ofertada: '',
      precio_ofertado: '',
      marca: '',
      modelo: '',
      caracteristicas_tecnicas: '',
      garantia: '',
      tiempo_entrega: '',
      observaciones: '',
      subtotal: 0
    }))
  } catch (error) {
    console.error('Error al cargar el presupuesto:', error)
    alert('Error al cargar los detalles del presupuesto')
  } finally {
    cargandoPresupuesto.value = false
  }
}

const toggleRenglon = (index) => {
  const renglon = renglones.value[index]
  if (!renglon.ofertado) {
    // Si se desmarca, limpiar los valores
    renglon.cantidad_ofertada = ''
    renglon.precio_ofertado = ''
    renglon.subtotal = 0
  } else {
    // Si se marca, prellenar con los valores del presupuesto
    renglon.cantidad_ofertada = renglon.cantidad_solicitada
    renglon.precio_ofertado = ''
    calcularSubtotal(index)
  }
}

const toggleTodosRenglones = (event) => {
  const checked = event.target.checked
  renglones.value.forEach((renglon, index) => {
    renglon.ofertado = checked
    if (checked) {
      renglon.cantidad_ofertada = renglon.cantidad_solicitada
      renglon.precio_ofertado = ''
    } else {
      renglon.cantidad_ofertada = ''
      renglon.precio_ofertado = ''
      renglon.subtotal = 0
    }
  })
}

const calcularSubtotal = (index) => {
  const renglon = renglones.value[index]
  const cantidad = parseFloat(renglon.cantidad_ofertada) || 0
  const precioUnitario = parseFloat(renglon.precio_ofertado) || 0
  renglon.subtotal = cantidad * precioUnitario
}

const verDescripcionRenglon = (renglon) => {
  renglonSeleccionado.value = renglon
  mostrarModalRenglon.value = true
}

const cerrarModalRenglon = () => {
  mostrarModalRenglon.value = false
  renglonSeleccionado.value = null
}

const abrirModalDescripcion = (index) => {
  indiceEdicion.value = index
  const renglon = renglones.value[index]

  descripcionOferta.value = {
    marca: renglon.marca || '',
    modelo: renglon.modelo || '',
    caracteristicas_tecnicas: renglon.caracteristicas_tecnicas || '',
    garantia: renglon.garantia || '',
    tiempo_entrega: renglon.tiempo_entrega || '',
    observaciones: renglon.observaciones || ''
  }

  mostrarModalOferta.value = true
}

const guardarDescripcionOferta = () => {
  const renglon = renglones.value[indiceEdicion.value]

  renglon.marca = descripcionOferta.value.marca
  renglon.modelo = descripcionOferta.value.modelo
  renglon.caracteristicas_tecnicas = descripcionOferta.value.caracteristicas_tecnicas
  renglon.garantia = descripcionOferta.value.garantia
  renglon.tiempo_entrega = descripcionOferta.value.tiempo_entrega
  renglon.observaciones = descripcionOferta.value.observaciones

  cerrarModalOferta()
}

const cerrarModalOferta = () => {
  mostrarModalOferta.value = false
  indiceEdicion.value = null
  descripcionOferta.value = {
    marca: '',
    modelo: '',
    caracteristicas_tecnicas: '',
    garantia: '',
    tiempo_entrega: '',
    observaciones: ''
  }
}

const formatearMoneda = (monto) => {
  if (!monto) return '0.00'
  return parseFloat(monto).toLocaleString('es-AR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

const guardarOferta = () => {
  // Preparar solo los renglones ofertados
  const detallesOferta = renglones.value
    .filter(r => r.ofertado && parseFloat(r.cantidad_ofertada) > 0 && parseFloat(r.precio_ofertado) > 0)
    .map(r => ({
      det_presupuesto_id: r.det_presupuesto_id,
      renglon: r.renglon,
      insumo_id: r.insumo_id,
      cantidad: parseFloat(r.cantidad_ofertada),
      precio_unitario: parseFloat(r.precio_ofertado),
      marca: r.marca || null,
      modelo: r.modelo || null,
      caracteristicas_tecnicas: r.caracteristicas_tecnicas || null,
      garantia: r.garantia || null,
      tiempo_entrega: r.tiempo_entrega || null,
      observaciones: r.observaciones || null
    }))

  if (detallesOferta.length === 0) {
    alert('Debe ofertar al menos un renglón con cantidad y precio.')
    return
  }

  form.detalles = detallesOferta

  form.post(route('ofertas.store'))
}
</script>
