<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { AlertCircle, ShieldCheck } from 'lucide-vue-next'

import PageHeader from '@/components/PageHeader.vue'
import AppLayout from '@/layouts/AppLayout.vue'

// ── Types ──────────────────────────────────────────────────────────────────
interface RoleData {
    id: number
    name: string
    permissions: string[]
}

defineProps<{
    roles: RoleData[]
    permissions: string[]
}>()

// ── Kamus Deskripsi Fitur ──────────────────────────────────────────────────
const permissionDetails: Record<string, { title: string; desc: string }> = {
    'manage-products':   { title: 'Manajemen Produk', desc: 'Boleh menambah, mengedit, dan menghapus produk.' },
    'manage-categories': { title: 'Manajemen Kategori', desc: 'Boleh mengatur kategori produk.' },
    'manage-users':      { title: 'Kelola Akun', desc: 'Boleh menambah dan mengedit akun pengguna.' },
    'manage-roles':      { title: 'Akses Role', desc: 'Boleh mengubah hak akses setiap jabatan.' },
    'view-reports':      { title: 'Laporan Penjualan', desc: 'Boleh melihat riwayat dan mengunduh laporan.' },
    'access-pos':        { title: 'Kasir (POS)', desc: 'Boleh membuka halaman kasir dan memproses transaksi.' },
}

// ── Fungsi Toggle ──────────────────────────────────────────────────────────
function togglePermission(role: RoleData, permissionName: string, currentStatus: boolean) {
    if (role.name === 'admin') {
        return 
    }

    // Menggunakan router.post lebih aman & ringan untuk aksi toggle
    router.post(`/roles/${role.id}/toggle`, {
        permission: permissionName,
        is_enabled: !currentStatus,
    }, {
        preserveScroll: true,
    })
}
</script>

<template>
    <AppLayout>
        <PageHeader 
            title="Konfigurasi Akses Role" 
            description="Atur fitur apa saja yang boleh diakses oleh masing-masing jabatan." 
        />

        <div class="space-y-6 mt-2">
            <!-- Jika array roles kosong, tampilkan pesan -->
            <div v-if="!roles || roles.length === 0" class="p-6 bg-white rounded-2xl shadow-sm text-center">
                <p class="text-neutral-50">Data Role belum tersedia.</p>
            </div>

            <!-- Looping Roles -->
            <div 
                v-for="role in roles" 
                :key="role.id"
                class="bg-white rounded-2xl shadow-sm border border-neutral-30 overflow-hidden"
            >
                <div class="px-6 py-4 border-b border-neutral-30 bg-neutral-20/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                            <ShieldCheck class="w-5 h-5 text-primary" />
                        </div>
                        <div>
                            <h3 class="text-h5 font-bold text-neutral-90 uppercase tracking-wide">
                                Role: {{ role.name }}
                            </h3>
                            <p class="text-body2 text-neutral-50">
                                {{ role.name === 'admin' ? 'Memiliki akses penuh ke seluruh sistem.' : 'Akses terbatas sesuai pengaturan di bawah.' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="role.name === 'admin'" class="px-6 py-4 bg-warning-light border-b border-warning/20 flex items-start gap-3">
                    <AlertCircle class="w-5 h-5 text-warning-dark shrink-0 mt-0.5" />
                    <p class="text-body2 text-warning-dark leading-relaxed">
                        Role <strong>Admin</strong> memiliki izin absolut. Anda tidak dapat mematikan fitur untuk role ini agar terhindar dari risiko kehilangan akses ke sistem.
                    </p>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        <div 
                            v-for="perm in permissions" 
                            :key="perm"
                            class="flex items-center justify-between p-4 border border-neutral-30 rounded-xl transition-colors"
                            :class="role.name === 'admin' ? 'bg-neutral-20/50 opacity-70' : 'hover:border-primary/30'"
                        >
                            <div class="pr-4">
                                <!-- Safe navigation dengan optional chaining -->
                                <p class="text-body1 font-semibold text-neutral-90">
                                    {{ permissionDetails[perm]?.title || perm }}
                                </p>
                                <p class="text-body2 text-neutral-50 mt-0.5 leading-snug">
                                    {{ permissionDetails[perm]?.desc || 'Akses fitur sistem' }}
                                </p>
                            </div>
                            
                            <label 
                                class="relative inline-flex items-center"
                                :class="role.name === 'admin' ? 'cursor-not-allowed' : 'cursor-pointer'"
                            >
                                <input 
                                    type="checkbox" 
                                    class="sr-only peer"
                                    :checked="(role.permissions || []).includes(perm)"
                                    :disabled="role.name === 'admin'"
                                    @change="togglePermission(role, perm, (role.permissions || []).includes(perm))"
                                >
                                <div class="w-11 h-6 bg-neutral-40 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-30 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary disabled:opacity-50"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>