<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import { CalendarDays, ChevronLeft, ChevronRight, LoaderCircle, Search } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import {
    DatePickerArrow,
    DatePickerCalendar,
    DatePickerCell,
    DatePickerCellTrigger,
    DatePickerContent,
    DatePickerField,
    DatePickerGrid,
    DatePickerGridBody,
    DatePickerGridHead,
    DatePickerGridRow,
    DatePickerHeadCell,
    DatePickerHeader,
    DatePickerHeading,
    DatePickerInput,
    DatePickerNext,
    DatePickerPrev,
    DatePickerRoot,
    DatePickerTrigger,
} from 'reka-ui';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import InputError from '@/components/InputError.vue';


// Calendar date interface for date picker calendar
interface CalendarDate {
    day: number,
    month: number,
    year: number,
    era: string,
    calendar: {
        identifier: string
    },
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Statistics',
        href: '/statistics',
    },
];

const calendarDate = ref <CalendarDate | null>(null); // This will be bound to the date picker
const formattedCalendarDate = ref<string>(''); // This will be bound to the hidden form input

watch(calendarDate, (newCalendarDate) => {
    console.log(newCalendarDate);
    formattedCalendarDate.value = newCalendarDate?.year + "-" + newCalendarDate?.month + "-" + newCalendarDate?.day;
});

const props = defineProps<{
    statistics?: any
}>();

console.log("Statistics", props?.statistics);

</script>

<template>
    <Head title="Statistics" />

    <AppLayout :breadcrumbs="breadcrumbs" class="relative">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="w-full">
                <Form method="get" :action="route('statistics.show')" v-slot="{ errors, processing }" class="w-full flex justify-center" >
                    <DatePickerRoot :is-date-unavailable="(date) => date.day === 19" class="flex gap-4">
                        <DatePickerField
                            v-slot="{ segments }"
                            class="text-green10 flex items-center justify-between rounded-lg border bg-white p-1 text-center shadow-sm select-none data-[invalid]:border-red-500"
                            v-model="calendarDate"
                        >
                            <div class="flex items-center">
                                <template v-for="item in segments" :key="item.part">
                                    <DatePickerInput v-if="item.part === 'literal'" :part="item.part">
                                        {{ item.value }}
                                    </DatePickerInput>
                                    <DatePickerInput
                                        v-else
                                        :part="item.part"
                                        class="data-[placeholder]:text-green9 rounded p-0.5 focus:shadow-[0_0_0_2px] focus:shadow-black focus:outline-none"
                                    >
                                        {{ item.value }}
                                    </DatePickerInput>
                                </template>
                            </div>

                            <DatePickerTrigger class="scale-75 rounded p-1 focus:shadow-[0_0_0_2px] focus:shadow-black">
                                <CalendarDays />
                            </DatePickerTrigger>

                            <!-- Submit button of the form -->
                            <Button type="submit" :disabled="processing">
                                <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                                <Search v-else />
                            </Button>
                        </DatePickerField>

                        <!-- Hidden input that contains the formatted date -->
                        <Input name="date" type="hidden" v-model="formattedCalendarDate" />

                        <!-- Errors input received from the request -->
                        <InputError :message="errors.date" />

                        <DatePickerContent
                            :side-offset="4"
                            class="data-[state=open]:data-[side=top]:animate-slideDownAndFade data-[state=open]:data-[side=right]:animate-slideLeftAndFade data-[state=open]:data-[side=bottom]:animate-slideUpAndFade data-[state=open]:data-[side=left]:animate-slideRightAndFade rounded-xl border bg-white shadow-sm will-change-[transform,opacity]"
                        >
                            <DatePickerArrow class="fill-white stroke-gray-300" />
                            <DatePickerCalendar v-slot="{ weekDays, grid }" class="p-4" v-model="calendarDate">
                                <DatePickerHeader class="flex items-center justify-between">
                                    <DatePickerPrev
                                        class="inline-flex h-7 w-7 cursor-pointer items-center justify-center rounded-md bg-transparent text-black hover:bg-stone-50 focus:shadow-[0_0_0_2px] focus:shadow-black active:scale-98 active:transition-all"
                                    >
                                        <ChevronLeft />
                                    </DatePickerPrev>

                                    <DatePickerHeading class="font-medium text-black" />
                                    <DatePickerNext
                                        class="inline-flex h-7 w-7 cursor-pointer items-center justify-center rounded-md bg-transparent text-black hover:bg-stone-50 focus:shadow-[0_0_0_2px] focus:shadow-black active:scale-98 active:transition-all"
                                    >
                                        <ChevronRight />
                                    </DatePickerNext>
                                </DatePickerHeader>
                                <div class="flex flex-col space-y-4 pt-4 sm:flex-row sm:space-y-0 sm:space-x-4">
                                    <DatePickerGrid
                                        v-for="month in grid"
                                        :key="month.value.toString()"
                                        class="w-full border-collapse space-y-1 select-none"
                                    >
                                        <DatePickerGridHead>
                                            <DatePickerGridRow class="mb-1 flex w-full justify-between">
                                                <DatePickerHeadCell v-for="day in weekDays" :key="day" class="text-green8 w-8 rounded-md text-xs">
                                                    {{ day }}
                                                </DatePickerHeadCell>
                                            </DatePickerGridRow>
                                        </DatePickerGridHead>
                                        <DatePickerGridBody>
                                            <DatePickerGridRow
                                                v-for="(weekDates, index) in month.rows"
                                                :key="`weekDate-${index}`"
                                                class="flex w-full"
                                            >
                                                <DatePickerCell v-for="weekDate in weekDates" :key="weekDate.toString()" :date="weekDate">
                                                    <DatePickerCellTrigger
                                                        :day="weekDate"
                                                        :month="month.value"
                                                        class="data-[today]:before:bg-green9 relative flex h-8 w-8 items-center justify-center rounded-[9px] border border-transparent bg-transparent text-sm font-normal whitespace-nowrap text-black outline-none before:absolute before:top-[5px] before:hidden before:h-1 before:w-1 before:rounded-full before:bg-white hover:border-black focus:shadow-[0_0_0_2px] focus:shadow-black data-[outside-view]:text-black/30 data-[selected]:bg-black data-[selected]:font-medium data-[selected]:text-white data-[selected]:before:bg-white data-[today]:before:block data-[unavailable]:pointer-events-none data-[unavailable]:text-black/30 data-[unavailable]:line-through"
                                                    />
                                                </DatePickerCell>
                                            </DatePickerGridRow>
                                        </DatePickerGridBody>
                                    </DatePickerGrid>
                                </div>
                            </DatePickerCalendar>
                        </DatePickerContent>
                    </DatePickerRoot>
                </Form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped></style>
