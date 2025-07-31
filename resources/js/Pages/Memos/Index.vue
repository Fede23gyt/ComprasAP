<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ items: Array });

const form = useForm({ descripcion: '', estado: 'Habilitado' });
const openModal = () => showModal.value = true;
const showModal = ref(false);
</script>

<template>
    <AppLayout title="Tipos de Memos">
        <section class="bg-gray-100 dark:bg-gray-900 pt-4 pb-6">
            <div class="max-w-7xl mx-auto px-4">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tipos de Memo</h1>
            </div>
        </section>

        <main class="max-w-7xl mx-auto px-4 py-8">
            <div class="flex justify-end mb-4">
                <button @click="openModal" class="bg-gray-400 dark:bg-gray-500 hover:bg-orange-500 text-white px-4 py-2 rounded">Nuevo</button>
            </div>

            <div class="overflow-x-auto rounded-xl shadow">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                    <tr class="h-4 text-xs bg-gray-50 dark:bg-gray-700/50">
                        <th class="px-4 py-1 text-left">Descripción</th>
                        <th class="px-4 py-1 text-left">Estado</th>
                        <th class="px-4 py-1 text-left">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in items" :key="item.id" class="h-4 text-xs hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="px-4 py-1">{{ item.descripcion }}</td>
                        <td class="px-4 py-1">
                            <span :class="item.estado === 'Habilitado' ? 'text-green-600' : 'text-red-600'">{{ item.estado }}</span>
                        </td>
                        <td class="px-4 py-1">
                            <Link :href="`/memos/${item.id}/edit`" class="text-secondary hover:text-orange-500">✏️</Link>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </AppLayout>
</template>
