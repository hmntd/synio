<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { Toaster } from '@/components/ui/sonner';
import 'vue-sonner/style.css'
import { ref } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

const loading = ref<boolean>(false);

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

</script>

<template>
    <AppLayout v-if="!loading" :breadcrumbs="breadcrumbs">
        <Toaster position="bottom-right" />
        <slot />
    </AppLayout>
</template>
