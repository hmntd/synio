<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import Gauge from './Gauge.vue';
import Tooltip from './ui/tooltip/Tooltip.vue';
import TooltipTrigger from './ui/tooltip/TooltipTrigger.vue';
import TooltipContent from './ui/tooltip/TooltipContent.vue';
import { usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const page = usePage();
const user = page.props.auth.user;
const weekdays = ref<number>(0);

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const daysInMonth = (iMonth: number, iYear: number) => {
    return 32 - new Date(iYear, iMonth, 32).getDate();
}

const isWeekday = (year: number, month: number, day: number) => {
    var day = new Date(year, month, day).getDay();
    return day != 0 && day != 6;
}

const getWeekdaysInMonth = (month: number, year: number) => {
    var days = daysInMonth(month, year);
    var weekdays = 0;
    for (var i = 0; i < days; i++) {
        if (isWeekday(year, month, i + 1))
            weekdays++;
    }
    return weekdays;
}

onMounted(() => {
    weekdays.value = Number(getWeekdaysInMonth(new Date().getMonth(), new Date().getFullYear()));
})
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-2">
                <SidebarTrigger class="-ml-1" />
                <template v-if="breadcrumbs && breadcrumbs.length > 0">
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </template>
            </div>
            <Tooltip>
                <TooltipTrigger class="w-[25%]">
                    <Gauge :hoursPerDay="Number(user.daily_hours_target)" :workedHours="Number(user.monthly_hours)"
                        :workingDays="weekdays" />
                </TooltipTrigger>
                <TooltipContent>
                    <p>Progress for today: {{ Math.round(user.daily_hours) }} from {{
                        Math.round(user.daily_hours_target) }}</p>
                </TooltipContent>
            </Tooltip>
        </div>
    </header>
</template>
