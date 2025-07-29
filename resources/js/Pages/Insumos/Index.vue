<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import InsumoFormModal from '@/Components/InsumoFormModal.vue';
import { useForm, Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    insumos: Array,
    padres: Array,
    flash: Object,
});

const search = ref('');

// Función para aplanar el árbol y poder mostrarlo en una tabla y buscar
const flatTree = (nodes, level = 0) => {
    return nodes.reduce((acc, node) => {
        acc.push({ ...node, level });
        if (node.children?.length) {
            acc.push(...flatTree(node.children, level + 1));
        }
        return acc;
    }, []);
};

const filteredTree = computed(() => {
    const term = search.value.toLowerCase();
    const tree = flatTree(props.insumos);
    if (!term) return tree;
    return tree.filter(
        o =>
            o.descripcion.toLowerCase().includes(term) ||
            (o.codigo && o.codigo.toLowerCase().includes(term))
    );
});

/* --- Lógica del Modal --- */
const showModal = ref(false);
const modalProps = ref({ insumo: null, padres: [] });

const openModal = (node = null) => {
    modalProps.value = {
        insumo: node,
        // Evitar que un insumo sea su propio padre en el dropdown
        padres: props.padres.filter(p => !node || p.id !== node.id),
    };
    showModal.value = true;
};
const closeModal = () => (showModal.value = false);

/* --- Lógica de Eliminación --- */
const confirmDelete = (insumo) => {
    if (confirm(`¿Estás seguro de eliminar el insumo "${insumo.descripcion}"? Esta acción no se puede deshacer.`)) {
        useForm({}).delete(route('insumos.destroy', insumo.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Gestión de Insumos" />

    <AppLayout>
        <section class="bg-gray-100 dark:bg-gray-900 pt-4 pb-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Gestión de Insumos</h1>
            </div>
        </section>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Buscador y Botón -->
            <div class="flex items-center justify-between mb-4">
                <label class="flex items-center space-x-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Buscar</span>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Código o descripción..."
                        class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 w-full max-w-xs"
                    />
                </label>

                <button
                    @click="openModal()"
                    class="bg-orange-600 hover:bg-orange-500 text-white font-semibold px-4 py-2 rounded-lg shadow transition whitespace-nowrap"
                >
                    Nuevo Insumo
                </button>
            </div>

            <!-- Tabla de Insumos -->
            <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-700/50 dark:bg-gray-700/50">
                    <tr class="h-4 text-xs">
                        <th class="px-4 py-1 text-left font-medium text-black dark:text-gray-300">Descripción</th>
                        <th class="px-4 py-1 text-left font-medium text-black dark:text-gray-300">Código</th>
                        <th class="px-4 py-1 text-left font-medium text-black dark:text-gray-300">Presentación</th>
                        <th class="px-4 py-1 text-left font-medium text-black dark:text-gray-300">Acciones</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr
                        v-for="node in filteredTree"
                        :key="node.id"
                        class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition h-4 text-xs"
                    >
                        <td class="px-4 py-1">
                                <span :style="{ marginLeft: node.level * 20 + 'px' }">
                                    <span v-if="node.level > 0" class="mr-2 text-gray-400">└─</span>
                                    {{ node.descripcion }}
                                </span>
                        </td>
                        <td class="px-4 py-1 font-mono">{{ node.codigo }}</td>
                        <td class="px-4 py-1">{{ node.presentacion }}</td>
                        <td class="px-4 py-1 flex items-center space-x-3">
                            <button @click="openModal(node)" class="text-orange-500 hover:text-orange-400" title="Editar">
                                ✏️
                            </button>
                            <button @click="confirmDelete(node)" class="text-red-500 hover:text-red-400" title="Eliminar">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    <tr v-if="filteredTree.length === 0">
                        <td colspan="4" class="text-center py-4 text-gray-500 dark:text-gray-400">
                            No se encontraron insumos que coincidan con la búsqueda.
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal para Crear/Editar -->
            <InsumoFormModal
                v-if="showModal"
                v-bind="modalProps"
                @close="closeModal"
                @saved="closeModal"
            />
        </main>
    </AppLayout>
</template>
