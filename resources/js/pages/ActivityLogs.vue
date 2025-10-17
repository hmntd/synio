<script setup lang="ts">
import Accordion from '@/components/ui/accordion/Accordion.vue';
import AccordionContent from '@/components/ui/accordion/AccordionContent.vue';
import AccordionItem from '@/components/ui/accordion/AccordionItem.vue';
import AccordionTrigger from '@/components/ui/accordion/AccordionTrigger.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { BreadcrumbItem, User } from '@/types';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Activity logs',
        href: '/activity-logs',
    },
];

const defaultValue = "item-1";
const accordionItems = ref<{
    users: User[],
}>({
    users: [],
});

const fetchLogs = async () => {
    const response = await fetch('/api/activity-logs', {
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        }
    });

    const data = await response.json();
    accordionItems.value.users = data.users;
}

onMounted(async () => {
    await fetchLogs();
})
</script>

<template>

    <Head title="Activity logs" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="p-3">
            <Accordion type="single" collapsible class="w-full">
                <AccordionItem v-for="user in accordionItems.users" :key="user.id" :value="`user-${user.id}`"
                    class="border rounded-lg mb-2">
                    <AccordionTrigger class="flex justify-between items-center p-3 hover:bg-muted rounded-lg">
                        <div class="flex flex-col text-left">
                            <span class="font-medium">{{ user.name }}</span>
                            <span class="text-sm text-muted-foreground">{{ user.email }}</span>
                        </div>
                        <Badge variant="outline">{{ user.roles[0] }}</Badge>
                    </AccordionTrigger>

                    <AccordionContent class="bg-muted/50 px-4 py-3 space-y-2">
                        <template v-if="user.activity_logs.length">
                            <div v-for="log in user.activity_logs" :key="log.id"
                                class="border-b last:border-b-0 py-1 text-sm">
                                <p>{{ log.action }}</p>
                                <p class="text-xs text-muted-foreground">{{ log.created_at }}</p>
                            </div>
                        </template>
                        <p v-else class="text-sm text-muted-foreground">No activity logs yet.</p>
                    </AccordionContent>
                </AccordionItem>
            </Accordion>
        </div>

    </AppLayout>
</template>