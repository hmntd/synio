<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Project, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import ProjectCard from '@/components/Cards/ProjectCard.vue';
import { computed, onMounted, ref } from 'vue';
import { Lightbulb } from 'lucide-vue-next';
import ProjectCardSkeleton from '@/components/Skeletons/ProjectCardSkeleton.vue';

interface ApiResponse {
    projects: Project[],
    message: string,
}

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

const projects = ref<Project[]>([]);
const message = ref<string>('');
const isLoading = ref<boolean>(false);

const showEmptyMessage = computed(() => projects.value.length === 0 && message.value);

const fetchProjects = async () => {
    isLoading.value = true;
    try {
        const token = localStorage.getItem('token');
        const res = await fetch('/api/projects', {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
        });
        if (!res.ok) throw new Error('Failed to fetch projects');
        const data: ApiResponse = await res.json();
        projects.value = data.projects;
        message.value = data.message;
    } catch (e) {
        console.error(e);
        message.value = 'Failed to load projects';
    } finally {
        isLoading.value = false;
    }
}

onMounted(async () => {
    await fetchProjects();
});
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
                        {{ message }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                        Check your API key in the Settings
                    </p>
                </div>
            </div>

            <div v-else class="grid auto-rows-min gap-4 w-full md:grid-cols-3">
                <ProjectCardSkeleton v-if="isLoading" v-for="i in 9" />
                <ProjectCard v-else v-for="project in projects" :key="project.id" :project="project" />
            </div>
        </div>
    </AppLayout>
</template>
