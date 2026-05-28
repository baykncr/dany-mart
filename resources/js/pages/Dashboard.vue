<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { AlertTriangle, Banknote, QrCode } from 'lucide-vue-next'

import AppLayout from '@/layouts/AppLayout.vue'
import PaymentMethodChart from '@/components/dashboard/PaymentMethodChart.vue'
import RevenueExpenseChart from '@/components/dashboard/RevenueExpenseChart.vue'
import SalesLineChart from '@/components/dashboard/SalesLineChart.vue'
import SummaryCard from '@/components/dashboard/SummaryCard.vue'
import TopProductsTable from '@/components/dashboard/TopProductsTable.vue'


// ── Types ──────────────────────────────────────────────────────────────────
interface SummaryData {
    today_transactions: number
    today_revenue: number
    today_gross_profit: number
    today_cogs: number
    revenue_delta: number | null
    month_transactions: number
    month_revenue: number
    month_gross_profit: number
    month_net_profit: number
    month_expenses: number
    total_products: number
    low_stock_products: number
    out_of_stock: number
}

interface CurrentPeriodData {
    today: string
    month: string
    year: number
}

interface RecentOrder {
    id: number
    order_number: string
    cashier: string
    total_amount: number
    payment_method: 'cash' | 'qris'
    items_count: number
    created_at: string
    created_at_fmt: string
}

defineProps<{
    summary: SummaryData
    monthlySales: any[]
    revenueVsExpense: any[]
    paymentMethods: any[]
    topProducts: any[]
    recentOrders: RecentOrder[]
    currentPeriod: CurrentPeriodData
}>()

defineOptions({ layout: AppLayout })

// ── Helpers ────────────────────────────────────────────────────────────────
function rupiah(val: number | string | null | undefined) {
    if (!val && val !== 0) {
        return 'Rp 0'
    }

    const num = Number(val)

    if (num >= 1_000_000_000) {
        return 'Rp ' + (num / 1_000_000_000).toFixed(1) + 'M'
    }

    if (num >= 1_000_000) {
        return 'Rp ' + (num / 1_000_000).toFixed(1) + 'Jt'
    }

    if (num >= 1_000) {
        return 'Rp ' + (num / 1_000).toFixed(0) + 'Rb'
    }

    return 'Rp ' + num.toLocaleString('id-ID')
}
</script>

<template>
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-h1 text-neutral-100">Dashboard</h1>
            <p class="text-body1 text-neutral-60 mt-1">{{ currentPeriod.today }}</p>
        </div>
        <div class="flex items-center gap-2 px-3 py-1.5 bg-success-light rounded-full">
            <span class="w-2 h-2 rounded-full bg-success animate-pulse" />
            <span class="text-body2 text-success-dark font-semibold">Live</span>
        </div>
    </div>

    <div class="mb-2">
        <p class="text-body2 text-neutral-50 font-semibold uppercase tracking-wider mb-3">Hari Ini</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <SummaryCard
                title="Total Transaksi"
                :value="summary.today_transactions"
                subtitle="Order selesai hari ini"
                icon="transaction"
                color="blue"
            />
            <SummaryCard
                title="Total Pendapatan"
                :value="rupiah(summary.today_revenue)"
                subtitle="Gross revenue hari ini"
                icon="revenue"
                color="blue"
                :delta="summary.revenue_delta"
            />
            <SummaryCard
                title="Laba Kotor"
                :value="rupiah(summary.today_gross_profit)"
                subtitle="Pendapatan − HPP"
                icon="profit"
                color="green"
            />
            <SummaryCard
                title="HPP (COGS)"
                :value="rupiah(summary.today_cogs)"
                subtitle="Biaya modal terjual"
                icon="expense"
                color="yellow"
            />
        </div>
    </div>

    <div class="mb-6">
        <p class="text-body2 text-neutral-50 font-semibold uppercase tracking-wider mb-3 mt-5">
            Bulan Ini — {{ currentPeriod.month }}
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <SummaryCard
                title="Total Transaksi"
                :value="summary.month_transactions"
                subtitle="Order bulan ini"
                icon="transaction"
                color="blue"
            />
            <SummaryCard
                title="Total Pendapatan"
                :value="rupiah(summary.month_revenue)"
                subtitle="Gross revenue bulan ini"
                icon="revenue"
                color="blue"
            />
            <SummaryCard
                title="Laba Bersih"
                :value="rupiah(summary.month_net_profit)"
                subtitle="Setelah pengeluaran"
                icon="profit"
                color="green"
            />
            <SummaryCard
                title="Total Pengeluaran"
                :value="rupiah(summary.month_expenses)"
                subtitle="Operasional & lainnya"
                icon="expense"
                color="red"
            />
        </div>
    </div>

    <div
        v-if="summary.low_stock_products > 0 || summary.out_of_stock > 0"
        class="mb-6 p-4 bg-warning-light border border-warning/30 rounded-2xl flex items-start gap-3"
    >
        <div class="w-8 h-8 rounded-lg bg-warning/20 flex items-center justify-center shrink-0 mt-0.5">
            <AlertTriangle class="w-4 h-4 text-warning-dark" />
        </div>
        <div>
            <p class="text-body1 font-semibold text-warning-dark">Peringatan Stok</p>
            <p class="text-body2 text-warning-dark/80 mt-0.5">
                <span v-if="summary.low_stock_products > 0">
                    <strong>{{ summary.low_stock_products }} produk</strong> memiliki stok menipis (≤10).
                </span>
                <span v-if="summary.out_of_stock > 0" class="ml-1">
                    <strong>{{ summary.out_of_stock }} produk</strong> stok habis.
                </span>
                Segera perbarui stok di halaman Produk.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-5">
        <div class="xl:col-span-2">
            <SalesLineChart :data="monthlySales" />
        </div>
        <div>
            <PaymentMethodChart :data="paymentMethods" />
        </div>
    </div>

    <div class="mb-5">
        <RevenueExpenseChart :data="revenueVsExpense" />
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-10">

        <TopProductsTable :products="topProducts" />

        <div class="bg-white rounded-2xl shadow-sm border border-neutral-30 overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-neutral-30 flex items-center justify-between">
                <div>
                    <h3 class="text-h5 text-neutral-90">Transaksi Terakhir</h3>
                    <p class="text-body2 text-neutral-50 mt-0.5">8 transaksi terbaru</p>
                </div>
                
                <Link
                    href="/transactions"
                    class="text-body2 text-primary font-semibold hover:underline"
                >
                    Lihat semua →
                </Link>
            </div>
            <div class="divide-y divide-neutral-20 flex-1">
                <div
                    v-for="order in recentOrders"
                    :key="order.id"
                    class="flex items-center gap-4 px-5 py-3.5 hover:bg-neutral-20/50 transition-colors"
                >
                    <div
                        class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                        :class="order.payment_method === 'cash'
                            ? 'bg-primary/10 text-primary'
                            : 'bg-success-light text-success-dark'"
                    >
                        <Banknote v-if="order.payment_method === 'cash'" class="w-4 h-4" />
                        <QrCode v-else class="w-4 h-4" />
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-body1 font-semibold text-neutral-90 font-mono truncate">
                            {{ order.order_number }}
                        </p>
                        <p class="text-body2 text-neutral-50">
                            {{ order.cashier }} · {{ order.items_count }} item · {{ order.created_at_fmt }}
                        </p>
                    </div>

                    <div class="text-right shrink-0">
                        <p class="text-body1 font-bold text-neutral-90 tabular-nums">
                            {{ rupiah(order.total_amount) }}
                        </p>
                        <span
                            class="text-body2 font-medium uppercase"
                            :class="order.payment_method === 'cash' ? 'text-primary' : 'text-success-dark'"
                        >
                            {{ order.payment_method }}
                        </span>
                    </div>
                </div>

                <div v-if="!recentOrders.length" class="px-5 py-10 text-center text-body2 text-neutral-50 h-full flex flex-col justify-center">
                    Belum ada transaksi hari ini.
                </div>
            </div>
        </div>
    </div>
</template>