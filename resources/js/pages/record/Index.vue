<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, RecordPagination, Record } from '@/types';
import { Head, Link, Form } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuGroup,
    DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    ChevronLeft, ChevronRight, ChevronsUpDown,
    LoaderCircle, Rocket, CalendarArrowDown,
    CalendarArrowUp, ClockArrowDown, ClockArrowUp,
    ArrowDownAZ, ArrowUpAZ, ArrowUp01, ArrowDown01,
} from 'lucide-vue-next';
import { recordsArrayInsertSort } from '@/lib/utils';
import InputError from '@/components/InputError.vue';
import { usePage } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Record',
        href: '/record',
    },
];

const props = defineProps<{
    records: RecordPagination;
}>();

const page = usePage();

// This is for rendering the sorted records
const recordsData = ref<Record[]>(props.records.data);
const selectedSortOption = ref<{label: string, direction: number}>({label: '', direction: 0});
const serverSorting = ref<boolean>(false);

// This function handles the record sorting the sorting options is chosen
function handleRecordSorting(sortingOption: keyof Record, direction: number) {
    if (!sortingOption || !direction) {
        console.log('Invalid sorting option or direction cannot sort the records');
    }

    // Sorting the records and saving them in the variable
    recordsData.value = recordsArrayInsertSort(direction, props.records.data, sortingOption);

    // Saving the sorting option in the session storage
    if (window) {
        window.sessionStorage.setItem('sorting_option', sortingOption);
        window.sessionStorage.setItem('direction', JSON.stringify(direction));
    }

    // Save the sorting options (label and direction) in a variable
    selectedSortOption.value.label = sortingOption;
    selectedSortOption.value.direction = direction;
}

// The records table's column labels
const tableFieldLabels = ['Date', 'Time', 'User', 'Action', 'Stall ID', 'Car plates'];

// Select menu's options
const selectOptions = [
    [
        { text: 'By date asc', icon: CalendarArrowUp, sorting_label: 'date', direction: 1},
        { text: 'By date desc', icon: CalendarArrowDown, sorting_label: 'date', direction: -1 },
    ],
    [
        { text: 'By time asc', icon: ClockArrowUp, sorting_label: 'time', direction: 1 },
        { text: 'By time desc', icon: ClockArrowDown, sorting_label: 'time', direction: -1 },
    ],
    [
        { text: 'By user asc', icon: ArrowUpAZ, sorting_label: 'user', direction: 1 },
        { text: 'By user desc', icon: ArrowDownAZ, sorting_label: 'user', direction: -1 },
    ],
    [
        { text: 'By stall id asc', icon: ArrowUp01, sorting_label: 'parking_space_id', direction: 1 },
        { text: 'By stall id desc', icon: ArrowDown01, sorting_label: 'parking_space_id', direction: -1 },
    ],
    [
        {text: 'By car plates asc', icon: ArrowUpAZ, sorting_label: 'registration_plates', direction: 1 },
        {text: 'By car plates desc', icon: ArrowDownAZ, sorting_label: 'registration_plates', direction: -1 },
    ],
];

onMounted(() => {
    // If the user previously chose a sorting option then sort the records based on it
    if (window.sessionStorage.getItem('sorting_option') && window.sessionStorage.getItem('direction')) {
        const sortingOption = window.sessionStorage.getItem('sorting_option');  // get the sorting option
        const direction = window.sessionStorage.getItem('direction');   // get the sorting direction

        // Sorting the records
        recordsData.value = recordsArrayInsertSort(Number(direction), props.records.data, sortingOption as keyof Record);

        selectedSortOption.value.label = sortingOption as string;    // save the sorting option label in the variable
        selectedSortOption.value.direction = Number(direction);   // save the sorting option direction in the variable
    }
})

watch(serverSorting, (newValue) => {
   console.log("Checkbox selected: ", newValue);
});

console.log("Records", props.records);
</script>

<template>
    <Head title="Parking" />

    <AppLayout :breadcrumbs="breadcrumbs" class="relative">
        <div class="flex h-full flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid h-12 w-full grid-cols-6 grid-rows-1 items-center gap-1 border-b border-solid max-sm:w-2xl">

                <!-- Rendering table's field labels -->
                <div v-for="(label, i) in tableFieldLabels" :key="i">
                    <p><b>{{ label }}</b></p>
                </div>

            </div>

            <!-- Rendering table's records with the data from the props -->
            <div
                v-for="record in recordsData"
                :key="record.id"
                class="grid w-full grid-cols-6 grid-rows-1 items-center gap-1 border-b border-solid max-sm:w-2xl"
            >
                <div>
                    <p>{{ record.date }}</p>
                </div>

                <div>
                    <p>{{ record.time }}</p>
                </div>

                <div>
                    <p :class="record.action === 'blocked' || record.action === 'unblocked' ? 'underline' : ''">{{ record.user }}</p>
                </div>

                <div>
                    <p>{{ record.action }}</p>
                </div>

                <div>
                    <p>{{ record.parking_space_id }}</p>
                </div>

                <div>
                    <p>{{ record.registration_plates }}</p>
                </div>
            </div>
        </div>

        <!-- Simple pagination -->
        <div class="my-4 flex w-full items-center justify-center gap-2 px-2 max-sm:justify-start">
            <!-- Rendering previous button -->
            <component :is="records.links.prev ? Link : 'div'" v-bind="records.links.prev ? { href: records.links.prev } : {}">
                <Button
                    type="button"
                    :variant="records.links.prev ? 'outline' : 'secondary'"
                    :class="records.links.prev ? 'cursor-pointer' : ''"
                >
                    <ChevronLeft />
                    <p class="max-sm:hidden">Previous</p>
                </Button>
            </component>

            <!-- Rendering next button -->
            <component :is="records.links.next ? Link : 'div'" v-bind="records.links.next ? { href: records.links.next } : {}">
                <Button
                    type="button"
                    :variant="records.links.next ? 'outline' : 'secondary'"
                    :class="records.links.next ? 'cursor-pointer' : ''"
                >
                    <p class="max-sm:hidden">Next</p>
                    <ChevronRight />
                </Button>
            </component>

            <!-- Sorting and filtering -->
            <Form id="records-form" class="flex gap-2" :action="route('record.filter')" method="get" v-slot="{ processing }">
                <!-- Search field (which is technically a filter) -->
                <div class="flex flex-col">
                    <Input type="search" name="q" placeholder="Search" minlength="1" maxlength="255" />
                    <Input type="hidden" name="option" :value="serverSorting && selectedSortOption.label" />
                    <Input type="hidden" name="direction" :value="serverSorting && selectedSortOption.direction" />

                    <InputError :message="page.props.errors.q" />
                </div>

                <!-- Sorting selection menu -->
                <DropdownMenu>
                    <!-- Select menu trigger button -->
                    <DropdownMenuTrigger :as-child="true">
                        <Button variant="ghost" size="default" class="relative size-10 w-auto rounded-md p-1">
                            ԲԱՏՈՆ ՁՅԱ
                            <ChevronsUpDown />
                        </Button>
                    </DropdownMenuTrigger>

                    <!-- Select menu options -->
                    <DropdownMenuContent align="end" class="w-56">
                        <!-- Menu label -->
                        <DropdownMenuLabel class="p-0 font-normal">
                            <div class="flex items-center gap-2 px-3 py-1.5 text-left text-sm justify-between">
                                <p>Apply for all the pages</p>
                                <Checkbox v-model="serverSorting"></Checkbox>
                            </div>
                        </DropdownMenuLabel>

                        <DropdownMenuSeparator />

                        <!-- Rendering the options -->
                        <DropdownMenuGroup v-for="(options, i) in selectOptions" :key="i" >
                            <DropdownMenuItem :as-child="true" v-for="(option, j) in options" :key="j">
                                <Button
                                    :variant="selectedSortOption.label === option.sorting_label && selectedSortOption.direction === option.direction ?  'secondary' : 'ghost'"
                                    class="flex w-full justify-between"
                                    @click="handleRecordSorting(option.sorting_label as keyof Record, option.direction)"
                                    type="button"
                                >
                                    {{ option.text }}
                                    <component :is="option.icon" />
                                </Button>
                            </DropdownMenuItem>

                            <DropdownMenuSeparator v-if="i < selectOptions.length - 1" />
                        </DropdownMenuGroup>
                    </DropdownMenuContent>
                </DropdownMenu>

                <Button type="submit" class="cursor-pointer" :disabled="processing">
                    <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                    <Rocket v-else />
                </Button>
            </Form>
        </div>
    </AppLayout>
</template>
