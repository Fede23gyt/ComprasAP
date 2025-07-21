<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InsumoForm from './Partials/InsumoForm.vue';
import { Head, useForm } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import { router } from '@inertiajs/vue3'

const props = defineProps({
    insumo: {
        type: Object,
        required: true
    },
    insumosList: {
        type: Array,
        required: true
    }
});

const form = useForm({ ...props.insumo });

const submit = () => {
    form.put(route('insumos.update', props.insumo.id));
};

const deleteInsumo = () => {
    if (confirm('¿Estás seguro de que deseas eliminar este insumo? Esta acción no se puede deshacer.')) {
        router.delete(route('insumos.destroy', props.insumo.id));
    }
}
</script>

<template>
    <Head :title="'Editar Insumo: ' + form.descripcion" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Insumo</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <InsumoForm :form="form" @submit="submit" :insumos-list="props.insumosList" :is-update="true" />
                        <DangerButton @click="deleteInsumo" class="mt-4">Eliminar Insumo</DangerButton>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>