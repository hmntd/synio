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
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';

interface Props {
    project: Project;
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

const openCreate = ref<boolean>(false);
const timeEntryTableRef = ref<InstanceType<typeof TimeEntryTable> | null>(null);
const choosedTimeEntry = ref<TimeEntry | null>(null);

const handleCreatedTimeEntry = () => {
    const toastMessage = choosedTimeEntry.value ? 'Time entry updated successfully!' : 'Time entry created successfully!';
    openCreate.value = false;
    timeEntryTableRef.value?.fetchTimeEntries();
    toast.success(toastMessage);
}

const handleEdit = (timeEntry: TimeEntry) => {
    choosedTimeEntry.value = timeEntry;
    openCreate.value = true;
    return;
}

watch(() => openCreate.value, () => {
    if (!openCreate.value) {
        choosedTimeEntry.value = null
    }
});
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
                <Dialog v-model:open="openCreate">
                    <DialogTrigger as-child>
                        <Button variant='ghost'>
                            <Plus class="w-4 h-4 mr-2" /> Add Time Entry
                        </Button>
                    </DialogTrigger>
                    <CreateTimeEntry :project_id="props.project.id" :activities="props.activities" :open="openCreate"
                        :time_entry="choosedTimeEntry" @close="handleCreatedTimeEntry" />
                </Dialog>
            </div>
            <Card>
                <CardHeader>
                    <h2 class="text-lg font-medium flex items-center gap-2">
                        <Clock class="w-4 h-4" /> Time Entries
                    </h2>
                </CardHeader>
                <CardContent>
                    <TimeEntryTable ref="timeEntryTableRef" :project_id="props.project.id" @edit="handleEdit" />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
