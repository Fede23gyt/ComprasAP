<!-- resources/js/Pages/Insumos/Index.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import InsumoFormModal from '@/Components/InsumoFormModal.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  insumos: Object, // paginado
  padres: Array,
});

const search = ref('');

// Buscador sobre la **página actual**
const filteredPage = computed(() =>
  props.insumos.data.filter(i =>
    i.descripcion.toLowerCase().includes(search.value.toLowerCase()) ||
    i.codigo.toLowerCase().includes(search.value.toLowerCase())
  )
);

const showModal = ref(false);
const modalProps = ref({ insumo: null, padres: [] });

const openModal = (edit = false, node = null) => {
  modalProps.value = { insumo: node, padres: props.padres };
  showModal.value = true;
};
const closeModal = () => (showModal.value = false);

const confirmToggle = (item, field) => {
  useForm({ [field]: !item[field] })
    .patch(`/insumos/${item.id}/toggle-${field}`);
};
</script>

<template>
  <AppLayout>
    <section class="bg-gray-100 dark:bg-gray-900 pt-4 pb-6">
      <div class="max-w-7xl mx-auto px-4"><h1 class="text-3xl font-bold">Insumos</h1></div>
    </section>

    <main class="max-w-7xl mx-auto px-4 py-8">
      <!-- Filtro + botón -->
      <div class="flex items-center justify-between mb-4">
        <label class="flex items-center space-x-2">
          <span class="text-sm font-medium">Buscar</span>
          <input
            v-model="search"
            @input="$inertia.get(route('insumos.index', { search: $event.target.value }))"
            placeholder="Buscar en todo el listado…"
            class="px-3 py-2 border rounded-lg"
          />
        </label>
      </div>

      <!-- Tabla compacta -->
      <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="h-10 text-xs bg-gray-50 dark:bg-gray-700/50">
          <tr>
            <th class="px-4 py-1 text-left">Código</th>
            <th class="px-4 py-1 text-left">Descripción</th>
            <th class="px-4 py-1 text-left">Clasificación</th>
            <th class="px-4 py-1 text-left">Nombre Clasificación</th>
            <th class="px-4 py-1 text-left">Registrable</th>
          </tr>
          </thead>
          <tbody>
          <tr
            v-for="item in filteredPage"
            :key="item.id"
            class="h-10 text-xs hover:bg-gray-50 dark:hover:bg-gray-700/30 transition"
          >
            <td class="px-4 py-1 font-mono">{{ item.codigo }}</td>
            <td class="px-4 py-1">{{ item.descripcion }}</td>
            <td class="px-4 py-1">{{ item.clasificacion }}</td>
            <td class="px-4 py-1">{{ item.clasif_economica?.descripcion ?? '-' }}</td>
            <td class="px-4 py-1">
              <label class="inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  :checked="item.registrable"
                  class="sr-only peer"
                  @change="confirmToggle(item, 'registrable')"
                />
                <div
                  :class="[
                      'relative w-10 h-5 rounded-full peer after:content-[\'\'] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 ' +
                       'after:bg-white after:border after:border-gray-300 after:rounded-full after:transition-all peer-checked:after:translate-x-full',
                      item.registrable ? 'bg-green-500' : 'bg-red-500'
                    ]"
                ></div>
              </label>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación moderna -->
      <div class="mt-5 flex items-center justify-between text-sm">
        <!-- Info de página -->
        <p class="text-gray-600 dark:text-gray-300">
          Mostrando
          <span class="font-medium">{{ insumos.from }} - {{ insumos.to }}</span>
          de
          <span class="font-medium">{{ insumos.total }}</span>
        </p>

        <!-- Links -->
        <nav class="flex space-x-1">
          <Link
            v-for="(link, index) in insumos.links"
            :key="index"
            :href="link.url ?? '#'"
            :class="[
        'px-3 py-1 rounded-md border',
        link.active
          ? 'bg-blue-600 text-white border-blue-600'
          : link.url
            ? 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600'
            : 'bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed'
      ]"
            v-html="link.label"
          />
        </nav>
      </div>
    </main>

    <!-- Modal -->
    <InsumoFormModal
      v-if="showModal"
      v-bind="modalProps"
      @close="closeModal"
      @saved="closeModal(); $inertia.reload()"
    />
  </AppLayout>
</template>
