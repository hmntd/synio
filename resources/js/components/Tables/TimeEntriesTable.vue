<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { TimeEntry } from '@/types';
import { toast } from 'vue-sonner';
import Button from '../ui/button/Button.vue';
import { ArrowDownWideNarrow, ArrowUpNarrowWide, SquarePen, Trash2 } from 'lucide-vue-next';
import Tooltip from '../ui/tooltip/Tooltip.vue';
import Dialog from '../ui/dialog/Dialog.vue';
import DialogTrigger from '../ui/dialog/DialogTrigger.vue';
import TooltipTrigger from '../ui/tooltip/TooltipTrigger.vue';
import DialogContent from '../ui/dialog/DialogContent.vue';
import DialogHeader from '../ui/dialog/DialogHeader.vue';
import DialogTitle from '../ui/dialog/DialogTitle.vue';
import DialogClose from '../ui/dialog/DialogClose.vue';
import TooltipContent from '../ui/tooltip/TooltipContent.vue';
import Skeleton from '../ui/skeleton/Skeleton.vue';
import Spinner from '../ui/spinner/Spinner.vue';
import { router } from '@inertiajs/vue3';

interface ApiResponse {
    time_entries: TimeEntry[];
}

const emits = defineEmits(['edit']);

const sort = ref<'asc' | 'desc'>('desc');
const timeEntries = ref<TimeEntry[]>([]);
const isLoading = ref(false);
const isDeleting = ref(false);

const fetchTimeEntries = async () => {
    isLoading.value = true;
    try {
        const token = localStorage.getItem('token');
        const res = await fetch('/api/time-entries', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });
        if (!res.ok) throw new Error('Failed to fetch time entries');
        const data: ApiResponse = await res.json();
        timeEntries.value = data.time_entries;
    } catch (e) {
        console.error(e);
        toast.error('Failed to load time entries');
    } finally {
        isLoading.value = false;
    }
}

const handleClick = (projectId: string) => {
    router.visit('/projects/' + projectId);
}

const editEntry = (entry: TimeEntry) => {
    emits('edit', entry);
};

const deleteEntry = async (id: number) => {
    isDeleting.value = true;
    try {
        const token = localStorage.getItem('token');
        const res = await fetch(`/api/time-entries/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });
        if (!res.ok) throw new Error('Failed to delete time entry');
        toast.success('Time entry deleted successfully!');
        fetchTimeEntries();
    } catch (e) {
        console.error(e);
        toast.error('Failed to delete time entry');
    } finally {
        isDeleting.value = false;
    }
};

onMounted(fetchTimeEntries);

defineExpose({
    fetchTimeEntries,
})
</script>

<template>
    <div class="overflow-x-auto w-full">
        <table v-if="!isLoading && timeEntries.length"
            class="min-w-full border border-border rounded-lg overflow-hidden">
            <thead class="bg-muted">
                <tr>
                    <th class="flex items-center gap-1 px-4 py-2 text-center text-sm font-semibold text-muted-foreground cursor-pointer"
                        @click="sort = sort === 'asc' ? 'desc' : 'asc'">
                        Date
                        <ArrowDownWideNarrow v-if="sort === 'desc'" :size="14" />
                        <ArrowUpNarrowWide v-else :size="14" />
                    </th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-muted-foreground">Project</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-muted-foreground">User</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-muted-foreground">Activity</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-muted-foreground">Comments</th>
                    <th class="px-4 py-2 text-center text-sm font-semibold text-muted-foreground">Hours</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-muted-foreground">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                <tr v-for="entry in timeEntries" :key="entry.id" class="hover:bg-muted/50">
                    <td class="px-4 py-2 text-sm text-left text-muted-foreground whitespace-nowrap">
                        {{ entry.spent_on }}</td>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <td class="px-4 py-2 text-sm font-medium text-left cursor-pointer"
                                @click="handleClick(entry.project.id)">
                                {{ entry.project.name }}
                            </td>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Go to the {{ entry.project.name }} project</p>
                        </TooltipContent>
                    </Tooltip>

                    <td class="px-4 py-2 text-sm font-medium">{{ entry.user.name }}</td>
                    <td class="px-4 py-2 text-sm text-muted-foreground">{{ entry.activity.name }}</td>
                    <td class="px-4 py-2 text-sm text-muted-foreground">
                        {{ entry.comments || 'No comment' }}
                    </td>
                    <td class="px-4 py-2 text-sm text-center font-semibold">{{ entry.hours }}h</td>
                    <td class="px-4 py-2 text-sm text-left font-semibold">
                        <div class="flex items-center">
                            <Tooltip>
                                <TooltipTrigger>
                                    <Button variant="ghost" size="sm"
                                        class="text-muted-foreground dark:hover:text-white" @click="editEntry(entry)">
                                        <SquarePen class="w-4 h-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>Edit time entry</p>
                                </TooltipContent>
                            </Tooltip>

                            <Tooltip>
                                <Dialog>
                                    <DialogTrigger as-child>
                                        <TooltipTrigger as-child>
                                            <Button variant="ghost" size="sm"
                                                class="text-muted-foreground dark:hover:text-white">
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </TooltipTrigger>
                                    </DialogTrigger>
                                    <DialogContent class="sm:max-w-md">
                                        <DialogHeader>
                                            <DialogTitle>Confirm Deletion</DialogTitle>
                                        </DialogHeader>
                                        <p>Are you sure you want to delete this entry?</p>
                                        <div class="flex justify-end gap-2">
                                            <DialogClose as-child>
                                                <Button variant="outline" :disabled="isDeleting">Cancel</Button>
                                            </DialogClose>

                                            <Button @click="deleteEntry(entry.id)" :disabled="isDeleting">
                                                <Spinner v-if="isDeleting" />{{ isDeleting ? "Deleting..." : "Delete" }}
                                            </Button>
                                        </div>
                                    </DialogContent>
                                </Dialog>
                                <TooltipContent>
                                    <p>Delete time entry</p>
                                </TooltipContent>
                            </Tooltip>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-else-if="isLoading" class="w-full h-full flex flex-col gap-2">
            <Skeleton v-for="i in 5" class="h-8 w-full" />
        </div>

        <p v-else class="text-sm text-muted-foreground text-center py-4">
            No time entries found.
        </p>
    </div>
</template>