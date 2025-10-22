<script setup lang="ts">
import { Pencil, Trash2 } from 'lucide-vue-next';
import Dialog from '../ui/dialog/Dialog.vue';
import DialogTrigger from '../ui/dialog/DialogTrigger.vue';
import Tooltip from '../ui/tooltip/Tooltip.vue';
import TooltipTrigger from '../ui/tooltip/TooltipTrigger.vue';
import TooltipContent from '../ui/tooltip/TooltipContent.vue';
import DialogContent from '../ui/dialog/DialogContent.vue';
import DialogHeader from '../ui/dialog/DialogHeader.vue';
import DialogTitle from '../ui/dialog/DialogTitle.vue';
import DialogDescription from '../ui/dialog/DialogDescription.vue';
import Label from '../ui/label/Label.vue';
import Input from '../ui/input/Input.vue';
import Select from '../ui/select/Select.vue';
import SelectTrigger from '../ui/select/SelectTrigger.vue';
import SelectValue from '../ui/select/SelectValue.vue';
import SelectContent from '../ui/select/SelectContent.vue';
import SelectItem from '../ui/select/SelectItem.vue';
import DialogClose from '../ui/dialog/DialogClose.vue';
import Button from '../ui/button/Button.vue';
import Spinner from '../ui/spinner/Spinner.vue';
import { onMounted, ref } from 'vue';
import { User } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

const props = defineProps<{
    user: User;
    roles: Array<any>;
}>();

const emits = defineEmits(['deleted', 'updated']);
const page = usePage();
const isDeleting = ref<boolean>(false);
const isSaving = ref<boolean>(false);
const openEditDialog = ref<boolean>(false);
const roles = ref([]);

const editForm = ref({
    name: props.user.name,
    email: props.user.email,
    role_id: props.user.roles[0].id,
});

const updateUser = async () => {
    if (props.user.id === page.props.auth.user.id) {
        return;
    }

    const response = await fetch(`/api/users/${props.user.id}`, {
        method: 'PUT',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        },
        body: JSON.stringify(editForm.value),
    });

    if (!response.ok) {
        console.error('Failed to update user');
        return toast.error('Failed to update user');
    }

    toast.success('User updated successfully!');
    openEditDialog.value = false;
    emits('updated');
}

const deleteUser = async () => {
    if (props.user.id === page.props.auth.user.id) {
        return;
    }

    const response = await fetch(`/api/users/${props.user.id}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        }
    });

    if (!response.ok) {
        console.error('Failed to delete user');
        return toast.error('Failed to delete user');
    }

    toast.success('User deleted successfully!');
    emits('deleted');
}
</script>

<template>
    <div class="flex items-center justify-center gap-8">
        <Tooltip>
            <template v-if="user.id !== page.props.auth.user.id">
                <Dialog v-model:open="openEditDialog">
                    <DialogTrigger as-child>
                        <TooltipTrigger as-child>
                            <Pencil class="cursor-pointer h-4 w-4" />
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Edit user</p>
                        </TooltipContent>
                    </DialogTrigger>

                    <DialogContent class="sm:max-w-md">
                        <DialogHeader>
                            <DialogTitle>Edit User</DialogTitle>
                            <DialogDescription>
                                Update the user’s name, email, or role.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="space-y-4">
                            <div class="grid gap-1">
                                <Label for="name">Name</Label>
                                <Input id="name" v-model="editForm.name" placeholder="Enter name" />
                            </div>

                            <div class="grid gap-1">
                                <Label for="email">Email</Label>
                                <Input id="email" type="email" v-model="editForm.email" placeholder="Enter email" />
                            </div>

                            <div class="grid gap-1">
                                <Label for="role">Role</Label>
                                <Select v-model="editForm.role_id">
                                    <SelectTrigger class="dark:bg-input/30" id="role">
                                        <SelectValue placeholder="Select a role" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="role in props.roles" :value="role.id">{{ role.name }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="flex justify-end gap-2 pt-2">
                                <DialogClose as-child>
                                    <Button variant="outline">Cancel</Button>
                                </DialogClose>

                                <Button :disabled="isSaving" @click="updateUser">
                                    <Spinner v-if="isSaving" class="h-4 w-4 animate-spin" />
                                    {{ isSaving ? 'Saving...' : 'Save Changes' }}
                                </Button>
                            </div>
                        </div>
                    </DialogContent>
                </Dialog>
            </template>

            <template v-else>
                <TooltipTrigger as-child>
                    <Pencil class="h-4 w-4 text-muted-foreground cursor-not-allowed opacity-50" />
                </TooltipTrigger>
                <TooltipContent>
                    <p>You cannot edit yourself</p>
                </TooltipContent>
            </template>
        </Tooltip>

        <Tooltip>
            <template v-if="user.id !== page.props.auth.user.id">
                <Dialog>
                    <DialogTrigger as-child>
                        <TooltipTrigger as-child>
                            <Trash2 class="cursor-pointer h-4 w-4" />
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Delete user</p>
                        </TooltipContent>
                    </DialogTrigger>

                    <DialogContent class="sm:max-w-md">
                        <DialogHeader>
                            <DialogTitle>Confirm Deletion</DialogTitle>
                        </DialogHeader>
                        <p>Are you sure you want to delete this user?</p>
                        <div class="flex justify-end gap-2">
                            <DialogClose as-child>
                                <Button variant="outline" :disabled="isDeleting">Cancel</Button>
                            </DialogClose>

                            <Button @click="deleteUser" :disabled="isDeleting">
                                <Spinner v-if="isDeleting" />
                                {{ isDeleting ? 'Deleting...' : 'Delete' }}
                            </Button>
                        </div>
                    </DialogContent>
                </Dialog>
            </template>

            <template v-else>
                <TooltipTrigger as-child>
                    <Trash2 class="h-4 w-4 text-muted-foreground cursor-not-allowed opacity-50" />
                </TooltipTrigger>
                <TooltipContent>
                    <p>You cannot delete yourself</p>
                </TooltipContent>
            </template>
        </Tooltip>
    </div>
</template>