<!-- resources/js/Components/InsumoFormModal.vue -->
<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    insumo: { type: Object, default: null },
    padres: Array, // Lista de insumos que pueden ser padres
});

const emit = defineEmits(['close', 'saved']);

const isEdit = !!props.insumo;
const form = useForm({
    descripcion: props.insumo?.descripcion ?? '',
    codigo: props.insumo?.codigo ?? '',
    clasificacion: props.insumo?.clasificacion ?? '',
    presentacion: props.insumo?.presentacion ?? '',
    unidad_solicitud: props.insumo?.unidad_solicitud ?? '',
    precio_insumo: props.insumo?.precio_insumo ?? 0.00,
    conversion: props.insumo?.conversion ?? 0.00,
    fecha_ultima: props.insumo?.fecha_ultima ?? '',
    tipo: props.insumo?.tipo ?? '',
    codigo_grupo: props.insumo?.codigo_grupo ?? '',
    parent_id: props.insumo?.parent_id ?? null,
    // Booleans
    precio_testigo: props.insumo?.precio_testigo ?? false,
    inventariable: props.insumo?.inventariable ?? false,
    registrable: props.insumo?.registrable ?? true,
    rend_tribunal: props.insumo?.rend_tribunal ?? false,
});

// --- CÓDIGO CORREGIDO ---
const submit = () => {
    if (isEdit) {
        // Si estamos editando, usamos el método PATCH
        form.patch(route('insumos.update', props.insumo.id), {
            onSuccess: () => emit('saved'),
            preserveScroll: true, // Mantiene la posición del scroll
        });
    } else {
        // Si estamos creando, usamos el método POST
        form.post(route('insumos.store'), {
            onSuccess: () => emit('saved'),
            preserveScroll: true,
        });
    }
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
                class="bg-gray-50 dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-4xl p-6 space-y-5"
            >
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                    {{ isEdit ? 'Modificar' : 'Agregar' }} Insumo
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Columna 1 -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Descripción</label>
                            <input v-model="form.descripcion" required class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            <div v-if="form.errors.descripcion" class="text-red-500 text-xs mt-1">{{ form.errors.descripcion }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Código</label>
                            <input v-model="form.codigo" required class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            <div v-if="form.errors.codigo" class="text-red-500 text-xs mt-1">{{ form.errors.codigo }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Depende de (Categoría)</label>
                            <select v-model="form.parent_id" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option :value="null">Sin categoría padre</option>
                                <option v-for="p in padres" :key="p.id" :value="p.id">{{ p.descripcion }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Columna 2 -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Presentación</label>
                            <input v-model="form.presentacion" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Unidad de Solicitud</label>
                            <input v-model="form.unidad_solicitud" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Precio Insumo</label>
                            <input v-model="form.precio_insumo" type="number" step="0.01" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                    </div>

                    <!-- Columna 3 (Booleans y otros) -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Propiedades</label>
                            <div class="mt-2 space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" v-model="form.precio_testigo" class="rounded form-checkbox text-orange-600">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">Precio Testigo</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" v-model="form.inventariable" class="rounded form-checkbox text-orange-600">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">Inventariable</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" v-model="form.registrable" class="rounded form-checkbox text-orange-600">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">Registrable</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" v-model="form.rend_tribunal" class="rounded form-checkbox text-orange-600">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">Rinde a Tribunal</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
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
                        class="px-6 py-2 rounded-md bg-orange-600 text-white hover:bg-orange-500 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Grabar
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
