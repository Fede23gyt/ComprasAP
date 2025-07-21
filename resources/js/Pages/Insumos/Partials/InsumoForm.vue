<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    insumosList: {
        type: Array,
        default: () => [],
    },
    isUpdate: {
        type: Boolean,
        default: false,
    }
});

defineEmits(['submit']);
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="mt-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Campos Principales -->
            <div>
                <InputLabel for="descripcion" value="Descripción" />
                <TextInput id="descripcion" type="text" class="mt-1 block w-full" v-model="form.descripcion" required autofocus />
                <InputError class="mt-2" :message="form.errors.descripcion" />
            </div>

            <div>
                <InputLabel for="codigo" value="Código" />
                <TextInput id="codigo" type="text" class="mt-1 block w-full" v-model="form.codigo" required />
                <InputError class="mt-2" :message="form.errors.codigo" />
            </div>

            <div>
                <InputLabel for="parent_id" value="Insumo Padre (Categoría)" />
                <select id="parent_id" v-model="form.parent_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option :value="null">Ninguno (Es categoría principal)</option>
                    <option v-for="insumo in insumosList" :key="insumo.id" :value="insumo.id">
                        {{ insumo.descripcion }}
                    </option>
                </select>
                <InputError class="mt-2" :message="form.errors.parent_id" />
            </div>

            <!-- Campos Adicionales -->
            <div>
                <InputLabel for="clasificacion" value="Clasificación" />
                <TextInput id="clasificacion" type="text" class="mt-1 block w-full" v-model="form.clasificacion" />
                <InputError class="mt-2" :message="form.errors.clasificacion" />
            </div>

            <div>
                <InputLabel for="presentacion" value="Presentación" />
                <TextInput id="presentacion" type="text" class="mt-1 block w-full" v-model="form.presentacion" />
                <InputError class="mt-2" :message="form.errors.presentacion" />
            </div>

            <div>
                <InputLabel for="unidad_solicitud" value="Unidad de Solicitud" />
                <TextInput id="unidad_solicitud" type="text" class="mt-1 block w-full" v-model="form.unidad_solicitud" />
                <InputError class="mt-2" :message="form.errors.unidad_solicitud" />
            </div>

            <div>
                <InputLabel for="precio_insumo" value="Precio" />
                <TextInput id="precio_insumo" type="number" step="0.01" class="mt-1 block w-full" v-model="form.precio_insumo" />
                <InputError class="mt-2" :message="form.errors.precio_insumo" />
            </div>
            
            <div>
                <InputLabel for="conversion" value="Conversión" />
                <TextInput id="conversion" type="number" step="0.0001" class="mt-1 block w-full" v-model="form.conversion" />
                <InputError class="mt-2" :message="form.errors.conversion" />
            </div>

            <div>
                <InputLabel for="fecha_ultima" value="Fecha Última" />
                <TextInput id="fecha_ultima" type="date" class="mt-1 block w-full" v-model="form.fecha_ultima" />
                <InputError class="mt-2" :message="form.errors.fecha_ultima" />
            </div>

            <!-- Checkboxes -->
            <div class="col-span-1 md:col-span-2 lg:col-span-3 grid grid-cols-2 sm:grid-cols-4 gap-4 items-center">
                <label class="flex items-center">
                    <Checkbox name="precio_testigo" v-model:checked="form.precio_testigo" />
                    <span class="ml-2 text-sm text-gray-600">Precio Testigo</span>
                </label>
                <label class="flex items-center">
                    <Checkbox name="inventariable" v-model:checked="form.inventariable" />
                    <span class="ml-2 text-sm text-gray-600">Inventariable</span>
                </label>
                <label class="flex items-center">
                    <Checkbox name="registrable" v-model:checked="form.registrable" />
                    <span class="ml-2 text-sm text-gray-600">Registrable</span>
                </label>
                <label class="flex items-center">
                    <Checkbox name="rend_tribunal" v-model:checked="form.rend_tribunal" />
                    <span class="ml-2 text-sm text-gray-600">Rinde a Tribunal</span>
                </label>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <PrimaryButton :disabled="form.processing">{{ isUpdate ? 'Actualizar' : 'Guardar' }}</PrimaryButton>
            <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
                <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Guardado.</p>
            </Transition>
        </div>
    </form>
</template>