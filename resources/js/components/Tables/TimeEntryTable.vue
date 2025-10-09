<script setup lang="ts">
import { ArrowDownWideNarrow, ArrowUpNarrowWide, ChevronLeft, ChevronRight, SquarePen, Trash2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import Button from '../ui/button/Button.vue';
import { TimeEntry } from '@/types';
import { router } from '@inertiajs/vue3';

interface Props {
    time_entries: {
        data: TimeEntry[];
        current_page: number;
        last_page: number;
        links: { url: string | null; label: string; active: boolean }[];
    };
    per_page: number;
    direction: string;
}

const props = defineProps<Props>();
const sort = ref(props.direction);
const perPage = ref(props.per_page);

const changePage = (page: number) => {
    if (page < 1 || page > props.time_entries.last_page) return
    router.get(window.location.pathname, { page, per_page: perPage.value }, { preserveScroll: true })
};

watch(sort, (value) => {
    router.get(window.location.pathname, { per_page: perPage.value, sort: value }, { preserveScroll: true })
})

watch(perPage, (value) => {
    router.get(window.location.pathname, { per_page: value, sort: sort.value }, { preserveScroll: true })
});

const pages = computed(() => {
    const total = props.time_entries.last_page;
    const current = props.time_entries.current_page;
    const visible = [];

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
</script>

<template>
    <div class="overflow-x-auto w-full">
        <table v-if="props.time_entries" class="min-w-full border border-border rounded-lg overflow-hidden">
            <thead class="bg-muted">
                <tr>
                    <th class="flex items-center gap-1 px-4 py-2 text-center text-sm font-semibold text-muted-foreground cursor-pointer"
                        @click="sort = sort === 'asc' ? 'desc' : 'asc'">
                        Date
                        <ArrowDownWideNarrow v-if="props.direction === 'desc'" :size="14" />
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
                <tr v-for="entry in props.time_entries.data" :key="entry.id" class="hover:bg-muted/50">
                    <td class="px-4 py-2 text-sm text-center text-muted-foreground whitespace-nowrap">
                        {{ entry.spent_on }}</td>
                    <td class="px-4 py-2 text-sm font-medium">{{ entry.user.name }}</td>
                    <td class="px-4 py-2 text-sm text-muted-foreground">{{ entry.activity.name }}</td>
                    <td class="px-4 py-2 text-sm text-muted-foreground">
                        {{ entry.comments || 'No comment' }}
                    </td>
                    <td class="px-4 py-2 text-sm text-center font-semibold">{{ entry.hours }}h</td>
                    <td class="px-4 py-2 text-sm text-left font-semibold">
                        <div class="flex items-center">
                            <Button variant="ghost" size="sm" class="text-muted-foreground dark:hover:text-white">
                                <SquarePen class="w-4 h-4" />
                            </Button>

                            <Button variant="ghost" size="sm" class="text-muted-foreground dark:hover:text-white">
                                <Trash2 class="w-4 h-4" />
                            </Button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <p v-else class="text-sm text-muted-foreground text-center py-4">
            No time entries found.
        </p>

        <div class="flex justify-between items-center mt-4">
            <div />
            <!-- Pagination -->
            <div class="flex justify-center items-center gap-2">
                <Button variant="ghost" class="h-8 w-8 rounded-2xl" :disabled="props.time_entries.current_page === 1"
                    @click="changePage(props.time_entries.current_page - 1)">
                    <ChevronLeft />
                </Button>

                <template v-for="(page, index) in pages" :key="index">
                    <Button v-if="page !== '...'" variant="ghost" size="sm" class="h-8 w-8 rounded-2xl" :class="{
                        'bg-primary text-primary-foreground': page === props.time_entries.current_page,
                    }" @click="changePage(page as number)">
                        {{ page }}
                    </Button>
                    <span v-else class="px-2 text-muted-foreground select-none">…</span>
                </template>

                <Button variant="ghost" class="h-8 w-8 rounded-2xl"
                    :disabled="props.time_entries.current_page === props.time_entries.last_page"
                    @click="changePage(props.time_entries.current_page + 1)">
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