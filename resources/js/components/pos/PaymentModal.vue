<script setup lang="ts">
import { AlertCircle, Banknote, CheckCircle2, Loader2, QrCode, X } from 'lucide-vue-next'
import { computed, ref, watch } from 'vue'
import ReceiptView from '@/components/pos/ReceiptView.vue'

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
    created_at: string
    cashier: string
    items: ReceiptItem[]
    total_amount: number
    payment_method: 'cash' | 'qris'
    payment_amount: number
    change_amount: number
    store_name: string
    store_tagline: string
}

const props = defineProps<{
    show: boolean
    totalAmount: number
    cart?: any[]
    processing: boolean
    receipt?: Receipt | null
}>()

const emit = defineEmits<{
    (e: 'close'): void
    (e: 'confirm', data: { payment_method: string; payment_amount: number }): void
    (e: 'new-transaction'): void
}>()

// ── Payment State ──────────────────────────────────────────────────────────
const paymentMethod = ref<'cash' | 'qris'>('cash')
const paymentInput  = ref('')
const showReceipt   = ref(false)

watch(() => props.show, (val) => {
    if (val) {
        paymentMethod.value = 'cash'
        paymentInput.value  = ''
        showReceipt.value   = false
    }
})

watch(() => props.receipt, (val) => {
    if (val) {
        showReceipt.value = true
    }
})

// Quick amount buttons
const quickAmounts = computed(() => {
    const total = props.totalAmount
    const amounts = [total]
    const roundUps = [5000, 10000, 20000, 50000, 100000]

    for (const round of roundUps) {
        const rounded = Math.ceil(total / round) * round

        if (rounded !== total && !amounts.includes(rounded)) {
            amounts.push(rounded)
        }

        if (amounts.length >= 5) {
            break
        }
    }

    return amounts
})

const paymentAmountNum = computed(() => {
    const parsed = parseInt(paymentInput.value)

    return parsed || 0
})

const changeAmount = computed(() => {
    return Math.max(0, paymentAmountNum.value - props.totalAmount)
})

const isPaymentValid = computed(() => {
    if (paymentMethod.value === 'qris') {
        return true
    }

    return paymentAmountNum.value >= props.totalAmount
})

function setQuickAmount(amount: number) {
    paymentInput.value = amount.toString()
}

function handleConfirm() {
    let payAmt = 0

    if (paymentMethod.value === 'qris') {
        payAmt = props.totalAmount
    } else {
        payAmt = paymentAmountNum.value
    }

    emit('confirm', {
        payment_method: paymentMethod.value,
        payment_amount: payAmt,
    })
}

function formatRupiah(val: number | string | undefined) {
    if (!val && val !== 0) {
        return '—'
    }

    return 'Rp ' + Number(val).toLocaleString('id-ID')
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-all duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-neutral-100/60 backdrop-blur-sm" />

                <Transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                >
                    <div v-if="show" class="relative bg-white rounded-3xl shadow-lg w-full max-w-md z-10 overflow-hidden">
                        
                        <template v-if="!showReceipt">
                            <div class="px-6 pt-6 pb-4 border-b border-neutral-40">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-h3 text-neutral-100">Pembayaran</h3>
                                    <button @click="emit('close')" class="p-1.5 rounded-lg text-neutral-50 hover:bg-neutral-20 hover:text-neutral-90 transition-colors cursor-pointer">
                                        <X class="w-5 h-5" />
                                    </button>
                                </div>
                                <div class="mt-4 p-4 bg-neutral-20 rounded-2xl text-center">
                                    <p class="text-body2 text-neutral-60 font-medium">Total Pembayaran</p>
                                    <p class="text-h1 font-bold text-neutral-100 tabular-nums mt-1">{{ formatRupiah(totalAmount) }}</p>
                                </div>
                            </div>

                            <div class="px-6 py-5 space-y-5">
                                <div>
                                    <p class="text-body2 font-medium text-neutral-70 mb-2">Metode Pembayaran</p>
                                    <div class="grid grid-cols-2 gap-3">
                                        <button @click="paymentMethod = 'cash'" class="flex flex-col items-center gap-2 p-3.5 rounded-xl border-2 transition-all duration-200 cursor-pointer" :class="paymentMethod === 'cash' ? 'border-primary bg-primary/5 shadow-sm' : 'border-neutral-30 hover:border-neutral-50'">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" :class="paymentMethod === 'cash' ? 'bg-primary' : 'bg-neutral-20'">
                                                <Banknote class="w-5 h-5" :class="paymentMethod === 'cash' ? 'text-white' : 'text-neutral-60'" />
                                            </div>
                                            <span class="text-body2 font-semibold" :class="paymentMethod === 'cash' ? 'text-primary' : 'text-neutral-70'">Tunai</span>
                                        </button>
                                        <button @click="paymentMethod = 'qris'" class="flex flex-col items-center gap-2 p-3.5 rounded-xl border-2 transition-all duration-200 cursor-pointer" :class="paymentMethod === 'qris' ? 'border-primary bg-primary/5 shadow-sm' : 'border-neutral-30 hover:border-neutral-50'">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" :class="paymentMethod === 'qris' ? 'bg-primary' : 'bg-neutral-20'">
                                                <QrCode class="w-5 h-5" :class="paymentMethod === 'qris' ? 'text-white' : 'text-neutral-60'" />
                                            </div>
                                            <span class="text-body2 font-semibold" :class="paymentMethod === 'qris' ? 'text-primary' : 'text-neutral-70'">QRIS</span>
                                        </button>
                                    </div>
                                </div>

                                <div v-if="paymentMethod === 'qris'" class="text-center">
                                    <div class="inline-block p-4 bg-neutral-20 rounded-2xl border-2 border-dashed border-neutral-40">
                                        <div class="w-40 h-40 bg-white rounded-xl mx-auto flex flex-col items-center justify-center border border-neutral-40 shadow-xs">
                                            <QrCode class="w-24 h-24 text-neutral-90" stroke-width="1.5" />
                                        </div>
                                        <p class="text-body2 text-neutral-60 mt-2 font-medium">Scan QR untuk membayar</p>
                                    </div>
                                </div>

                                <div v-if="paymentMethod === 'cash'" class="space-y-3">
                                    <div>
                                        <p class="text-body2 font-medium text-neutral-70 mb-2">Nominal Cepat</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button v-for="amount in quickAmounts" :key="amount" @click="setQuickAmount(amount)" class="px-3 py-1.5 rounded-lg text-body2 font-semibold border-2 transition-all duration-150 cursor-pointer" :class="paymentAmountNum === amount ? 'bg-primary border-primary text-white shadow-sm' : 'border-neutral-30 text-neutral-70 hover:border-primary/50 hover:text-primary bg-white'">
                                                {{ amount === totalAmount ? 'Pas' : formatRupiah(amount) }}
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-body2 font-medium text-neutral-70 mb-1.5">Nominal Diterima</p>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-4 flex items-center text-h5 font-bold text-neutral-50">Rp</span>
                                            <input v-model="paymentInput" type="number" min="0" class="w-full pl-12 pr-4 py-3.5 border-2 rounded-xl text-h4 font-bold text-neutral-90 shadow-md focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all tabular-nums" :class="paymentAmountNum > 0 && paymentAmountNum < totalAmount ? 'border-danger' : 'border-neutral-40'" />
                                        </div>
                                        <p v-if="paymentAmountNum > 0 && paymentAmountNum < totalAmount" class="text-body2 text-danger mt-1.5 flex items-center gap-1.5">
                                            <AlertCircle class="w-3.5 h-3.5" /> Kurang {{ formatRupiah(totalAmount - paymentAmountNum) }}
                                        </p>
                                    </div>
                                    <div v-if="changeAmount > 0" class="flex items-center justify-between p-4 bg-success-light rounded-xl border border-success/20">
                                        <span class="text-body1 font-semibold text-success-dark">Kembalian</span>
                                        <span class="text-h4 font-bold text-success-dark tabular-nums">{{ formatRupiah(changeAmount) }}</span>
                                    </div>
                                </div>

                                <button @click="handleConfirm" :disabled="!isPaymentValid || processing" class="w-full flex items-center justify-center gap-2 py-4 rounded-xl text-body1 font-bold transition-all duration-200 cursor-pointer disabled:cursor-not-allowed" :class="isPaymentValid && !processing ? 'bg-primary hover:bg-primary-hover active:bg-primary-pressed text-white shadow-sm' : 'bg-neutral-30 text-neutral-50'">
                                    <Loader2 v-if="processing" class="w-5 h-5 animate-spin" />
                                    <CheckCircle2 v-else class="w-5 h-5" />
                                    {{ processing ? 'Memproses...' : 'Konfirmasi Pembayaran' }}
                                </button>
                            </div>
                        </template>

                        <template v-else-if="receipt">
                        <div id="printable-receipt" class="px-6 py-5 flex flex-col" style="min-height: 500px;">
                            <ReceiptView
                                :receipt="(receipt as any)"
                                @new-transaction="emit('new-transaction')"
                            />
                        </div>
                    </template>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style>
@media print {
    /* 1. Sembunyikan aplikasi POS di latar belakang (Laravel Inertia selalu pakai id #app) */
    #app {
        display: none !important;
    }

    /* 2. Sembunyikan overlay latar belakang gelap/blur */
    .backdrop-blur-sm {
        display: none !important;
    }

    /* 3. Bebaskan wadah modal dari Flexbox agar tidak memusatkan/mengecilkan konten */
    .fixed.inset-0 {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        display: block !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* 4. Hapus batasan ukuran, border-radius, shadow, dan BUKA overflow-nya */
    .max-w-md {
        max-width: 100% !important;
        width: 100% !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        overflow: visible !important; /* <-- INI KUNCI AGAR TIDAK KOSONG */
    }

    /* 5. Atur margin kertas jadi 0 (sangat disarankan untuk Printer Kasir Thermal) */
    @page {
        margin: 0;
    }
}
</style>