<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { edit } from '@/routes/notifications';
import { Form, Head, useForm, usePage } from '@inertiajs/vue3';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import TimePicker from '@/components/TimePicker.vue';
import { Loader2 } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import Switch from '@/components/ui/switch/Switch.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Notification settings',
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;
const form = useForm({
    frequency: user.notification_settings.frequency,
    time: user.notification_settings.send_at,
    enabled: Boolean(user.notification_settings.enabled),
    day_of_week: user.notification_settings.day_of_week,
});
const daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

const submit = () => {
    form.patch('/settings/notifications', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Settings saved successfully!');
        },
        onError: () => {
            toast.error('Failed to save settings. Please check your input.');
        },
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Notification settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Notifications settings" description="Configure your notification preferences" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="enabled">Enabled</Label>
                        <Switch v-model="form.enabled" />
                        <InputError class="mt-2" :message="form.errors.enabled" />
                    </div>

                    <div v-if="form.enabled" class="grid gap-2">
                        <Label for="frequency">Frequency</Label>
                        <div class="inline-flex items-center border border-input rounded-lg overflow-hidden w-fit">
                            <button type="button" :class="[
                                'px-4 py-1.5 text-sm font-medium transition-colors cursor-pointer',
                                form.frequency === 'daily'
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-background text-muted-foreground hover:bg-accent',
                            ]" @click="form.frequency = 'daily'">
                                Daily
                            </button>
                            <button type="button" :class="[
                                'px-4 py-1.5 text-sm font-medium transition-colors cursor-pointer',
                                form.frequency === 'weekly'
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-background text-muted-foreground hover:bg-accent',
                            ]" @click="form.frequency = 'weekly'">
                                Weekly
                            </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.frequency" />
                    </div>

                    <div v-if="form.enabled && form.frequency === 'weekly'" class="grid gap-2">
                        <Label for="day_of_week">Day of Week</Label>
                        <div class="inline-flex items-center border border-input rounded-lg overflow-hidden w-fit">
                            <button v-for="day in daysOfWeek" type="button" :class="[
                                'px-4 py-1.5 text-sm font-medium transition-colors cursor-pointer',
                                form.day_of_week === day
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-background text-muted-foreground hover:bg-accent',
                            ]" @click="form.day_of_week = day">
                                {{ day.charAt(0).toUpperCase() + day.slice(1) }}
                            </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.frequency" />
                    </div>

                    <div v-if="form.enabled" class="grid gap-2">
                        <Label for="spent_on">Spent on</Label>
                        <TimePicker v-model="form.time" />
                        <InputError class="mt-2" :message="form.errors.time" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button type="submit" :disabled="form.processing" data-test="update-profile-button">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Save
                        </Button>

                        <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-show="form.recentlySuccessful" class="text-sm text-green-600">
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>

        </SettingsLayout>
    </AppLayout>
</template>
