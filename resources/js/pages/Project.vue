<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Project, TimeEntry, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import { Clock, Plus } from 'lucide-vue-next';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import TimeEntryTable from '@/components/Tables/TimeEntryTable.vue';
import Dialog from '@/components/ui/dialog/Dialog.vue';
import DialogTrigger from '@/components/ui/dialog/DialogTrigger.vue';
import CreateTimeEntry from '@/components/Modals/CreateTimeEntry.vue';
import { onMounted } from 'vue';

interface Props {
    project: Project;
    time_entries: {
        data: TimeEntry[];
        current_page: number;
        last_page: number;
        links: { url: string | null; label: string; active: boolean }[];
    };
    per_page: number;
    direction: string;
    activities: { id: number; name: string }[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Projects',
        href: '/projects',
    },
    {
        title: props.project.name,
        href: '/projects/' + props.project.id,
    },
];
</script>

<template>

    <Head :title="props.project.name + ' - Project'" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">{{ props.project.name }}</h1>
                    <p class="text-muted-foreground text-sm">Identifier: {{ props.project.identifier }}</p>
                    <p class="text-muted-foreground text-sm">Description: {{ props.project.description }}</p>
                </div>
                <Dialog>
                    <DialogTrigger as-child>
                        <Button variant='ghost'>
                            <Plus class="w-4 h-4 mr-2" /> Add Time Entry
                        </Button>
                    </DialogTrigger>
                    <CreateTimeEntry :activities="props.activities" />
                </Dialog>
            </div>
            <Card>
                <CardHeader>
                    <h2 class="text-lg font-medium flex items-center gap-2">
                        <Clock class="w-4 h-4" /> Time Entries
                    </h2>
                </CardHeader>
                <CardContent>
                    <TimeEntryTable :time_entries="props.time_entries" :per_page="props.per_page"
                        :direction="direction" />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
