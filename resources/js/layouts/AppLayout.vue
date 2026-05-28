<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import Sidebar from '@/components/Sidebar.vue'

interface FlashProps {
    success?: string | null;
    error?: string | null;
    [key: string]: any;
}

const page = usePage()
const flash = computed(() => (page.props.flash as FlashProps) ?? ({} as FlashProps))

// Flash auto-dismiss
const showFlash = ref(false)
const flashTimeout = ref<number | null>(null)

function watchFlash() {
    if (flash.value.success || flash.value.error) {
        showFlash.value = true
        clearTimeout(flashTimeout.value as number)
        flashTimeout.value = window.setTimeout(() => {
            showFlash.value = false
        }, 3500)
    }
}

// Watch flash changes on navigation
router.on('finish', () => watchFlash())
onMounted(() => watchFlash())
onUnmounted(() => clearTimeout(flashTimeout.value as number))
</script>

<template>
    <div class="flex h-screen bg-neutral-20 overflow-hidden font-sans">
        <!-- Sidebar -->
        <Sidebar />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <!-- Flash Notification -->
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="showFlash && (flash.success || flash.error)"
                    class="absolute top-4 right-4 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg min-w-72 max-w-sm"
                    :class="flash.success
                        ? 'bg-success text-white'
                        : 'bg-danger text-white'"
                >
                    <span class="text-lg">
                        {{ flash.success ? '✓' : '✕' }}
                    </span>
                    <p class="text-body1 font-medium flex-1">
                        {{ flash.success || flash.error }}
                    </p>
                    <button @click="showFlash = false" class="opacity-70 hover:opacity-100 transition-opacity">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </Transition>

            <!-- Scrollable content -->
            <main class="flex-1 overflow-y-auto">
                <div class="p-6 lg:p-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>