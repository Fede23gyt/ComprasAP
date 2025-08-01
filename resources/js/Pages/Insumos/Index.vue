<!-- resources/js/Pages/Insumos/Index.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import InsumoFormModal from '@/Components/InsumoFormModal.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    insumos: Array,
    padres: Array,
});

const search = ref('');

const flatTree = (nodes, level = 0) => {
  const result = [];
  nodes.forEach(n => {
    result.push({ ...n, level });
    if (n.children?.length) result.push(...flatTree(n.children, level + 1));
  });
  return result;
};

const filteredTree = computed(() => {
  const term = search.value.toLowerCase();
  const tree = flatTree(props.insumos);
  return term
    ? tree.filter(i =>
      i.descripcion.toLowerCase().includes(term) ||
      i.codigo.toLowerCase().includes(term)
    )
    : tree;
});

const showModal = ref(false);
const modalProps = ref({ insumo: null, padres: [] });

const openModal = (edit = false, node = null) => {
  modalProps.value = { props.insumo: node, padres };
  showModal.value = true;
};
const closeModal = () => (showModal.value = false);

const confirmToggle = (item, field) => {
  const action = item[field] ? 'desmarcar' : 'marcar';
  if (confirm(`¿Estás seguro de ${action} “${item.descripcion}”?`)) {
    useForm({ [field]: !item[field] })
      .patch(`/insumos/${item.id}/toggle-${field}`);
  }
};
</script>

<template>
  <AppLayout>
    <section class="bg-gray-100 dark:bg-gray-900 pt-4 pb-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Insumos</h1>
      </div>
    </section>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Buscador + botón -->
      <div class="flex items-center justify-between mb-4">
        <label class="flex items-center space-x-2">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Buscar</span>
          <input
            v-model="search"
            type="text"
            placeholder="Código o descripción..."
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-secondary w-full max-w-xs"
          />
        </label>
        <button
          @click="openModal(false)"
          class="bg-gray-400 dark:bg-gray-500 hover:bg-orange-500 text-white font-semibold px-4 py-2 rounded-lg shadow transition whitespace-nowrap"
        >
          Nuevo Insumo
        </button>
      </div>

      <!-- Tabla compacta -->
      <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead>
          <tr class="h-10 text-xs bg-gray-50 dark:bg-gray-700/50">
            <th class="px-4 py-1 text-left">Código</th>
            <th class="px-4 py-1 text-left">Descripción</th>
            <th class="px-4 py-1 text-left">Registrable</th>
            <th class="px-4 py-1 text-left">Acciones</th>
          </tr>
          </thead>
          <tbody>
          <tr
            v-for="item in filteredTree"
            :key="item.id"
            class="h-10 text-xs hover:bg-gray-50 dark:hover:bg-gray-700/30 transition"
          >
            <td class="px-4 py-1 font-mono">{{ item.codigo }}</td>
            <td class="px-4 py-1">
              <span :style="{ marginLeft: item.level * 15 + 'px' }"></span>
              {{ item.descripcion }}
            </td>
            <td class="px-4 py-1">
              <label class="inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  :checked="item.registrable ?? false"
                  class="sr-only peer"
                  @change="confirmToggle(item, 'registrable')"
                />
                <div
                  :class="[
                      'relative w-10 h-5 rounded-full peer after:content-[\'\'] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:bg-white after:border after:border-gray-300 after:rounded-full after:transition-all peer-checked:after:translate-x-full',
                      item.registrable ? 'bg-green-500' : 'bg-red-500'
                    ]"
                ></div>
              </label>
            </td>
            <td class="px-4 py-1">
              <button
                @click="openModal(true, item)"
                class="text-secondary hover:text-orange-500"
              >
                ✏️
              </button>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <!-- Modal -->
      <InsumoFormModal
        v-if="showModal"
        v-bind="modalProps"
        @close="closeModal"
        @saved="closeModal(); $inertia.reload()"
      />
    </main>
  </AppLayout>
</template>
