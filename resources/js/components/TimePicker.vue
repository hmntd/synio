<script setup lang="ts">
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    modelValue: String, // format: "HH:mm"
})
const emit = defineEmits(['update:modelValue'])

const open = ref(false)
const hours = ref(0)
const minutes = ref(0)

const displayTime = computed(() => {
    return `${String(hours.value).padStart(2, '0')}:${String(minutes.value).padStart(2, '0')}`
});

const onTimeInput = (e: Event, type: 'hours' | 'minutes') => {
    const input = e.target as HTMLInputElement
    let value = input.value.replace(/[^0-9]/g, '')

    if (value === '') {
        input.value = ''
        return
    }

    let num = parseInt(value, 10)

    if (type === 'hours') {
        num = Math.min(Math.max(num, 0), 23)
        hours.value = num
    } else {
        num = Math.min(Math.max(num, 0), 59)
        minutes.value = num
    }

    input.value = String(num).padStart(2, '0')
}


watch([hours, minutes], () => {
    emit('update:modelValue', displayTime.value)
})

watch(
    () => props.modelValue,
    (val) => {
        if (!val) return
        const [h, m] = val.split(':').map(Number)
        hours.value = h
        minutes.value = m
    },
    { immediate: true }
)

function toggle() {
    open.value = !open.value
}

function increment(type: string) {
    if (type === 'hours') hours.value = (hours.value + 1) % 24
    else minutes.value = (minutes.value + 1) % 60
}

function decrement(type: string) {
    if (type === 'hours') hours.value = (hours.value + 23) % 24
    else minutes.value = (minutes.value + 59) % 60
}

// close on outside click
function onClickOutside(e: MouseEvent) {
    const target = e.target as HTMLElement | null
    if (!target?.closest('.relative.inline-block')) {
        open.value = false
    }
}

onMounted(() => document.addEventListener('click', onClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', onClickOutside))
</script>

<template>
    <div class="relative inline-block w-full">
        <div class="h-9 w-full flex items-center justify-between rounded-md border border-input bg-transparent px-3 py-1 text-sm
             focus-within:ring-2 focus-within:ring-ring/50 transition-colors cursor-pointer" @click="toggle">
            <span>{{ displayTime }}</span>
            <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </div>

        <div v-if="open"
            class="absolute z-10 mt-1 left-0 w-full rounded-md border border-input bg-popover shadow-md p-3 flex justify-center gap-2">
            <div class="flex flex-col items-center">
                <button type="button" class="text-lg cursor-pointer opacity-60 hover:opacity-100"
                    @click="increment('hours')">▲</button>
                <input type="text" v-model="hours" min="0" max="23"
                    class="w-12 text-center bg-transparent border border-input rounded-md text-sm py-0.5"
                    @input="onTimeInput($event, 'hours')" />
                <button type="button" class="text-lg cursor-pointer opacity-60 hover:opacity-100"
                    @click="decrement('hours')">▼</button>
            </div>

            <span class="text-lg font-medium">:</span>

            <div class="flex flex-col items-center">
                <button type="button" class="text-lg cursor-pointer opacity-60 hover:opacity-100"
                    @click="increment('minutes')">▲</button>
                <input type="text" v-model="minutes" min="0" max="59"
                    class="w-12 text-center bg-transparent border border-input rounded-md text-sm py-0.5"
                    @input="onTimeInput($event, 'minutes')" />
                <button type="button" class="text-lg cursor-pointer opacity-60 hover:opacity-100"
                    @click="decrement('minutes')">▼</button>
            </div>
        </div>
    </div>
</template>
