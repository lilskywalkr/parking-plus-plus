<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Auth, type BreadcrumbItem, type ParkingSpace } from '@/types';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';
import { Head, Link } from '@inertiajs/vue3';
import { TriangleAlert, Car } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth as Auth);

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
                <!-- The button is clickable if the stall is available or if the user is an admin -->
                <button
                    v-for="stall in spaces"
                    :key="stall.id"
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                    :class="stall.available || (!stall.available && !stall.user_id && auth.user.is_admin) ? 'cursor-pointer hover:outline-2' : ''"
                >
                    <!-- If the stall is available (either for a user or an admin) or if the user is an admin and the stall is reserved by any user -->
                    <!-- set a link to the reserve or block page accordingly to the role -->
                    <component
                        :is="stall.available || (auth.user.is_admin && !stall.user_id) ? Link : 'div'"
                        v-bind="
                            stall.available || (auth.user.is_admin && !stall.user_id)
                                ? { href: route('parking.reserve', stall.id), class: 'relative z-1' }
                                : {}
                        "
                        class="grid h-full w-full place-items-center"
                    >
                        <p class="text-gray-300">No. {{ stall.id }}</p>
                    </component>

                    <!-- If the stall is blocked by an admin -->
                    <TriangleAlert v-if="!stall.available && !stall.user_id" color="#efda4d" class="absolute top-2 right-2" />

                    <!-- If the stall is reserved by a user -->
                    <Car v-else-if="!stall.available && stall.user_id" color="#3f3f46" class="absolute top-2 right-2" />

                    <!-- Display the placeholder pattern which indicates unavailability if the stall is reserved or blocked -->
                    <PlaceholderPattern v-if="!stall.available" />
                </button>
            </div>
        </div>
    </AppLayout>
</template>
