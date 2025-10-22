<script setup lang="ts">
import Accordion from '@/components/ui/accordion/Accordion.vue';
import AccordionContent from '@/components/ui/accordion/AccordionContent.vue';
import AccordionItem from '@/components/ui/accordion/AccordionItem.vue';
import AccordionTrigger from '@/components/ui/accordion/AccordionTrigger.vue';
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { BreadcrumbItem, User } from '@/types';
import { Head } from '@inertiajs/vue3';
import { CheckCircle, Clock, X } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Mentorships',
        href: '/mentorships',
    },
];

const mentees = ref<{
    approved: User[],
    pending: User[],
    declined: User[],
}>({
    approved: [],
    pending: [],
    declined: [],
});

const weekdays = ref<number>(0);

const fetchMentees = async () => {
    const response = await fetch('/api/mentees', {
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        }
    });

    const data = await response.json();
    mentees.value = data;
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
    await fetchMentees();
    weekdays.value = Number(getWeekdaysInMonth(new Date().getMonth(), new Date().getFullYear()));
})
</script>

<template>

    <Head title="Mentorships" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">

            <div class="flex justify-end items-center p-4">
                <Button variant="default">
                    Request Mentorship
                </Button>
            </div>

            <!-- Accepted mentees -->
            <div>
                <h2 class="text-lg font-semibold mb-2 flex items-center gap-2">
                    <CheckCircle class="h-5 w-5 text-green-500" />
                    Accepted mentees
                </h2>

                <template v-if="mentees.approved && mentees.approved.length > 0">
                    <Accordion type="single" collapsible class="w-full">
                        <AccordionItem v-for="user in mentees.approved" :key="user.id" :value="`approved-${user.id}`"
                            class="border rounded-lg mb-2">
                            <AccordionTrigger class="flex justify-between items-center p-3 hover:bg-muted rounded-lg">
                                <div class="flex flex-col text-left">
                                    <span class="font-medium">{{ user.name }}</span>
                                    <span class="text-sm text-muted-foreground">{{ user.email }}</span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="flex flex-col items-center justify-center text-right">
                                        <span class="text-sm font-semibold">{{ user.monthly_hours }}h / {{
                                            user.daily_hours_target * weekdays }}h</span>
                                        <span class="text-xs"
                                            :class="user.monthly_hours >= (user.daily_hours_target * weekdays) ? 'text-green-500' : 'text-red-500'">
                                            {{ (user.monthly_hours >= (user.daily_hours_target * weekdays)) ? 'Goal met'
                                                :
                                            'Goal not met' }}
                                        </span>
                                    </div>
                                </div>
                            </AccordionTrigger>

                            <AccordionContent class="bg-muted/50 px-4 py-3 space-y-4">
                                <template v-if="user.projects.length">
                                    <div v-for="project in user.projects" :key="project.id" class="border-b pb-2">
                                        <h4 class="font-medium text-sm">Project: {{ project.name }}</h4>

                                        <template v-if="project.time_entries && project.time_entries.length">
                                            <ul class="ml-4 mt-1 text-sm text-muted-foreground space-y-1">
                                                <li v-for="entry in project.time_entries" :key="entry.id"
                                                    class="flex justify-between border-b border-neutral-700 pb-2 last:border-b-0">
                                                    <div class="flex flex-col gap-2">
                                                        <p>
                                                            <span class="text-white">Activity:</span>
                                                            {{ entry.activity.name }}
                                                        </p>
                                                        <p>
                                                            <span class="text-white">Description:</span>
                                                            {{ entry.description || 'No description' }}
                                                        </p>
                                                        <p>
                                                            <span class="text-white">Spent on:</span>
                                                            {{ new Date(entry.spent_on).toLocaleDateString() }}
                                                        </p>
                                                    </div>
                                                    <span>{{ entry.hours }}h</span>
                                                </li>
                                            </ul>
                                        </template>

                                        <p v-else class="text-xs text-muted-foreground ml-4">No time entries yet.</p>
                                    </div>
                                </template>

                                <p v-else class="text-sm text-muted-foreground">No projects yet.</p>
                            </AccordionContent>
                        </AccordionItem>
                    </Accordion>
                </template>
                <template v-else>
                    <div class="text-center py-6 text-sm text-muted-foreground border border-dashed rounded-lg">
                        <p>No accepted mentees found yet.</p>
                        <p class="text-xs">Once a mentee is approved, they’ll appear here.</p>
                    </div>
                </template>
            </div>

            <!-- Pending mentees -->
            <div v-if="mentees.pending.length !== 0">
                <h2 class="text-lg font-semibold mb-2 flex items-center gap-2">
                    <Clock class="h-5 w-5 text-yellow-500" />
                    Pending mentees
                </h2>

                <Accordion type="single" collapsible class="w-full">
                    <AccordionItem v-for="user in mentees.pending" :key="user.id" :value="`pending-${user.id}`"
                        class="border rounded-lg mb-2">
                        <AccordionTrigger class="flex justify-between items-center p-3 hover:bg-muted rounded-lg">
                            <div class="flex flex-col text-left">
                                <span class="font-medium">{{ user.name }}</span>
                                <span class="text-sm text-muted-foreground">{{ user.email }}</span>
                            </div>
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

            <!-- Declined mentees -->
            <div v-if="mentees.declined.length !== 0">
                <h2 class="text-lg font-semibold mb-2 flex items-center gap-2">
                    <X class="h-5 w-5 text-red-500" />
                    Declined mentees
                </h2>

                <Accordion type="single" collapsible class="w-full">
                    <AccordionItem v-for="user in mentees.declined" :key="user.id" :value="`declined-${user.id}`"
                        class="border rounded-lg mb-2">
                        <AccordionTrigger class="flex justify-between items-center p-3 hover:bg-muted rounded-lg">
                            <div class="flex flex-col text-left">
                                <span class="font-medium">{{ user.name }}</span>
                                <span class="text-sm text-muted-foreground">{{ user.email }}</span>
                            </div>
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
        </div>
    </AppLayout>
</template>