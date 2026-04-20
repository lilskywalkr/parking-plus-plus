<script setup lang="ts">
import { Order } from '@/types';

const props = defineProps<{
    order: Order
}>();

console.log(props.order);
</script>

<template>
    <h1>If you were redirected here then your payment was successfully credited. You will receive an email with the receipt url</h1>
    <h2><b>Payment details</b></h2>

    <div>
        <p v-for="(value, key, i) in order" :key="i">
            {{ key.split('_').join(' ').toUpperCase() }}:
            <b>
                {{
                    Array.isArray(value) ? (
                        value.join(', ')
                    ) : (
                        key === 'created_at' ? new Date(Number(value)).toDateString() + ", " + new Date(Number(value)).toLocaleTimeString() : value
                    )
                }}
            </b>
        </p>
    </div>
</template>

<style scoped>

</style>
