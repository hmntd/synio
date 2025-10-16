<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { TimeEntry, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import TimeEntriesTable from '@/components/Tables/TimeEntriesTable.vue';
import { Clock, Lightbulb, Plus } from 'lucide-vue-next';
import Dialog from '@/components/ui/dialog/Dialog.vue';
import DialogTrigger from '@/components/ui/dialog/DialogTrigger.vue';
import Button from '@/components/ui/button/Button.vue';
import CreateTimeEntry from '@/components/Modals/CreateTimeEntry.vue';
import { onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Time Entries',
        href: '/time-entries',
    },
];

const timeEntriesTableRef = ref<InstanceType<typeof TimeEntriesTable> | null>(null);
const openCreate = ref<boolean>(false);
const choosedTimeEntry = ref<TimeEntry | null>(null);
const activities = ref([]);
const showEmptyMessage = ref<boolean>(false);

const handleCreatedTimeEntry = (timeEntry: TimeEntry) => {
    const toastMessage = choosedTimeEntry.value ? 'Time entry updated successfully!' : 'Time entry created successfully!';
    openCreate.value = false;
    timeEntriesTableRef.value?.fetchTimeEntries();
    toast.success(toastMessage);
};

watch(() => openCreate.value, () => {
    if (!openCreate.value) {
        choosedTimeEntry.value = null
    }
});

onMounted(async () => {
    const response = await fetch('/api/activities', {
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        }
    });
    const data = await response.json();
    activities.value = data.activities;
});
</script>

<template>

    <Head title="Time Entries" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

            <div class="flex justify-between">
                <h2 class="text-lg font-medium flex items-center gap-2">
                    <Clock class="w-4 h-4" /> Time Entries
                </h2>
                <Dialog v-if="!showEmptyMessage" v-model:open="openCreate">
                    <DialogTrigger as-child>
                        <Button variant='ghost'>
                            <Plus class="w-4 h-4 mr-2" /> Add Time Entry
                        </Button>
                    </DialogTrigger>
                    <CreateTimeEntry :project_id="choosedTimeEntry?.project_id" :activities="activities"
                        :open="openCreate" :time_entry="choosedTimeEntry" @close="handleCreatedTimeEntry" />
                </Dialog>
            </div>

            <div v-if="showEmptyMessage"
                class="flex flex-col gap-6 items-center justify-center py-20 text-center rounded-2xl transition">
                <Lightbulb class="size-16 text-gray-400 dark:text-gray-500" />
                <div class="flex flex-col items-center justify-center text-center">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-100">
                        Failed to load time entries
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                        Check your API key in the Settings
                    </p>
                </div>
            </div>

            <TimeEntriesTable ref="timeEntriesTableRef" @edit="choosedTimeEntry = $event; openCreate = true"
                @show-empty="showEmptyMessage = $event" />
        </div>
    </AppLayout>
</template>
