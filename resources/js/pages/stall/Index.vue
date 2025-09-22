<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { type BreadcrumbItem, type ParkingSpace } from '@/types';
// import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { LoaderCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const page = usePage();
const processing = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Stall',
        href: '/stall',
    },
];

defineProps<{
    reserved_stalls?: ParkingSpace[][];
}>();

</script>

<template>
    <Head title="Stall" />

    <AppLayout :breadcrumbs="breadcrumbs" class="relative">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-5" v-for="(spaces, index) in reserved_stalls" :key="index">
                <button
                    v-for="stall in spaces"
                    :key="stall.id"
                    class="group relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border hover:outline-2"
                >
                    <div class="group-hover:hidden w-full h-full grid place-items-center">
                        <p class="text-gray-300">No. {{ stall.id }}</p>
                    </div>

                    <form
                        :action="route('stall.checkout', stall.id)"
                        method="post"
                        @submit="processing = true"
                        class="group-hover:grid w-full h-full place-items-center"
                    >
                        <input type="hidden" name="_token" :value="page.props.csrf_token" />
                        <Button type="submit" class="mx-auto block flex w-1/2" :tabindex="4" :disabled="processing">
                            <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                            Drive out
                        </Button>
                    </form>
<!--                    <PlaceholderPattern />-->
                </button>
            </div>
        </div>

        <form
            :action="route('stall.checkout')"
            method="post"
            @submit="processing = true"
            v-if="reserved_stalls?.length"
        >
            <input type="hidden" name="_token" :value="page.props.csrf_token" />
            <Button type="submit" class="mx-auto block flex w-1/2 mb-6" :tabindex="4" :disabled="processing">
                <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                Checkout
            </Button>
        </form>

        <div v-else class="absolute left-1/2 top-1/2 -translate-1/2 text-center">
            <h2>You <b>don't</b> have any reserved parking stalls</h2>
        </div>
    </AppLayout>
</template>

<style scoped></style>
