<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type ParkingSpace } from '@/types';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';
import { Head, Link } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Parking',
        href: '/parking',
    },
];

const props = defineProps<{
    parking_spaces: ParkingSpace[][]; // Two-dimensional array of parking places
}>();

console.log(props.parking_spaces);
</script>

<template>
    <Head title="Parking" />

    <AppLayout :breadcrumbs="breadcrumbs" class="relative">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-5" v-for="(spaces, index) in parking_spaces" :key="index">
                <button
                    v-for="stall in spaces"
                    :key="stall.id"
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                    :class="stall.available ? 'cursor-pointer hover:outline-2' : ''"
                >
                    <template v-if="stall.available">
                        <Link :href="route('parking.reserve', stall.id)">
                            <div class="w-full h-full grid place-items-center">
                                <p class="text-gray-300">No. {{ stall.id }}</p>
                            </div>
                        </Link>
                    </template>

                    <template v-else>
                        <div class="w-full h-full grid place-items-center">
                            <p class="text-gray-300">No. {{ stall.id }}</p>
                        </div>
                        <PlaceholderPattern />
                    </template>
                </button>
            </div>
        </div>
    </AppLayout>
</template>
