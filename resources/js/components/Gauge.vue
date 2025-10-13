<script setup>
import { computed } from 'vue'

const props = defineProps({
  progress: {
    type: Number,
    default: 0,
  },
})

const radius = 45;
const circumference = 2 * Math.PI * radius;

const clampedProgress = computed(() =>
  Math.min(Math.max(props.progress, 0), 100)
)

const offset = computed(() => {
  const progress = Math.min(Math.max(props.progress, 0), 100)
  return circumference - (progress / 100) * circumference
});
</script>

<template>
  <div class="relative w-10 h-10">
    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
      <circle class="text-muted stroke-current" stroke-width="10" cx="50" cy="50" r="45" fill="transparent" />
      <circle class="text-green-500 stroke-current transition-all duration-500 ease-in-out" stroke-width="10"
        stroke-linecap="round" :stroke-dasharray="circumference" :stroke-dashoffset="offset" cx="50" cy="50" r="45"
        fill="transparent" />
    </svg>

    <div class="absolute inset-0 flex items-center justify-center text-sm font-medium">
      {{ Math.round(clampedProgress) }}%
    </div>
  </div>
</template>
