<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Project, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import ProjectCard from '@/components/Cards/ProjectCard.vue';

interface Props {
    projects: Array<Project>;
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
</script>

<template>

    <Head title="Projects" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 w-full md:grid-cols-3">
                <ProjectCard v-for="project in props.projects" :key="project.id" :project="project" />
            </div>
        </div>
    </AppLayout>
</template>
