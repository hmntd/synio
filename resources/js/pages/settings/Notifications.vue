<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { edit } from '@/routes/notifications';
import { Form, Head, usePage } from '@inertiajs/vue3';
import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import Switch from '@/components/ui/switch/Switch.vue';
import { ref } from 'vue';
import TimePicker from '@/components/TimePicker.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Notification settings',
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;
const frequency = ref('daily');
const time = ref('18:00');
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Notification settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Notifications settings" description="Configure your notification preferences" />

                <Form v-bind="ProfileController.update.form()" class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }">
                    <div class="grid gap-2">
                        <Label for="frequency">Frequency</Label>
                        <div class="inline-flex items-center border border-input rounded-lg overflow-hidden w-fit">
                            <button type="button" :class="[
                                'px-4 py-1.5 text-sm font-medium transition-colors cursor-pointer',
                                frequency === 'daily'
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-background text-muted-foreground hover:bg-accent',
                            ]" @click="frequency = 'daily'">
                                Daily
                            </button>
                            <button type="button" :class="[
                                'px-4 py-1.5 text-sm font-medium transition-colors cursor-pointer',
                                frequency === 'weekly'
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-background text-muted-foreground hover:bg-accent',
                            ]" @click="frequency = 'weekly'">
                                Weekly
                            </button>
                        </div>
                        <InputError class="mt-2" :message="errors.frequency" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Spent on</Label>
                        <TimePicker v-model="time" />
                        <InputError class="mt-2" :message="errors.email" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" data-test="update-profile-button">Save</Button>

                        <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-show="recentlySuccessful" class="text-sm text-neutral-600">
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>

        </SettingsLayout>
    </AppLayout>
</template>
