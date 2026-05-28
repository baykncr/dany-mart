<script setup lang="ts">
defineProps<{
    title: string
    description?: string
}>()
</script>

<template>
    <div class="mb-6">
        <h1 class="text-h1 text-neutral-100">{{ title }}</h1>
        <p v-if="description" class="text-body1 text-neutral-60 mt-1">{{ description }}</p>
    </div>
</template>