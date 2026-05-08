<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, watch, onMounted } from 'vue';

import StatisticsDateForm from '@/components/StatisticsDateForm.vue';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Chart } from 'chart.js/auto';

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

const props = defineProps<{
    statistics?: any
}>();

const calendarDate = ref <CalendarDate | null>(null); // This will be bound to the date picker
const formattedCalendarDate = ref<string>(''); // This will be bound to the hidden form input
const statisticsCanvas = ref<HTMLCanvasElement | null>(null);

// This function draws the statistics of each parking stall
function drawStatistics() {
    if (!statisticsCanvas.value) {
        console.error("canvas was not found, cannot draw statistics");
        return;
    }

    const data = Array(...props.statistics.data);

    new Chart(
        statisticsCanvas.value,
        {
            type: 'bar',
            data: {
                labels: data.map(row => ('No. ' + row.id)),
                datasets: [
                    {
                        label: 'Drive In counts',
                        data: data.map(row => row.drive_in_count)
                    }
                ]
            }
        }
    );
}

watch(calendarDate, (newValue: CalendarDate | null) => {
    // If the new calendar value is not null
    if (newValue) {
        console.log(newValue);
        formattedCalendarDate.value = newValue.year + "-" + newValue.month + "-" + (newValue.day / 10 < 1 ? '0' + newValue.day : newValue.day);
    }
});

console.log("Statistics", props?.statistics);

onMounted(() => {
    drawStatistics();   // Drawing the charts once the component is rendered
});

</script>

<template>
    <Head title="Statistics" />

    <AppLayout :breadcrumbs="breadcrumbs" class="relative">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="w-full">
                <StatisticsDateForm
                    v-model:calendarDate="calendarDate"
                    v-model:formattedCalendarDate="formattedCalendarDate"
                />
            </div>

            <div class="w-full h-full grid place-items-center">
                <canvas class="w-full h-full" ref="statisticsCanvas" v-if="statistics"></canvas>
            </div>

            <template v-if="statistics">
                <!-- Simple pagination -->
                <div class="my-2 flex w-full items-center justify-center gap-2 px-2 max-sm:justify-start">
                    <!-- Rendering previous button -->
                    <component :is="statistics.prev_page_url ? Link : 'div'" v-bind="statistics.prev_page_url ? { href: statistics.prev_page_url } : {}">
                        <Button
                            type="button"
                            :variant="statistics.prev_page_url ? 'outline' : 'secondary'"
                            :class="statistics.prev_page_url ? 'cursor-pointer' : ''"
                        >
                            <ChevronLeft />
                            <p class="max-sm:hidden">Previous</p>
                        </Button>
                    </component>

                    <!-- Rendering next button -->
                    <component :is="statistics.next_page_url ? Link : 'div'" v-bind="statistics.next_page_url ? { href: statistics.next_page_url } : {}">
                        <Button
                            type="button"
                            :variant="statistics.next_page_url ? 'outline' : 'secondary'"
                            :class="statistics.next_page_url ? 'cursor-pointer' : ''"
                        >
                            <p class="max-sm:hidden">Next</p>
                            <ChevronRight />
                        </Button>
                    </component>
                </div>
            </template>
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
