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
const progress = ref<number>(0);

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

onMounted(() => {
    const user = page.props.auth.user;
    if (user) {
        progress.value = user.daily_hours / user.daily_hours_target * 100;
    }
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
                <TooltipTrigger>
                    <Gauge :progress="progress" />
                </TooltipTrigger>
                <TooltipContent>
                    <p>Progress for today: {{ Math.round(page.props.auth.user.daily_hours) }} from {{
                        Math.round(page.props.auth.user.daily_hours_target) }}</p>
                </TooltipContent>
            </Tooltip>
        </div>
    </header>
</template>
