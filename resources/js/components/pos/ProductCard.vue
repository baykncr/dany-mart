<script setup lang="ts">
import { Package, Plus } from 'lucide-vue-next'

interface Category {
    id: number
    name: string
    code: string
}

interface Product {
    id: number
    code: string
    name: string
    unit: string
    selling_price: number
    stock: number
    photo: string | null
    category_id: number
    category?: Category
}

defineProps<{
    product: Product
    quantity: number
}>()

const emit = defineEmits<{
    (e: 'add', product: Product): void
}>()

function formatRupiah(val: number | string) {
    return 'Rp ' + Number(val).toLocaleString('id-ID')
}
</script>

<template>
    <div
        class="group relative bg-white rounded-2xl shadow-sm border border-neutral-40 overflow-hidden flex flex-col transition-all duration-200 hover:shadow-md hover:-translate-y-0.5"
        :class="{ 'opacity-60': product.stock === 0 }"
    >
        <div class="absolute top-2 right-2 z-10">
            <span
                v-if="product.stock <= 5 && product.stock > 0"
                class="px-2 py-0.5 rounded-full text-body2 font-semibold bg-warning-light text-warning-dark shadow-xs"
            >
                Sisa {{ product.stock }}
            </span>
            <span
                v-if="product.stock === 0"
                class="px-2 py-0.5 rounded-full text-body2 font-semibold bg-danger-light text-danger shadow-xs"
            >
                Habis
            </span>
        </div>

        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="scale-0 opacity-0"
            enter-to-class="scale-100 opacity-100"
            leave-active-class="transition-all duration-150"
            leave-from-class="scale-100 opacity-100"
            leave-to-class="scale-0 opacity-0"
        >
            <div
                v-if="quantity > 0"
                class="absolute top-2 left-2 z-10 w-6 h-6 rounded-full bg-primary flex items-center justify-center shadow-sm"
            >
                <span class="text-body2 font-bold text-white">{{ quantity }}</span>
            </div>
        </Transition>

        <div class="w-full h-28 bg-neutral-20 flex-shrink-0 overflow-hidden">
            <img
                v-if="product.photo"
                :src="product.photo"
                :alt="product.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-neutral-40">
                <Package class="w-10 h-10" stroke-width="1.5" />
            </div>
        </div>

        <div class="p-3 flex flex-col flex-1">
            <p class="text-body1 font-semibold text-neutral-90 line-clamp-2 leading-tight mb-1">
                {{ product.name }}
            </p>
            <p class="text-body2 text-neutral-50 mb-auto">{{ product.category?.name }}</p>
            <div class="mt-2 flex items-center justify-between gap-2">
                <span class="text-body1 font-bold text-primary">{{ formatRupiah(product.selling_price) }}</span>
                <span class="text-body2 text-neutral-50">/{{ product.unit }}</span>
            </div>
        </div>

        <button
            @click="emit('add', product)"
            :disabled="product.stock === 0"
            class="w-full flex items-center justify-center gap-2 py-2.5 text-body2 font-semibold transition-all duration-150 cursor-pointer disabled:cursor-not-allowed"
            :class="product.stock === 0
                ? 'bg-neutral-30 text-neutral-50'
                : quantity > 0
                    ? 'bg-primary text-white hover:bg-primary-hover active:bg-primary-pressed'
                    : 'bg-primary-surface text-primary hover:bg-primary hover:text-white'"
        >
            <Plus class="w-4 h-4" />
            {{ product.stock === 0 ? 'Stok Habis' : quantity > 0 ? 'Tambah Lagi' : 'Tambah' }}
        </button>
    </div>
</template>