<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, RecordPagination } from '@/types';
import { Head, Link, Form } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuGroup,
    DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    ChevronLeft, ChevronRight, ChevronsUpDown,
    LoaderCircle, Search, CalendarArrowDown,
    CalendarArrowUp, ClockArrowDown, ClockArrowUp,
    ArrowDownAZ, ArrowUpAZ, ArrowUp01, ArrowDown01,
} from 'lucide-vue-next';
import { recordsArrayInsertSort } from '@/lib/utils';
import InputError from '@/components/InputError.vue';
import { usePage } from '@inertiajs/vue3';

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

const sortedRecordsData = recordsArrayInsertSort(1, props.records.data, 'parking_space_id');
console.log("Sorted array", sortedRecordsData);

// Select menu's options
const selectOptions = [
    [
        { text: 'By date asc', icon: CalendarArrowUp },
        { text: 'By date desc', icon: CalendarArrowDown },
    ],
    [
        { text: 'By time asc', icon: ClockArrowUp },
        { text: 'By time desc', icon: ClockArrowDown },
    ],
    [
        { text: 'By username asc', icon: ArrowUpAZ },
        { text: 'By username desc', icon: ArrowDownAZ },
    ],
    [
        { text: 'By stall number asc', icon: ArrowUp01 },
        { text: 'By stall number desc', icon: ArrowDown01 }
    ]
];

// The records table's column labels
const tableFieldLabels = ['Date', 'Time', 'User', 'Action', 'Stall', 'Car plates'];

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
                v-for="record in records.data"
                :key="record.id"
                class="grid w-full grid-cols-6 grid-rows-1 items-center gap-1 border-b border-solid max-sm:w-2xl"
            >
                <div>
                    <p>{{ new Date(record.created_at).toLocaleDateString() }}</p>
                </div>

                <div>
                    <p>{{ new Date(record.created_at).toLocaleTimeString() }}</p>
                </div>

                <div>
                    <p :class="record.user.is_admin ? 'underline' : ''">{{ record.user.name }}</p>
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
            <component :is="records.prev_page_url ? Link : 'div'" v-bind="records.prev_page_url ? { href: records.prev_page_url } : {}">
                <Button
                    type="button"
                    :variant="records.prev_page_url ? 'outline' : 'secondary'"
                    :class="records.prev_page_url ? 'cursor-pointer' : ''"
                >
                    <ChevronLeft />
                    <p class="max-sm:hidden">Previous</p>
                </Button>
            </component>

            <!-- Rendering next button -->
            <component :is="records.next_page_url ? Link : 'div'" v-bind="records.next_page_url ? { href: records.next_page_url } : {}">
                <Button
                    type="button"
                    :variant="records.next_page_url ? 'outline' : 'secondary'"
                    :class="records.next_page_url ? 'cursor-pointer' : ''"
                >
                    <p class="max-sm:hidden">Next</p>
                    <ChevronRight />
                </Button>
            </component>

            <!-- Sorting and filtering -->
            <Form class="flex gap-2" :action="route('record.search')" method="get" v-slot="{ processing }">
                <!-- Search field (which is technically a filter) -->
                <div class="flex flex-col">
                    <Input type="search" name="q" placeholder="Search" minlength="1" maxlength="255" />
                    <InputError :message="page.props.errors.q" />
                </div>

                <Button type="submit" class="cursor-pointer" :disabled="processing">
                    <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                    <Search v-else />
                </Button>

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
                            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                <p>Select sorting option</p>
                            </div>
                        </DropdownMenuLabel>

                        <DropdownMenuSeparator />

                        <!-- Rendering the options -->
                        <DropdownMenuGroup v-for="(options, i) in selectOptions" :key="i">
                            <DropdownMenuItem :as-child="true" v-for="(option, j) in options" :key="j">
                                <Button variant="ghost" class="flex w-full justify-between">
                                    {{ option.text }}
                                    <component :is="option.icon" />
                                </Button>
                            </DropdownMenuItem>

                            <DropdownMenuSeparator v-if="i < selectOptions.length - 1" />
                        </DropdownMenuGroup>
                    </DropdownMenuContent>
                </DropdownMenu>
            </Form>
        </div>
    </AppLayout>
</template>
