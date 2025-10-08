<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Project, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { onMounted } from 'vue';
import ProjectCard from '@/components/Cards/ProjectCard.vue';
import Button from '@/components/ui/button/Button.vue';
import { Clock, Plus } from 'lucide-vue-next';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';

interface Props {
    project: Project;
    time_entries: any[];
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
        <div class="space-y-6 p-6"> <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">{{ props.project.name }}</h1>
                    <p class="text-muted-foreground text-sm">Identifier: {{ props.project.identifier }}</p>
                    <p class="text-muted-foreground text-sm">Description: {{ props.project.description }}</p>
                </div>
                <Button variant='ghost'>
                    <Plus class="w-4 h-4 mr-2" /> Add Time Entry
                </Button>
            </div>
            <Card>
                <CardHeader>
                    <h2 class="text-lg font-medium flex items-center gap-2">
                        <Clock class="w-4 h-4" /> Time Entries
                    </h2>
                </CardHeader>
                <CardContent>
                    <div v-if="props.time_entries.length" class="divide-y divide-border">
                        <div v-for="entry in props.time_entries" :key="entry.id" class="flex justify-between py-3">
                            <div>
                                <div class="font-medium">{{ entry.user.name }}</div>
                                <div class="text-sm text-muted-foreground"> {{ entry.activity }} — {{ entry.comments ||
                                    'No comment' }} </div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold">{{ entry.hours }}h</div>
                                <div class="text-sm text-muted-foreground">{{ entry.spent_on }}</div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center text-muted-foreground py-6"> No time entries yet. </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
