<script setup lang="ts">
import UserTableActions from '@/components/Tables/UserTableActions.vue';
import Button from '@/components/ui/button/Button.vue';
import Dialog from '@/components/ui/dialog/Dialog.vue';
import DialogTrigger from '@/components/ui/dialog/DialogTrigger.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { BreadcrumbItem, User } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { CircleCheck, CircleX } from 'lucide-vue-next';
import { onMounted, ref, watch } from 'vue';
import { useAuthToken } from '@/composables/useAuthToken';
import DialogContent from '@/components/ui/dialog/DialogContent.vue';
import DialogHeader from '@/components/ui/dialog/DialogHeader.vue';
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue';
import DialogFooter from '@/components/ui/dialog/DialogFooter.vue';
import Input from '@/components/ui/input/Input.vue';
import Select from '@/components/ui/select/Select.vue';
import SelectTrigger from '@/components/ui/select/SelectTrigger.vue';
import SelectValue from '@/components/ui/select/SelectValue.vue';
import SelectContent from '@/components/ui/select/SelectContent.vue';
import SelectItem from '@/components/ui/select/SelectItem.vue';
import { toast } from 'vue-sonner';

const { ensureToken } = useAuthToken();

type UserGroups = {
    all: User[]
    developers: User[]
    mentors: User[]
    admins: User[]
    invitations: {
        email: string,
        role_id: number,
    }[]
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
    'invitations': [],
});
const roles = ref([]);
const tabs: (keyof UserGroups)[] = ['all', 'developers', 'mentors', 'admins', 'invitations'];
const weekdays = ref<number>(0);
const openInvite = ref<boolean>(false);
const form = useForm({
    email: '',
    role_id: '',
});

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

const fetchRoles = async () => {
    const response = await fetch('/api/roles', {
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        }
    });

    const data = await response.json();
    roles.value = data.roles;
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

const inviteUser = async () => {
    const response = await fetch('/api/users/invitations', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        },
        body: JSON.stringify(form)
    });

    if (response.ok) {
        toast.success('User invited successfully');
        openInvite.value = false;
        await fetchUsers();
    }
}

watch(() => openInvite.value, (newVal) => {
    if (!newVal) {
        form.reset();
    }
})

onMounted(async () => {
    await ensureToken();
    weekdays.value = Number(getWeekdaysInMonth(new Date().getMonth(), new Date().getFullYear()));
    await fetchUsers();
    await fetchRoles();
});
</script>

<template>

    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="flex justify-end items-center p-3">
            <Dialog v-model:open="openInvite">
                <DialogTrigger>
                    <Button variant="default">
                        Invite new user
                    </Button>
                </DialogTrigger>
                <DialogContent class="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>Invite new user</DialogTitle>
                        <Input v-model="form.email" placeholder="Email" />
                        <Select v-model="form.role_id">
                            <SelectTrigger class="dark:bg-input/30" id="role">
                                <SelectValue placeholder="Select a role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="role in roles" :value="role.id">{{ role.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <DialogFooter>
                            <Button type="submit" @click="inviteUser">
                                Save changes
                            </Button>
                        </DialogFooter>
                    </DialogHeader>
                </DialogContent>
            </Dialog>
        </div>

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
                    <tr>
                        <th v-if="chosenTab !== 'invitations'"
                            class="px-4 py-2 text-center text-sm font-semibold text-muted-foreground">Name</th>
                        <th v-if="chosenTab === 'all' || chosenTab === 'invitations'"
                            class="px-4 py-2 text-center text-sm font-semibold text-muted-foreground">Role</th>
                        <th class="px-4 py-2 text-center text-sm font-semibold text-muted-foreground">Email</th>
                        <th v-if="chosenTab !== 'invitations'"
                            class="px-4 py-2 text-center text-sm font-semibold text-muted-foreground">Hours this Month
                        </th>
                        <th v-if="chosenTab !== 'invitations'"
                            class="px-4 py-2 text-center text-sm font-semibold text-muted-foreground">Completed This
                            Month
                            Goal</th>
                        <th class="px-4 py-2 text-center text-sm font-semibold text-muted-foreground">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="user in users[chosenTab]" :key="user.id" class="hover:bg-muted/50">
                        <td v-if="chosenTab !== 'invitations'"
                            class="px-4 py-2 text-sm text-center text-muted-foreground whitespace-nowrap">
                            {{ user.name }}
                        </td>
                        <td v-if="chosenTab === 'all' || chosenTab === 'invitations'"
                            class="px-4 py-2 text-sm text-center text-muted-foreground whitespace-nowrap">
                            {{ user.roles ? user.roles[0].name : user.role.name }}
                        </td>
                        <td class="px-4 py-2 text-sm text-center text-muted-foreground whitespace-nowrap">
                            {{ user.email }}
                        </td>
                        <td v-if="chosenTab !== 'invitations'"
                            class="px-4 py-2 text-sm text-center text-muted-foreground whitespace-nowrap">
                            {{ user.monthly_hours }}
                        </td>
                        <td v-if="chosenTab !== 'invitations'"
                            class="px-4 py-2 text-xs flex justify-center items-center text-muted-foreground whitespace-nowrap">
                            <div class="flex items-center justify-center">
                                <CircleCheck v-if="user.monthly_hours >= (user.daily_hours_target * weekdays)"
                                    class="text-green-600 h-5 w-5" />
                                <CircleX v-else class="text-red-400 h-5 w-5" />
                            </div>
                        </td>
                        <td class="px-4 py-2 text-sm text-center text-muted-foreground whitespace-nowrap">
                            <UserTableActions :user="user" @deleted="fetchUsers" @updated="fetchUsers" :roles="roles" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </AppLayout>
</template>