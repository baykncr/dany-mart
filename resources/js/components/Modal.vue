<script setup lang="ts">
import { X } from 'lucide-vue-next'
import { watch, onMounted, onUnmounted } from 'vue'


// 1. Deklarasi Props ala TypeScript (lebih aman dan strict)
const props = withDefaults(defineProps<{
    show: boolean
    title: string
    size?: 'sm' | 'md' | 'lg' | 'xl'
}>(), {
    size: 'md',
})

// 2. Deklarasi Emits ala TypeScript
const emit = defineEmits<{
    (e: 'close'): void
}>()

// 3. Mapping class dengan tipe Record agar TS paham
const sizeClasses: Record<'sm' | 'md' | 'lg' | 'xl', string> = {
    sm: 'max-w-sm',
    md: 'max-w-lg',
    lg: 'max-w-2xl',
    xl: 'max-w-4xl',
}

// 4. Typing KeyboardEvent untuk event listener
function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') {
        emit('close')
    }
}

onMounted(() => {
    document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown)
})

watch(() => props.show, (val) => {
    document.body.style.overflow = val ? 'hidden' : ''
})
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @click.self="$emit('close')"
            >
                <div class="absolute inset-0 bg-neutral-100/50 backdrop-blur-sm" />

                <Transition
                    enter-active-class="transition-all duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-2"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition-all duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-2"
                >
                    <div
                        v-if="show"
                        class="relative w-full bg-white rounded-2xl shadow-lg z-10 flex flex-col max-h-[90vh]"
                        :class="sizeClasses[props.size]"
                    >
                        <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-40 flex-shrink-0">
                            <h3 class="text-h3 text-neutral-100">{{ title }}</h3>
                            <button
                                @click="$emit('close')"
                                class="p-1.5 rounded-lg text-neutral-60 hover:bg-neutral-20 hover:text-neutral-90 transition-colors cursor-pointer"
                            >
                                <X class="w-5 h-5" />
                            </button>
                        </div>

                        <div class="overflow-y-auto flex-1 px-6 py-5">
                            <slot />
                        </div>

                        <div v-if="$slots.footer" class="px-6 py-4 border-t border-neutral-40 flex-shrink-0">
                            <slot name="footer" />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>