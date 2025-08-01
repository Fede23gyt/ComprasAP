<!-- resources/js/Components/InsumoViewModal.vue -->
<script setup>
defineProps({
  item: Object,
});
const emit = defineEmits(['close']);
</script>

<template>
  <Teleport to="body">
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="$emit('close')">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-lg p-6 space-y-4">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Detalle Insumo</h2>

        <!-- Campos deshabilitados -->
        <div class="space-y-3">
          <div><span class="font-medium">Código:</span> {{ item.codigo }}</div>
          <div><span class="font-medium">Descripción:</span> {{ item.descripcion }}</div>
          <div><span class="font-medium">Clasificación:</span> {{ item.clasificacion }}</div>
          <div><span class="font-medium">Nombre Clasificación:</span> {{ item.clasif_economica?.descripcion ?? '-' }}</div>
          <div><span class="font-medium">Presentación:</span> {{ item.presentacion }}</div>
          <div><span class="font-medium">Unidad:</span> {{ item.unidad_solicitada }}</div>
          <div><span class="font-medium">Precio:</span> ${{ item.precio }}</div>
          <div><span class="font-medium">Registrable:</span> {{ item.registrable ? 'Sí' : 'No' }}</div>
        </div>

        <button @click="$emit('close')" class="w-full mt-4 bg-gray-300 dark:bg-gray-600 px-4 py-2 rounded">Cerrar</button>
      </div>
    </div>
  </Teleport>
</template>
