<script setup lang="ts">

import { useForm } from '@inertiajs/vue3'
import {
    ArrowLeftRight,
    CalendarDays,
    Eye,
    EyeOff,
    ImagePlus,
    KeyRound,
    Pencil,
    Plus,
    Search,
    ShieldCheck,
    ShoppingCart,
    Trash2,
    UserCircle2,
    UserCog,
    Users,
    
} from 'lucide-vue-next'
import { computed, ref } from 'vue'

import ConfirmDialog from '@/components/ConfirmDialog.vue'
import InputError from '@/components/InputError.vue'
import Modal from '@/components/Modal.vue'
import PageHeader from '@/components/PageHeader.vue'
import AppLayout from '@/layouts/AppLayout.vue'

// ── Types ──────────────────────────────────────────────────────────────────
interface UserRow {
    id: number
    name: string
    username: string
    email: string
    role: 'admin' | 'user'
    photo: string | null
    orders_count: number
    stock_histories_count: number
    created_at: string
    is_current_user: boolean
}

const props = defineProps<{
    users: UserRow[]
    currentUser: { id: number; name: string; role: string }
}>()

// ── Search / Filter ────────────────────────────────────────────────────────
const search     = ref('')
const roleFilter = ref<'' | 'admin' | 'user'>('')

const filtered = computed(() => {
    let list = props.users

    if (search.value.trim()) {
        const q = search.value.toLowerCase()

        list = list.filter(u =>
            u.name.toLowerCase().includes(q)
            || u.username.toLowerCase().includes(q)
            || u.email.toLowerCase().includes(q),
        )
    }

    if (roleFilter.value) {
        list = list.filter(u => u.role === roleFilter.value)
    }

    return list
})

const adminCount = computed(() => props.users.filter(u => u.role === 'admin').length)
const kasirCount = computed(() => props.users.filter(u => u.role === 'user').length)

// ── Create / Edit Modal ────────────────────────────────────────────────────
const showModal    = ref(false)
const editTarget   = ref<UserRow | null>(null)
const photoPreview = ref<string | null>(null)

const form = useForm({
    name:                  '',
    username:              '',
    email:                 '',
    role:                  'user' as 'admin' | 'user',
    password:              '',
    password_confirmation: '',
    photo:                 null as File | null,
    _method:               '',
})

function openCreate() {
    editTarget.value   = null
    photoPreview.value = null
    form.reset()
    form.role    = 'user'
    form._method = ''
    form.clearErrors()
    showModal.value = true
}

function openEdit(user: UserRow) {
    editTarget.value   = user
    photoPreview.value = user.photo
    form.name                  = user.name
    form.username              = user.username
    form.email                 = user.email
    form.role                  = user.role
    form.password              = ''
    form.password_confirmation = ''
    form.photo                 = null
    form._method               = 'PUT'
    form.clearErrors()
    showModal.value = true
}

function closeModal() {
    showModal.value    = false
    photoPreview.value = null
    form.reset()
}

function handlePhotoChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0]

    if (!file) {
        return
    }

    form.photo         = file
    photoPreview.value = URL.createObjectURL(file)
}

function removePhoto() {
    form.photo         = null
    photoPreview.value = editTarget.value?.photo ?? null
}

function submit() {
    const url = editTarget.value
        ? `/users/${editTarget.value.id}`
        : '/users'

    form.post(url, { forceFormData: true, onSuccess: closeModal })
}

// ── Password Modal ─────────────────────────────────────────────────────────
const showPasswordModal = ref(false)
const passwordTarget    = ref<UserRow | null>(null)
const showNewPw         = ref(false)
const showConfirmPw     = ref(false)

const pwForm = useForm({
    password:              '',
    password_confirmation: '',
})

function openPasswordModal(user: UserRow) {
    passwordTarget.value    = user
    showNewPw.value         = false
    showConfirmPw.value     = false
    showPasswordModal.value = true
    pwForm.reset()
    pwForm.clearErrors()
}

function submitPassword() {
    pwForm.patch(`/users/${passwordTarget.value!.id}/password`, {
        onSuccess: () => {
            showPasswordModal.value = false
            pwForm.reset()
        },
    })
}

// ── Delete ─────────────────────────────────────────────────────────────────
const showConfirm  = ref(false)
const deleteTarget = ref<UserRow | null>(null)
const deleting     = ref(false)

function confirmDelete(user: UserRow) {
    deleteTarget.value = user
    showConfirm.value  = true
}

function doDelete() {
    if (!deleteTarget.value) {
        return
    }

    deleting.value = true

    useForm({}).delete(`/users/${deleteTarget.value.id}`, {
        onFinish: () => {
            deleting.value    = false
            showConfirm.value = false
        },
    })
}

// ── Helpers ────────────────────────────────────────────────────────────────
function initials(name: string) {
    return name
        .split(' ')
        .map((n: string) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2)
}
</script>

<template>
    <AppLayout>
        <PageHeader
            title="Kelola Akun"
            description="Manajemen akun pengguna sistem SmartPOS Connect."
        />

        <!-- ── Stats Strip ──────────────────────────────────────────────── -->
        <div class="grid grid-cols-3 gap-4 mb-5">
            <div class="bg-white rounded-2xl shadow-sm border border-neutral-30 px-5 py-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                    <Users class="w-5 h-5 text-primary" />
                </div>
                <div>
                    <p class="text-body2 text-neutral-50">Total Pengguna</p>
                    <p class="text-h4 font-bold text-neutral-100">{{ users.length }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-neutral-30 px-5 py-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                    <ShieldCheck class="w-5 h-5 text-primary" />
                </div>
                <div>
                    <p class="text-body2 text-neutral-50">Admin</p>
                    <p class="text-h4 font-bold text-neutral-100">{{ adminCount }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-neutral-30 px-5 py-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-success-light flex items-center justify-center">
                    <UserCog class="w-5 h-5 text-success-dark" />
                </div>
                <div>
                    <p class="text-body2 text-neutral-50">Kasir</p>
                    <p class="text-h4 font-bold text-neutral-100">{{ kasirCount }}</p>
                </div>
            </div>
        </div>

        <!-- ── Toolbar ───────────────────────────────────────────────────── -->
        <div class="flex flex-col gap-3 mb-5 sm:flex-row">
            <div class="relative flex-1 max-w-sm">
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-neutral-50">
                    <Search class="w-4 h-4" />
                </div>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama, username, email..."
                    class="w-full pl-9 pr-4 py-2.5 bg-white border border-neutral-40 rounded-xl text-body1 text-neutral-90 placeholder-neutral-50 shadow-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                />
            </div>

            <select
                v-model="roleFilter"
                class="px-3.5 py-2.5 bg-white border border-neutral-40 rounded-xl text-body1 text-neutral-90 shadow-xs focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
            >
                <option value="">Semua Role</option>
                <option value="admin">Admin</option>
                <option value="user">Kasir</option>
            </select>

            <button
                class="flex items-center gap-2 px-4 py-2.5 bg-primary hover:bg-primary-hover active:bg-primary-pressed text-white rounded-xl text-body1 font-medium shadow-sm transition-all whitespace-nowrap"
                @click="openCreate"
            >
                <Plus class="w-4 h-4" />
                Tambah Akun
            </button>
        </div>

        <!-- ── User Cards Grid ───────────────────────────────────────────── -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div
                v-for="user in filtered"
                :key="user.id"
                class="bg-white rounded-2xl shadow-sm border border-neutral-30 overflow-hidden transition-all duration-200 hover:shadow-md hover:-translate-y-0.5"
                :class="{ 'ring-2 ring-primary/30': user.is_current_user }"
            >
                <!-- Card Header -->
                <div class="px-5 pt-5 pb-4 flex items-start gap-4">
                    <!-- Avatar -->
                    <div class="relative flex-shrink-0">
                        <div class="w-14 h-14 rounded-2xl overflow-hidden bg-primary/10 border-2 border-neutral-20">
                            <img
                                v-if="user.photo"
                                :src="user.photo"
                                :alt="user.name"
                                class="w-full h-full object-cover"
                            />
                            <div
                                v-else
                                class="w-full h-full flex items-center justify-center"
                            >
                                <span class="text-h4 font-bold text-primary">{{ initials(user.name) }}</span>
                            </div>
                        </div>

                        <!-- Online dot for current user -->
                        <span
                            v-if="user.is_current_user"
                            class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-success border-2 border-white rounded-full"
                        />
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-body1 font-bold text-neutral-90 truncate">{{ user.name }}</p>
                                <p class="text-body2 text-neutral-50 font-mono truncate">@{{ user.username }}</p>
                            </div>

                            <span
                                class="flex-shrink-0 px-2.5 py-0.5 rounded-full text-body2 font-semibold"
                                :class="user.role === 'admin'
                                    ? 'bg-primary/10 text-primary'
                                    : 'bg-success-light text-success-dark'"
                            >
                                {{ user.role === 'admin' ? 'Admin' : 'Kasir' }}
                            </span>
                        </div>

                        <p class="text-body2 text-neutral-50 mt-1 truncate">{{ user.email }}</p>
                    </div>
                </div>

                <!-- Stats row -->
                <div class="px-5 py-3 bg-neutral-20 border-t border-b border-neutral-30 flex items-center gap-5">
                    <div class="flex items-center gap-1.5 text-body2 text-neutral-60">
                        <ShoppingCart class="w-3.5 h-3.5" />
                        <span><strong class="text-neutral-90">{{ user.orders_count }}</strong> transaksi</span>
                    </div>

                    <div class="flex items-center gap-1.5 text-body2 text-neutral-60">
                        <ArrowLeftRight class="w-3.5 h-3.5" />
                        <span><strong class="text-neutral-90">{{ user.stock_histories_count }}</strong> stok</span>
                    </div>

                    <div class="ml-auto flex items-center gap-1.5 text-body2 text-neutral-50">
                        <CalendarDays class="w-3 h-3" />
                        {{ user.created_at }}
                    </div>
                </div>

                <!-- "Akun Anda" label -->
                <div
                    v-if="user.is_current_user"
                    class="px-5 py-2 bg-primary/5 border-b border-primary/10"
                >
                    <p class="text-body2 text-primary font-semibold flex items-center gap-1.5">
                        <ShieldCheck class="w-3.5 h-3.5" />
                        Ini adalah akun Anda saat ini
                    </p>
                </div>

                <!-- Actions -->
                <div class="px-5 py-3.5 flex items-center gap-2">
                    <button
                        class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl border border-neutral-30 text-body2 font-semibold text-neutral-70 hover:bg-neutral-20 hover:border-neutral-50 transition-colors"
                        @click="openEdit(user)"
                    >
                        <Pencil class="w-3.5 h-3.5" />
                        Edit
                    </button>

                    <button
                        class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl border border-neutral-30 text-body2 font-semibold text-neutral-70 hover:bg-primary/5 hover:border-primary/30 hover:text-primary transition-colors"
                        @click="openPasswordModal(user)"
                    >
                        <KeyRound class="w-3.5 h-3.5" />
                        Password
                    </button>

                    <button
                        v-if="!user.is_current_user"
                        class="p-2 rounded-xl border border-neutral-30 text-neutral-50 hover:bg-danger-light hover:border-danger/30 hover:text-danger transition-colors"
                        title="Hapus akun"
                        @click="confirmDelete(user)"
                    >
                        <Trash2 class="w-4 h-4" />
                    </button>

                    <div v-else class="w-[38px]" />
                </div>
            </div>

            <!-- Empty state -->
            <div
                v-if="!filtered.length"
                class="md:col-span-2 xl:col-span-3 flex flex-col items-center justify-center py-20 gap-4 text-neutral-50"
            >
                <UserCircle2 class="w-14 h-14" />
                <p class="text-body1 font-medium">Tidak ada pengguna ditemukan</p>
                <button
                    class="text-body2 text-primary hover:underline"
                    @click="search = ''; roleFilter = ''"
                >
                    Reset filter
                </button>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════
            MODAL: Tambah / Edit Akun
        ═══════════════════════════════════════════════════════════════ -->
        <Modal
            :show="showModal"
            :title="editTarget ? `Edit Akun — ${editTarget.name}` : 'Tambah Akun Baru'"
            size="md"
            @close="closeModal"
        >
            <form
                class="space-y-4"
                @submit.prevent="submit"
            >
                <!-- Foto -->
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl overflow-hidden bg-neutral-20 border-2 border-neutral-30 flex-shrink-0">
                        <img
                            v-if="photoPreview"
                            :src="photoPreview"
                            class="w-full h-full object-cover"
                        />
                        <div
                            v-else
                            class="w-full h-full flex items-center justify-center text-neutral-40"
                        >
                            <UserCircle2 class="w-7 h-7" />
                        </div>
                    </div>

                    <div class="flex-1">
                        <label class="block text-body2 font-medium text-neutral-70 mb-1.5">Foto Profil</label>
                        <div class="flex gap-2">
                            <label class="flex-1 flex items-center justify-center gap-2 px-3 py-2 border border-neutral-40 rounded-xl text-body2 text-neutral-70 hover:bg-neutral-20 cursor-pointer transition-colors">
                                <ImagePlus class="w-4 h-4" />
                                Pilih Foto
                                <input
                                    type="file"
                                    class="hidden"
                                    accept="image/*"
                                    @change="handlePhotoChange"
                                />
                            </label>

                            <button
                                v-if="photoPreview"
                                type="button"
                                class="px-3 py-2 border border-danger/30 rounded-xl text-body2 text-danger hover:bg-danger-light transition-colors"
                                @click="removePhoto"
                            >
                                Hapus
                            </button>
                        </div>
                        <InputError :message="form.errors.photo" />
                    </div>
                </div>

                <div class="border-t border-neutral-30" />

                <!-- Nama -->
                <div>
                    <label class="block text-body2 font-medium text-neutral-70 mb-1.5">
                        Nama Lengkap <span class="text-danger">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="cth: Siti Rahayu"
                        class="w-full px-3.5 py-2.5 border border-neutral-40 rounded-xl text-body1 text-neutral-90 placeholder-neutral-50 shadow-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                        :class="{ 'border-danger': form.errors.name }"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <!-- Username + Role -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-body2 font-medium text-neutral-70 mb-1.5">
                            Username <span class="text-danger">*</span>
                        </label>
                        <input
                            v-model="form.username"
                            type="text"
                            placeholder="cth: kasir1"
                            autocomplete="off"
                            class="w-full px-3.5 py-2.5 border border-neutral-40 rounded-xl text-body1 font-mono text-neutral-90 placeholder-neutral-50 shadow-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                            :class="{ 'border-danger': form.errors.username }"
                        />
                        <InputError :message="form.errors.username" />
                    </div>

                    <div>
                        <label class="block text-body2 font-medium text-neutral-70 mb-1.5">
                            Role <span class="text-danger">*</span>
                        </label>
                        <select
                            v-model="form.role"
                            :disabled="editTarget?.is_current_user"
                            class="w-full px-3.5 py-2.5 border border-neutral-40 rounded-xl text-body1 text-neutral-90 shadow-xs focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all bg-white disabled:bg-neutral-20 disabled:text-neutral-50"
                        >
                            <option value="user">Kasir</option>
                            <option value="admin">Admin</option>
                        </select>
                        <p
                            v-if="editTarget?.is_current_user"
                            class="text-body2 text-neutral-50 mt-1"
                        >
                            Tidak dapat mengubah role akun sendiri.
                        </p>
                        <InputError :message="form.errors.role" />
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-body2 font-medium text-neutral-70 mb-1.5">
                        Email <span class="text-danger">*</span>
                    </label>
                    <input
                        v-model="form.email"
                        type="email"
                        placeholder="cth: kasir1@danymart.com"
                        class="w-full px-3.5 py-2.5 border border-neutral-40 rounded-xl text-body1 text-neutral-90 placeholder-neutral-50 shadow-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                        :class="{ 'border-danger': form.errors.email }"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <!-- Password — hanya saat CREATE -->
                <template v-if="!editTarget">
                    <div class="border-t border-neutral-30" />

                    <p class="text-body2 text-neutral-50">
                        Password harus min. 8 karakter, mengandung huruf besar, kecil, dan angka.
                    </p>

                    <div>
                        <label class="block text-body2 font-medium text-neutral-70 mb-1.5">
                            Password <span class="text-danger">*</span>
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            placeholder="••••••••"
                            autocomplete="new-password"
                            class="w-full px-3.5 py-2.5 border border-neutral-40 rounded-xl text-body1 text-neutral-90 placeholder-neutral-50 shadow-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                            :class="{ 'border-danger': form.errors.password }"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div>
                        <label class="block text-body2 font-medium text-neutral-70 mb-1.5">
                            Konfirmasi Password <span class="text-danger">*</span>
                        </label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="••••••••"
                            autocomplete="new-password"
                            class="w-full px-3.5 py-2.5 border border-neutral-40 rounded-xl text-body1 text-neutral-90 placeholder-neutral-50 shadow-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                        />
                    </div>
                </template>
            </form>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        class="px-5 py-2.5 border border-neutral-40 rounded-xl text-body1 font-medium text-neutral-70 hover:bg-neutral-20 transition-colors"
                        @click="closeModal"
                    >
                        Batal
                    </button>

                    <button
                        :disabled="form.processing"
                        class="px-5 py-2.5 bg-primary hover:bg-primary-hover active:bg-primary-pressed text-white rounded-xl text-body1 font-medium shadow-sm transition-all disabled:opacity-60 flex items-center gap-2"
                        @click="submit"
                    >
                        <svg
                            v-if="form.processing"
                            class="w-4 h-4 animate-spin"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            />
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8v8H4z"
                            />
                        </svg>
                        {{ form.processing ? 'Menyimpan...' : (editTarget ? 'Simpan Perubahan' : 'Buat Akun') }}
                    </button>
                </div>
            </template>
        </Modal>

        <!-- ══════════════════════════════════════════════════════════════
            MODAL: Ganti Password
        ═══════════════════════════════════════════════════════════════ -->
        <Modal
            :show="showPasswordModal"
            :title="`Ganti Password — ${passwordTarget?.name}`"
            size="sm"
            @close="showPasswordModal = false"
        >
            <form
                class="space-y-4"
                @submit.prevent="submitPassword"
            >
                <div class="p-3 bg-warning-light border border-warning/20 rounded-xl">
                    <p class="text-body2 text-warning-dark">
                        Password harus min. 8 karakter, mengandung huruf besar, huruf kecil, dan angka.
                    </p>
                </div>

                <!-- New password -->
                <div>
                    <label class="block text-body2 font-medium text-neutral-70 mb-1.5">
                        Password Baru <span class="text-danger">*</span>
                    </label>
                    <div class="relative">
                        <input
                            v-model="pwForm.password"
                            :type="showNewPw ? 'text' : 'password'"
                            placeholder="••••••••"
                            autocomplete="new-password"
                            class="w-full px-3.5 py-2.5 pr-10 border border-neutral-40 rounded-xl text-body1 text-neutral-90 placeholder-neutral-50 shadow-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                            :class="{ 'border-danger': pwForm.errors.password }"
                        />
                        <button
                            type="button"
                            class="absolute inset-y-0 right-3 flex items-center text-neutral-50 hover:text-neutral-70"
                            @click="showNewPw = !showNewPw"
                        >
                            <Eye v-if="!showNewPw" class="w-4 h-4" />
                            <EyeOff v-else class="w-4 h-4" />
                        </button>
                    </div>
                    <InputError :message="pwForm.errors.password" />
                </div>

                <!-- Confirm password -->
                <div>
                    <label class="block text-body2 font-medium text-neutral-70 mb-1.5">
                        Konfirmasi Password <span class="text-danger">*</span>
                    </label>
                    <div class="relative">
                        <input
                            v-model="pwForm.password_confirmation"
                            :type="showConfirmPw ? 'text' : 'password'"
                            placeholder="••••••••"
                            autocomplete="new-password"
                            class="w-full px-3.5 py-2.5 pr-10 border border-neutral-40 rounded-xl text-body1 text-neutral-90 placeholder-neutral-50 shadow-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                        />
                        <button
                            type="button"
                            class="absolute inset-y-0 right-3 flex items-center text-neutral-50 hover:text-neutral-70"
                            @click="showConfirmPw = !showConfirmPw"
                        >
                            <Eye v-if="!showConfirmPw" class="w-4 h-4" />
                            <EyeOff v-else class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </form>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        class="px-5 py-2.5 border border-neutral-40 rounded-xl text-body1 font-medium text-neutral-70 hover:bg-neutral-20 transition-colors"
                        @click="showPasswordModal = false"
                    >
                        Batal
                    </button>

                    <button
                        :disabled="pwForm.processing"
                        class="px-5 py-2.5 bg-primary hover:bg-primary-hover text-white rounded-xl text-body1 font-medium shadow-sm transition-all disabled:opacity-60 flex items-center gap-2"
                        @click="submitPassword"
                    >
                        <svg
                            v-if="pwForm.processing"
                            class="w-4 h-4 animate-spin"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            />
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8v8H4z"
                            />
                        </svg>
                        {{ pwForm.processing ? 'Menyimpan...' : 'Ganti Password' }}
                    </button>
                </div>
            </template>
        </Modal>

        <!-- Confirm Delete -->
        <ConfirmDialog
            :show="showConfirm"
            :title="`Hapus akun '${deleteTarget?.name}'?`"
            message="Akun yang memiliki riwayat transaksi tidak dapat dihapus."
            :loading="deleting"
            @close="showConfirm = false"
            @confirm="doDelete"
        />
    </AppLayout>
</template>