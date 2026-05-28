<script setup lang="ts">
import axios from 'axios'
import { CheckCircle2, Search, SearchX, ShoppingCart, Trash2, X } from 'lucide-vue-next'
import { computed, onMounted, onUnmounted, ref } from 'vue'

import CartItem from '@/components/pos/CartItem.vue'
import PaymentModal from '@/components/pos/PaymentModal.vue'
import ProductCard from '@/components/pos/ProductCard.vue'
import RecommendationModal from '@/components/pos/RecommendationModal.vue'
import AppLayout from '@/layouts/AppLayout.vue'

// ── Types ──────────────────────────────────────────────────────────────────
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

interface CartItemData extends Product {
    quantity: number
    subtotal: number
}

const props = defineProps<{
    products: Product[]
    categories: Category[]
}>()

// ─────────────────────────────────────────────────────────────────────────────
// 1. PRODUCT FILTER STATE
// ─────────────────────────────────────────────────────────────────────────────
const search = ref('')
const activeCategoryId = ref<number | null>(null)
const searchInput = ref<HTMLInputElement | null>(null)

const filteredProducts = computed(() => {
    let list = props.products

    if (activeCategoryId.value) {
        list = list.filter(p => p.category_id === activeCategoryId.value)
    }

    if (search.value.trim()) {
        const q = search.value.toLowerCase()
        list = list.filter(p => p.name.toLowerCase().includes(q) || p.code.toLowerCase().includes(q))
    }

    return list
})

// ─────────────────────────────────────────────────────────────────────────────
// 2. CART STATE
// ─────────────────────────────────────────────────────────────────────────────
const cart = ref<CartItemData[]>([])

const cartTotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.subtotal, 0)
})

const cartItemCount = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.quantity, 0)
})

function cartQuantityFor(productId: number) {
    return cart.value.find(i => i.id === productId)?.quantity ?? 0
}

function addToCart(product: Product) {
    const existing = cart.value.find(i => i.id === product.id)

    if (existing) {
        if (existing.quantity >= product.stock) {
            return // stok limit
        }

        existing.quantity++
        existing.subtotal = existing.quantity * existing.selling_price
    } else {
        cart.value.push({
            id: product.id,
            name: product.name,
            code: product.code,
            unit: product.unit,
            selling_price: product.selling_price,
            stock: product.stock,
            photo: product.photo,
            category_id: product.category_id,
            category: product.category,
            quantity: 1,
            subtotal: product.selling_price,
        })
    }
}

function increaseItem(productId: number) {
    const item = cart.value.find(i => i.id === productId)

    if (!item || item.quantity >= item.stock) {
        return
    }

    item.quantity++
    item.subtotal = item.quantity * item.selling_price
}

function decreaseItem(productId: number) {
    const item = cart.value.find(i => i.id === productId)

    if (!item) {
        return
    }

    if (item.quantity <= 1) {
        removeItem(productId)

        return
    }

    item.quantity--
    item.subtotal = item.quantity * item.selling_price
}

function removeItem(productId: number) {
    cart.value = cart.value.filter(i => i.id !== productId)
}

function clearCart() {
    cart.value = []
}

// ─────────────────────────────────────────────────────────────────────────────
// 3. RECOMMENDATION ENGINE
// ─────────────────────────────────────────────────────────────────────────────
const showRecommendation = ref(false)
const recommendations = ref<Product[]>([])
const dominantCategory = ref<Category | null>(null)
const loadingRecommendation = ref(false)

async function handleCheckout() {
    if (!cart.value.length) {
        return
    }

    showRecommendation.value = true
    loadingRecommendation.value = true
    recommendations.value = []
    dominantCategory.value = null

    try {
        const payload = cart.value.map(item => ({
            product_id: item.id,
            quantity: item.quantity,
        }))

        const { data } = await axios.post('/pos/recommend', { cart: payload })
        
        recommendations.value = data.recommendations ?? []
        dominantCategory.value = data.dominant_category ?? null
    } catch (e) {
        console.error('Recommendation error:', e)
        recommendations.value = []
    } finally {
        loadingRecommendation.value = false
    }
}

// Bypass strict TypeScript checking dengan (any)
function addRecommendedToCart(product: any) {
    addToCart(product as Product)
}

function proceedToPayment() {
    showRecommendation.value = false
    showPayment.value = true
}

function skipRecommendation() {
    showRecommendation.value = false
    showPayment.value = true
}

// ─────────────────────────────────────────────────────────────────────────────
// 4. PAYMENT
// ─────────────────────────────────────────────────────────────────────────────
const showPayment = ref(false)
const processingPayment = ref(false)
const receipt = ref(null)

async function handlePaymentConfirm({ payment_method, payment_amount }: { payment_method: string, payment_amount: number }) {
    processingPayment.value = true
    receipt.value = null

    try {
        const payload = {
            cart: cart.value.map(item => ({
                product_id: item.id,
                quantity: item.quantity,
                unit_price: item.selling_price,
                subtotal: item.subtotal,
            })),
            total_amount: cartTotal.value,
            payment_method,
            payment_amount,
        }

        const { data } = await axios.post('/pos/checkout', payload)
        
        receipt.value = data.receipt

        cart.value.forEach(item => {
            const product = props.products.find(p => p.id === item.id)
            
            if (product) {
                product.stock -= item.quantity
            }
        })
    } catch (e: any) {
        const msg = e.response?.data?.message ?? 'Terjadi kesalahan. Silakan coba lagi.'
        alert(msg)
    } finally {
        processingPayment.value = false
    }
}

function handleNewTransaction() {
    clearCart()
    receipt.value = null
    showPayment.value = false
    search.value = ''
    activeCategoryId.value = null
}

// ─────────────────────────────────────────────────────────────────────────────
// 5. KEYBOARD SHORTCUT
// ─────────────────────────────────────────────────────────────────────────────
function handleKeydown(e: KeyboardEvent) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
        e.preventDefault()
        searchInput.value?.focus()
    }

    if (e.key === 'Escape') {
        search.value = ''
    }
}

onMounted(() => {
    document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown)
})

// ─────────────────────────────────────────────────────────────────────────────
// 6. HELPERS
// ─────────────────────────────────────────────────────────────────────────────
function formatRupiah(val: number) {
    return 'Rp ' + Number(val).toLocaleString('id-ID')
}
</script>

<template>
    <AppLayout>
        <div class="flex gap-5 h-[calc(100vh-5rem)]">

            <div class="flex-1 flex flex-col min-w-0 gap-4">

                <div class="flex flex-col sm:flex-row gap-3 flex-shrink-0">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-neutral-50">
                            <Search class="w-4 h-4" />
                        </div>
                        <input
                            id="pos-search"
                            ref="searchInput"
                            v-model="search"
                            type="text"
                            placeholder="Cari produk... (Ctrl+F)"
                            class="w-full pl-10 pr-4 py-2.5 bg-white border border-neutral-40 rounded-xl text-body1 text-neutral-90 placeholder-neutral-50 shadow-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                        />
                        <button
                            v-if="search"
                            @click="search = ''"
                            class="absolute inset-y-0 right-3 flex items-center text-neutral-40 hover:text-neutral-70 cursor-pointer"
                        >
                            <X class="w-4 h-4" />
                        </button>
                    </div>

                    <div class="flex items-center px-4 py-2.5 bg-white rounded-xl border border-neutral-40 shadow-xs">
                        <span class="text-body2 text-neutral-60">
                            <span class="font-bold text-neutral-90">{{ filteredProducts.length }}</span> produk
                        </span>
                    </div>
                </div>

                <div class="flex gap-2 overflow-x-auto pb-1 flex-shrink-0 scrollbar-hide">
                    <button
                        @click="activeCategoryId = null"
                        class="flex-shrink-0 px-4 py-1.5 rounded-full text-body2 font-semibold border-2 transition-all duration-150 whitespace-nowrap cursor-pointer"
                        :class="!activeCategoryId
                            ? 'bg-primary border-primary text-white shadow-sm'
                            : 'bg-white border-neutral-30 text-neutral-60 hover:border-primary/50 hover:text-primary'"
                    >
                        Semua
                    </button>
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        @click="activeCategoryId = activeCategoryId === cat.id ? null : cat.id"
                        class="flex-shrink-0 px-4 py-1.5 rounded-full text-body2 font-semibold border-2 transition-all duration-150 whitespace-nowrap cursor-pointer"
                        :class="activeCategoryId === cat.id
                            ? 'bg-primary border-primary text-white shadow-sm'
                            : 'bg-white border-neutral-30 text-neutral-60 hover:border-primary/50 hover:text-primary'"
                    >
                        {{ cat.name }}
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <div
                        v-if="filteredProducts.length"
                        class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 pb-4 pr-1"
                    >
                        <ProductCard
                            v-for="product in filteredProducts"
                            :key="product.id"
                            :product="product"
                            :quantity="cartQuantityFor(product.id)"
                            @add="addToCart"
                        />
                    </div>

                    <div v-else class="flex flex-col items-center justify-center h-full gap-4 text-neutral-50 py-20">
                        <SearchX class="w-16 h-16" stroke-width="1.5" />
                        <p class="text-body1 font-medium">Produk tidak ditemukan</p>
                        <button @click="search = ''; activeCategoryId = null"
                            class="text-body2 text-primary hover:underline cursor-pointer">
                            Reset filter
                        </button>
                    </div>
                </div>
            </div>

            <div class="w-80 xl:w-96 flex-shrink-0 flex flex-col bg-white rounded-2xl shadow-sm border border-neutral-40 overflow-hidden">

                <div class="px-5 py-4 border-b border-neutral-40 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <ShoppingCart class="w-5 h-5 text-primary" />
                        <h2 class="text-h5 text-neutral-90">Keranjang Belanja</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <span
                            v-if="cartItemCount > 0"
                            class="px-2.5 py-0.5 bg-primary text-white text-body2 font-bold rounded-full"
                        >
                            {{ cartItemCount }}
                        </span>
                        <button
                            v-if="cart.length"
                            @click="clearCart"
                            class="p-1.5 rounded-lg text-neutral-40 hover:bg-danger-light hover:text-danger transition-colors cursor-pointer"
                            title="Kosongkan keranjang"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-5 divide-y divide-neutral-30">
                    <div
                        v-if="!cart.length"
                        class="flex flex-col items-center justify-center h-full gap-3 text-neutral-40 py-12"
                    >
                        <ShoppingCart class="w-14 h-14" stroke-width="1.5" />
                        <p class="text-body2 text-center leading-relaxed">
                            Keranjang masih kosong.<br>Pilih produk dari katalog.
                        </p>
                    </div>

                    <TransitionGroup
                        tag="div"
                        enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 -translate-x-3"
                        enter-to-class="opacity-100 translate-x-0"
                        leave-active-class="transition-all duration-150 ease-in absolute w-full"
                        leave-from-class="opacity-100 translate-x-0"
                        leave-to-class="opacity-0 -translate-x-3"
                    >
                        <CartItem
                            v-for="item in cart"
                            :key="item.id"
                            :item="item"
                            @increase="increaseItem"
                            @decrease="decreaseItem"
                            @remove="removeItem"
                        />
                    </TransitionGroup>
                </div>

                <div class="border-t border-neutral-40 px-5 py-4 space-y-3 flex-shrink-0">

                    <div class="space-y-2">
                        <div class="flex justify-between text-body2 text-neutral-60">
                            <span>{{ cartItemCount }} item</span>
                            <span>{{ formatRupiah(cartTotal) }}</span>
                        </div>
                    </div>

                    <div class="border-t border-dashed border-neutral-40" />

                    <div class="flex items-center justify-between">
                        <span class="text-body1 font-bold text-neutral-90">Total</span>
                        <span class="text-h2 font-bold text-primary tabular-nums">
                            {{ formatRupiah(cartTotal) }}
                        </span>
                    </div>

                    <button
                        @click="handleCheckout"
                        :disabled="!cart.length"
                        class="w-full flex items-center justify-center gap-2.5 py-4 rounded-xl text-body1 font-bold transition-all duration-200 cursor-pointer disabled:cursor-not-allowed"
                        :class="cart.length
                            ? 'bg-primary hover:bg-primary-hover active:bg-primary-pressed text-white shadow-sm hover:shadow-md'
                            : 'bg-neutral-30 text-neutral-50'"
                    >
                        <CheckCircle2 class="w-5 h-5" />
                        Checkout & Bayar
                    </button>
                </div>
            </div>
        </div>

        <RecommendationModal
            :show="showRecommendation"
            :recommendations="recommendations"
            :dominant-category="dominantCategory"
            :loading="loadingRecommendation"
            @add-to-cart="addRecommendedToCart"
            @skip="skipRecommendation"
            @proceed="proceedToPayment"
        />

        <PaymentModal
            :show="showPayment"
            :total-amount="cartTotal"
            :cart="cart"
            :processing="processingPayment"
            :receipt="receipt"
            @close="showPayment = false"
            @confirm="handlePaymentConfirm"
            @new-transaction="handleNewTransaction"
        />
    </AppLayout>
</template>

<style scoped>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>