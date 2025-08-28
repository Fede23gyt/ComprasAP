<template>
  <!-- Overlay del modal -->
  <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      
      <!-- Fondo oscuro -->
      <div 
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
        aria-hidden="true"
        @click="$emit('cerrar')"
      ></div>

      <!-- Espaciador para centrar el modal -->
      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <!-- Panel del modal -->
      <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
        
        <!-- Header del modal -->
        <div class="sm:flex sm:items-start">
          <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/20 sm:mx-0 sm:h-10 sm:w-10">
            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10m0 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v2m0 0v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
          </div>
          
          <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
              Descripción Ampliada del Insumo
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Agrega una descripción detallada para especificar características particulares del insumo
            </p>
          </div>

          <!-- Botón cerrar -->
          <button
            @click="$emit('cerrar')"
            type="button"
            class="ml-auto -mx-1.5 -my-1.5 bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 inline-flex h-8 w-8"
          >
            <span class="sr-only">Cerrar</span>
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
          </button>
        </div>

        <!-- Contenido del modal -->
        <div class="mt-5">
          <div class="space-y-4">
            
            <!-- Área de texto para el comentario -->
            <div>
              <label for="comentario" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Descripción detallada
              </label>
              <textarea
                id="comentario"
                ref="textareaRef"
                v-model="comentarioInterno"
                rows="6"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                placeholder="Especifica características técnicas, modelo, marca, observaciones especiales, etc..."
                maxlength="1000"
              ></textarea>
              
              <!-- Contador de caracteres -->
              <div class="flex justify-between items-center mt-2">
                <div class="text-xs text-gray-500 dark:text-gray-400">
                  Usa este espacio para detallar especificaciones técnicas que no están en la descripción básica del insumo
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                  {{ comentarioInterno.length }}/1000
                </div>
              </div>
            </div>

            <!-- Ejemplos de información útil -->
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
              <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Información útil para incluir:
              </h4>
              <ul class="text-xs text-blue-800 dark:text-blue-200 space-y-1">
                <li>• Marca, modelo y especificaciones técnicas</li>
                <li>• Características de calidad o rendimiento requeridas</li>
                <li>• Compatibilidad con equipos existentes</li>
                <li>• Observaciones sobre el uso previsto</li>
                <li>• Cualquier detalle que ayude en la cotización</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Botones de acción -->
        <div class="mt-6 sm:mt-6 sm:flex sm:flex-row-reverse space-y-2 sm:space-y-0 sm:space-x-reverse sm:space-x-3">
          <button
            @click="guardar"
            type="button"
            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm transition-colors duration-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Guardar Comentario
          </button>
          
          <button
            @click="$emit('cerrar')"
            type="button"
            class="w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:w-auto sm:text-sm transition-colors duration-200"
          >
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'

const props = defineProps({
  comentario: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['guardar', 'cerrar'])

// Estado interno
const comentarioInterno = ref(props.comentario)
const textareaRef = ref(null)

// Watcher para actualizar el comentario interno cuando cambie la prop
watch(() => props.comentario, (nuevoComentario) => {
  comentarioInterno.value = nuevoComentario
}, { immediate: true })

// Enfocar el textarea cuando se monta el componente
nextTick(() => {
  if (textareaRef.value) {
    textareaRef.value.focus()
    // Posicionar el cursor al final del texto
    const length = comentarioInterno.value.length
    textareaRef.value.setSelectionRange(length, length)
  }
})

// Métodos
const guardar = () => {
  emit('guardar', comentarioInterno.value.trim())
}

// Manejar tecla Escape para cerrar
const handleKeydown = (event) => {
  if (event.key === 'Escape') {
    emit('cerrar')
  }
}

// Agregar listener para la tecla Escape
document.addEventListener('keydown', handleKeydown)

// Limpiar listener cuando el componente se desmonte
import { onUnmounted } from 'vue'
onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>