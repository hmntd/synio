<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Project, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import ProjectCard from '@/components/Cards/ProjectCard.vue';
import { computed } from 'vue';
import { Lightbulb } from 'lucide-vue-next';

interface Props {
    projects: Array<Project>;
    message: string;
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
];

const showEmptyMessage = computed(() => props.projects.length === 0 && props.message);
</script>

<template>

    <Head title="Projects" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div v-if="showEmptyMessage"
                class="flex flex-col gap-6 items-center justify-center py-20 text-center rounded-2xl transition">
                <Lightbulb class="size-16 text-gray-400 dark:text-gray-500" />
                <div class="flex flex-col items-center justify-center text-center">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-100">
                        {{ props.message }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                        Check your API key in the Settings
                    </p>
                </div>
            </div>

            <div v-else class="grid auto-rows-min gap-4 w-full md:grid-cols-3">
                <ProjectCard v-for="project in props.projects" :key="project.id" :project="project" />
            </div>
        </div>
    </AppLayout>
</template>
