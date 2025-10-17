<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { BreadcrumbItem, User } from '@/types';
import { Head } from '@inertiajs/vue3';
import { CircleCheck, CircleX } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

type UserGroups = {
    all: User[]
    developers: User[]
    mentors: User[]
    admins: User[]
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Users',
        href: '/users',
    },
];

const chosenTab = ref<keyof UserGroups>('all');
const users = ref<UserGroups>({
    'all': [],
    'developers': [],
    'mentors': [],
    'admins': [],
});
const tabs: (keyof UserGroups)[] = ['all', 'developers', 'mentors', 'admins'];
const weekdays = ref<number>(0);

const fetchUsers = async () => {
    const response = await fetch('/api/users', {
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        }
    });

    const data = await response.json();
    users.value = data;
}

const daysInMonth = (iMonth: number, iYear: number) => {
    return 32 - new Date(iYear, iMonth, 32).getDate();
}

const isWeekday = (year: number, month: number, day: number) => {
    var day = new Date(year, month, day).getDay();
    return day != 0 && day != 6;
}

const getWeekdaysInMonth = (month: number, year: number) => {
    var days = daysInMonth(month, year);
    var weekdays = 0;
    for (var i = 0; i < days; i++) {
        if (isWeekday(year, month, i + 1))
            weekdays++;
    }
    return weekdays;
}

onMounted(async () => {
    weekdays.value = Number(getWeekdaysInMonth(new Date().getMonth(), new Date().getFullYear()));
    await fetchUsers();
});
</script>

<template>

    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="flex flex-col items-center justify-center gap-2 p-3">
            <!-- Tabs -->
            <div class="inline-flex items-center border border-input rounded-2xl overflow-hidden w-full">
                <button v-for="tab in tabs" type="button" :class="[
                    'px-4 py-1 text-sm font-medium transition-colors cursor-pointer w-[25%]',
                    chosenTab === tab
                        ? 'bg-primary text-primary-foreground'
                        : 'bg-background text-muted-foreground hover:bg-accent',
                ]" @click="chosenTab = tab">
                    {{ tab.charAt(0).toUpperCase() + tab.slice(1) }}
                </button>
            </div>

            <!-- Table -->
            <table class="min-w-full border border-border rounded-lg overflow-hidden">
                <thead class="bg-muted">
                    <th class="px-4 py-2 text-center text-sm font-semibold text-muted-foreground">Name</th>
                    <th v-if="chosenTab === 'all'"
                        class="px-4 py-2 text-center text-sm font-semibold text-muted-foreground">Role</th>
                    <th class="px-4 py-2 text-center text-sm font-semibold text-muted-foreground">Email</th>
                    <th class="px-4 py-2 text-center text-sm font-semibold text-muted-foreground">Hours this Month</th>
                    <th class="px-4 py-2 text-center text-sm font-semibold text-muted-foreground">Completed This Month
                        Goal</th>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="user in users[chosenTab]" :key="user.id" class="hover:bg-muted/50">
                        <td class="px-4 py-2 text-sm text-center text-muted-foreground whitespace-nowrap">
                            {{ user.name }}
                        </td>
                        <td v-if="chosenTab === 'all'"
                            class="px-4 py-2 text-sm text-center text-muted-foreground whitespace-nowrap">
                            {{ user.roles[0].name }}
                        </td>
                        <td class="px-4 py-2 text-sm text-center text-muted-foreground whitespace-nowrap">
                            {{ user.email }}
                        </td>
                        <td class="px-4 py-2 text-sm text-center text-muted-foreground whitespace-nowrap">
                            {{ user.monthly_hours }}
                        </td>
                        <td
                            class="px-4 py-2 text-xs flex justify-center items-center text-muted-foreground whitespace-nowrap">
                            <CircleCheck v-if="user.monthly_hours >= (user.daily_hours_target * weekdays)"
                                class="text-green-600" />
                            <CircleX v-else class="text-red-400" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </AppLayout>
</template>