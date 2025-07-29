<!-- resources/js/Components/OficinaFormModal.vue -->
<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    oficina: { type: Object, default: null },
    padres: Array,
});

const emit = defineEmits(['close', 'saved']);

const isEdit   = !!props.oficina;
const form     = useForm({
    nombre: props.oficina?.nombre ?? '',
    codigo_oficina: props.oficina?.codigo_oficina ?? '',
    abreviacion: props.oficina?.abreviacion ?? '',
    parent_id: props.oficina?.parent_id ?? null,
    estado: props.oficina?.estado ?? 'Habilitada',
});

const submit = () => {
    const method = isEdit ? 'patch' : 'post';
    const url    = isEdit ? `/oficinas/${props.oficina.id}` : '/oficinas';
    form[method](url, {
        onSuccess: () => emit('saved'),
        onError: () => {}, // Ziggy muestra errores automáticamente
    });
};

const cancel = () => emit('close');
</script>

<template>
    <Teleport to="body">
        <div
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
            @click.self="cancel"
        >
            <div
                class="bg-gray-50 dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-2xl p-6 space-y-5"
            >
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                    {{ isEdit ? 'Modificar' : 'Agregar' }} Oficina
                </h2>

                <!-- Campos ordenados -->
                <!-- 1ª fila -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Nombre</label>
                    <input v-model="form.nombre" required class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                    <div v-if="form.errors.nombre" class="text-red-500 text-xs mt-1">{{ form.errors.nombre }}</div>
                </div>

                <!-- 2ª fila -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Código</label>
                        <input v-model="form.codigo_oficina" required class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        <div v-if="form.errors.codigo_oficina" class="text-red-500 text-xs mt-1">{{ form.errors.codigo_oficina }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Abreviación</label>
                        <input v-model="form.abreviacion" required class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        <div v-if="form.errors.abreviacion" class="text-red-500 text-xs mt-1">{{ form.errors.abreviacion }}</div>
                    </div>
                </div>

                <!-- 3ª fila -->
                <div class="grid grid-cols-2 gap-4 items-center">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Depende de</label>
                        <select v-model="form.parent_id" class="mt-1 w-full rounded-md">
                            <option :value="null">Sin padre</option>
                            <option v-for="p in padres" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Estado</label>
                        <div class="flex items-center space-x-4 mt-1">
                            <label class="flex items-center">
                                <input type="radio" v-model="form.estado" value="Habilitada" class="form-radio text-green-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">Habilitada</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" v-model="form.estado" value="No habilitada" class="form-radio text-red-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">No habilitada</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Botones fijos -->
                <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button
                        type="button"
                        @click="cancel"
                        class="px-6 py-2 rounded-md bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 transition"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        @click="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 rounded-md bg-orange-600 text-white hover:bg-orange-500 transition disabled:cursor-not-allowed"
                    >
                        Grabar
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
