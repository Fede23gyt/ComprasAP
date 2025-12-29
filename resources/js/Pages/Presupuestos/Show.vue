<template>
  <AppLayout>
    <Head :title="title" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-gray-300 dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                {{ title }}
              </h2>

              <div class="flex space-x-2">
                <Link
                  :href="route('presupuestos.index')"
                  class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Volver
                </Link>

                <Link
                  v-if="presupuesto.estado === 'borrador'"
                  :href="route('presupuestos.edit', presupuesto.id)"
                  class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Editar
                </Link>

                <button
                  v-if="presupuesto.estado === 'borrador'"
                  @click="enviarPresupuesto"
                  class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Confirmar
                </button>

                <button
                  v-if="presupuesto.estado === 'enviado' && canApprove()"
                  @click="aprobarPresupuesto"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Aprobar
                </button>

                <button
                  v-if="presupuesto.estado === 'enviado' && canApprove()"
                  @click="rechazarPresupuesto"
                  class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Rechazar
                </button>

                <button
                  v-if="presupuesto.estado === 'enviado' || presupuesto.estado === 'aprobado'"
                  @click="marcarDesierta"
                  class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Marcar Desierta
                </button>

                <button
                  v-if="presupuesto.estado === 'enviado' || presupuesto.estado === 'aprobado'"
                  @click="cerrarPresupuesto"
                  class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Cerrar
                </button>

                <button
                  v-if="presupuesto.estado === 'desierta'"
                  @click="reabrirPresupuesto"
                  class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Reabrir como Nuevo Llamado
                </button>

                <Link
                  v-if="presupuesto.nota_pedido_id && presupuesto.ofertas_count > 0"
                  :href="route('presupuestos.comparar', presupuesto.nota_pedido_id)"
                  class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Comparar Ofertas
                </Link>
                <button
                  v-else-if="presupuesto.nota_pedido_id && presupuesto.ofertas_count === 0"
                  disabled
                  class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed opacity-50"
                  title="Sin ofertas para comparar"
                >
                  Comparar Ofertas
                </button>

                <button
                  @click="imprimirPDF"
                  class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                  </svg>
                  PDF
                </button>
              </div>
            </div>

            <!-- Información general -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</h3>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                  <span :class="getEstadoClass(presupuesto.estado)">
                    {{ getEstadoTexto(presupuesto.estado) }}
                  </span>
                </p>
              </div>

              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha</h3>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                  {{ formatDate(presupuesto.fecha_presupuesto) }}
                </p>
              </div>

              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total</h3>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                  ${{ formatNumber(presupuesto.total_presupuesto) }}
                </p>
              </div>

              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Creado por</h3>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                  {{ presupuesto.usuario?.name }}
                </p>
              </div>
            </div>

            <!-- Información detallada -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
              <!-- Información de la nota de pedido -->
              <div class="bg-blue-100 dark:bg-blue-900/20 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-blue-800 dark:text-blue-200 mb-4">
                  Nota de Pedido
                </h3>
                <div class="space-y-2 dark:text-gray-100">
                  <p><span class="font-medium">Número:</span> {{ presupuesto.nota_pedido?.numero_nota }}/{{ presupuesto.nota_pedido?.ejercicio_nota }}</p>
                  <p><span class="font-medium">Descripción:</span> {{ presupuesto.nota_pedido?.descripcion }}</p>
                  <p><span class="font-medium">Oficina:</span> {{ presupuesto.nota_pedido?.oficina?.nombre }}</p>
                  <p><span class="font-medium">Tipo:</span> {{ presupuesto.nota_pedido?.tipo_nota?.nombre }}</p>
                </div>
              </div>

              <!-- Condiciones Generales -->
              <div class="bg-green-100 dark:bg-green-900/20 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-green-800 dark:text-green-100 mb-4">
                  Condiciones Generales
                </h3>
                <div class="space-y-2 dark:text-gray-100">
                  <p><span class="font-medium">Tipo de Compra:</span> {{ presupuesto.tipo_compra?.descripcion || 'N/A' }}</p>
                  <p><span class="font-medium">Nro. de Llamado:</span> {{ presupuesto.nro_llamado }}</p>
                  <p><span class="font-medium">Lugar de Entrega:</span> {{ presupuesto.lugar_entrega || 'No especificado' }}</p>
                  <p><span class="font-medium">Domicilio de Entrega:</span> {{ presupuesto.domicilio_entrega || 'No especificado' }}</p>
                  <p><span class="font-medium">Dependencia:</span> {{ presupuesto.dependencia || 'No especificada' }}</p>
                  <p><span class="font-medium">Plazo de Pago:</span> {{ presupuesto.plazo_pago || 'No especificado' }}</p>
                </div>
              </div>
            </div>

            <!-- Condiciones comerciales -->
            <div class="bg-yellow-100 dark:bg-yellow-900/20 p-4 rounded-lg mb-8">
              <h3 class="text-lg font-medium text-yellow-800 dark:text-yellow-200 mb-4">
                Condiciones Comerciales
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <p class="font-medium dark:text-gray-100">Plazo de entrega:</p>
                  <p class="font-medium dark:text-gray-100">{{ presupuesto.plazo_entrega ? presupuesto.plazo_entrega + ' días' : 'No especificado' }}</p>
                </div>
                <div>
                  <p class="font-medium dark:text-gray-100">Forma de pago:</p>
                  <p class="font-medium dark:text-gray-100">{{ presupuesto.forma_pago || 'No especificada' }}</p>
                </div>
                <div>
                  <p class="font-medium dark:text-gray-100">Validez de oferta:</p>
                  <p class="font-medium dark:text-gray-100">{{ presupuesto.validez_oferta ? presupuesto.validez_oferta + ' días' : 'No especificada' }}</p>
                </div>
              </div>
            </div>

            <!-- Observaciones y Comentarios -->
            <div v-if="presupuesto.observaciones || presupuesto.comentario" class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg mb-8">
              <div v-if="presupuesto.observaciones" class="mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                  Observaciones
                </h3>
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ presupuesto.observaciones }}</p>
              </div>

              <div v-if="presupuesto.comentario">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                  Comentarios
                </h3>
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ presupuesto.comentario }}</p>
              </div>
            </div>

            <!-- Detalles del presupuesto -->
            <div class="mb-8">
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Detalles del Presupuesto
              </h3>

              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Renglón
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Insumo
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Cantidad
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Precio Unitario
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Total
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Unidad
                      </th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Marca/Modelo
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="detalle in (presupuesto.detalles || [])" :key="detalle.id">
                      <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                        {{ detalle.renglon }}
                      </td>
                      <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                        <div>
                          <div class="font-medium">{{ detalle.insumo?.descripcion }}</div>
                          <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ detalle.insumo?.codigo }}
                          </div>
                        </div>
                      </td>
                      <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                        {{ formatNumber(detalle.cantidad) }}
                      </td>
                      <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                        ${{ formatNumber(detalle.precio_unitario) }}
                      </td>
                      <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                        ${{ formatNumber(detalle.total_renglon) }}
                      </td>
                      <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                        {{ detalle.unidad_medida || detalle.insumo?.unidad_medida }}
                      </td>
                      <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">
                        <div v-if="detalle.marca || detalle.modelo">
                          <div>{{ detalle.marca }}</div>
                          <div class="text-xs text-gray-500 dark:text-gray-400">{{ detalle.modelo }}</div>
                        </div>
                        <span v-else class="text-gray-400">-</span>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                      <td colspan="4" class="px-4 py-2 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                        Total General:
                      </td>
                      <td class="px-4 py-2 text-sm font-bold text-gray-900 dark:text-gray-100">
                        ${{ formatNumber(presupuesto.total_presupuesto) }}
                      </td>
                      <td colspan="2"></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>

            <!-- Especificaciones técnicas y observaciones de detalles -->
            <div v-if="hasDetallesConInfoAdicional" class="mb-8">
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Información Adicional de los Detalles
              </h3>

              <div v-for="detalle in (presupuesto.detalles || [])" :key="'info-' + detalle.id"
                   v-if="detalle && (detalle.especificaciones_tecnicas || detalle.observaciones)"
                   class="mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">
                  Renglón {{ detalle.renglon }} - {{ detalle.insumo?.descripcion }}
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div v-if="detalle.especificaciones_tecnicas">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Especificaciones Técnicas:</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ detalle.especificaciones_tecnicas }}</p>
                  </div>

                  <div v-if="detalle.observaciones">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Observaciones:</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ detalle.observaciones }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Detalle del Memo Seleccionado -->
            <div v-if="presupuesto.memo" class="mb-8">
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Detalle del Memo
              </h3>
              
              <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Número de Memo:</p>
                    <p class="text-sm text-blue-700 dark:text-blue-300">{{ presupuesto.memo.numero }}</p>
                  </div>
                  
                  <div>
                    <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Fecha:</p>
                    <p class="text-sm text-blue-700 dark:text-blue-300">{{ formatDate(presupuesto.memo.fecha) }}</p>
                  </div>
                  
                  <div class="md:col-span-2">
                    <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Asunto:</p>
                    <p class="text-sm text-blue-700 dark:text-blue-300">{{ presupuesto.memo.asunto }}</p>
                  </div>
                  
                  <div v-if="presupuesto.memo.descripcion" class="md:col-span-2">
                    <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Descripción:</p>
                    <p class="text-sm text-blue-700 dark:text-blue-300 whitespace-pre-wrap">{{ presupuesto.memo.descripcion }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para aprobar -->
    <Modal :show="showAprobarModal" @close="showAprobarModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
          Aprobar Presupuesto
        </h2>

        <form @submit.prevent="confirmarAprobacion">
          <div class="mb-4">
            <InputLabel for="observaciones_aprobacion" value="Observaciones (opcional)" />
            <textarea
              id="observaciones_aprobacion"
              class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
              v-model="formAprobacion.observaciones"
              rows="3"
              placeholder="Observaciones de la aprobación..."
            ></textarea>
          </div>

          <div class="flex justify-end space-x-4">
            <SecondaryButton @click="showAprobarModal = false">
              Cancelar
            </SecondaryButton>
            <PrimaryButton type="submit">
              Confirmar Aprobación
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Modal para rechazar -->
    <Modal :show="showRechazarModal" @close="showRechazarModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
          Rechazar Presupuesto
        </h2>

        <form @submit.prevent="confirmarRechazo">
          <div class="mb-4">
            <InputLabel for="motivo_rechazo" value="Motivo del rechazo *" />
            <textarea
              id="motivo_rechazo"
              class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
              v-model="formRechazo.motivo"
              rows="3"
              placeholder="Describa el motivo del rechazo..."
              required
            ></textarea>
          </div>

          <div class="flex justify-end space-x-4">
            <SecondaryButton @click="showRechazarModal = false">
              Cancelar
            </SecondaryButton>
            <PrimaryButton type="submit">
              Confirmar Rechazo
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Modal para marcar como desierta -->
    <Modal :show="showDesierterModal" @close="showDesierterModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
          Marcar Presupuesto como Desierta
        </h2>

        <form @submit.prevent="confirmarMarcarDesierta">
          <div class="mb-4">
            <InputLabel for="motivo_desierta" value="Motivo (opcional)" />
            <textarea
              id="motivo_desierta"
              class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
              v-model="formDesierta.motivo"
              rows="3"
              placeholder="Describa el motivo por el cual se declara desierta..."
            ></textarea>
          </div>

          <div class="flex justify-end space-x-4">
            <SecondaryButton @click="showDesierterModal = false">
              Cancelar
            </SecondaryButton>
            <PrimaryButton type="submit" class="bg-orange-600 hover:bg-orange-700">
              Confirmar Desierta
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Modal para cerrar presupuesto -->
    <Modal :show="showCerrarModal" @close="showCerrarModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
          Cerrar Presupuesto
        </h2>

        <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md">
          <p class="text-sm text-yellow-800 dark:text-yellow-200">
            <strong>Atención:</strong> Al cerrar este presupuesto, todos los insumos asociados serán liberados y podrán ser utilizados en futuros presupuestos.
          </p>
        </div>

        <form @submit.prevent="confirmarCerrar">
          <div class="mb-4">
            <InputLabel for="motivo_cerrar" value="Motivo (opcional)" />
            <textarea
              id="motivo_cerrar"
              class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
              v-model="formCerrar.motivo"
              rows="3"
              placeholder="Describa el motivo del cierre..."
            ></textarea>
          </div>

          <div class="flex justify-end space-x-4">
            <SecondaryButton @click="showCerrarModal = false">
              Cancelar
            </SecondaryButton>
            <PrimaryButton type="submit" class="bg-gray-600 hover:bg-gray-700">
              Confirmar Cierre
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps({
  title: String,
  presupuesto: Object,
  auth: Object
})

// Verificar si el usuario puede aprobar (director o administrador)
const canApprove = () => {
  return props.auth?.user?.roles?.some(role =>
    role.name === 'director' || role.name === 'administrador'
  ) || false
}

const showAprobarModal = ref(false)
const showRechazarModal = ref(false)
const showDesierterModal = ref(false)
const showCerrarModal = ref(false)

const formAprobacion = ref({
  observaciones: ''
})

const formRechazo = ref({
  motivo: ''
})

const formDesierta = ref({
  motivo: ''
})

const formCerrar = ref({
  motivo: ''
})

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('es-AR')
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
    rechazado: 'Rechazado',
    desierta: 'Desierta',
    cerrada: 'Cerrada'
  }
  return estados[estado] || estado
}

const getEstadoClass = (estado) => {
  const clases = {
    borrador: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 px-2 py-1 rounded text-sm',
    enviado: 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100 px-2 py-1 rounded text-sm',
    aprobado: 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100 px-2 py-1 rounded text-sm',
    rechazado: 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100 px-2 py-1 rounded text-sm',
    desierta: 'bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-100 px-2 py-1 rounded text-sm',
    cerrada: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 px-2 py-1 rounded text-sm'
  }
  return clases[estado] || 'bg-gray-100 text-gray-800 px-2 py-1 rounded text-sm'
}

const hasDetallesConInfoAdicional = () => {
  return props.presupuesto.detalles?.some(detalle =>
    detalle.especificaciones_tecnicas || detalle.observaciones
  )
}

const enviarPresupuesto = () => {
  if (confirm('¿Está seguro de confirmar este presupuesto? Una vez confirmado no podrá ser editado.')) {
    router.post(route('presupuestos.enviar', props.presupuesto.id), {}, {
      preserveScroll: true,
      onSuccess: () => {
        router.reload()
      }
    })
  }
}

const aprobarPresupuesto = () => {
  showAprobarModal.value = true
}

const rechazarPresupuesto = () => {
  showRechazarModal.value = true
}

const confirmarAprobacion = () => {
  router.post(route('presupuestos.aprobar', props.presupuesto.id), formAprobacion.value, {
    preserveScroll: true,
    onSuccess: () => {
      showAprobarModal.value = false
      router.reload()
    }
  })
}

const confirmarRechazo = () => {
  router.post(route('presupuestos.rechazar', props.presupuesto.id), formRechazo.value, {
    preserveScroll: true,
    onSuccess: () => {
      showRechazarModal.value = false
      router.reload()
    }
  })
}

const marcarDesierta = () => {
  showDesierterModal.value = true
}

const cerrarPresupuesto = () => {
  showCerrarModal.value = true
}

const reabrirPresupuesto = () => {
  if (confirm('¿Está seguro de reabrir este presupuesto como un nuevo llamado?')) {
    router.post(route('presupuestos.reabrir', props.presupuesto.id), {}, {
      preserveScroll: true,
      onSuccess: () => {
        router.reload()
      }
    })
  }
}

const confirmarMarcarDesierta = () => {
  router.post(route('presupuestos.marcar-desierta', props.presupuesto.id), formDesierta.value, {
    preserveScroll: true,
    onSuccess: () => {
      showDesierterModal.value = false
      router.reload()
    }
  })
}

const confirmarCerrar = () => {
  router.post(route('presupuestos.cerrar', props.presupuesto.id), formCerrar.value, {
    preserveScroll: true,
    onSuccess: () => {
      showCerrarModal.value = false
      router.reload()
    }
  })
}

const imprimirPDF = () => {
  window.open(route('presupuestos.pdf', props.presupuesto.id), '_blank')
}
</script>
