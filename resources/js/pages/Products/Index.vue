<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { Search, Plus, Package, Edit2, Trash2, TrendingUp, X, ImagePlus, Loader2 } from 'lucide-vue-next'
import { ref, computed, watch } from 'vue'

import ConfirmDialog from '@/components/ConfirmDialog.vue'
import InputError from '@/components/InputError.vue'
import Modal from '@/components/Modal.vue'
import PageHeader from '@/components/PageHeader.vue'
import AppLayout from '@/layouts/AppLayout.vue'

// ── Types ──────────────────────────────────────────────────────────────────
interface Category {
    id: number
    name: string
}

interface Product {
    id: number
    code: string
    name: string
    unit: string
    purchase_price: number
    selling_price: number
    stock: number
    photo?: string | null
    category?: Category
}

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

interface PaginatedProducts {
    data: Product[]
    from: number
    to: number
    total: number
    last_page: number
    links: PaginationLink[]
}

const props = defineProps<{
    products: PaginatedProducts
    categories: Category[]
    filters?: {
        search?: string
        category_id?: string | number
    }
}>()

// ── Filters ────────────────────────────────────────────────────────────────
const search     = ref(props.filters?.search ?? '')
const categoryId = ref(props.filters?.category_id ?? '')

let searchTimer: number | null = null

watch(search, () => {
    if (searchTimer) {
        clearTimeout(searchTimer)
    }

    searchTimer = window.setTimeout(() => applyFilters(), 400)
})

watch(categoryId, () => applyFilters())

function applyFilters() {
    router.get(
        '/products',
        {
            search: search.value || undefined,
            category_id: categoryId.value || undefined,
        },
        { preserveState: true, replace: true }
    )
}

// ── Modal ──────────────────────────────────────────────────────────────────
const showModal    = ref(false)
const showConfirm  = ref(false)
const editTarget   = ref<Product | null>(null)
const deleteTarget = ref<Product | null>(null)
const photoPreview = ref<string | null>(null)

const form = useForm({
    category_id: '' as string | number,
    code: '',
    name: '',
    unit: 'pcs',
    purchase_price: '' as string | number,
    selling_price: '' as string | number,
    stock: 0,
    photo: null as File | null,
    _method: '',
})

const unitOptions = ['pcs', 'botol', 'kaleng', 'kg', 'gram', 'liter', 'ml', 'dus', 'karton', 'sachet', 'strip', 'pak']

function openCreate() {
    editTarget.value = null
    photoPreview.value = null
    form.reset()
    form._method = ''
    form.unit = 'pcs'
    form.stock = 0
    form.clearErrors()
    showModal.value = true
}

function openEdit(product: Product) {
    editTarget.value = product
    photoPreview.value = product.photo ?? null
    form.category_id    = product.category?.id ?? ''
    form.code           = product.code
    form.name           = product.name
    form.unit           = product.unit
    form.purchase_price = product.purchase_price
    form.selling_price  = product.selling_price
    form.stock          = product.stock
    form.photo          = null
    form._method        = 'PUT'
    form.clearErrors()
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    photoPreview.value = null
    form.reset()
}

function handlePhotoChange(e: Event) {
    const target = e.target as HTMLInputElement
    const file = target.files?.[0]

    if (!file) {
        return
    }

    form.photo = file
    photoPreview.value = URL.createObjectURL(file)
}

function removePhoto() {
    form.photo = null
    photoPreview.value = editTarget.value?.photo ?? null
}

function submit() {
    const url = editTarget.value
        ? `/products/${editTarget.value.id}`
        : '/products'

    form.post(url, {
        forceFormData: true,
        onSuccess: closeModal,
    })
}

// ── Delete ─────────────────────────────────────────────────────────────────
function confirmDelete(product: Product) {
    deleteTarget.value = product
    showConfirm.value = true
}

const deleting = ref(false)

function doDelete() {
    if (!deleteTarget.value) {
        return
    }

    deleting.value = true
    useForm({}).delete(`/products/${deleteTarget.value.id}`, {
        onFinish: () => {
            deleting.value = false
            showConfirm.value = false
            deleteTarget.value = null
        },
    })
}

// ── Helpers ────────────────────────────────────────────────────────────────
const profitMargin = computed(() => {
    const buy  = parseInt(form.purchase_price as string) || 0
    const sell = parseInt(form.selling_price as string)  || 0

    if (!buy || !sell) {
        return null
    }

    const margin = (((sell - buy) / sell) * 100).toFixed(1)

    return { amount: sell - buy, percent: margin }
})

function formatRupiah(val: number | string | undefined) {
    if (!val && val !== 0) {
        return '—'
    }

    return 'Rp ' + Number(val).toLocaleString('id-ID')
}

function stockBadge(stock: number) {
    if (stock === 0) {
        return { text: 'Habis',   cls: 'bg-danger-light text-danger' }
    }

    if (stock <= 10) {
        return { text: 'Menipis', cls: 'bg-warning-light text-warning-dark' }
    }

    return { text: 'Tersedia',    cls: 'bg-success-light text-success-dark' }
}
</script>

<template>
    <AppLayout>
        <PageHeader
            title="Manajemen Produk"
            description="Kelola seluruh data produk, harga, dan stok."
        />

        <div class="flex flex-col sm:flex-row gap-3 mb-5">
            <div class="relative flex-1 max-w-xs">
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                    <Search class="w-4 h-4 text-neutral-50" />
                </div>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama / kode produk..."
                    class="w-full pl-9 pr-4 py-2.5 bg-white border border-neutral-40 rounded-xl text-body1 text-neutral-90 placeholder-neutral-50 shadow-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                />
            </div>

            <select
                v-model="categoryId"
                class="px-3.5 py-2.5 bg-white border border-neutral-40 rounded-xl text-body1 text-neutral-90 shadow-xs focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all cursor-pointer"
            >
                <option value="">Semua Kategori</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>

            <button
                @click="openCreate"
                class="flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-primary-hover active:bg-primary-pressed text-white rounded-xl text-body1 font-medium shadow-sm transition-all whitespace-nowrap cursor-pointer"
            >
                <Plus class="w-4 h-4" />
                Tambah Produk
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-neutral-40 bg-neutral-20">
                            <th class="text-left px-5 py-3.5 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Produk</th>
                            <th class="text-left px-5 py-3.5 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Kategori</th>
                            <th class="text-right px-5 py-3.5 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Harga Modal</th>
                            <th class="text-right px-5 py-3.5 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Harga Jual</th>
                            <th class="text-center px-5 py-3.5 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Stok</th>
                            <th class="text-right px-5 py-3.5 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-40">
                        <tr
                            v-for="product in products.data"
                            :key="product.id"
                            class="hover:bg-neutral-20/50 transition-colors"
                        >
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 bg-neutral-30 border border-neutral-40">
                                        <img
                                            v-if="product.photo"
                                            :src="product.photo"
                                            :alt="product.name"
                                            class="w-full h-full object-cover"
                                        />
                                        <div v-else class="w-full h-full flex items-center justify-center">
                                            <Package class="w-5 h-5 text-neutral-50" />
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-body1 font-medium text-neutral-90">{{ product.name }}</p>
                                        <p class="text-body2 text-neutral-50">{{ product.code }} · {{ product.unit }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-primary/10 text-primary text-body2 font-medium">
                                    {{ product.category?.name ?? '—' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-right text-body1 text-neutral-70">
                                {{ formatRupiah(product.purchase_price) }}
                            </td>
                            <td class="px-5 py-4 text-right text-body1 font-semibold text-neutral-90">
                                {{ formatRupiah(product.selling_price) }}
                            </td>

                            <td class="px-5 py-4 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-body1 font-semibold text-neutral-90">{{ product.stock }}</span>
                                    <span
                                        class="inline-block px-2 py-0.5 rounded-full text-body2 font-medium"
                                        :class="stockBadge(product.stock).cls"
                                    >
                                        {{ stockBadge(product.stock).text }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        @click="openEdit(product)"
                                        class="p-2 rounded-lg text-neutral-60 hover:bg-primary/10 hover:text-primary transition-colors cursor-pointer"
                                        title="Edit"
                                    >
                                        <Edit2 class="w-4 h-4" />
                                    </button>
                                    <button
                                        @click="confirmDelete(product)"
                                        class="p-2 rounded-lg text-neutral-60 hover:bg-danger-light hover:text-danger transition-colors cursor-pointer"
                                        title="Hapus"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="!products.data.length">
                            <td colspan="6" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-neutral-50">
                                    <Package class="w-12 h-12" stroke-width="1.5" />
                                    <p class="text-body1 font-medium">Belum ada produk</p>
                                    <p class="text-body2">Tambahkan produk atau ubah filter pencarian.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="products.last_page > 1" class="px-5 py-4 border-t border-neutral-40 flex items-center justify-between">
                <p class="text-body2 text-neutral-60">
                    Menampilkan {{ products.from }}–{{ products.to }} dari {{ products.total }} produk
                </p>
                <div class="flex items-center gap-1">
                    <template v-for="link in products.links" :key="link.label">
                        <component
                            :is="link.url ? 'a' : 'span'"
                            :href="link.url ?? undefined"
                            class="px-3 py-1.5 rounded-lg text-body2 transition-colors"
                            :class="link.active
                                ? 'bg-primary text-white font-semibold'
                                : link.url
                                    ? 'text-neutral-70 hover:bg-neutral-30 cursor-pointer'
                                    : 'text-neutral-40 cursor-not-allowed'"
                        >
                            <span v-html="link.label"></span>
                        </component>
                    </template>
                </div>
            </div>
        </div>

        <Modal
            :show="showModal"
            :title="editTarget ? 'Edit Produk' : 'Tambah Produk Baru'"
            size="lg"
            @close="closeModal"
        >
            <form @submit.prevent="submit" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div class="space-y-4">

                        <div>
                            <label class="block text-body2 font-medium text-neutral-70 mb-1.5">
                                Nama Produk <span class="text-danger">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="cth: Aqua 600ml"
                                class="w-full px-3.5 py-2.5 border border-neutral-40 rounded-xl text-body1 text-neutral-90 placeholder-neutral-50 shadow-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                :class="{ 'border-danger': form.errors.name }"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div>
                            <label class="block text-body2 font-medium text-neutral-70 mb-1.5">
                                Kode Produk <span class="text-danger">*</span>
                            </label>
                            <input
                                v-model="form.code"
                                type="text"
                                placeholder="cth: MNM-001"
                                class="w-full px-3.5 py-2.5 border border-neutral-40 rounded-xl text-body1 font-mono text-neutral-90 placeholder-neutral-50 shadow-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                :class="{ 'border-danger': form.errors.code }"
                            />
                            <InputError :message="form.errors.code" />
                        </div>

                        <div>
                            <label class="block text-body2 font-medium text-neutral-70 mb-1.5">
                                Kategori <span class="text-danger">*</span>
                            </label>
                            <select
                                v-model="form.category_id"
                                class="w-full px-3.5 py-2.5 border border-neutral-40 rounded-xl text-body1 text-neutral-90 shadow-xs focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all bg-white cursor-pointer"
                                :class="{ 'border-danger': form.errors.category_id }"
                            >
                                <option value="" disabled>Pilih kategori...</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.category_id" />
                        </div>

                        <div>
                            <label class="block text-body2 font-medium text-neutral-70 mb-1.5">
                                Satuan (Unit) <span class="text-danger">*</span>
                            </label>
                            <div class="flex gap-2">
                                <select
                                    v-model="form.unit"
                                    class="flex-1 px-3.5 py-2.5 border border-neutral-40 rounded-xl text-body1 text-neutral-90 shadow-xs focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all bg-white cursor-pointer"
                                >
                                    <option v-for="u in unitOptions" :key="u" :value="u">{{ u }}</option>
                                </select>
                                <input
                                    v-model="form.unit"
                                    type="text"
                                    placeholder="unit lain..."
                                    class="w-32 px-3.5 py-2.5 border border-neutral-40 rounded-xl text-body1 text-neutral-90 placeholder-neutral-50 shadow-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                />
                            </div>
                            <InputError :message="form.errors.unit" />
                        </div>
                    </div>

                    <div class="space-y-4">

                        <div>
                            <label class="block text-body2 font-medium text-neutral-70 mb-1.5">
                                Harga Modal <span class="text-danger">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-3.5 flex items-center text-body1 text-neutral-50 pointer-events-none">Rp</span>
                                <input
                                    v-model.number="form.purchase_price"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                    class="w-full pl-10 pr-4 py-2.5 border border-neutral-40 rounded-xl text-body1 text-neutral-90 placeholder-neutral-50 shadow-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                    :class="{ 'border-danger': form.errors.purchase_price }"
                                />
                            </div>
                            <InputError :message="form.errors.purchase_price" />
                        </div>

                        <div>
                            <label class="block text-body2 font-medium text-neutral-70 mb-1.5">
                                Harga Jual <span class="text-danger">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-3.5 flex items-center text-body1 text-neutral-50 pointer-events-none">Rp</span>
                                <input
                                    v-model.number="form.selling_price"
                                    type="number"
                                    min="1"
                                    placeholder="0"
                                    class="w-full pl-10 pr-4 py-2.5 border border-neutral-40 rounded-xl text-body1 text-neutral-90 placeholder-neutral-50 shadow-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                    :class="{ 'border-danger': form.errors.selling_price }"
                                />
                            </div>
                            <InputError :message="form.errors.selling_price" />

                            <div v-if="profitMargin" class="mt-2 flex items-center gap-2 px-3 py-2 bg-success-light rounded-lg">
                                <TrendingUp class="w-4 h-4 text-success-dark flex-shrink-0" />
                                <span class="text-body2 text-success-dark font-medium">
                                    Laba: {{ formatRupiah(profitMargin.amount) }} ({{ profitMargin.percent }}% margin)
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-body2 font-medium text-neutral-70 mb-1.5">
                                Stok Awal <span class="text-danger">*</span>
                            </label>
                            <input
                                v-model.number="form.stock"
                                type="number"
                                min="0"
                                class="w-full px-3.5 py-2.5 border border-neutral-40 rounded-xl text-body1 text-neutral-90 shadow-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                                :class="{ 'border-danger': form.errors.stock }"
                            />
                            <InputError :message="form.errors.stock" />
                        </div>

                        <div>
                            <label class="block text-body2 font-medium text-neutral-70 mb-1.5">Foto Produk</label>

                            <div
                                v-if="photoPreview"
                                class="relative w-full h-36 rounded-xl overflow-hidden mb-2 border border-neutral-40 group"
                            >
                                <img :src="photoPreview" class="w-full h-full object-cover" />
                                <button
                                    type="button"
                                    @click="removePhoto"
                                    class="absolute top-2 right-2 p-1.5 bg-danger text-white rounded-lg shadow-sm opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer"
                                >
                                    <X class="w-3.5 h-3.5" />
                                </button>
                            </div>

                            <label
                                class="flex flex-col items-center gap-2 w-full py-5 border-2 border-dashed border-neutral-40 rounded-xl cursor-pointer hover:border-primary/50 hover:bg-primary-surface/20 transition-all"
                            >
                                <ImagePlus class="w-8 h-8 text-neutral-50" stroke-width="1.5" />
                                <span class="text-body2 text-neutral-60 text-center px-4">
                                    <span class="text-primary font-medium">Klik untuk upload</span> atau drag & drop
                                </span>
                                <span class="text-body2 text-neutral-50">JPG, PNG, WEBP maks. 2MB</span>
                                <input type="file" class="hidden" accept="image/*" @change="handlePhotoChange" />
                            </label>
                            <InputError :message="form.errors.photo" />
                        </div>
                    </div>
                </div>
            </form>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-5 py-2.5 border border-neutral-40 rounded-xl text-body1 font-medium text-neutral-70 hover:bg-neutral-20 transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        @click="submit"
                        :disabled="form.processing"
                        class="px-5 py-2.5 bg-primary hover:bg-primary-hover active:bg-primary-pressed text-white rounded-xl text-body1 font-medium shadow-sm transition-all disabled:opacity-60 flex items-center gap-2 cursor-pointer"
                    >
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                        {{ form.processing ? 'Menyimpan...' : (editTarget ? 'Simpan Perubahan' : 'Tambah Produk') }}
                    </button>
                </div>
            </template>
        </Modal>

        <ConfirmDialog
            :show="showConfirm"
            :title="`Hapus '${deleteTarget?.name}'?`"
            message="Produk yang sudah pernah masuk transaksi tidak dapat dihapus."
            :loading="deleting"
            @close="showConfirm = false"
            @confirm="doDelete"
        />
    </AppLayout>
</template>