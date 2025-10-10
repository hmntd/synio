<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { Toaster } from '@/components/ui/sonner';
import 'vue-sonner/style.css'
import { onMounted } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

onMounted(async () => {
    const response = await fetch('/api/create-token', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    });

    const data = await response.json();
    localStorage.setItem('token', data.token);
})
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Toaster position="bottom-right" />
        <slot />
    </AppLayout>
</template>
