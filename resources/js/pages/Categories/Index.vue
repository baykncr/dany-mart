<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Search, Plus, Edit2, Trash2, FolderOpen, Loader2 } from 'lucide-vue-next'
import { ref, computed } from 'vue'

import ConfirmDialog from '@/components/ConfirmDialog.vue'
import InputError from '@/components/InputError.vue'
import Modal from '@/components/Modal.vue'
import PageHeader from '@/components/PageHeader.vue'
import AppLayout from '@/layouts/AppLayout.vue'

// ── Types ──────────────────────────────────────────────────────────────────
interface Category {
    id: number
    name: string
    code: string
    priority_order: number
    association_categories: number[]
    products_count?: number
}

const props = defineProps<{
    categories: Category[]
    allCategories: Category[]
}>()

// ── State ──────────────────────────────────────────────────────────────────
const showModal    = ref(false)
const showConfirm  = ref(false)
const editTarget   = ref<Category | null>(null)
const deleteTarget = ref<Category | null>(null)
const search       = ref('')
const deleting     = ref(false)

const filteredCategories = computed(() =>
    props.categories.filter(c =>
        c.name.toLowerCase().includes(search.value.toLowerCase()) ||
        c.code.toLowerCase().includes(search.value.toLowerCase())
    )
)

// ── Form ───────────────────────────────────────────────────────────────────
const form = useForm({
    name: '',
    code: '',
    priority_order: 0,
    association_categories: [] as number[],
})

function openCreate() {
    editTarget.value = null
    form.reset()
    form.clearErrors()
    showModal.value = true
}

function openEdit(category: Category) {
    editTarget.value = category
    form.name                   = category.name
    form.code                   = category.code
    form.priority_order         = category.priority_order
    form.association_categories = [...(category.association_categories ?? [])]
    form.clearErrors()
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    form.reset()
}

function submit() {
    if (editTarget.value) {
        form.put(`/categories/${editTarget.value.id}`, {
            onSuccess: closeModal,
        })
    } else {
        form.post('/categories', {
            onSuccess: closeModal,
        })
    }
}

// ── Delete ─────────────────────────────────────────────────────────────────
function confirmDelete(category: Category) {
    deleteTarget.value = category
    showConfirm.value  = true
}

function doDelete() {
    if (!deleteTarget.value) {
        return
    }
    
    deleting.value = true
    useForm({}).delete(`/categories/${deleteTarget.value.id}`, {
        onFinish: () => {
            deleting.value     = false
            showConfirm.value  = false
            deleteTarget.value = null
        },
    })
}

// ── Helpers ────────────────────────────────────────────────────────────────
function toggleAssociation(id: number) {
    const idx = form.association_categories.indexOf(id)

    if (idx === -1) {
        form.association_categories.push(id)
    } else {
        form.association_categories.splice(idx, 1)
    }
}

function getAssociationNames(ids?: number[]) {
    if (!ids?.length) {
        return '—'
    }

    return props.allCategories
        .filter(c => ids.includes(c.id))
        .map(c => c.name)
        .join(', ')
}
</script>

<template>
    <AppLayout>
        <PageHeader
            title="Kategori Produk"
            description="Kelola kategori untuk mengelompokkan produk Anda."
        />

        <div class="bg-white rounded-2xl shadow-sm border border-neutral-30 p-4 mb-5 flex flex-wrap gap-4 items-center justify-between">
            <div class="relative w-full max-w-md">
                <div class="absolute inset-y-0 left-3 flex items-center text-neutral-50 pointer-events-none">
                    <Search class="w-4 h-4" />
                </div>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama atau kode kategori..."
                    class="w-full pl-9 pr-4 py-2.5 border border-neutral-40 rounded-xl text-body1 text-neutral-90 placeholder-neutral-50 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                />
            </div>
            <button
                @click="openCreate"
                class="flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-primary-hover active:bg-primary-pressed text-white rounded-xl text-body1 font-medium shadow-sm transition-all cursor-pointer"
            >
                <Plus class="w-5 h-5" />
                Tambah Kategori
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-neutral-30 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-neutral-30 bg-neutral-20">
                            <th class="text-left px-5 py-3.5 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Kategori</th>
                            <th class="text-left px-5 py-3.5 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Kode</th>
                            <th class="text-center px-5 py-3.5 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Prioritas</th>
                            <th class="text-left px-5 py-3.5 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Asosiasi</th>
                            <th class="text-center px-5 py-3.5 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Produk</th>
                            <th class="text-right px-5 py-3.5 text-body2 font-semibold text-neutral-60 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-20">
                        <tr v-for="cat in filteredCategories" :key="cat.id" class="hover:bg-neutral-20/50 transition-colors">
                            <td class="px-5 py-4 text-body1 font-semibold text-neutral-90">{{ cat.name }}</td>
                            <td class="px-5 py-4 font-mono text-body2 text-neutral-60">{{ cat.code }}</td>
                            <td class="px-5 py-4 text-center text-body1 tabular-nums">{{ cat.priority_order }}</td>
                            <td class="px-5 py-4 text-body2 text-neutral-60 max-w-xs truncate">{{ getAssociationNames(cat.association_categories) }}</td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full bg-primary/10 text-primary text-body2 font-semibold">
                                    {{ cat.products_count ?? 0 }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEdit(cat)" class="p-2 rounded-lg text-neutral-50 hover:text-primary hover:bg-primary/10 transition-colors cursor-pointer">
                                        <Edit2 class="w-4 h-4" />
                                    </button>
                                    <button @click="confirmDelete(cat)" class="p-2 rounded-lg text-neutral-50 hover:text-danger hover:bg-danger-light transition-colors cursor-pointer">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredCategories.length === 0">
                            <td colspan="6" class="px-5 py-12 text-center text-neutral-50">
                                <FolderOpen class="w-8 h-8 mx-auto mb-3 opacity-50" />
                                <p class="text-body1">Tidak ada kategori ditemukan.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

                    <Modal 
                :show="showModal" 
                :title="editTarget ? 'Edit Kategori' : 'Tambah Kategori'"
                size="md"
                @close="closeModal" 
>
            <div class="px-6 py-4 border-b border-neutral-30 flex items-center justify-between">
                <h3 class="text-h4 font-bold text-neutral-100">{{ editTarget ? 'Edit Kategori' : 'Tambah Kategori' }}</h3>
            </div>
            <form @submit.prevent="submit" class="p-6 space-y-4">
                <div>
                    <label class="block text-body2 font-medium text-neutral-70 mb-1">Nama Kategori <span class="text-danger">*</span></label>
                    <input v-model="form.name" type="text" class="w-full px-3 py-2 border border-neutral-40 rounded-xl text-neutral-80 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all" required />
                    <InputError :message="form.errors.name" class="mt-1" />
                </div>
                <div>
                    <label class="block text-body2 font-medium text-neutral-70 mb-1">Kode Kategori <span class="text-danger">*</span></label>
                    <input v-model="form.code" type="text" class="w-full px-3 py-2 border border-neutral-40 rounded-xl text-neutral-80 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all uppercase" required />
                    <InputError :message="form.errors.code" class="mt-1" />
                </div>
                <div>
                    <label class="block text-body2 font-medium text-neutral-70 mb-1">Prioritas (Makin kecil makin di atas)</label>
                    <input v-model="form.priority_order" type="number" class="w-full px-3 py-2 border border-neutral-40 rounded-xl text-neutral-80 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all" />
                    <InputError :message="form.errors.priority_order" class="mt-1" />
                </div>
                
                <div>
                    <label class="block text-body2 font-medium text-neutral-70 mb-2">Asosiasi Kategori</label>
                    <div class="max-h-40 overflow-y-auto border border-neutral-30 rounded-xl p-3 space-y-2 bg-neutral-20/50">
                        <label v-for="cat in allCategories.filter(c => c.id !== editTarget?.id)" :key="cat.id" class="flex items-center gap-3 cursor-pointer p-1">
                            <input 
                                type="checkbox" 
                                :checked="form.association_categories.includes(cat.id)"
                                @change="toggleAssociation(cat.id)"
                                class="w-4 h-4 rounded border-neutral-40 text-primary focus:ring-primary/30 cursor-pointer"
                            />
                            <span class="text-body2 text-neutral-80">{{ cat.name }}</span>
                        </label>
                        <p v-if="allCategories.length <= (editTarget ? 1 : 0)" class="text-body2 text-neutral-50 text-center py-2">Tidak ada kategori lain.</p>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" @click="closeModal" class="px-4 py-2 rounded-xl text-neutral-70 hover:bg-neutral-20 font-medium transition-colors cursor-pointer">Batal</button>
                    <button type="submit" :disabled="form.processing" class="flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary-hover text-white rounded-xl font-medium transition-colors disabled:opacity-50 cursor-pointer">
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                        {{ editTarget ? 'Simpan' : 'Tambah' }}
                    </button>
                </div>
            </form>
        </Modal>

        <ConfirmDialog
            :show="showConfirm"
            title="Hapus Kategori?"
            :message="`Apakah Anda yakin ingin menghapus kategori '${deleteTarget?.name}'? Semua produk di dalamnya mungkin akan kehilangan kategori ini.`"
            :loading="deleting"
            @close="showConfirm = false"
            @confirm="doDelete"
        />
    </AppLayout>
</template>