<script setup lang="ts">
// ── Types ──────────────────────────────────────────────────────────────────
interface TopProduct {
    name: string
    category_name: string
    unit: string
    total_qty: number | string
    total_revenue: number | string
    stock: number
}

defineProps<{
    products: TopProduct[]
}>()

// ── Helpers ────────────────────────────────────────────────────────────────
function rupiah(val: number | string) {
    return 'Rp ' + Number(val).toLocaleString('id-ID')
}

function stockClass(stock: number) {
    if (stock === 0) {
        return 'bg-danger-light text-danger'
    }

    if (stock <= 10) {
        return 'bg-warning-light text-warning-dark'
    }

    return 'bg-success-light text-success-dark'
}
</script>

<template>
    <div class="bg-white rounded-2xl shadow-sm border border-neutral-30 overflow-hidden">
        <div class="px-5 py-4 border-b border-neutral-30 flex items-center justify-between">
            <div>
                <h3 class="text-h5 text-neutral-90">Produk Terlaris</h3>
                <p class="text-body2 text-neutral-50 mt-0.5">Berdasarkan qty terjual bulan ini</p>
            </div>
            <span class="px-3 py-1 bg-primary/10 text-primary text-body2 font-semibold rounded-full">
                Top {{ products.length }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-neutral-20 border-b border-neutral-30">
                        <th class="text-left px-5 py-3 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">#</th>
                        <th class="text-left px-5 py-3 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Produk</th>
                        <th class="text-center px-5 py-3 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Terjual</th>
                        <th class="text-right px-5 py-3 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Pendapatan</th>
                        <th class="text-center px-5 py-3 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-20">
                    <tr
                        v-for="(product, i) in products"
                        :key="i"
                        class="hover:bg-neutral-20/50 transition-colors"
                    >
                        <td class="px-5 py-3.5">
                            <div
                                class="w-7 h-7 rounded-lg flex items-center justify-center text-body2 font-bold"
                                :class="i < 3 ? 'bg-primary text-white' : 'bg-neutral-20 text-neutral-60'"
                            >
                                {{ i + 1 }}
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="text-body1 font-semibold text-neutral-90">{{ product.name }}</p>
                            <p class="text-body2 text-neutral-50">{{ product.category_name }}</p>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="text-body1 font-bold text-neutral-90 tabular-nums">
                                {{ Number(product.total_qty).toLocaleString('id-ID') }}
                            </span>
                            <span class="text-body2 text-neutral-50 ml-1">{{ product.unit }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <span class="text-body1 font-semibold text-neutral-80 tabular-nums">
                                {{ rupiah(product.total_revenue) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span
                                class="inline-block px-2.5 py-0.5 rounded-full text-body2 font-semibold tabular-nums"
                                :class="stockClass(product.stock)"
                            >
                                {{ product.stock }}
                            </span>
                        </td>
                    </tr>
                    <tr v-if="!products.length">
                        <td colspan="5" class="px-5 py-10 text-center text-neutral-50 text-body2">
                            Belum ada data penjualan bulan ini.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>