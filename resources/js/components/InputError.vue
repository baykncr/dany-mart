<script setup lang="ts">
import { AlertCircle } from 'lucide-vue-next'

defineProps<{
    message?: string
}>()
</script>

<template>
    <p v-if="message" class="text-body2 text-danger mt-1.5 flex items-center gap-1.5">
        <AlertCircle class="w-3.5 h-3.5 flex-shrink-0" />
        <span>{{ message }}</span>
    </p>
</template>