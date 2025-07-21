<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InsumoForm from './Partials/InsumoForm.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    insumosList: {
        type: Array,
        required: true
    }
});

const form = useForm({
    parent_id: null,
    descripcion: '',
    codigo: '',
    clasificacion: '',
    presentacion: '',
    unidad_solicitud: '',
    precio_insumo: 0.00,
    precio_testigo: false,
    inventariable: false,
    registrable: true,
    rend_tribunal: false,
    conversion: 0.0000,
    fecha_ultima: '',
    tipo: '',
    codigo_grupo: '',
});

const submit = () => {
    form.post(route('insumos.store'));
};
</script>

<template>
    <Head title="Crear Insumo" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Crear Nuevo Insumo</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <InsumoForm :form="form" @submit="submit" :insumos-list="props.insumosList" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>