<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { home } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, BookOpenText, Clock, FileClock, Folder, LayoutGrid, UsersRound } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { onMounted, ref } from 'vue';

const page = usePage();
const mainNavItems = ref<NavItem[]>([
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Projects',
        href: '/projects',
        icon: Folder,
    },
    {
        title: 'Time Entries',
        href: '/time-entries',
        icon: Clock,
    },
]);

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];

onMounted(() => {
    if (page.props.auth.user.canSendMentorshipRequests || page.props.auth.user.canApproveMentorshipRequests) {
        mainNavItems.value.push({
            title: 'Mentorships',
            href: '/mentorships',
            icon: BookOpenText,
        })
    }

    if (page.props.auth.user.canViewLogs) {
        mainNavItems.value.push({
            title: 'Activity Logs',
            href: '/activity-logs',
            icon: FileClock,
        });

        mainNavItems.value.push({
            title: 'Users',
            href: '/users',
            icon: UsersRound,
        })
    }
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="home()">
                        <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
