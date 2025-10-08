<script setup lang="ts">
import { Badge } from 'lucide-vue-next';
import Card from '../ui/card/Card.vue';
import { Project } from '@/types';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    project: Project
}>();

const handleClick = () => {
    router.visit('/projects/' + props.project.id);
}
</script>

<template>
    <Card
        class="p-4 flex flex-col justify-between cursor-pointer hover:bg-sidebar-accent hover:text-sidebar-accent-foreground transition-shadow">
        <div class="flex items-start justify-between" @click="handleClick">
            <div>
                <h3 class="text-lg font-semibold text-foreground">{{ props.project.name }}</h3>
                <p v-if="props.project.identifier" class="text-sm text-muted-foreground"> Identifier: {{
                    props.project.identifier }}
                </p>
            </div>
            <Badge variant="secondary" v-if="props.project.is_active"> Active </Badge>
        </div>
        <p v-if="props.project.description" class="mt-2 text-sm text-muted-foreground line-clamp-3"> {{
            props.project.description }}
        </p>
    </Card>
</template>