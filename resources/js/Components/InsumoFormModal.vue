<!-- Form Content -->
<div class="p-6">
<form @submit.prevent="submitForm" class="space-y-6">
  <!-- Fila 1: Código y Clasificación -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Código -->
    <div>
      <label
        for="codigo"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
      >
        Código <span class="text-red-500" v-if="!isReadOnly">*</span>
      </label>
      <input
        ref="codigoInput"
        id="codigo"
        v-model="form.codigo"
        type="text"
        :readonly="isReadOnly"
        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-150"
        :class="{
                  'border-red-500': form.errors.codigo,
                  'bg-gray-50 dark:bg-gray-600': isReadOnly
                }"
        placeholder="Ingresa el código del insumo"
        maxlength="50"
      />
      <div v-if="form.errors.codigo" class="mt-1 text-sm text-red-600 dark:text-red-400">
        {{ form.errors.codigo }}
      </div>
    </div>

    <!-- Clasificación -->
    <div>
      <label
        for="clasificacion"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
      >
        Clasificación
      </label>
      <input
        id="clasificacion"
        v-model="form.clasificacion"
        type="text"
        :readonly="isReadOnly"
        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-150"
        :class="{
                  'border-red-500': form.errors.clasificacion,
                  'bg-gray-50 dark:bg-gray-600': isReadOnly
                }"
        placeholder="Clasificación del insumo"
        maxlength="20"
      />
      <div v-if="form.errors.clasificacion" class="mt-1 text-sm text-red-600 dark:text-red-400">
        {{ form.errors.clasificacion }}
      </div>
    </div>
  </div>

  <!-- Descripción -->
  <div>
    <label
      for="descripcion"
      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
    >
      Descripción <span class="text-red-500" v-if="!isReadOnly">*</span>
    </label>
    <textarea
      id="descripcion"
      v-model="form.descripcion"
      :readonly="isReadOnly"
      rows="3"
      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-150 resize-none"
      :class="{
                'border-red-500': form.errors.descripcion,
                'bg-gray-50 dark:bg-gray-600': isReadOnly
              }"
      placeholder="Descripción detallada del insumo"
      maxlength="500"
    ></textarea>
    <div v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600 dark:text-red-400">
      {{ form.errors.descripcion }}
    </div>
    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
      {{ form.descripcion.length }}/500 caracteres
    </div>
  </div>

  <!-- Fila 2: Presentación y Unidad Solicitud -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Presentación -->
    <div>
      <label
        for="presentacion"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
      >
        Presentación
      </label>
      <input
        id="presentacion"
        v-model="form.presentacion"
        type="text"
        :readonly="isReadOnly"
        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-150"
        :class="{
                  'border-red-500': form.errors.presentacion,
                  'bg-gray-50 dark:bg-gray-600': isReadOnly
                }"
        placeholder="Ej: Caja, Unidad, Litro, etc."
        maxlength="50"
      />
      <div v-if="form.errors.presentacion" class="mt-1 text-sm text-red-600 dark:text-red-400">
        {{ form.errors.presentacion }}
      </div>
    </div>

    <!-- Unidad Solicitud -->
    <div>
      <label
        for="unidad_solicitud"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
      >
        Unidad de Solicitud
      </label>
      <input
        id="unidad_solicitud"
        v-model="form.unidad_solicitud"
        type="text"
        :readonly="isReadOnly"
        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-150"
        :class="{
                  'border-red-500': form.errors.unidad_solicitud,
                  'bg-gray-50 dark:bg-gray-600': isReadOnly
                }"
        placeholder="Ej: m², kg, unid, etc."
        maxlength="50"
      />
      <div v-if="form.errors.unidad_solicitud" class="mt-1 text-sm text-red-600 dark:text-red-400">
        {{ form.errors.unidad_solicitud }}
      </div>
    </div>
  </div>

  <!-- Fila 3: Precios -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Precio Insumo -->
    <div>
      <label
        for="precio_insumo"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
      >
        Precio del Insumo
      </label>
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <span class="text-gray-500 sm:text-sm">$</span>
        </div>
        <input
          id="precio_insumo"
          v-model="form.precio_insumo"
          type="number"
          step="0.01"
          min="0"
          :readonly="isReadOnly"
          class="w-full pl-7 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-150"
          :class="{
                    'border-red-500': form.errors.precio_insumo,
                    'bg-gray-50 dark:bg-gray-600': isReadOnly
                  }"
          placeholder="0.00"
        />
      </div>
      <div v-if="form.errors.precio_insumo" class="mt-1 text-sm text-red-600 dark:text-red-400">
        {{ form.errors.precio_insumo }}
      </div>
    </div>

    <!-- Conversión -->
    <div>
      <label
        for="conversion"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
      >
        Conversión
      </label>
      <input
        id="conversion"
        v-model="form.conversion"
        type="number"
        step="0.0001"
        min="0"
        :readonly="isReadOnly"
        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-150"
        :class="{
                  'border-red-500': form.errors.conversion,
                  'bg-gray-50 dark:bg-gray-600': isReadOnly
                }"
        placeholder="0.0000"
      />
      <div v-if="form.errors.conversion" class="mt-1 text-sm text-red-600 dark:text-red-400">
        {{ form.errors.conversion }}
      </div>
    </div>

    <!-- Fecha Última -->
    <div>
      <label
        for="fecha_ultima"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
      >
        Última Actualización
      </label>
      <input
        id="fecha_ultima"
        v-model="form.fecha_ultima"
        type="date"
        :readonly="isReadOnly"
        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-blue-500 transition-colors duration-150"
        :class="{
                  'border-red-500': form.errors.fecha_ultima,
                  'bg-gray-50 dark:bg-gray-600': isReadOnly
                }"
      />
      <div v-if="form.errors.fecha_ultima" class="mt-1 text-sm text-red-600 dark:text-red-400">
        {{ form.errors.fecha_ultima }}
      </div>
    </div>
  </div>

  <!-- Estados y Configuraciones -->
  <div class="space-y-4">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-600 pb-2">
      Configuraciones
    </h3>

    <!-- Fila de Toggles -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Registrable -->
      <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
        <div class="flex items-center justify-between mb-2">
          <label class="inline-flex items-center cursor-pointer" :class="{ 'cursor-not-allowed': isReadOnly }">
            <input
              v-model="form.registrable"
              type="checkbox"
              :disabled="isReadOnly"
              class="sr-only peer"
            />
            <div
              class="relative w-10 h-5 rounded-full transition-colors duration-200 ease-in-out after:content-[''] after:absolute after:top-0.5 after:w-4 after:h-4 after:bg-white after:border after:border-gray-300 after:rounded-full after:transition-all after:duration-200 after:ease-in-out"
              :class="[
                        form.registrable ? 'bg-green-500' : 'bg-red-500',
                        form.registrable ? 'after:left-[1.25rem]' : 'after:left-0.5',
                        isReadOnly ? 'opacity-60' : ''
                      ]"
            ></div>
          </label>
        </div>
        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
          Registrable
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">
          {{ form.registrable ? 'Puede ser registrado' : 'No puede ser registrado' }}
        </div>
      </div>

      <!-- Precio Testigo -->
      <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
        <div class="flex items-center justify-between mb-2">
          <label class="inline-flex items-center cursor-pointer" :class="{ 'cursor-not-allowed': isReadOnly }">
            <input
              v-model="form.precio_testigo"
              type="checkbox"
              :disabled="isReadOnly"
              class="sr-only peer"
            />
            <div
              class="relative w-10 h-5 rounded-full transition-colors duration-200 ease-in-out after:content-[''] after:absolute after:top-0.5 after:w-4 after:h-4 after:bg-white after:border after:border-gray-300 after:rounded-full after:transition-all after:duration-200 after:ease-in-out"
              :class="[
                        form.precio_testigo ? 'bg-green-500' : 'bg-red-500',
                        form.precio_testigo ? 'after:left-[1.25rem]' : 'after:left-0.5',
                        isReadOnly ? 'opacity-60' : ''
                      ]"
            ></div>
          </label>
        </div>
        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
          Precio Testigo
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">
          {{ form.precio_testigo ? 'Es precio testigo' : 'No es precio testigo' }}
        </div>
      </div>

      <!-- Inventariable -->
      <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
        <div class="flex items-center justify-between mb-2">
          <label class="inline-flex items-center cursor-pointer" :class="{ 'cursor-not-allowed': isReadOnly }">
            <input
              v-model="form.inventariable"
              type="checkbox"
              :disabled="isReadOnly"
              class="sr-only peer"
            />
            <div
              class="relative w-10 h-5 rounded-full transition-colors duration-200 ease-in-out after:content-[''] after:absolute after:top-0.5 after:w-4 after:h-4 after:bg-white after:border after:border-gray-300 after:rounded-full after:transition-all after:duration-200 after:ease-in-out"
              :class="[
                        form.inventariable ? 'bg-green-500' : 'bg-red-500',
                        form.inventariable ? 'after:left-[1.25rem]' : 'after:left-0.5',
                        isReadOnly ? 'opacity-60' : ''
                      ]"
            ></div>
          </label>
        </div>
        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
          Inventariable
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">
          {{ form.inventariable ? 'Lleva inventario' : 'No lleva inventario' }}
        </div>
      </div>

      <!-- Rinde Tribunal -->
      <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
        <div class="flex items-center justify-between mb-2">
          <label class="inline-flex items-center cursor-pointer" :class="{ 'cursor-not-allowed': isReadOnly }">
            <input
              v-model="form.rend_tribunal"
              type="checkbox"
              :disabled="isReadOnly"
              class="sr-only peer"
            />
            <div
              class="relative w-10 h-5 rounded-full transition-colors duration-200 ease-in-out after:content-[''] after:absolute after:top-0.5 after:w-4 after:h-4 after:bg-white after:border after:border-gray-300 after:rounded-full after:transition-all after:duration-200 after:ease-in-out"
              :class="[
                        form.rend_tribunal ? 'bg-green-500' : 'bg-red-500',
                        form.rend_tribunal ? 'after:left-[1.25rem]' : 'after:left-0.5',
                        isReadOnly ? 'opacity-60' : ''
                      ]"
            ></div>
          </label>
        </div>
        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
          Rinde Tribunal
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">
          {{ form.rend_tribunal ? 'Rinde al tribunal' : 'No rinde al tribunal' }}
        </div>
      </div>
    </div>
  </div>

  <!-- Información adicional si es modo view -->
  <div v-if="isReadOnly && item" class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-200 dark:border-gray-600">
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        Fecha de creación
      </label>
      <div class="text-sm text-gray-900 dark:text-gray-100">
        {{ item.created_at ? new Date(item.created_at).toLocaleString() : 'N/A' }}
      </div>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        Última actualización
      </label>
      <div class="text-sm text-gray-900 dark:text-gray-100">
        {{ item.updated_at ? new Date(item.updated_at).toLocaleString() : 'N/A' }}
      </div>
    </div>
  </div>
</form>
</div>
<div v-if="isReadOnly && item" class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-200 dark:border-gray-600">
<div>
  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
    Fecha de creación
  </label>
  <div class="text-sm text-gray-900 dark:text-gray-100">
    {{ item.created_at ? new Date(item.created_at).toLocaleString() : 'N/A' }}
  </div>
</div>
<div>
  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
    Última actualización
  </label>
  <div class="text-sm text-gray-900 dark:text-gray-100">
    {{ item.updated_at ? new Date(item.updated_at).toLocaleString() : 'N/A' }}
  </div>
</div>
</div>
</form>
</div>focus:border-blue-500 transition-colors duration-150"
:class="{
'border-red-500': form.errors.fecha_ultima,
'bg-gray-50 dark:bg-gray-600': isReadOnly
}"
/>
<div v-if="form.errors.fecha_ultima" class="mt-1 text-sm text-red-600 dark:text-red-400">
{{ form.errors.fecha_ultima }}
</div>
</div>
</div>

<!-- Estados y Configuraciones -->
<div class="space-y-4">
<h3 class="text-lg font-medium text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-600 pb-2">
  Configuraciones
</h3>

<!-- Fila de Toggles -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
  <!-- Registrable -->
  <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
    <div class="flex items-center justify-between mb-2">
      <label class="inline-flex items-center cursor-pointer" :class="{ 'cursor-not-allowed': isReadOnly }">
        <input
          v-model="form.registrable"
          type="checkbox"
          :disabled="isReadOnly"
          class="sr-only peer"
        />
        <div
          class="relative w-10 h-5 rounded-full transition-colors duration-200 ease-in-out after:content-[''] after:absolute after:top-0.5 after:w-4 after:h-4 after:bg-white after:border after:border-gray-300 after:rounded-full after:transition-all after:duration-200 after:ease-in-out"
          :class="[
                        form.registrable ? 'bg-green-500' : 'bg-red-500',
                        form.registrable ? 'after:left-[1.25rem]' : 'after:left-0.5',
                        isReadOnly ? 'opacity-60' : ''
                      ]"
        ></div>
      </label>
    </div>
    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
      Registrable
    </div>
    <div class="text-xs text-gray-500 dark:text-gray-400">
      {{ form.registrable ? 'Puede ser registrado' : 'No puede ser registrado' }}
    </div>
  </div>

  <!-- Precio Testigo -->
  <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
    <div class="flex items-center justify-between mb-2">
      <label class="inline-flex items-center cursor-pointer" :class="{ 'cursor-not-allowed': isReadOnly }">
        <input
          v-model="form.precio_testigo"
          type="checkbox"
          :disabled="isReadOnly"
          class="sr-only peer"
        />
        <div
          class="relative w-10 h-5 rounded-full transition-colors duration-200 ease-in-out after:content-[''] after:absolute after:top-0.5 after:w-4 after:h-4 after:bg-white after:border after:border-gray-300 after:rounded-full after:transition-all after:duration-200 after:ease-in-out"
          :class="[
                        form.precio_testigo ? 'bg-green-500' : 'bg-red-500',
                        form.precio_testigo ? 'after:left-[1.25rem]' : 'after:left-0.5',
                        isReadOnly ? 'opacity-60' : ''
                      ]"
        ></div>
      </label>
    </div>
    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
      Precio Testigo
    </div>
    <div class="text-xs text-gray-500 dark:text-gray-400">
      {{ form.precio_testigo ? 'Es precio testigo' : 'No es precio testigo' }}
    </div>
  </div>

  <!-- Inventariable -->
  <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
    <div class="flex items-center justify-between mb-2">
      <label class="inline-flex items-center cursor-pointer" :class="{ 'cursor-not-allowed': isReadOnly }">
        <input
          v-model="form.inventariable"
          type="checkbox"
          :disabled="isReadOnly"
          class="sr-only peer"
        />
        <div
          class="relative w-10 h-5 rounded-full transition-colors duration-200 ease-in-out after:content-[''] after:absolute after:top-0.5 after:w-4 after:h-4 after:bg-white after:border after:border-gray-300 after:rounded-full after:transition-all after:duration-200 after:ease-in-out"
          :class="[
                        form.inventariable ? 'bg-green-500' : 'bg-red-500',
                        form.inventariable ? 'after:left-[1.25rem]' : 'after:left-0.5',
                        isReadOnly ? 'opacity-60' : ''
                      ]"
        ></div>
      </label>
    </div>
    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
      Inventariable
    </div>
    <div class="text-xs text-gray-500 dark:text-gray-400">
      {{ form.inventariable ? 'Lleva inventario' : 'No lleva inventario' }}
    </div>
  </div>

  <!-- Rinde Tribunal -->
  <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
    <div class="flex items-center justify-between mb-2">
      <label class="inline-flex items-center cursor-pointer" :class="{ 'cursor-not-allowed': isReadOnly }">
        <input
          v-model="form.rend_tribunal"
          type="checkbox"
          :disabled="isReadOnly"
          class="sr-only peer"
        />
        <div
          class="relative w-10 h-5 rounded-full transition-colors duration-200 ease-in-out after:content-[''] after:absolute after:top-0.5 after:w-4 after:h-4 after:bg-white after:border after:border-gray-300 after:rounded-full after:transition-all after:duration-200 after:ease-in-out"
          :class="[
                        form.rend_tribunal ? 'bg-green-500' : 'bg-red-500',
                        form.rend_tribunal ? 'after:left-[1.25rem]' : 'after:left-0.5',
                        isReadOnly ? 'opacity-60' : ''
                      ]"
        ></div>
      </label>
    </div>
    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
      Rinde Tribunal
    </div>
    <div class="text-xs text-gray-500 dark:text-gray-400">
      {{ form.rend_tribunal ? 'Rinde al tribunal' : 'No rinde al tribunal' }}
    </div>
  </div>
</div>
</div>

<!-- Información adicional si es modo view -->
<div v-if="isReadOnly && item" class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-200 dark:border-gray-600">
<div>
  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
    Fecha de creación
  </label>
  <div class="text-sm text-gray-900 dark:text-gray-100">
    {{ item.created_at ? new Date(item.created_at).toLocaleString() : 'N/A' }}
  </div>
</div>
<div>
  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
    Última actualización
  </label>
  <div class="text-sm text-gray-900 dark:text-gray-100">
    {{ item.updated_at ? new Date(item.updated_at).toLocaleString() : 'N/A' }}
  </div>
</div>
</div>
<div v-if="isReadOnly && item<!-- resources/js/Components/InsumoFormModal.vue -->
<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, watch, computed, onUnmounted } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
  item: {
    type: Object,
    default: null
  },
  mode: {
    type: String,
    default: 'view', // 'view' | 'create' | 'edit'
    validator: (value) => ['view', 'create', 'edit'].includes(value)
  },
  padres: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['close', 'saved']);

// Títulos dinámicos
const modalTitle = computed(() => {
  switch (props.mode) {
    case 'create': return 'Nuevo Insumo';
    case 'edit': return 'Editar Insumo';
    default: return 'Detalles del Insumo';
  }
});

const submitButtonText = computed(() => {
  return props.mode === 'create' ? 'Crear Insumo' : 'Actualizar Insumo';
});

const isReadOnly = computed(() => props.mode === 'view');

// Form setup
const form = useForm({
  codigo: '',
  descripcion: '',
  clasificacion: '',
  registrable: true,
  presentacion: '',
  unidad_solicitud: '',
  precio_insumo: '',
  precio_testigo: false,
  inventariable: false,
  rend_tribunal: false,
  conversion: '',
  fecha_ultima: ''
});

// Inicializar formulario cuando cambie el item o el modo
watch(() => [props.item, props.mode], () => {
  if ((props.mode === 'edit' || props.mode === 'view') && props.item) {
    form.codigo = props.item.codigo || '';
    form.descripcion = props.item.descripcion || '';
    form.clasificacion = props.item.clasificacion || '';
    form.registrable = props.item.registrable || false;
    form.presentacion = props.item.presentacion || '';
    form.unidad_solicitud = props.item.unidad_solicitud || '';
    form.precio_insumo = props.item.precio_insumo || '';
    form.precio_testigo = props.item.precio_testigo || false;
    form.inventariable = props.item.inventariable || false;
    form.rend_tribunal = props.item.rend_tribunal || false;
    form.conversion = props.item.conversion || '';
    form.fecha_ultima = props.item.fecha_ultima || '';
  } else if (props.mode === 'create') {
    form.codigo = '';
    form.descripcion = '';
    form.clasificacion = '';
    form.registrable = true;
    form.presentacion = '';
    form.unidad_solicitud = '';
    form.precio_insumo = '';
    form.precio_testigo = false;
    form.inventariable = false;
    form.rend_tribunal = false;
    form.conversion = '';
    form.fecha_ultima = '';
  }

  // Limpiar errores
  form.clearErrors();
}, { immediate: true });

// Referencia al primer input para focus
const codigoInput = ref(null);

// Focus al primer input cuando se abre el modal (solo si no es view)
watch(() => props.mode, () => {
  if (props.mode !== 'view') {
    setTimeout(() => {
      if (codigoInput.value) {
        codigoInput.value.focus();
      }
    }, 100);
  }
}, { immediate: true });

const closeModal = () => {
  emit('close');
};

// Confirmación antes de cerrar si hay cambios
const confirmClose = async () => {
  if (props.mode !== 'view' && form.isDirty) {
    const result = await Swal.fire({
      title: '¿Cerrar sin guardar?',
      text: 'Tienes cambios sin guardar. ¿Estás seguro de que quieres cerrar?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Sí, cerrar',
      cancelButtonText: 'Continuar editando',
      reverseButtons: true
    });

    if (result.isConfirmed) {
      closeModal();
    }
  } else {
    closeModal();
  }
};

const submitForm = async () => {
  if (props.mode === 'view') return;

  // Validación básica en frontend
  if (!form.codigo.trim() || !form.descripcion.trim()) {
    await Swal.fire({
      title: 'Campos requeridos',
      text: 'El código y la descripción son obligatorios',
      icon: 'error',
      confirmButtonText: 'Entendido'
    });
    return;
  }

  // Confirmación antes de guardar
  const action = props.mode === 'create' ? 'crear' : 'actualizar';
  const result = await Swal.fire({
    title: `¿Confirmar ${action}?`,
    text: `¿Estás seguro de que quieres ${action} este insumo?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#22c55e',
    cancelButtonColor: '#6b7280',
    confirmButtonText: `Sí, ${action}`,
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  });

  if (!result.isConfirmed) return;

  // Submit del formulario
  if (props.mode === 'create') {
    form.post('/insumos', {
      onSuccess: () => {
        emit('saved', 'Insumo creado correctamente');
      },
      onError: (errors) => {
        handleFormErrors(errors);
      }
    });
  } else {
    form.patch(`/insumos/${props.item.id}`, {
      onSuccess: () => {
        emit('saved', 'Insumo actualizado correctamente');
      },
      onError: (errors) => {
        handleFormErrors(errors);
      }
    });
  }
};

const handleFormErrors = (errors) => {
  const errorMessages = Object.values(errors).flat();
  Swal.fire({
    title: 'Error de validación',
    html: `<ul style="text-align: left;">${errorMessages.map(error => `<li>${error}</li>`).join('')}</ul>`,
icon: 'error',
confirmButtonText: 'Entendido'
});
};

// Cerrar modal con ESC
const handleKeydown = (event) => {
if (event.key === 'Escape') {
confirmClose();
}
};

// Event listener para ESC
document.addEventListener('keydown', handleKeydown);

// Cleanup al desmontar
onUnmounted(() => {
document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
  <!-- Modal Backdrop -->
  <div
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
    @click.self="confirmClose"
  >
    <!-- Modal Content -->
    <div
      class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
      @click.stop
    >
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
          {{ modalTitle }}
        </h2>
        <button
          @click="confirmClose"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-150"
          type="button"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Form Content -->
      <div class="p-6">
        <form @submit.prevent="submitForm" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Código -->
            <div>
              <label
                for="codigo"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
              >
                Código <span class="text-red-500" v-if="!isReadOnly">*</span>
              </label>
              <input
                ref="codigoInput"
                id="codigo"
                v-model="form.codigo"
                type="text"
                :readonly="isReadOnly"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-150"
                :class="{
                  'border-red-500': form.errors.codigo,
                  'bg-gray-50 dark:bg-gray-600': isReadOnly
                }"
                placeholder="Ingresa el código del insumo"
                maxlength="50"
              />
              <div v-if="form.errors.codigo" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.codigo }}
              </div>
            </div>

            <!-- Clasificación -->
            <div>
              <label
                for="clasificacion"
                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
              >
                Clasificación
              </label>
              <input
                id="clasificacion"
                v-model="form.clasificacion"
                type="text"
                :readonly="isReadOnly"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-150"
                :class="{
                  'border-red-500': form.errors.clasificacion,
                  'bg-gray-50 dark:bg-gray-600': isReadOnly
                }"
                placeholder="Clasificación del insumo"
                maxlength="20"
              />
              <div v-if="form.errors.clasificacion" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.clasificacion }}
              </div>
            </div>
          </div>

          <!-- Descripción -->
          <div>
            <label
              for="descripcion"
              class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
            >
              Descripción <span class="text-red-500" v-if="!isReadOnly">*</span>
            </label>
            <textarea
              id="descripcion"
              v-model="form.descripcion"
              :readonly="isReadOnly"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-150 resize-none"
              :class="{
                'border-red-500': form.errors.descripcion,
                'bg-gray-50 dark:bg-gray-600': isReadOnly
              }"
              placeholder="Descripción detallada del insumo"
              maxlength="500"
            ></textarea>
            <div v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.descripcion }}
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              {{ form.descripcion.length }}/500 caracteres
            </div>
          </div>

          <!-- Estado Registrable -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
              Estado
            </label>
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
              <div class="flex items-center space-x-3">
                <label class="inline-flex items-center cursor-pointer" :class="{ 'cursor-not-allowed': isReadOnly }">
                  <input
                    v-model="form.registrable"
                    type="checkbox"
                    :disabled="isReadOnly"
                    class="sr-only peer"
                  />
                  <div
                    class="relative w-12 h-6 rounded-full transition-colors duration-200 ease-in-out after:content-[''] after:absolute after:top-0.5 after:w-5 after:h-5 after:bg-white after:border after:border-gray-300 after:rounded-full after:transition-all after:duration-200 after:ease-in-out"
                    :class="[
                      form.registrable
                        ? 'bg-green-500'
                        : 'bg-red-500',
                      form.registrable
                        ? 'after:left-[1.5rem]'
                        : 'after:left-0.5',
                      isReadOnly ? 'opacity-60' : ''
                    ]"
                  ></div>
                </label>
                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                  Registrable
                </span>
              </div>
              <div class="text-right">
                <div
                  class="text-sm font-medium"
                  :class="form.registrable ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                >
                  {{ form.registrable ? 'Habilitado' : 'No habilitado' }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                  {{ form.registrable ? 'Puede ser registrado' : 'No puede ser registrado' }}
                </div>
              </div>
            </div>
          </div>

          <!-- Información adicional si es modo view -->
          <div v-if="isReadOnly && item" class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-200 dark:border-gray-600">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Fecha de creación
              </label>
              <div class="text-sm text-gray-900 dark:text-gray-100">
                {{ item.created_at ? new Date(item.created_at).toLocaleString() : 'N/A' }}
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Última actualización
              </label>
              <div class="text-sm text-gray-900 dark:text-gray-100">
                {{ item.updated_at ? new Date(item.updated_at).toLocaleString() : 'N/A' }}
              </div>
            </div>
          </div>
        </form>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-end space-x-3 p-6 border-t border-gray-200 dark:border-gray-700">
        <button
          @click="confirmClose"
          type="button"
          class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-150"
          :disabled="form.processing"
        >
          {{ isReadOnly ? 'Cerrar' : 'Cancelar' }}
        </button>

        <button
          v-if="isReadOnly && mode === 'view'"
          @click="$emit('close'); $nextTick(() => openEditModal(item))"
          type="button"
          class="px-4 py-2 text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600 rounded-lg transition-colors duration-150"
        >
          Editar
        </button>

        <button
          v-if="!isReadOnly"
          @click="submitForm"
          type="button"
          class="px-4 py-2 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-lg transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="form.processing"
        >
          <span v-if="form.processing" class="flex items-center">
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Procesando...
          </span>
          <span v-else>{{ submitButtonText }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Scroll personalizado para el modal */
.max-h-\[90vh\]::-webkit-scrollbar {
  width: 6px;
}

.max-h-\[90vh\]::-webkit-scrollbar-track {
  background: transparent;
}

.max-h-\[90vh\]::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.5);
  border-radius: 3px;
}

.max-h-\[90vh\]::-webkit-scrollbar-thumb:hover {
  background-color: rgba(156, 163, 175, 0.7);
}
</style>
