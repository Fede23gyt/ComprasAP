<template>
  <AppLayout>
    <Head :title="title" />

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                {{ title }}
              </h2>
              
              <div class="flex space-x-2">
                <Link
                  :href="route('presupuestos.index')"
                  class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Ver Presupuestos
                </Link>
                
                <button
                  @click="mostrarModalNuevaOferta"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                  Nueva Oferta
                </button>
              </div>
            </div>

            <!-- Filtros -->
            <div class="mb-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
              <form @submit.prevent="aplicarFiltros" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                  <InputLabel for="busqueda" value="Buscar" />
                  <TextInput
                    id="busqueda"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="filtros.busqueda"
                    placeholder="Proveedor, presupuesto, nota..."
                  />
                </div>

                <div>
                  <InputLabel for="estado" value="Estado" />
                  <select
                    id="estado"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    v-model="filtros.estado"
                  >
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="evaluando">En evaluación</option>
                    <option value="aprobada">Aprobada</option>
                    <option value="rechazada">Rechazada</option>
                    <option value="adjudicada">Adjudicada</option>
                  </select>
                </div>

                <div>
                  <InputLabel for="proveedor" value="Proveedor" />
                  <TextInput
                    id="proveedor"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="filtros.proveedor"
                    placeholder="Nombre del proveedor"
                  />
                </div>

                <div class="flex items-end space-x-2">
                  <PrimaryButton type="submit">
                    Aplicar Filtros
                  </PrimaryButton>
                  
                  <button
                    type="button"
                    @click="limpiarFiltros"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                  >
                    Limpiar
                  </button>
                </div>
              </form>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
              <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Total Ofertas</h3>
                <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">24</p>
              </div>
              
              <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Aprobadas</h3>
                <p class="text-2xl font-bold text-green-900 dark:text-green-100">8</p>
              </div>
              
              <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">En Evaluación</h3>
                <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">6</p>
              </div>
              
              <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Rechazadas</h3>
                <p class="text-2xl font-bold text-red-900 dark:text-red-100">3</p>
              </div>
            </div>

            <!-- Tabla de ofertas -->
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      N° Oferta
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Presupuesto
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Proveedor
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Fecha Oferta
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Monto
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Estado
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                      Acciones
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <!-- Oferta de ejemplo 1 -->
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                      OF-2024-001
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                      <div>
                        <div>PR-2024-015/2024</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Nota: NP-2024-123</div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                      <div>
                        <div class="font-medium">Proveedor Ejemplo S.A.</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">CUIT: 30-12345678-9</div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                      15/01/2024
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                      <div class="font-semibold">$45,250.00</div>
                      <div class="text-xs text-green-600 dark:text-green-400">-5.2% vs. ref.</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                        Aprobada
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex space-x-2">
                        <button class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                          Ver
                        </button>
                        <button class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                          Evaluar
                        </button>
                        <button class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300">
                          Comparar
                        </button>
                      </div>
                    </td>
                  </tr>

                  <!-- Oferta de ejemplo 2 -->
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                      OF-2024-002
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                      <div>
                        <div>PR-2024-016/2024</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Nota: NP-2024-124</div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                      <div>
                        <div class="font-medium">Distribuidora Comercial S.R.L.</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">CUIT: 33-87654321-0</div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                      16/01/2024
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                      <div class="font-semibold">$38,750.00</div>
                      <div class="text-xs text-green-600 dark:text-green-400">-12.8% vs. ref.</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                        En Evaluación
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex space-x-2">
                        <button class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                          Ver
                        </button>
                        <button class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                          Evaluar
                        </button>
                        <button class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                          Rechazar
                        </button>
                      </div>
                    </td>
                  </tr>

                  <!-- Oferta de ejemplo 3 -->
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                      OF-2024-003
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                      <div>
                        <div>PR-2024-017/2024</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Nota: NP-2024-125</div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                      <div>
                        <div class="font-medium">Importadora del Norte S.A.</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">CUIT: 30-55555555-5</div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                      17/01/2024
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                      <div class="font-semibold">$52,300.00</div>
                      <div class="text-xs text-red-600 dark:text-red-400">+15.3% vs. ref.</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                        Rechazada
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex space-x-2">
                        <button class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                          Ver
                        </button>
                        <button class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300">
                          Reactivar
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Paginación -->
            <div class="mt-6 flex justify-between items-center">
              <div class="text-sm text-gray-700 dark:text-gray-300">
                Mostrando 3 de 24 ofertas
              </div>
              
              <div class="flex space-x-2">
                <button class="px-3 py-1 text-sm bg-gray-300 dark:bg-gray-600 rounded disabled:opacity-50">
                  Anterior
                </button>
                <button class="px-3 py-1 text-sm bg-blue-600 text-white rounded">
                  1
                </button>
                <button class="px-3 py-1 text-sm bg-gray-300 dark:bg-gray-600 rounded">
                  2
                </button>
                <button class="px-3 py-1 text-sm bg-gray-300 dark:bg-gray-600 rounded">
                  Siguiente
                </button>
              </div>
            </div>

            <!-- Información adicional -->
            <div class="mt-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                Información del Módulo de Ofertas
              </h3>
              <p class="text-sm text-gray-700 dark:text-gray-300">
                Este módulo permite gestionar las ofertas recibidas de proveedores para los presupuestos generados. 
                Aquí podrá cargar nuevas ofertas, evaluarlas, comparar precios y realizar el seguimiento del proceso 
                de adjudicación.
              </p>
              
              <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                  <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Próximas funcionalidades:</h4>
                  <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-1">
                    <li>Carga masiva de ofertas via Excel/CSV</li>
                    <li>Comparativa automática de precios</li>
                    <li>Evaluación técnica y económica</li>
                    <li>Generación de actas de adjudicación</li>
                  </ul>
                </div>
                
                <div>
                  <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Estados disponibles:</h4>
                  <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-1">
                    <li><span class="text-yellow-600 dark:text-yellow-400">Pendiente</span> - Oferta recibida</li>
                    <li><span class="text-blue-600 dark:text-blue-400">En Evaluación</span> - En proceso de análisis</li>
                    <li><span class="text-green-600 dark:text-green-400">Aprobada</span> - Cumple requisitos</li>
                    <li><span class="text-red-600 dark:text-red-400">Rechazada</span> - No cumple requisitos</li>
                    <li><span class="text-purple-600 dark:text-purple-400">Adjudicada</span> - Ganadora del proceso</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para nueva oferta -->
    <Modal :show="mostrarModal" @close="mostrarModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
          Nueva Oferta de Proveedor
        </h2>
        
        <form @submit.prevent="crearOferta">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <InputLabel for="presupuesto_id" value="Presupuesto *" />
              <select
                id="presupuesto_id"
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                v-model="formOferta.presupuesto_id"
                required
              >
                <option value="">Seleccionar presupuesto</option>
                <option value="1">PR-2024-015/2024 - Nota NP-2024-123</option>
                <option value="2">PR-2024-016/2024 - Nota NP-2024-124</option>
                <option value="3">PR-2024-017/2024 - Nota NP-2024-125</option>
              </select>
            </div>

            <div>
              <InputLabel for="proveedor_id" value="Proveedor *" />
              <select
                id="proveedor_id"
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                v-model="formOferta.proveedor_id"
                required
              >
                <option value="">Seleccionar proveedor</option>
                <option value="1">Proveedor Ejemplo S.A. (30-12345678-9)</option>
                <option value="2">Distribuidora Comercial S.R.L. (33-87654321-0)</option>
                <option value="3">Importadora del Norte S.A. (30-55555555-5)</option>
              </select>
            </div>

            <div>
              <InputLabel for="monto_oferta" value="Monto Ofertado *" />
              <TextInput
                id="monto_oferta"
                type="number"
                class="mt-1 block w-full"
                v-model="formOferta.monto_oferta"
                step="0.01"
                min="0"
                placeholder="0.00"
                required
              />
            </div>

            <div>
              <InputLabel for="fecha_oferta" value="Fecha Oferta *" />
              <TextInput
                id="fecha_oferta"
                type="date"
                class="mt-1 block w-full"
                v-model="formOferta.fecha_oferta"
                required
              />
            </div>

            <div class="md:col-span-2">
              <InputLabel for="observaciones" value="Observaciones" />
              <textarea
                id="observaciones"
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                v-model="formOferta.observaciones"
                rows="3"
                placeholder="Observaciones sobre la oferta..."
              ></textarea>
            </div>
          </div>
          
          <div class="flex justify-end space-x-4">
            <SecondaryButton @click="mostrarModal = false">
              Cancelar
            </SecondaryButton>
            <PrimaryButton type="submit">
              Crear Oferta
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps({
  title: String
})

const mostrarModal = ref(false)
const filtros = ref({
  busqueda: '',
  estado: '',
  proveedor: ''
})

const formOferta = ref({
  presupuesto_id: '',
  proveedor_id: '',
  monto_oferta: '',
  fecha_oferta: '',
  observaciones: ''
})

const aplicarFiltros = () => {
  console.log('Aplicando filtros:', filtros.value)
  // Aquí se conectaría con la API para filtrar las ofertas
}

const limpiarFiltros = () => {
  filtros.value = {
    busqueda: '',
    estado: '',
    proveedor: ''
  }
}

const mostrarModalNuevaOferta = () => {
  // Resetear formulario
  formOferta.value = {
    presupuesto_id: '',
    proveedor_id: '',
    monto_oferta: '',
    fecha_oferta: new Date().toISOString().split('T')[0],
    observaciones: ''
  }
  mostrarModal.value = true
}

const crearOferta = () => {
  console.log('Creando oferta:', formOferta.value)
  // Aquí se conectaría con la API para crear la oferta
  mostrarModal.value = false
  
  // Simular éxito
  alert('Oferta creada exitosamente')
}
</script>