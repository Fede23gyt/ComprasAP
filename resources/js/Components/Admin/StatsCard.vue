<template>
  <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
    <div class="p-5">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <component :is="icon" class="h-6 w-6" :class="iconColor" />
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              {{ title }}
            </dt>
            <dd class="flex items-baseline">
              <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                {{ formattedValue }}
              </div>
              <div v-if="change !== undefined" class="ml-2 flex items-baseline text-sm font-semibold"
                   :class="changeColor">
                <ArrowUpIcon v-if="change > 0" class="self-center flex-shrink-0 h-4 w-4" />
                <ArrowDownIcon v-else-if="change < 0" class="self-center flex-shrink-0 h-4 w-4" />
                <span class="sr-only"> {{ change >= 0 ? 'Increased' : 'Decreased' }} by </span>
                {{ Math.abs(change) }}{{ changeUnit }}
              </div>
            </dd>
          </dl>
        </div>
      </div>
      
      <!-- Subtitle or additional info -->
      <div v-if="subtitle" class="mt-3">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          {{ subtitle }}
        </p>
      </div>
      
      <!-- Progress bar -->
      <div v-if="progress !== undefined" class="mt-3">
        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
          <span>{{ progressLabel }}</span>
          <span>{{ progress }}%</span>
        </div>
        <div class="mt-1 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
          <div class="h-2 rounded-full transition-all duration-300"
               :class="progressColor"
               :style="`width: ${Math.min(progress, 100)}%`">
          </div>
        </div>
      </div>
    </div>
    
    <!-- Action button -->
    <div v-if="actionText && actionUrl" class="bg-gray-50 dark:bg-gray-700 px-5 py-3">
      <div class="text-sm">
        <Link :href="actionUrl" 
              class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300">
          {{ actionText }}
          <ChevronRightIcon class="inline-block ml-1 h-4 w-4" />
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ArrowUpIcon, ArrowDownIcon, ChevronRightIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [Number, String],
    required: true
  },
  icon: {
    type: Object,
    required: true
  },
  iconColor: {
    type: String,
    default: 'text-gray-400'
  },
  subtitle: String,
  change: Number,
  changeUnit: {
    type: String,
    default: '%'
  },
  progress: Number,
  progressLabel: String,
  progressColor: {
    type: String,
    default: 'bg-blue-500'
  },
  actionText: String,
  actionUrl: String,
  format: {
    type: String,
    default: 'number'
  }
})

const formattedValue = computed(() => {
  if (props.format === 'currency') {
    return new Intl.NumberFormat('es-ES', { 
      style: 'currency', 
      currency: 'USD' 
    }).format(props.value)
  } else if (props.format === 'percentage') {
    return `${props.value}%`
  } else if (props.format === 'number') {
    return new Intl.NumberFormat('es-ES').format(props.value)
  }
  return props.value
})

const changeColor = computed(() => {
  if (props.change === undefined) return ''
  return props.change > 0 ? 'text-green-600' : props.change < 0 ? 'text-red-600' : 'text-gray-500'
})
</script>