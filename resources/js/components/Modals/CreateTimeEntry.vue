<script setup lang="ts">
import {
    DateFormatter,

    DateValue,

    getLocalTimeZone,
    parseDate,
    today,
} from "@internationalized/date"
import Button from '../ui/button/Button.vue';
import DialogContent from '../ui/dialog/DialogContent.vue';
import DialogDescription from '../ui/dialog/DialogDescription.vue';
import DialogFooter from '../ui/dialog/DialogFooter.vue';
import DialogHeader from '../ui/dialog/DialogHeader.vue';
import DialogTitle from '../ui/dialog/DialogTitle.vue';
import Input from '../ui/input/Input.vue';
import Label from '../ui/label/Label.vue';
import { ref, watch } from 'vue';
import Calendar from '../ui/calendar/Calendar.vue';
import Popover from "../ui/popover/Popover.vue";
import PopoverTrigger from "../ui/popover/PopoverTrigger.vue";
import { CalendarIcon } from "lucide-vue-next";
import PopoverContent from "../ui/popover/PopoverContent.vue";
import { useForm } from "@inertiajs/vue3";
import DialogClose from "../ui/dialog/DialogClose.vue";
import { toast } from "vue-sonner";
import Spinner from "../ui/spinner/Spinner.vue";
import { TimeEntry } from "@/types";

interface Props {
    project_id: number;
    activities: { id: number; name: string }[];
    open: boolean;
    time_entry: TimeEntry | null;
}

const props = defineProps<Props>();
const emits = defineEmits(['close']);

const form = useForm({
    hours: "",
    comments: "",
    activity_id: "",
    spent_on: "",
    project_id: props.project_id,
    errors: {},
});

const df = new DateFormatter("en-US", {
    dateStyle: "long",
});
const date = ref<DateValue>(today(getLocalTimeZone()));
const creating = ref(false);

const onHoursInput = (e: Event) => {
    let value = (e.target as HTMLInputElement).value

    value = value.replace(/[^0-9.,]/g, '')

    value = value.replace(',', '.')

    if (value.includes('.')) {
        const [intPart, decPart] = value.split('.')
        value = intPart + '.' + decPart.slice(0, 2)
    }

    form.hours = value
}

const validate = () => {
    let valid = true;
    form.errors = {};

    if (!form.spent_on) {
        form.errors.spent_on = "Date is required.";
        valid = false;
    }

    if (!form.hours || isNaN(Number(form.hours))) {
        form.errors.hours = "Hours must be a number.";
        valid = false;
    }

    if (!form.activity_id) {
        form.errors.activity_id = "Please select an activity.";
        valid = false;
    }

    return valid;
};

const update = async () => {
    creating.value = true;

    const response = await fetch(`/api/time-entries/${props.time_entry?.id}`, {
        method: 'PUT',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        },
        body: JSON.stringify(form),
    });

    form.reset();
    creating.value = false;
    if (!response.ok) {
        const data = await response.json();
        form.errors = data.errors;
        toast.error('Failed to update time entry');
        return;
    }

    emits('close');
}

const submit = async () => {
    form.spent_on = df.format(date.value.toDate(getLocalTimeZone()));

    if (!validate()) return;

    if (props.time_entry) return update();

    creating.value = true;
    const response = await fetch('/api/time-entries', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        },
        body: JSON.stringify(form),
    });

    form.reset();
    creating.value = false;
    if (!response.ok) {
        const data = await response.json();
        form.errors = data.errors;
        toast.error('Failed to create time entry');
        return;
    }

    emits('close');
}

watch(() => props.time_entry, () => {
    if (!props.time_entry) {
        form.reset();
        date.value = today(getLocalTimeZone());
        return;
    }

    form.hours = props.time_entry.hours;
    form.comments = props.time_entry.comments;
    form.activity_id = props.time_entry.activity_id;
    form.spent_on = props.time_entry.spent_on;
    date.value = parseDate(props.time_entry.spent_on);
})
</script>

<template>
    <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
            <DialogTitle>{{ time_entry ? 'Edit' : 'Create' }} Time Entry</DialogTitle>
            <DialogDescription>
                Fill in the details for your time entry below, including hours, activity, and any comments. Click save
                when you're done.
            </DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4">
            <div class="grid grid-cols-4 items-center gap-4">
                <Label for="date" class="text-right">
                    Date
                </Label>
                <div class="flex flex-col col-span-3">
                    <Popover>
                        <PopoverTrigger as-child>
                            <Button id="date" variant="outline" :class="[
                                'w-[280px] justify-between text-left font-normal',
                                !date && 'text-muted-foreground'
                            ]">
                                {{ date ? df.format(date.toDate(getLocalTimeZone())) : "Pick a date" }}
                                <CalendarIcon class="mr-2 h-4 w-4" />
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0">
                            <Calendar v-model="date" initial-focus />
                        </PopoverContent>
                    </Popover>
                    <p v-if="form.errors.spent_on" class="text-destructive text-sm mt-1">{{ form.errors.spent_on }}</p>
                </div>
            </div>

            <div class="grid grid-cols-4 items-center gap-4">
                <Label for="hours" class="text-right">
                    Hours
                </Label>
                <div class="flex flex-col col-span-3">
                    <Input id="hours" v-model="form.hours" type="text" @input="onHoursInput"
                        placeholder="Enter hours" />
                    <p v-if="form.errors.hours" class="text-destructive text-sm mt-1">{{ form.errors.hours }}</p>
                </div>
            </div>

            <div class="grid grid-cols-4 items-center gap-4">
                <Label for="comments" class="text-right">
                    Comments
                </Label>
                <div class="flex flex-col col-span-3">
                    <Input id="comments" v-model="form.comments" type="text" placeholder="Enter the comments" />
                    <p v-if="form.errors.comments" class="text-destructive text-sm mt-1">{{ form.errors.comments }}</p>
                </div>
            </div>

            <div class="grid grid-cols-4 items-center gap-4">
                <Label for="activity" class="text-right">
                    Activity
                </Label>
                <div class="flex flex-col col-span-3 w-full">
                    <select id="activity" v-model="form.activity_id" class="h-9 w-full rounded-md border border-input 
                        bg-transparent dark:bg-input/30 px-3 py-1 text-sm
                        focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50 
                        transition-colors">
                        <option class="bg-background" v-for="activity in props.activities" :key="activity.id"
                            :value="activity.id">
                            {{ activity.name }}
                        </option>
                    </select>

                    <p v-if="form.errors.activity_id" class="text-destructive text-sm mt-1 col-span-3">{{
                        form.errors.activity_id }}
                    </p>
                </div>
            </div>
        </div>
        <DialogFooter>
            <DialogClose as-child>
                <Button variant="outline" :disabled="creating">Close</Button>
            </DialogClose>

            <Button type="submit" :disabled="creating" @click="submit">
                <Spinner v-if="creating" />{{ creating ? "Saving..." : "Save" }}
            </Button>
        </DialogFooter>
    </DialogContent>
</template>