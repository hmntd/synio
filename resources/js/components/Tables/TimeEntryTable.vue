<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { TimeEntry } from '@/types';
import { toast } from 'vue-sonner';
import Button from '../ui/button/Button.vue';
import { ArrowDownWideNarrow, ArrowUpNarrowWide, ChevronLeft, ChevronRight, SquarePen, Trash2 } from 'lucide-vue-next';
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

interface ApiResponse {
    data: TimeEntry[];
    current_page: number;
    last_page: number;
    per_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Props {
    project_id: number;
}

const props = defineProps<Props>();
const emits = defineEmits(['edit']);

const sort = ref<'asc' | 'desc'>('desc');
const perPage = ref(25);
const page = ref(1);
const timeEntries = ref<TimeEntry[]>([]);
const pagination = ref({
    current_page: 1,
    last_page: 1,
    links: [] as { url: string | null; label: string; active: boolean }[],
});
const isLoading = ref(false);
const isDeleting = ref(false);

const fetchTimeEntries = async () => {
    isLoading.value = true;
    try {
        const token = localStorage.getItem('token');
        const res = await fetch(`/api/projects/${props.project_id}/time-entries?page=${page.value}&per_page=${perPage.value}&sort=${sort.value}`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });
        if (!res.ok) throw new Error('Failed to fetch time entries');
        const data: ApiResponse = await res.json();
        timeEntries.value = data.data;
        pagination.value = {
            current_page: data.current_page,
            last_page: data.last_page,
            links: data.links,
        };
    } catch (e) {
        console.error(e);
        toast.error('Failed to load time entries');
    } finally {
        isLoading.value = false;
    }
};

watch([sort, perPage], () => {
    page.value = 1;
    fetchTimeEntries();
});

const changePage = (newPage: number) => {
    if (newPage < 1 || newPage > pagination.value.last_page) return;
    page.value = newPage;
    fetchTimeEntries();
};

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

const pages = computed(() => {
    const total = pagination.value.last_page;
    const current = pagination.value.current_page;
    const visible: (number | string)[] = [];

    if (total <= 7) {
        for (let i = 1; i <= total; i++) visible.push(i);
    } else {
        if (current <= 3) {
            visible.push(1, 2, 3, 4, '...', total);
        } else if (current >= total - 2) {
            visible.push(1, '...', total - 3, total - 2, total - 1, total);
        } else {
            visible.push(1, '...', current - 1, current, current + 1, '...', total);
        }
    }
    return visible;
});

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

        <div class="flex justify-between items-center mt-4">
            <div />
            <!-- Pagination -->
            <div class="flex justify-center items-center gap-2">
                <Button variant="ghost" class="h-8 w-8 rounded-2xl" :disabled="pagination.current_page === 1"
                    @click="changePage(pagination.current_page - 1)">
                    <ChevronLeft />
                </Button>

                <template v-for="(page, index) in pages" :key="index">
                    <Button v-if="page !== '...'" variant="ghost" size="sm" class="h-8 w-8 rounded-2xl" :class="{
                        'bg-primary text-primary-foreground': page === pagination.current_page,
                    }" @click="changePage(page as number)">
                        {{ page }}
                    </Button>
                    <span v-else class="px-2 text-muted-foreground select-none">…</span>
                </template>

                <Button variant="ghost" class="h-8 w-8 rounded-2xl"
                    :disabled="pagination.current_page === pagination.last_page"
                    @click="changePage(pagination.current_page + 1)">
                    <ChevronRight />
                </Button>
            </div>

            <div class="flex items-center gap-2 p-2">
                <label for="per-page" class="text-sm text-muted-foreground">Per page:</label>
                <select id="per-page" v-model="perPage" class="h-8 p-1 rounded-md border border-border bg-background text-sm text-foreground
                        focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2
                        transition-colors duration-150
                        hover:bg-muted/50
                        dark:bg-background dark:text-foreground dark:border-border">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>

            </div>
        </div>
    </div>
</template>