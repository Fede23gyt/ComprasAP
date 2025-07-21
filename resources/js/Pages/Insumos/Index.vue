<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    insumos: {
        type: Object,
        required: true,
    }
});

const { flash } = usePage().props;

</script>

<template>
    <Head title="Insumos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestión de Insumos
                </h2>
                <Link :href="route('insumos.create')">
                    <PrimaryButton>Crear Insumo</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
<!--                 <div v-if="flash.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ flash.success }}</span>
                </div>-->

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto relative">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3 px-6">Descripción</th>
                                    <th scope="col" class="py-3 px-6">Código</th>
                                    <th scope="col" class="py-3 px-6">Presentación</th>
                                    <th scope="col" class="py-3 px-6"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-if="insumos.data.length > 0">
                                    <!-- Bucle principal para padres -->
                                    <template v-for="insumo in insumos.data" :key="insumo.id">
                                        <tr class="bg-white border-b hover:bg-gray-50">
                                            <td class="py-4 px-6 font-bold">{{ insumo.descripcion }}</td>
                                            <td class="py-4 px-6">{{ insumo.codigo }}</td>
                                            <td class="py-4 px-6">{{ insumo.presentacion }}</td>
                                            <td class="py-4 px-6 text-right">
                                                <Link :href="route('insumos.edit', insumo.id)" class="font-medium text-indigo-600 hover:text-indigo-900">Editar</Link>
                                            </td>
                                        </tr>
                                        <!-- Bucle anidado para hijos -->
                                        <tr v-for="child in insumo.children" :key="child.id" class="bg-gray-50 border-b hover:bg-gray-100">
                                            <td class="py-4 px-6" style="padding-left: 48px;">{{ child.descripcion }}</td>
                                            <td class="py-4 px-6">{{ child.codigo }}</td>
                                            <td class="py-4 px-6">{{ child.presentacion }}</td>
                                            <td class="py-4 px-6 text-right">
                                                <Link :href="route('insumos.edit', child.id)" class="font-medium text-indigo-600 hover:text-indigo-900">Editar</Link>
                                            </td>
                                        </tr>
                                    </template>
                                </template>
                                <tr v-else>
                                    <td colspan="4" class="py-4 px-6 text-center text-gray-500">
                                        No se encontraron insumos.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Paginación con sintaxis de Vue -->
                    <div v-if="insumos.links.length > 1" class="p-4 flex justify-center items-center space-x-2">
                        <template v-for="(link, key) in insumos.links" :key="key">
                            <div v-if="link.url === null" class="px-4 py-2 text-sm text-gray-400 border rounded-md" v-html="link.label" />
                            <Link v-else class="px-4 py-2 text-sm border rounded-md hover:bg-gray-100" :class="{ 'bg-indigo-500 text-white': link.active }" :href="link.url" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
