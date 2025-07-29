<!-- resources/js/Pages/Oficinas/Index.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import OficinaFormModal from '@/Components/OficinaFormModal.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    oficinas: Array,
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
    const tree = flatTree(props.oficinas); // ✅ usa props.oficinas
    return term
        ? tree.filter(
            o =>
                o.nombre.toLowerCase().includes(term) ||
                o.codigo_oficina.toLowerCase().includes(term)
        )
        : tree;
});

/* --- Modal --- */
const showModal = ref(false);
const modalProps = ref({ oficina: null, padres: [] });

const openModal = (edit = false, node = null) => {
    modalProps.value = {
        oficina: node,
        padres: props.padres, // ← usa props.padres
    };
    showModal.value = true;
};
const closeModal = () => (showModal.value = false);

/* --- Confirmación para toggle --- */
const confirmToggle = (node) => {
    if (
        confirm(
            `¿Estás seguro de ${
                node.estado === 'Habilitada' ? 'inhabilitar' : 'habilitar'
            } la oficina “${node.nombre}”?`
        )
    ) {
        useForm({}).patch(`/oficinas/${node.id}/toggle`);
    }
};
</script>

<template>
    <AppLayout>
        <section class="bg-gray-100 dark:bg-gray-900 pt-4 pb-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Oficinas</h1>
            </div>
        </section>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Buscador + botón en la misma línea -->
            <div class="flex items-center justify-between mb-4">
                <label class="flex items-center space-x-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Buscar</span>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Código o nombre..."
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-secondary w-full max-w-xs"
                    />
                </label>

                <button
                    @click="openModal(false)"
                    class="bg-gray-400 dark:bg-gray-500 hover:bg-orange-500 text-white font-semibold px-4 py-2 rounded-lg shadow transition whitespace-nowrap"
                >
                    Nueva Oficina
                </button>
            </div>

            <!-- Tabla compacta -->
            <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-700/50 dark:bg-gray-700/50">
                    <tr class="h-4 text-xs">
                        <th class="px-4 py-1 text-left font-medium text-black dark:text-gray-300">Código</th>
                        <th class="px-4 py-1 text-left font-medium text-black dark:text-gray-300">Nombre</th>
                        <th class="px-4 py-1 text-left font-medium text-black dark:text-gray-300">Estado</th>
                        <th class="px-4 py-1 text-left font-medium text-black dark:text-gray-300">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr
                        v-for="node in filteredTree"
                        :key="node.id"
                        class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition h-4 text-xs"
                    >
                        <td class="px-4 py-1 font-mono">{{ node.codigo_oficina }}</td>
                        <td class="px-4 py-1">
                            <span :style="{ marginLeft: node.level * 15 + 'px' }"></span>
                            {{ node.nombre }}
                            <span class="text-xs text-gray-500 dark:text-gray-400">({{ node.abreviacion }})</span>
                        </td>
                        <td class="px-4 py-1">
                            <label class="inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    :checked="node.estado === 'Habilitada'"
                                    class="sr-only peer"
                                    @change="confirmToggle(node)"
                                />
                                <div
                                    :class="[
                      'relative w-10 h-5 rounded-full peer after:content-[\'\'] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:bg-white after:border after:border-gray-300 after:rounded-full after:transition-all peer-checked:after:translate-x-full',
                      node.estado === 'Habilitada' ? 'bg-green-500' : 'bg-orange-400'
                    ]"
                                ></div>
                            </label>
                        </td>
                        <td class="px-4 py-1">
                            <button
                                @click="openModal(true, node)"
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
            <OficinaFormModal
                v-if="showModal"
                v-bind="modalProps"
                @close="closeModal"
                @saved="closeModal(); $inertia.reload()"
            />
        </main>
    </AppLayout>
</template>
