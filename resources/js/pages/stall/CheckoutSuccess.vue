<script setup lang="ts">
defineProps<{
    customer?: any
}>();
</script>

<template>
    <h1>Your payment has been successfully credited</h1>
    <p>{{ customer.name }}</p>
</template>

<style scoped>

</style>
