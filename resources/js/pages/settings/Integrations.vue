<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Eye, EyeOff, TestTube, CheckCircle, Loader2, RefreshCcw } from 'lucide-vue-next';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    integrations: {
        redmine_base_url?: string;
        redmine_api_key?: string;
        slack_user_id?: string;
        telegram_user_id?: string;
    };
    routes: {
        update: string;
        test_redmine: string;
        clear_redmine_key: string;
        test_slack: string;
        clear_slack_key: string;
        test_telegram: string;
        clear_telegram_key: string;
    };
}

const props = defineProps<Props>();
const page = usePage();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Integration settings',
        href: '/settings/integrations',
    },
];

// Form state
const form = useForm({
    redmine_base_url: props.integrations.redmine_base_url || '',
    redmine_api_key: '',
    slack_user_id: props.integrations.slack_user_id || '',
    telegram_user_id: props.integrations.telegram_user_id || '',
});

// UI state
const showRedmineKey = ref<boolean>(false);
const isTestingRedmine = ref<boolean>(false);
const isClearingRedmineKey = ref<boolean>(false);
const isTestingSlack = ref<boolean>(false);
const isClearingSlackKey = ref<boolean>(false);
const isTestingTelegram = ref<boolean>(false);
const isClearingTelegramKey = ref<boolean>(false);

const provided = ref<{
    redmine: boolean,
    slack: boolean,
    telegram: boolean,
}>({
    redmine: !!props.integrations.redmine_api_key,
    slack: !!props.integrations.slack_user_id,
    telegram: !!props.integrations.telegram_user_id,
});

const submit = () => {
    form.patch(props.routes.update, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Settings saved successfully!');
            form.redmine_api_key = '';
        },
        onError: () => {
            toast.error('Failed to save settings. Please check your input.');
        },
    });
};

const clearRedmineKey = async () => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    isClearingRedmineKey.value = true;
    const loadingToast = toast.loading('Clearing the API key...');

    try {
        const response = await fetch(props.routes.clear_redmine_key, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token as string,
            },
        });

        toast.dismiss(loadingToast);

        if (response.ok) {
            provided.value.redmine = false;
            toast.success('The API key cleared succesfull', {
                description: 'Your Redmine API key is cleared, you can enter the new.',
            });
        } else {
            toast.error('Error', {
                description: 'Please retry later',
            });
        }
    } catch (error) {
        toast.dismiss(loadingToast);
        toast.error('Error', {
            description: 'An unexpected error occurred.',
        });
    } finally {
        isClearingRedmineKey.value = false;
    }
};

const testRedmineConnection = async () => {
    if (!form.redmine_api_key) {
        toast.error('Please enter your Redmine API key first.');
        return;
    }

    isTestingRedmine.value = true;
    form.errors.redmine_api_key = '';
    const loadingToast = toast.loading('Testing connection...');
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    try {
        const response = await fetch(props.routes.test_redmine, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token as string,
            },
            body: JSON.stringify({
                redmine_api_key: form.redmine_api_key,
                redmine_base_url: form.redmine_base_url,
            }),
        });

        const data = await response.json();

        toast.dismiss(loadingToast);

        if (response.ok && data.success) {
            toast.success('Connection successful!', {
                description: 'Your Redmine API key is valid.',
            });
        } else {
            form.errors.redmine_api_key = 'Invalid API key';
            toast.error('Connection failed', {
                description: data.message || 'Please check your credentials.',
            });
        }
    } catch (error) {
        console.log('errror', error);
        toast.dismiss(loadingToast);
        toast.error('Connection failed', {
            description: 'An unexpected error occurred.',
        });
    } finally {
        isTestingRedmine.value = false;
    }
};

const testSlackConnection = async () => {
    if (!form.slack_user_id) {
        toast.error('Please enter your Slack account id first.');
        return;
    }

    isTestingRedmine.value = true;
    form.errors.slack_user_id = '';
    const loadingToast = toast.loading('Testing connection...');
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    try {
        const response = await fetch(props.routes.test_slack, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token as string,
            },
            body: JSON.stringify({
                slack_user_id: form.slack_user_id,
            }),
        });

        const data = await response.json();

        toast.dismiss(loadingToast);

        if (response.ok && data.success) {
            toast.success('Connection successful!', {
                description: 'Your Slack account id is valid.',
            });
        } else {
            form.errors.redmine_api_key = 'Invalid account ID';
            toast.error('Connection failed', {
                description: data.message || 'Please check your credentials.',
            });
        }
    } catch (error) {
        console.log('errror', error);
        toast.dismiss(loadingToast);
        toast.error('Connection failed', {
            description: 'An unexpected error occurred.',
        });
    } finally {
        isTestingRedmine.value = false;
    }
}

const clearSlackKey = async () => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    isClearingSlackKey.value = true;
    const loadingToast = toast.loading('Clearing the Slack key...');

    try {
        const response = await fetch(props.routes.clear_slack_key, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token as string,
            },
        });

        toast.dismiss(loadingToast);

        if (response.ok) {
            form.slack_user_id = '';
            provided.value.slack = false;
            toast.success('The Slack key cleared succesfull', {
                description: 'Your Slack account key is cleared, you can enter the new.',
            });
        } else {
            toast.error('Error', {
                description: 'Please retry later',
            });
        }
    } catch (error) {
        toast.dismiss(loadingToast);
        toast.error('Error', {
            description: 'An unexpected error occurred.',
        });
    } finally {
        isClearingSlackKey.value = false;
    }
}

const testTelegramConnection = async () => {
    if (!form.telegram_user_id) {
        toast.error('Please enter your Telegram account id first.');
        return;
    }

    isTestingTelegram.value = true;
    form.errors.telegram_user_id = '';
    const loadingToast = toast.loading('Testing connection...');
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    try {
        const response = await fetch(props.routes.test_telegram, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token as string,
            },
            body: JSON.stringify({
                telegram_user_id: form.telegram_user_id,
            }),
        });

        const data = await response.json();

        toast.dismiss(loadingToast);

        if (response.ok && data.success) {
            toast.success('Connection successful!', {
                description: 'Your Telegram account id is valid.',
            });
        } else {
            form.errors.telegram_user_id = 'Invalid account id';
            toast.error('Connection failed', {
                description: data.message || 'Please check your credentials.',
            });
        }
    } catch (error) {
        console.log('errror', error);
        toast.dismiss(loadingToast);
        toast.error('Connection failed', {
            description: 'An unexpected error occurred.',
        });
    } finally {
        isTestingTelegram.value = false;
    }
}

const clearTelegramKey = async () => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    isClearingTelegramKey.value = true;
    const loadingToast = toast.loading('Clearing the API key...');

    try {
        const response = await fetch(props.routes.clear_telegram_key, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token as string,
            },
        });

        toast.dismiss(loadingToast);

        if (response.ok) {
            form.telegram_user_id = '';
            provided.value.telegram = false;
            toast.success('The Telegram key cleared succesfull', {
                description: 'Your Telegram account key is cleared, you can enter the new.',
            });
        } else {
            toast.error('Error', {
                description: 'Please retry later',
            });
        }
    } catch (error) {
        toast.dismiss(loadingToast);
        toast.error('Error', {
            description: 'An unexpected error occurred.',
        });
    } finally {
        isClearingTelegramKey.value = false;
    }
}

watch(() => page.props.auth.user, () => {
    provided.value.redmine = page.props.auth.user.redmine_api_provided;
    provided.value.slack = page.props.auth.user.slack_provided;
    provided.value.telegram = page.props.auth.user.telegram_provided;
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Integration settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Integration settings" description="Connect your external services and tools" />

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Redmine Integration -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <div class="h-4 w-4 rounded bg-red-500"></div>
                                Redmine
                            </CardTitle>
                            <CardDescription>
                                Connect to your Redmine instance for time tracking synchronization.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="redmine_base_url">Base URL (optional)</Label>
                                <Input id="redmine_base_url" type="url" v-model="form.redmine_base_url"
                                    placeholder="https://your-redmine.com" class="font-mono text-sm"
                                    autocomplete="off" />
                                <p class="text-xs text-muted-foreground">
                                    Leave empty to use your organization's default Redmine URL.
                                </p>
                                <InputError :message="form.errors.redmine_base_url" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="redmine_api_key">API Key</Label>

                                <template v-if="provided.redmine">
                                    <div class="flex items-center justify-between rounded-md text-sm">
                                        <div class="flex items-center gap-2">
                                            <CheckCircle class="h-4 w-4 text-green-500" />
                                            <span>Redmine API key is connected</span>
                                        </div>

                                        <div class="flex gap-1">
                                            <Button variant="outline" size="sm" :disabled="isClearingRedmineKey"
                                                @click="clearRedmineKey">
                                                <Loader2 v-if="isClearingRedmineKey" class="h-4 w-4 animate-spin" />
                                                <RefreshCcw class="h-4 w-4 mr-1" /> Clear
                                            </Button>
                                        </div>
                                    </div>
                                </template>

                                <template v-else>
                                    <div class="relative">
                                        <Input id="redmine_api_key" :type="showRedmineKey ? 'text' : 'password'"
                                            v-model="form.redmine_api_key" :placeholder="props.integrations.redmine_api_key
                                                ? 'Enter new API key to change'
                                                : 'Enter your Redmine API key'" class="font-mono text-sm pr-20"
                                            autocomplete="new-password" />

                                        <div class="absolute right-1 top-1 flex gap-1">
                                            <Button type="button" variant="ghost" size="sm" class="h-8 w-8 p-0"
                                                @click="showRedmineKey = !showRedmineKey">
                                                <Eye v-if="!showRedmineKey" class="h-4 w-4" />
                                                <EyeOff v-else class="h-4 w-4" />
                                            </Button>

                                            <Button type="button" variant="ghost" size="sm" class="h-8 w-8 p-0"
                                                @click="testRedmineConnection"
                                                :disabled="isTestingRedmine || !form.redmine_api_key">
                                                <Loader2 v-if="isTestingRedmine" class="h-4 w-4 animate-spin" />
                                                <TestTube v-else class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </template>

                                <p class="text-xs text-muted-foreground">
                                    Find your API key in Redmine under My Account → API access key.
                                </p>
                                <InputError :message="form.errors.redmine_api_key" />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Slack Integration -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <div class="h-4 w-4 rounded bg-purple-500"></div>
                                Slack
                            </CardTitle>
                            <CardDescription>
                                Connect your Slack account to receive notifications and reminders.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="slack_user_id">User ID</Label>

                                <template v-if="provided.slack">
                                    <div class="flex items-center justify-between rounded-md text-sm">
                                        <div class="flex items-center gap-2">
                                            <CheckCircle class="h-4 w-4 text-green-500" />
                                            <span>Slack account is connected</span>
                                        </div>

                                        <div class="flex gap-1">
                                            <Button variant="outline" size="sm" :disabled="isClearingSlackKey"
                                                @click="clearSlackKey">
                                                <Loader2 v-if="isClearingSlackKey" class="h-4 w-4 animate-spin" />
                                                <RefreshCcw class="h-4 w-4 mr-1" /> Clear
                                            </Button>
                                        </div>
                                    </div>
                                </template>

                                <template v-else>
                                    <div class="relative">
                                        <Input id="slack_user_id" v-model="form.slack_user_id" placeholder="U01234ABCD"
                                            class="font-mono text-sm" autocomplete="off" />
                                        <div class="absolute right-1 top-1 flex gap-1">
                                            <Button type="button" variant="ghost" size="sm" class="h-8 w-8 p-0"
                                                @click="testSlackConnection"
                                                :disabled="isTestingSlack || !form.slack_user_id">
                                                <Loader2 v-if="isTestingSlack" class="h-4 w-4 animate-spin" />
                                                <TestTube v-else class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </template>

                                <p class="text-xs text-muted-foreground">
                                    Get your Slack User ID from your profile or ask your workspace admin.
                                    Click your profile picture → Profile → More → Copy member ID.
                                </p>
                                <InputError :message="form.errors.slack_user_id" />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Telegram Integration -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <div class="h-4 w-4 rounded bg-blue-500"></div>
                                Telegram
                            </CardTitle>
                            <CardDescription>
                                Connect your Telegram account to receive direct messages and updates.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="telegram_user_id">Chat ID</Label>

                                <template v-if="provided.telegram">
                                    <div class="flex items-center justify-between rounded-md text-sm">
                                        <div class="flex items-center gap-2">
                                            <CheckCircle class="h-4 w-4 text-green-500" />
                                            <span>Telegram account is connected</span>
                                        </div>

                                        <div class="flex gap-1">
                                            <Button variant="outline" size="sm" :disabled="isClearingTelegramKey"
                                                @click="clearTelegramKey">
                                                <Loader2 v-if="isClearingTelegramKey" class="h-4 w-4 animate-spin" />
                                                <RefreshCcw class="h-4 w-4 mr-1" /> Clear
                                            </Button>
                                        </div>
                                    </div>
                                </template>

                                <template v-else>
                                    <div class="relative">
                                        <Input id="telegram_user_id" v-model="form.telegram_user_id"
                                            placeholder="123456789" class="font-mono text-sm" autocomplete="off" />
                                        <div class="absolute right-1 top-1 flex gap-1">
                                            <Button type="button" variant="ghost" size="sm" class="h-8 w-8 p-0"
                                                @click="testTelegramConnection"
                                                :disabled="isTestingTelegram || !form.telegram_user_id">
                                                <Loader2 v-if="isTestingTelegram" class="h-4 w-4 animate-spin" />
                                                <TestTube v-else class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </template>

                                <p class="text-xs text-muted-foreground">
                                    To get your Chat ID: Start a chat with
                                    <code class="rounded bg-muted px-1 py-0.5 text-xs">@userinfobot</code>
                                    on Telegram and send any message. The bot will reply with your Chat ID.
                                </p>
                                <InputError :message="form.errors.telegram_user_id" />
                            </div>
                        </CardContent>
                    </Card>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <Button type="submit" :disabled="form.processing">
                                <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                Save integrations
                            </Button>

                            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                <p v-show="form.recentlySuccessful"
                                    class="flex items-center gap-2 text-sm text-green-600">
                                    <CheckCircle class="h-4 w-4" />
                                    Saved successfully
                                </p>
                            </Transition>
                        </div>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>