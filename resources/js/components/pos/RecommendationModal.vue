<script setup lang="ts">
import { Check, CheckCheck, ChevronRight, Package, Plus, Sparkles, X } from 'lucide-vue-next'
import { ref, watch } from 'vue'

interface Category {
    id: number
    name: string
}

interface RecommendationProduct {
    id: number
    name: string
    selling_price: number
    stock: number
    photo: string | null
    category?: Category
}

const props = defineProps<{
    show: boolean
    recommendations?: RecommendationProduct[]
    dominantCategory?: Category | null
    loading: boolean
}>()

const emit = defineEmits<{
    (e: 'add-to-cart', product: RecommendationProduct): void
    (e: 'add-all-to-cart', products: RecommendationProduct[]): void
    (e: 'skip'): void
    (e: 'proceed'): void
}>()

const addedIds = ref<number[]>([])

// Reset state setiap kali modal dibuka kembali
watch(() => props.show, (val) => {
    if (val) addedIds.value = []
})

function handleAdd(product: RecommendationProduct) {
    if (isAdded(product.id)) return
    emit('add-to-cart', product)
    addedIds.value.push(product.id)
}

function handleAddAll() {
    const unaddedProducts = (props.recommendations ?? []).filter(p => !isAdded(p.id))
    if (!unaddedProducts.length) return

    emit('add-all-to-cart', unaddedProducts)
    unaddedProducts.forEach(p => {
        if (!addedIds.value.includes(p.id)) {
            addedIds.value.push(p.id)
        }
    })
}

function isAdded(id: number) {
    return addedIds.value.includes(id)
}

const allAdded = () => {
    const recs = props.recommendations ?? []
    return recs.length > 0 && recs.every(p => isAdded(p.id))
}

function formatRupiah(val: number | string) {
    return 'Rp ' + Number(val).toLocaleString('id-ID')
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-neutral-100/60 backdrop-blur-sm" />

                <Transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 scale-90 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                >
                    <div v-if="show" class="relative bg-white rounded-3xl shadow-lg w-full max-w-xl z-10 overflow-hidden">

                        <!-- Header -->
                        <div class="bg-gradient-to-r from-primary to-primary-hover px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                                    <Sparkles class="w-5 h-5 text-white" />
                                </div>
                                <div>
                                    <p class="text-body2 text-white/70 font-medium">SmartPOS Recommendation</p>
                                    <h3 class="text-h3 text-white font-bold leading-tight">Pelanggan Juga Suka</h3>
                                </div>
                            </div>

                            <div v-if="dominantCategory" class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 bg-white/15 rounded-full">
                                <span class="text-body2 text-white/80">Berdasarkan kategori:</span>
                                <span class="text-body2 text-white font-semibold">{{ dominantCategory.name }}</span>
                            </div>
                        </div>

                        <!-- Loading state -->
                        <div v-if="loading" class="p-10 flex flex-col items-center gap-4">
                            <div class="w-12 h-12 rounded-full border-4 border-primary-surface border-t-primary animate-spin" />
                            <p class="text-body1 text-neutral-60">Menganalisis keranjang belanja...</p>
                        </div>

                        <div v-else class="p-6">
                            <!-- Empty state -->
                            <div v-if="!recommendations?.length" class="text-center py-6">
                                <p class="text-body1 text-neutral-60">Tidak ada rekomendasi saat ini.</p>
                            </div>

                            <template v-else>
                                <!-- Product list -->
                                <div class="space-y-3">
                                    <div
                                        v-for="product in recommendations"
                                        :key="product.id"
                                        class="flex items-center gap-4 p-4 rounded-2xl border-2 transition-all duration-200"
                                        :class="isAdded(product.id)
                                            ? 'border-success bg-success-light/30'
                                            : 'border-neutral-30 hover:border-primary/30 hover:bg-primary-surface/20'"
                                    >
                                        <!-- Product image -->
                                        <div class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0 bg-neutral-20 border border-neutral-40">
                                            <img
                                                v-if="product.photo"
                                                :src="product.photo"
                                                :alt="product.name"
                                                class="w-full h-full object-cover"
                                            />
                                            <div v-else class="w-full h-full flex items-center justify-center text-neutral-40">
                                                <Package class="w-7 h-7" stroke-width="1.5" />
                                            </div>
                                        </div>

                                        <!-- Product info -->
                                        <div class="flex-1 min-w-0">
                                            <p class="text-body1 font-semibold text-neutral-90 leading-tight">
                                                {{ product.name }}
                                            </p>
                                            <p class="text-body2 text-neutral-50 mt-0.5">
                                                {{ product.category?.name }}
                                            </p>
                                            <div class="flex items-center gap-3 mt-1.5">
                                                <span class="text-body1 font-bold text-primary">
                                                    {{ formatRupiah(product.selling_price) }}
                                                </span>
                                                <span class="flex items-center gap-1 text-body2 text-neutral-50">
                                                    <Package class="w-3.5 h-3.5" />
                                                    Stok: {{ product.stock }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Add individual product button -->
                                        <button
                                            @click="handleAdd(product)"
                                            :disabled="isAdded(product.id)"
                                            class="flex-shrink-0 flex flex-col items-center gap-1 px-4 py-2.5 rounded-xl font-medium text-body2 transition-all duration-200 cursor-pointer disabled:cursor-default"
                                            :class="isAdded(product.id)
                                                ? 'bg-success text-white'
                                                : 'bg-primary text-white hover:bg-primary-hover active:bg-primary-pressed shadow-sm'"
                                        >
                                            <Check v-if="isAdded(product.id)" class="w-5 h-5" />
                                            <Plus v-else class="w-5 h-5" />
                                            <span>{{ isAdded(product.id) ? 'Ditambahkan' : 'Tambahkan' }}</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Add All button — hanya tampil jika ada >1 produk dan belum semua ditambahkan -->
                                <div
                                    v-if="recommendations.length > 1 && !allAdded()"
                                    class="mt-4"
                                >
                                    <button
                                        @click="handleAddAll"
                                        class="w-full flex items-center justify-center gap-2 py-3 rounded-xl border-2 border-primary text-primary text-body1 font-semibold hover:bg-primary-surface/30 active:bg-primary-surface/50 transition-all duration-200 cursor-pointer"
                                    >
                                        <CheckCheck class="w-5 h-5" />
                                        <span>Tambahkan Semua Rekomendasi</span>
                                    </button>
                                </div>
                            </template>

                            <!-- Action buttons -->
                            <div class="flex gap-3 mt-5">
                                <button
                                    @click="emit('skip')"
                                    class="flex-1 py-3 border-2 border-neutral-30 rounded-xl text-body1 font-semibold text-neutral-60 hover:bg-neutral-20 hover:border-neutral-40 transition-all cursor-pointer"
                                >
                                    Lewati
                                </button>
                                <button
                                    @click="emit('proceed')"
                                    class="flex-1 py-3 bg-primary hover:bg-primary-hover active:bg-primary-pressed rounded-xl text-body1 font-bold text-white shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer"
                                >
                                    <span>Lanjut Bayar</span>
                                    <ChevronRight class="w-5 h-5" stroke-width="2.5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>