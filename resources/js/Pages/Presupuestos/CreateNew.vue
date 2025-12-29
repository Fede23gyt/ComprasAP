<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head :title="title" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-6">
              {{ title }}
            </h2>

            <!-- Paso 1: Selección Múltiple de Notas de Pedido -->
            <div v-if="step === 'select_notes'">
              <div class="mb-6">
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                  Seleccione una o más notas de pedido confirmadas para crear el presupuesto:
                </p>

                <!-- Filtro de búsqueda -->
                <div class="mb-4">
                  <TextInput
                    type="text"
                    class="w-full"
                    v-model="searchTerm"
                    placeholder="Buscar por número, descripción o expediente..."
                  />
                </div>

                <!-- Lista de notas de pedido con selección múltiple -->
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                          <input
                            type="checkbox"
                            @change="toggleSelectAll"
                            :checked="filteredNotas.length > 0 && selectedNotaIds.length === filteredNotas.length"
                            class="rounded border-gray-300 dark:border-gray-600"
                          >
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                          Nota
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                          Descripción
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                          Oficina
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                          Total Est.
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="nota in filteredNotas" :key="nota.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-4 py-3 whitespace-nowrap">
                          <input
                            type="checkbox"
                            :value="nota.id"
                            v-model="selectedNotaIds"
                            class="rounded border-gray-300 dark:border-gray-600"
                          >
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                          <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ nota.numero_nota }}/{{ nota.ejercicio_nota }}
                          </div>
                          <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ formatDate(nota.fecha_nota) }}
                          </div>
                        </td>
                        <td class="px-4 py-3">
                          <div class="text-sm text-gray-900 dark:text-gray-100">
                            {{ nota.descripcion }}
                          </div>
                          <div v-if="nota.expediente" class="text-xs text-gray-500 dark:text-gray-400">
                            Exp: {{ nota.expediente }}
                          </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                          {{ nota.oficina?.nombre }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                          ${{ formatNumber(nota.total_estimado) }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Mensaje si no hay notas -->
                <div v-if="filteredNotas.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                  No se encontraron notas de pedido confirmadas.
                </div>
              </div>

              <!-- Botones -->
              <div class="flex justify-between">
                <Link
                  :href="route('presupuestos.index')"
                  class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Cancelar
                </Link>
                
                <button
                  @click="proceedToNextStep"
                  :disabled="selectedNotaIds.length === 0"
                  class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Continuar ({{ selectedNotaIds.length }} seleccionadas)
                </button>
              </div>
            </div>

            <!-- Paso 2: Selección del orden de renglones (solo si hay múltiples notas) -->
            <div v-else-if="step === 'select_order'">
              <div class="mb-6">
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                  Ha seleccionado múltiples notas de pedido. ¿Cómo desea ordenar los renglones en el presupuesto?
                </p>

                <!-- Notas seleccionadas -->
                <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                  <h3 class="text-lg font-medium text-blue-800 dark:text-blue-200 mb-2">
                    Notas de Pedido Seleccionadas ({{ notasSeleccionadas?.length }})
                  </h3>
                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div v-for="nota in notasSeleccionadas" :key="nota.id" class="text-blue-700 dark:text-blue-300 text-sm">
                      {{ nota.numero_nota }}/{{ nota.ejercicio_nota }} - {{ nota.oficina?.nombre }}
                    </div>
                  </div>
                </div>

                <!-- Opciones de ordenamiento -->
                <div class="space-y-4">
                  <div>
                    <label class="flex items-center">
                      <input
                        type="radio"
                        v-model="selectedOrdenRenglones"
                        value="codigo_insumo"
                        class="border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                      >
                      <span class="ml-3 text-sm text-gray-900 dark:text-gray-100">
                        <strong>Por Código de Insumo:</strong> Los renglones se ordenarán por el código del insumo independientemente de la nota de pedido
                      </span>
                    </label>
                  </div>
                  
                  <div>
                    <label class="flex items-center">
                      <input
                        type="radio"
                        v-model="selectedOrdenRenglones"
                        value="nota_pedido_insumo"
                        class="border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                      >
                      <span class="ml-3 text-sm text-gray-900 dark:text-gray-100">
                        <strong>Por Nota de Pedido/Insumo:</strong> Los renglones se agruparán primero por nota de pedido y luego por insumo
                      </span>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Botones -->
              <div class="flex justify-between">
                <button
                  @click="goBackToNoteSelection"
                  class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Volver
                </button>
                
                <button
                  @click="proceedToBudgetCreation"
                  :disabled="!selectedOrdenRenglones"
                  class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Continuar
                </button>
              </div>
            </div>

            <!-- Paso 3: Formulario de creación del presupuesto -->
            <div v-else-if="step === 'create_budget'">
              <!-- Información de las notas seleccionadas -->
              <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-blue-800 dark:text-blue-200 mb-2">
                  Notas de Pedido: 
                  <span v-for="(nota, index) in notasSeleccionadas" :key="nota.id">
                    {{ nota.numero_nota }}/{{ nota.ejercicio_nota }}<span v-if="index < notasSeleccionadas.length - 1">, </span>
                  </span>
                </h3>
                <div v-if="ordenRenglones" class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                  Orden de renglones: {{ ordenRenglones === 'codigo_insumo' ? 'Por código de insumo' : 'Por nota de pedido/insumo' }}
                </div>
              </div>

              <form @submit.prevent="submit">
                <!-- Campos del presupuesto -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                  <!-- Tipo de Compra -->
                  <div>
                    <InputLabel for="tipo_compra_id" value="Tipo de Compra *" />
                    <select
                      id="tipo_compra_id"
                      class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                      v-model="form.tipo_compra_id"
                      required
                    >
                      <option value="">Seleccionar tipo de compra</option>
                      <option v-for="tipo in tiposCompra" :key="tipo.id" :value="tipo.id">
                        {{ tipo.descripcion }}
                      </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.tipo_compra_id" />
                  </div>

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

                  <!-- Lugar de Entrega -->
                  <div>
                    <InputLabel for="lugar_entrega" value="Lugar de Entrega" />
                    <TextInput
                      id="lugar_entrega"
                      type="text"
                      class="mt-1 block w-full"
                      v-model="form.lugar_entrega"
                      placeholder="Lugar donde se entregará"
                    />
                    <InputError class="mt-2" :message="form.errors.lugar_entrega" />
                  </div>

                  <!-- Domicilio de Entrega -->
                  <div>
                    <InputLabel for="domicilio_entrega" value="Domicilio de Entrega" />
                    <TextInput
                      id="domicilio_entrega"
                      type="text"
                      class="mt-1 block w-full"
                      v-model="form.domicilio_entrega"
                      placeholder="Dirección de entrega"
                    />
                    <InputError class="mt-2" :message="form.errors.domicilio_entrega" />
                  </div>

                  <!-- Dependencia -->
                  <div>
                    <InputLabel for="dependencia" value="Dependencia" />
                    <TextInput
                      id="dependencia"
                      type="text"
                      class="mt-1 block w-full"
                      v-model="form.dependencia"
                      placeholder="Dependencia solicitante"
                    />
                    <InputError class="mt-2" :message="form.errors.dependencia" />
                  </div>

                  <!-- Número de Llamado -->
                  <div>
                    <InputLabel for="nro_llamado" value="Número de Llamado *" />
                    <TextInput
                      id="nro_llamado"
                      type="number"
                      class="mt-1 block w-full"
                      v-model="form.nro_llamado"
                      min="1"
                      required
                    />
                    <InputError class="mt-2" :message="form.errors.nro_llamado" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                      Próximo número sugerido: {{ proximoNroLlamado }}
                    </p>
                  </div>

                  <!-- Plazo de Pago -->
                  <div>
                    <InputLabel for="plazo_pago" value="Plazo de Pago" />
                    <TextInput
                      id="plazo_pago"
                      type="text"
                      class="mt-1 block w-full"
                      v-model="form.plazo_pago"
                      placeholder="Ej: 30 días, contado, etc."
                    />
                    <InputError class="mt-2" :message="form.errors.plazo_pago" />
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
                      placeholder="Ej: Contado, transferencia, etc."
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
                      placeholder="Observaciones del presupuesto..."
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.observaciones" />
                  </div>

                  <!-- Comentario -->
                  <div class="md:col-span-2">
                    <InputLabel for="comentario" value="Comentario" />
                    <textarea
                      id="comentario"
                      class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                      v-model="form.comentario"
                      rows="3"
                      placeholder="Comentarios adicionales (opcional)..."
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.comentario" />
                  </div>
                </div>

                <!-- Resumen de insumos consolidados -->
                <div class="mb-6">
                  <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Insumos del Presupuesto
                  </h3>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Los precios se completarán automáticamente con los valores de las notas de pedido. Puede modificarlos según corresponda.
                  </p>

                  <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                      <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Renglón
                          </th>
                          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Insumo
                          </th>
                          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Cantidad
                          </th>
                          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Precio Unit.
                          </th>
                          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
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
                            <div class="font-medium">{{ detalle.insumo?.descripcion }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                              {{ detalle.insumo?.codigo }}
                            </div>
                          </td>
                          <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                            {{ formatNumber(detalle.cantidad) }}
                          </td>
                          <td class="px-4 py-3">
                            <TextInput
                              type="number"
                              step="0.01"
                              min="0"
                              v-model="detalle.precio_unitario"
                              class="w-full"
                              @input="calcularSubtotal(index)"
                            />
                          </td>
                          <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                            ${{ formatNumber(detalle.subtotal || 0) }}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- Total -->
                  <div class="mt-4 text-right">
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                      Total: ${{ formatNumber(totalPresupuesto) }}
                    </div>
                  </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-between">
                  <button
                    type="button"
                    @click="goBackToOrderSelection"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                  >
                    Volver
                  </button>
                  
                  <PrimaryButton :disabled="form.processing">
                    {{ form.processing ? 'Guardando...' : 'Crear Presupuesto' }}
                  </PrimaryButton>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

const props = defineProps({
  title: String,
  notasPedido: {
    type: Array,
    default: () => []
  },
  notasSeleccionadas: {
    type: Array,
    default: () => []
  },
  notasPedidoIds: {
    type: Array,
    default: () => []
  },
  ordenRenglones: String,
  proveedores: Array,
  tiposCompra: Array,
  ejercicioActual: Number,
  proximoNroLlamado: {
    type: Number,
    default: 1
  },
  step: {
    type: String,
    default: 'select_notes'
  }
})

const breadcrumbs = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'Presupuestos', href: '/presupuestos' },
  { name: 'Nuevo Presupuesto', href: null }
]

// Estado para búsqueda y selección de notas
const searchTerm = ref('')
const selectedNotaIds = ref([...props.notasPedidoIds])
const selectedOrdenRenglones = ref(props.ordenRenglones || '')

// Notas filtradas
const filteredNotas = computed(() => {
  if (!searchTerm.value) return props.notasPedido
  
  const term = searchTerm.value.toLowerCase()
  return props.notasPedido.filter(nota => 
    nota.numero_nota.toString().includes(term) ||
    nota.descripcion.toLowerCase().includes(term) ||
    (nota.expediente && nota.expediente.toLowerCase().includes(term)) ||
    nota.oficina?.nombre.toLowerCase().includes(term)
  )
})

// Preparar detalles del formulario consolidando insumos de múltiples notas
const prepararDetallesFormulario = () => {
  if (!props.notasSeleccionadas?.length) return []
  
  const insumosMap = new Map()
  let renglon = 1
  
  // Decidir el orden basado en la selección del usuario
  const notas = [...props.notasSeleccionadas]
  
  if (props.ordenRenglones === 'codigo_insumo') {
    // Recopilar todos los detalles y ordenar por código de insumo
    const todosLosDetalles = []
    notas.forEach(nota => {
      nota.detalles?.forEach(detalle => {
        todosLosDetalles.push({
          ...detalle,
          nota_pedido_id: nota.id,
          nota_numero: `${nota.numero_nota}/${nota.ejercicio_nota}`
        })
      })
    })
    
    // Ordenar por código de insumo
    todosLosDetalles.sort((a, b) => a.insumo?.codigo.localeCompare(b.insumo?.codigo))
    
    // Consolidar insumos iguales
    todosLosDetalles.forEach(detalle => {
      const key = detalle.insumo_id
      if (insumosMap.has(key)) {
        const existing = insumosMap.get(key)
        existing.cantidad += detalle.cantidad
        existing.notas_origen.push(detalle.nota_numero)
      } else {
        insumosMap.set(key, {
          insumo_id: detalle.insumo_id,
          insumo: detalle.insumo,
          renglon: renglon++,
          cantidad: detalle.cantidad,
          precio_unitario: detalle.precio_unitario || 0,
          unidad_medida: detalle.insumo?.unidad_medida || '',
          especificaciones_tecnicas: '',
          marca: '',
          modelo: '',
          observaciones: '',
          nota_pedido_id: detalle.nota_pedido_id,
          notas_origen: [detalle.nota_numero],
          subtotal: 0
        })
      }
    })
  } else {
    // Agrupar por nota de pedido, luego por insumo
    notas.forEach(nota => {
      nota.detalles?.forEach(detalle => {
        const key = `${nota.id}_${detalle.insumo_id}`
        if (!insumosMap.has(key)) {
          insumosMap.set(key, {
            insumo_id: detalle.insumo_id,
            insumo: detalle.insumo,
            renglon: renglon++,
            cantidad: detalle.cantidad,
            precio_unitario: detalle.precio_unitario || 0,
            unidad_medida: detalle.insumo?.unidad_medida || '',
            especificaciones_tecnicas: '',
            marca: '',
            modelo: '',
            observaciones: '',
            nota_pedido_id: nota.id,
            notas_origen: [`${nota.numero_nota}/${nota.ejercicio_nota}`],
            subtotal: 0
          })
        }
      })
    })
  }
  
  return Array.from(insumosMap.values())
}

const form = useForm({
  notas_pedido_ids: selectedNotaIds.value,
  tipo_compra_id: '',
  proveedor_id: '',
  lugar_entrega: '',
  domicilio_entrega: '',
  dependencia: '',
  nro_llamado: props.proximoNroLlamado,
  plazo_pago: '',
  comentario: '',
  orden_renglones: props.ordenRenglones || '',
  plazo_entrega: '',
  forma_pago: '',
  validez_oferta: '',
  observaciones: '',
  detalles: prepararDetallesFormulario()
})

// Cálculos
const totalPresupuesto = computed(() => {
  return form.detalles.reduce((total, detalle) => {
    return total + (detalle.subtotal || 0)
  }, 0)
})

// Métodos
const toggleSelectAll = () => {
  if (selectedNotaIds.value.length === filteredNotas.value.length) {
    selectedNotaIds.value = []
  } else {
    selectedNotaIds.value = filteredNotas.value.map(nota => nota.id)
  }
}

const proceedToNextStep = () => {
  if (selectedNotaIds.value.length > 1) {
    // Redirigir al paso de selección de orden
    const params = new URLSearchParams()
    selectedNotaIds.value.forEach(id => params.append('notas_pedido_ids[]', id))
    router.get(`${route('presupuestos.create')}?${params.toString()}`)
  } else {
    // Ir directamente a crear presupuesto
    const params = new URLSearchParams()
    selectedNotaIds.value.forEach(id => params.append('notas_pedido_ids[]', id))
    params.append('orden_renglones', 'codigo_insumo') // Default para una sola nota
    router.get(`${route('presupuestos.create')}?${params.toString()}`)
  }
}

const proceedToBudgetCreation = () => {
  const params = new URLSearchParams()
  selectedNotaIds.value.forEach(id => params.append('notas_pedido_ids[]', id))
  params.append('orden_renglones', selectedOrdenRenglones.value)
  router.get(`${route('presupuestos.create')}?${params.toString()}`)
}

const goBackToNoteSelection = () => {
  router.get(route('presupuestos.create'))
}

const goBackToOrderSelection = () => {
  const params = new URLSearchParams()
  selectedNotaIds.value.forEach(id => params.append('notas_pedido_ids[]', id))
  router.get(`${route('presupuestos.create')}?${params.toString()}`)
}

const calcularSubtotal = (index) => {
  const detalle = form.detalles[index]
  detalle.subtotal = detalle.cantidad * (detalle.precio_unitario || 0)
}

const submit = () => {
  // Actualizar IDs de notas seleccionadas en el formulario
  form.notas_pedido_ids = selectedNotaIds.value
  form.orden_renglones = props.ordenRenglones
  
  // Calcular subtotales finales
  form.detalles.forEach((detalle, index) => {
    calcularSubtotal(index)
  })
  
  form.post(route('presupuestos.store'))
}

const formatNumber = (number) => {
  if (!number) return '0.00'
  return new Intl.NumberFormat('es-AR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(number)
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('es-AR')
}

// Calcular subtotales iniciales
nextTick(() => {
  form.detalles.forEach((detalle, index) => {
    calcularSubtotal(index)
  })
})
</script>