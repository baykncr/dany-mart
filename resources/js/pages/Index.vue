<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3'
import { Banknote, Download, Loader2, QrCode, Receipt, Search, X } from 'lucide-vue-next'
import { ref, watch } from 'vue'

import PageHeader from '@/components/PageHeader.vue'
import AppLayout from '@/layouts/AppLayout.vue'

// ── Types ──────────────────────────────────────────────────────────────────
interface OrderItem {
    name: string
    qty: number
    unit: string
    price: number
    subtotal: number
}

interface Order {
    id: number
    order_number: string
    cashier: string
    items_count: number
    total_amount: number
    payment_method: 'cash' | 'qris'
    payment_amount: number
    change_amount: number
    created_at: string
    items: OrderItem[]
}

// Struktur standar Pagination dari Laravel
interface PaginatedOrders {
    data: Order[]
    from: number
    to: number
    total: number
    last_page: number
    links: { url: string | null; label: string; active: boolean }[]
}

interface Filters {
    search?: string
    date_from?: string
    date_to?: string
    payment_method?: string
}

interface Summary {
    total_orders: number
    total_revenue: number
}

const props = defineProps<{
    orders: PaginatedOrders
    filters: Filters
    summary: Summary
}>()

const page    = usePage()
const isAdmin = page.props.auth.user.role === 'admin'

// ── Filters ────────────────────────────────────────────────────────────────
const search        = ref(props.filters?.search ?? '')
const dateFrom      = ref(props.filters?.date_from ?? '')
const dateTo        = ref(props.filters?.date_to ?? '')
const paymentMethod = ref(props.filters?.payment_method ?? '')

let searchTimer: ReturnType<typeof setTimeout> | null = null

watch(search, () => {
    if (searchTimer) {
        clearTimeout(searchTimer)
    }

    searchTimer = setTimeout(applyFilters, 400)
})

watch([dateFrom, dateTo, paymentMethod], applyFilters)

function applyFilters() {
    router.get('/transactions', {
        search:         search.value || undefined,
        date_from:      dateFrom.value || undefined,
        date_to:        dateTo.value || undefined,
        payment_method: paymentMethod.value || undefined,
    }, { preserveState: true, replace: true })
}

function resetFilters() {
    search.value        = ''
    dateFrom.value      = ''
    dateTo.value        = ''
    paymentMethod.value = ''
    applyFilters()
}

const hasFilters = ref(false)

watch([search, dateFrom, dateTo, paymentMethod], () => {
    hasFilters.value = !!(search.value || dateFrom.value || dateTo.value || paymentMethod.value)
}, { immediate: true })

// ── Export ─────────────────────────────────────────────────────────────────
const exporting = ref(false)

function handleExport() {
    exporting.value = true
    const params = new URLSearchParams()

    if (dateFrom.value) {
        params.set('date_from', dateFrom.value)
    }

    if (dateTo.value) {
        params.set('date_to', dateTo.value)
    }

    if (paymentMethod.value) {
        params.set('payment_method', paymentMethod.value)
    }

    if (search.value) {
        params.set('search', search.value)
    }

    const url = '/transactions/export?' + params.toString()
    
    window.location.href = url

    setTimeout(() => { 
        exporting.value = false 
    }, 3000)
}

// ── Detail modal ───────────────────────────────────────────────────────────
const selectedOrder = ref<Order | null>(null)

// ── Helpers ────────────────────────────────────────────────────────────────
function rupiah(val: number | string | undefined) {
    if (!val && val !== 0) {
        return 'Rp 0'
    }

    return 'Rp ' + Number(val).toLocaleString('id-ID')
}
</script>

<template>
    <AppLayout>
        <PageHeader
            title="Riwayat Transaksi"
            description="Seluruh data transaksi penjualan."
        />

        <div class="grid grid-cols-2 gap-4 mb-5">
            <div class="bg-white rounded-2xl shadow-sm border border-neutral-30 px-5 py-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                    <Receipt class="w-5 h-5 text-primary" />
                </div>
                <div>
                    <p class="text-body2 text-neutral-50">Total Transaksi</p>
                    <p class="text-h4 font-bold text-neutral-100 tabular-nums">{{ summary.total_orders }}</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-neutral-30 px-5 py-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-success-light flex items-center justify-center shrink-0">
                    <Banknote class="w-5 h-5 text-success-dark" />
                </div>
                <div>
                    <p class="text-body2 text-neutral-50">Total Pendapatan</p>
                    <p class="text-h4 font-bold text-neutral-100 tabular-nums">{{ rupiah(summary.total_revenue) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-neutral-30 p-4 mb-5">
            <div class="flex flex-wrap gap-3 items-end">
                <div class="relative flex-1 min-w-48">
                    <div class="absolute inset-y-0 left-3 flex items-center text-neutral-50 pointer-events-none">
                        <Search class="w-4 h-4" />
                    </div>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari no. transaksi..."
                        class="w-full pl-9 pr-4 py-2.5 border border-neutral-40 rounded-xl text-body1 text-neutral-90 placeholder-neutral-50 shadow-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                    />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-body2 text-neutral-60 font-medium">Dari</label>
                    <input
                        v-model="dateFrom"
                        type="date"
                        class="px-3 py-2.5 border border-neutral-40 rounded-xl text-body1 text-neutral-90 shadow-xs focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                    />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-body2 text-neutral-60 font-medium">Sampai</label>
                    <input
                        v-model="dateTo"
                        type="date"
                        class="px-3 py-2.5 border border-neutral-40 rounded-xl text-body1 text-neutral-90 shadow-xs focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                    />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-body2 text-neutral-60 font-medium">Metode</label>
                    <select
                        v-model="paymentMethod"
                        class="px-3 py-2.5 border border-neutral-40 rounded-xl text-body1 text-neutral-90 shadow-xs bg-white focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                    >
                        <option value="">Semua</option>
                        <option value="cash">Tunai</option>
                        <option value="qris">QRIS</option>
                    </select>
                </div>

                <button
                    v-if="hasFilters"
                    @click="resetFilters"
                    class="px-4 py-2.5 border border-neutral-40 rounded-xl text-body1 text-neutral-70 hover:bg-neutral-20 transition-colors cursor-pointer"
                >
                    Reset
                </button>

                <button
                    v-if="isAdmin"
                    @click="handleExport"
                    :disabled="exporting"
                    class="ml-auto flex items-center gap-2 px-4 py-2.5 bg-success hover:bg-success-dark text-white rounded-xl text-body1 font-semibold shadow-sm transition-all disabled:opacity-60 cursor-pointer"
                >
                    <Loader2 v-if="exporting" class="w-4 h-4 animate-spin" />
                    <Download v-else class="w-4 h-4" />
                    {{ exporting ? 'Mengunduh...' : 'Export Excel' }}
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-neutral-30 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-neutral-30 bg-neutral-20">
                            <th class="text-left px-5 py-3.5 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">No. Transaksi</th>
                            <th class="text-left px-5 py-3.5 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Waktu</th>
                            <th class="text-left px-5 py-3.5 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Kasir</th>
                            <th class="text-center px-5 py-3.5 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Item</th>
                            <th class="text-right px-5 py-3.5 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Total</th>
                            <th class="text-center px-5 py-3.5 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Metode</th>
                            <th class="text-center px-5 py-3.5 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-20">
                        <tr
                            v-for="order in orders.data"
                            :key="order.id"
                            class="hover:bg-neutral-20/50 transition-colors"
                        >
                            <td class="px-5 py-4">
                                <span class="font-mono text-body2 font-semibold text-neutral-80">{{ order.order_number }}</span>
                            </td>
                            <td class="px-5 py-4 text-body2 text-neutral-60">{{ order.created_at }}</td>
                            <td class="px-5 py-4 text-body1 text-neutral-80">{{ order.cashier }}</td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-block px-2.5 py-0.5 bg-neutral-20 text-neutral-70 text-body2 font-semibold rounded-full">
                                    {{ order.items_count }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right text-body1 font-bold text-neutral-90 tabular-nums">
                                {{ rupiah(order.total_amount) }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase"
                                    :class="order.payment_method === 'cash'
                                        ? 'bg-primary/10 text-primary'
                                        : 'bg-success-light text-success-dark'"
                                >
                                    <Banknote v-if="order.payment_method === 'cash'" class="w-3.5 h-3.5" />
                                    <QrCode v-else class="w-3.5 h-3.5" />
                                    {{ order.payment_method === 'cash' ? 'Tunai' : 'QRIS' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <button
                                    @click="selectedOrder = order"
                                    class="p-1.5 rounded-lg text-neutral-50 hover:bg-primary/10 hover:text-primary transition-colors cursor-pointer"
                                >
                                    <Receipt class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!orders.data.length">
                            <td colspan="7" class="px-5 py-16 text-center text-body2 text-neutral-50">
                                Tidak ada transaksi yang cocok dengan filter saat ini.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="orders.last_page > 1" class="px-5 py-4 border-t border-neutral-30 flex items-center justify-between">
                <p class="text-body2 text-neutral-60">
                    {{ orders.from }}–{{ orders.to }} dari {{ orders.total }} transaksi
                </p>
                <div class="flex items-center gap-1">
                    <template v-for="link in orders.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="px-3 py-1.5 rounded-lg text-body2 transition-colors"
                            :class="link.active
                                ? 'bg-primary text-white font-semibold'
                                : 'text-neutral-70 hover:bg-neutral-30'"
                        >
                            <span v-html="link.label"></span>
                        </Link>
                        <span
                            v-else
                            class="px-3 py-1.5 text-body2 text-neutral-40"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100"
                leave-active-class="transition-all duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="selectedOrder" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="selectedOrder = null">
                    <div class="absolute inset-0 bg-neutral-100/50 backdrop-blur-sm" @click="selectedOrder = null" />
                    <div class="relative bg-white rounded-2xl shadow-lg w-full max-w-md z-10 overflow-hidden">
                        <div class="px-6 py-4 border-b border-neutral-30 flex items-center justify-between">
                            <div>
                                <h3 class="text-h3 text-neutral-100">Detail Transaksi</h3>
                                <p class="text-body2 text-neutral-50 font-mono">{{ selectedOrder.order_number }}</p>
                            </div>
                            <button @click="selectedOrder = null" class="p-1.5 rounded-lg text-neutral-50 hover:bg-neutral-20 transition-colors cursor-pointer">
                                <X class="w-5 h-5" />
                            </button>
                        </div>
                        <div class="px-6 py-5 space-y-4">
                            <div class="grid grid-cols-2 gap-3 text-body2">
                                <div><span class="text-neutral-50">Kasir</span><p class="text-neutral-90 font-semibold mt-0.5">{{ selectedOrder.cashier }}</p></div>
                                <div><span class="text-neutral-50">Waktu</span><p class="text-neutral-90 font-semibold mt-0.5">{{ selectedOrder.created_at }}</p></div>
                                <div><span class="text-neutral-50">Metode Bayar</span>
                                    <p class="mt-0.5">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                            :class="selectedOrder.payment_method === 'cash' ? 'bg-primary/10 text-primary' : 'bg-success-light text-success-dark'">
                                            <Banknote v-if="selectedOrder.payment_method === 'cash'" class="w-3.5 h-3.5" />
                                            <QrCode v-else class="w-3.5 h-3.5" />
                                            {{ selectedOrder.payment_method === 'cash' ? 'Tunai' : 'QRIS' }}
                                        </span>
                                    </p>
                                </div>
                                <div><span class="text-neutral-50">Kembalian</span><p class="text-neutral-90 font-semibold mt-0.5">{{ rupiah(selectedOrder.change_amount) }}</p></div>
                            </div>

                            <div class="border border-neutral-30 rounded-xl overflow-hidden">
                                <div class="bg-neutral-20 px-4 py-2.5 border-b border-neutral-30">
                                    <p class="text-body2 font-semibold text-neutral-60 uppercase tracking-wide">Item Pembelian</p>
                                </div>
                                <div class="divide-y divide-neutral-20 max-h-64 overflow-y-auto">
                                    <div v-for="item in selectedOrder.items" :key="item.name" class="px-4 py-3 flex items-center justify-between">
                                        <div>
                                            <p class="text-body1 font-medium text-neutral-90">{{ item.name }}</p>
                                            <p class="text-body2 text-neutral-50">{{ item.qty }} {{ item.unit }} × {{ rupiah(item.price) }}</p>
                                        </div>
                                        <p class="text-body1 font-bold text-neutral-90 tabular-nums">{{ rupiah(item.subtotal) }}</p>
                                    </div>
                                </div>
                                <div class="px-4 py-3 bg-neutral-20 border-t border-neutral-30 flex justify-between">
                                    <span class="text-body1 font-bold text-neutral-90">Total</span>
                                    <span class="text-body1 font-bold text-primary tabular-nums">{{ rupiah(selectedOrder.total_amount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>