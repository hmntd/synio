<script setup>
import { computed } from 'vue'

const props = defineProps({
  workedHours: { type: Number, default: 0 },
  workingDays: { type: Number, default: 20 },
  hoursPerDay: { type: Number, default: 8 },
})

const plan = computed(() => props.workingDays * props.hoursPerDay)

const progress = computed(() => {
  const p = (props.workedHours / plan.value) * 100
  return Math.min(Math.max(p, 0), 100)
})

const color = computed(() => {
  if (progress.value < 50) return 'bg-red-500'
  if (progress.value < 90) return 'bg-yellow-500'
  return 'bg-green-500'
})
</script>

<template>
  <div class="w-full space-y-1">
    <div class="flex justify-between text-xs text-muted-foreground">
      <span>{{ workedHours.toFixed(1) }}h / {{ plan }}h</span>
      <span>{{ Math.round(progress) }}%</span>
    </div>

    <div class="h-3 w-full bg-muted rounded-full overflow-hidden">
      <div class="h-full transition-all duration-700 ease-in-out rounded-full" :class="color"
        :style="{ width: progress + '%' }"></div>
    </div>
  </div>
</template>
