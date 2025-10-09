<script setup lang="ts">
import {
  DateFormatter,

  DateValue,

  getLocalTimeZone,
} from "@internationalized/date"
import Button from '../ui/button/Button.vue';
import DialogContent from '../ui/dialog/DialogContent.vue';
import DialogDescription from '../ui/dialog/DialogDescription.vue';
import DialogFooter from '../ui/dialog/DialogFooter.vue';
import DialogHeader from '../ui/dialog/DialogHeader.vue';
import DialogTitle from '../ui/dialog/DialogTitle.vue';
import Input from '../ui/input/Input.vue';
import Label from '../ui/label/Label.vue';
import { ref } from 'vue';
import Calendar from '../ui/calendar/Calendar.vue';
import Popover from "../ui/popover/Popover.vue";
import PopoverTrigger from "../ui/popover/PopoverTrigger.vue";
import { CalendarIcon } from "lucide-vue-next";
import PopoverContent from "../ui/popover/PopoverContent.vue";

interface Props {
    activities: { id: number; name: string }[];
}

const props = defineProps<Props>();

const df = new DateFormatter("en-US", {
  dateStyle: "long",
})
const date = ref<DateValue>();
</script>

<template>
    <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
            <DialogTitle>Edit profile</DialogTitle>
            <DialogDescription>
                Make changes to your profile here. Click save when you're done.
            </DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4">
            <div class="grid grid-cols-4 items-center gap-4">
                <Popover>
                    <PopoverTrigger as-child>
                        <Button variant="outline" :class="cn(
                            'w-[280px] justify-start text-left font-normal',
                            !date && 'text-muted-foreground',
                        )">
                            <CalendarIcon class="mr-2 h-4 w-4" />
                            {{ date ? df.format(date.toDate(getLocalTimeZone())) : "Pick a date" }}
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent class="w-auto p-0">
                        <Calendar v-model="date" initial-focus />
                    </PopoverContent>
                </Popover>
            </div>
            <div class="grid grid-cols-4 items-center gap-4">
                <Label for="username" class="text-right">
                    Username
                </Label>
                <Input id="username" value="@peduarte" class="col-span-3" />
            </div>
        </div>
        <DialogFooter>
            <Button type="submit">
                Save changes
            </Button>
        </DialogFooter>
    </DialogContent>
</template>