<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    oficinas: Array,
    modelValue: [Number, Array],
    multiple: Boolean,
});

const emit = defineEmits(['update:modelValue']);

const search = ref('');

// Función recursiva que filtra y marca visibilidad
function filterTree(nodes, term) {
    if (!term.trim()) return nodes;

    const lower = term.toLowerCase();
    const result = [];

    function walk(list) {
        for (const node of list) {
            const visibleSelf =
                node.nombre.toLowerCase().includes(lower) ||
                node.abreviacion.toLowerCase().includes(lower);

            const children = node.children ? filterTree(node.children, term) : [];

            // Mostrar nodo si coincide él mismo o algún hijo
            if (visibleSelf || children.length) {
                result.push({ ...node, children, visible: true });
            }
        }
        return result;
    }

    walk(nodes);
    return result;
}

const visibleTree = computed(() => filterTree(props.oficinas, search.value));

function toggle(node) {
    if (props.multiple) {
        const values = Array.isArray(props.modelValue) ? props.modelValue : [];
        const index = values.indexOf(node.id);
        const newValues = index > -1
            ? values.filter(id => id !== node.id)
            : [...values, node.id];
        emit('update:modelValue', newValues);
    } else {
        emit('update:modelValue', node.id);
    }
}
</script>

<template>
    <div>
        <!-- Campo de búsqueda -->
        <input
            v-model="search"
            type="text"
            placeholder="Buscar oficina..."
            class="border px-3 py-2 rounded w-full mb-4"
        />

        <!-- Árbol filtrado -->
        <ul>
            <li
                v-for="node in tree"
                :key="node.id"
                class="flex items-center space-x-2 py-1"
                :style="{ paddingLeft: (node.level || 0) * 20 + 'px' }"
            >
                <input
                    :type="multiple ? 'checkbox' : 'radio'"
                    :name="multiple ? 'oficina_ids[]' : 'oficina_id'"
                    :value="node.id"
                    :checked="multiple ? modelValue?.includes(node.id) : modelValue === node.id"
                    @change="toggle(node)"
                />
                <span>{{ node.nombre }} ({{ node.abreviacion }})</span>
            </li>
            <!-- Recursión segura -->
            <OficinaTree
                v-if="Array.isArray(node.children) && node.children.length"
                :oficinas="node.children"
                :multiple="multiple"
                :model-value="modelValue"
                @update:modelValue="$emit('update:modelValue', $event)"
            />

        </ul>
    </div>
</template>
