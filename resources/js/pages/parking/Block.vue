<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Form, Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem, ParkingSpace } from '@/types';
import { LoaderCircle } from 'lucide-vue-next';
import { Card, CardContent, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    stall: ParkingSpace
}>();

const stallId = ref(props.stall.id as number);
const stallAvailable = ref(props.stall.available as boolean);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Block',
        href: `/parking/${stallId.value}/block`,
    },
];

console.log(props.stall)
</script>

<template>
    <Head title="Parking" />

    <AppLayout :breadcrumbs="breadcrumbs" class="relative">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="h-full w-full max-md:fixed md:grid md:place-items-center">
                <Card class="items-center max-md:fixed max-md:top-1/2 max-md:left-1/2 max-md:w-9/10 max-md:-translate-1/2 lg:w-xl">
                    <CardTitle> You are about to {{ stallAvailable ? 'block' : 'unblock' }} the stall No. {{ stallId }} </CardTitle>
                    <Separator />

                    <Form :action="route('parking.block.save')" method="patch" v-slot="{ errors, processing }">
                        <CardContent>
                            <Input id="stall_id" type="hidden" name="stall_id" required v-model="stallId" />
                            <InputError :message="errors.stall_id" />
                            <InputError :message="errors.reserved" />
                        </CardContent>

                        <div class="mt-4 flex gap-2">
                            <Link :href="route('parking')">
                                <Button type="button" variant="destructive" class="mx-auto"> Close </Button>
                            </Link>

                            <Button type="submit" class="mx-auto block flex w-2/3" :tabindex="4" :disabled="processing">
                                <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                                {{ stallAvailable ? 'Block' : 'Unblock' }}
                            </Button>
                        </div>
                    </Form>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped></style>
