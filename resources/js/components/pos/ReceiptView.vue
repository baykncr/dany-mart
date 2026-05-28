<script setup lang="ts">
import { Check, Printer, RotateCcw } from 'lucide-vue-next'
import { ref } from 'vue'

// ── Types ──────────────────────────────────────────────────────────────────
interface ReceiptItem {
    name: string
    qty: number
    unit: string
    unit_price: number
    subtotal: number
}

interface Receipt {
    order_number: string
    cashier_name: string
    store_name: string
    store_tagline: string
    transaction_at: string
    items: ReceiptItem[]
    item_count: number
    total_amount: number
    payment_method: 'cash' | 'qris'
    payment_amount: number
    change_amount: number
}

defineProps<{
    receipt: Receipt
}>()

const emit = defineEmits<{
    (e: 'new-transaction'): void
}>()

// ── Helpers ────────────────────────────────────────────────────────────────
const printing = ref(false)

function rupiah(val: number | string) {
    return 'Rp ' + Number(val).toLocaleString('id-ID')
}

function paymentMethodLabel(method: string) {
    return method === 'qris' ? 'QRIS' : 'Tunai'
}

// ── Print ──────────────────────────────────────────────────────────────────
function handlePrint() {
    printing.value = true
    setTimeout(() => {
        window.print()
        printing.value = false
    }, 100)
}
</script>

<template>
    <div class="flex flex-col h-full">

        <div class="text-center pb-5 border-b border-neutral-30 flex-shrink-0">
            <div class="relative w-16 h-16 mx-auto mb-3">
                <div class="w-16 h-16 rounded-full bg-success flex items-center justify-center shadow-md">
                    <Check class="w-8 h-8 text-white" stroke-width="2.5" />
                </div>
                <div class="absolute inset-0 rounded-full bg-success/30 animate-ping" />
            </div>
            <h3 class="text-h3 text-neutral-100 font-bold">Transaksi Berhasil!</h3>
            <p class="text-body2 text-neutral-50 mt-1 font-mono">{{ receipt.order_number }}</p>
        </div>

        <div class="flex-1 overflow-y-auto py-4">
            <div id="smartpos-receipt" class="bg-white border border-neutral-30 rounded-2xl overflow-hidden shadow-xs mx-auto max-w-sm">
                
                <div class="bg-primary px-5 py-4 text-center">
                    <p class="text-white font-bold text-base tracking-wider uppercase">{{ receipt.store_name }}</p>
                    <p class="text-primary-surface text-xs mt-0.5">{{ receipt.store_tagline }}</p>
                </div>

                <div class="px-5 py-3 bg-neutral-20 border-b border-neutral-30 text-center space-y-0.5">
                    <p class="text-body2 text-neutral-60 font-mono">{{ receipt.transaction_at }}</p>
                    <p class="text-body2 text-neutral-60">
                        Kasir: <span class="font-semibold text-neutral-80">{{ receipt.cashier_name }}</span>
                    </p>
                </div>

                <div class="px-5 py-2">
                    <div class="border-t-2 border-dashed border-neutral-30" />
                </div>

                <div class="px-5 space-y-2.5 pb-2">
                    <div v-for="(item, i) in receipt.items" :key="i" class="text-xs font-mono">
                        <p class="text-neutral-90 font-semibold leading-tight mb-0.5 break-words">{{ item.name }}</p>
                        <div class="flex items-baseline justify-between gap-2">
                            <span class="text-neutral-50 shrink-0">
                                {{ item.qty }} {{ item.unit }} × {{ rupiah(item.unit_price) }}
                            </span>
                            <span class="text-neutral-80 font-bold tabular-nums">{{ rupiah(item.subtotal) }}</span>
                        </div>
                    </div>
                </div>

                <div class="px-5 py-2">
                    <div class="border-t-2 border-dashed border-neutral-30" />
                </div>

                <div class="px-5 pb-3 space-y-1.5 font-mono text-xs">
                    <div class="flex justify-between text-neutral-50">
                        <span>Jumlah item</span>
                        <span>{{ receipt.item_count }} pcs</span>
                    </div>
                    <div class="flex justify-between text-neutral-70 font-semibold">
                        <span>Subtotal</span>
                        <span class="tabular-nums">{{ rupiah(receipt.total_amount) }}</span>
                    </div>
                    <div class="border-t border-neutral-30 my-1" />
                    <div class="flex justify-between items-center">
                        <span class="text-neutral-90 font-bold text-sm">TOTAL</span>
                        <span class="text-primary font-bold text-sm tabular-nums">{{ rupiah(receipt.total_amount) }}</span>
                    </div>
                    <div class="flex justify-between text-neutral-70">
                        <span>Dibayar ({{ paymentMethodLabel(receipt.payment_method) }})</span>
                        <span class="tabular-nums">{{ rupiah(receipt.payment_amount) }}</span>
                    </div>
                    <div v-if="receipt.payment_method === 'cash' && receipt.change_amount > 0"
                        class="flex justify-between items-center bg-success-light rounded-lg px-2.5 py-1.5">
                        <span class="text-success-dark font-bold">Kembali</span>
                        <span class="text-success-dark font-bold tabular-nums">{{ rupiah(receipt.change_amount) }}</span>
                    </div>
                </div>

                <div class="border-t-2 border-dashed border-neutral-30 mx-5" />
                <div class="px-5 py-3 text-center">
                    <p class="text-body2 text-neutral-50 font-mono text-xs leading-relaxed">
                        Terima kasih di<br><span class="font-bold text-neutral-70">{{ receipt.store_name }}</span>
                    </p>
                    <p class="text-body2 text-neutral-40 font-mono text-xs mt-1">Barang yang sudah dibeli<br>tidak dapat dikembalikan.</p>
                </div>
            </div>
        </div>

        <div class="flex gap-3 pt-4 border-t border-neutral-30 flex-shrink-0">
            <button
                @click="handlePrint"
                :disabled="printing"
                class="flex-1 flex items-center justify-center gap-2 py-3 border-2 border-neutral-30 rounded-xl text-body1 font-semibold text-neutral-70 hover:bg-neutral-20 hover:border-neutral-50 transition-all cursor-pointer"
            >
                <Printer class="w-4 h-4" />
                {{ printing ? 'Mencetak...' : 'Cetak Struk' }}
            </button>
            <button
                @click="emit('new-transaction')"
                class="flex-1 flex items-center justify-center gap-2 py-3 bg-primary hover:bg-primary-hover rounded-xl text-body1 font-bold text-white shadow-sm transition-all cursor-pointer"
            >
                <RotateCcw class="w-4 h-4" />
                Transaksi Baru
            </button>
        </div>
    </div>
</template>

<style scoped>
@media print {
    body * { visibility: hidden; }
    #smartpos-receipt, #smartpos-receipt * { visibility: visible; }
    #smartpos-receipt {
        position: absolute; left: 0; top: 0; width: 100%; margin: 0; padding: 0; border: none; background: white;
    }
}
</style>