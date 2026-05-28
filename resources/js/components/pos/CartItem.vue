<script setup lang="ts">
import { Minus, Package, Plus, X } from 'lucide-vue-next'

// Definisi tipe data yang ketat biar aman
interface CartItemData {
    id: number
    name: string
    selling_price: number
    stock: number
    quantity: number
    subtotal: number
    photo: string | null
}

defineProps<{
    item: CartItemData
}>()

const emit = defineEmits<{
    (e: 'increase', id: number): void
    (e: 'decrease', id: number): void
    (e: 'remove', id: number): void
}>()

function formatRupiah(val: number | string) {
    return 'Rp ' + Number(val).toLocaleString('id-ID')
}
</script>

<template>
    <div class="flex items-start gap-3 py-3 group">
        <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 bg-neutral-20 border border-neutral-40">
            <img v-if="item.photo" :src="item.photo" class="w-full h-full object-cover" />
            <div v-else class="w-full h-full flex items-center justify-center text-neutral-40">
                <Package class="w-5 h-5" stroke-width="1.5" />
            </div>
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
                <p class="text-body1 font-medium text-neutral-90 leading-tight line-clamp-1">
                    {{ item.name }}
                </p>
                <button
                    @click="emit('remove', item.id)"
                    class="flex-shrink-0 p-1 rounded text-neutral-40 hover:text-danger hover:bg-danger-light transition-colors opacity-0 group-hover:opacity-100 cursor-pointer"
                >
                    <X class="w-3.5 h-3.5" />
                </button>
            </div>
            <p class="text-body2 text-primary font-semibold mt-0.5">
                {{ formatRupiah(item.selling_price) }}
            </p>

            <div class="flex items-center justify-between mt-2">
                <div class="flex items-center gap-1">
                    <button
                        @click="emit('decrease', item.id)"
                        class="w-7 h-7 flex items-center justify-center rounded-lg border border-neutral-40 bg-white text-neutral-70 hover:bg-neutral-30 hover:border-neutral-50 active:bg-neutral-40 transition-colors cursor-pointer"
                    >
                        <Minus class="w-3.5 h-3.5" stroke-width="2.5" />
                    </button>
                    <span class="w-8 text-center text-body1 font-bold text-neutral-90 tabular-nums">
                        {{ item.quantity }}
                    </span>
                    <button
                        @click="emit('increase', item.id)"
                        :disabled="item.quantity >= item.stock"
                        class="w-7 h-7 flex items-center justify-center rounded-lg border border-neutral-40 bg-white text-neutral-70 hover:bg-neutral-30 hover:border-neutral-50 active:bg-neutral-40 transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
                    >
                        <Plus class="w-3.5 h-3.5" stroke-width="2.5" />
                    </button>
                </div>
                <span class="text-body1 font-bold text-neutral-90 tabular-nums">
                    {{ formatRupiah(item.subtotal) }}
                </span>
            </div>
        </div>
    </div>
</template>