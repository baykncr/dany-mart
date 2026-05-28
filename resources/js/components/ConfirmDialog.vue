<script setup lang="ts">
import { AlertTriangle, Loader2 } from 'lucide-vue-next'

withDefaults(defineProps<{
    show: boolean
    title?: string
    message?: string
    loading?: boolean
}>(), {
    title: 'Konfirmasi Hapus',
    message: 'Tindakan ini tidak dapat dibatalkan.',
    loading: false,
})

defineEmits<{
    (e: 'close'): void
    (e: 'confirm'): void
}>()
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-all duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-neutral-100/50 backdrop-blur-sm" @click="$emit('close')" />
                <div class="relative bg-white rounded-2xl shadow-lg w-full max-w-sm p-6 z-10">
                    
                    <div class="w-12 h-12 rounded-full bg-danger-light flex items-center justify-center mx-auto mb-4">
                        <AlertTriangle class="w-6 h-6 text-danger" />
                    </div>
                    
                    <h3 class="text-h3 text-neutral-100 text-center mb-2">{{ title }}</h3>
                    <p class="text-body1 text-neutral-60 text-center mb-6">{{ message }}</p>
                    
                    <div class="flex gap-3">
                        <button
                            @click="$emit('close')"
                            class="flex-1 px-4 py-2.5 border border-neutral-40 rounded-xl text-body1 font-medium text-neutral-70 hover:bg-neutral-20 transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            @click="$emit('confirm')"
                            :disabled="loading"
                            class="flex-1 px-4 py-2.5 bg-danger hover:bg-danger-dark rounded-xl text-body1 font-medium text-white transition-colors disabled:opacity-60 flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
                            {{ loading ? 'Menghapus...' : 'Ya, Hapus' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>