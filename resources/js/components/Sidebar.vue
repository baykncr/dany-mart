<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3'
import { FileText, LayoutDashboard, LogOut, Package, ShoppingCart, Tags, Users, ShieldCheck } from 'lucide-vue-next'
import { onMounted, onUnmounted, ref, computed } from 'vue'

const page = usePage()

const currentTime = ref('')
const currentDate = ref('')
let timer: number | null = null

function updateTime() {
    const now = new Date()
    currentTime.value = now.toLocaleTimeString('id-ID', {
        hour: '2-digit', minute: '2-digit', second: '2-digit'
    })
    currentDate.value = now.toLocaleDateString('id-ID', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
    })
}

onMounted(() => {
    updateTime()
    timer = window.setInterval(updateTime, 1000)
})

onUnmounted(() => {
    if (timer) {
        clearInterval(timer)
    }
})

interface AuthUser {
    name: string
    role: string
}

const user = page.props.auth.user as unknown as AuthUser

// ── 1. DEFINISI MENU ───────────────────────────────────────────────────────
const navItems = [
    { label: 'Dashboard', href: '/dashboard', routeName: 'dashboard', icon: LayoutDashboard, adminOnly: true },
    
    // Menu dengan Permission (Tampil kalau di-toggle ON)
    { label: 'Kasir (POS)', href: '/pos', routeName: 'pos.index', icon: ShoppingCart, permission: 'access-pos' },
    { label: 'Produk', href: '/products', routeName: 'products.index', icon: Package, permission: 'manage-products' },
    { label: 'Kategori', href: '/categories', routeName: 'categories.index', icon: Tags, permission: 'manage-categories' },
    { label: 'Laporan', href: '/transactions', routeName: 'transactions.index', icon: FileText, permission: 'view-reports' },
    
    // Menu mutlak khusus Admin (WAJIB ada adminOnly)
    { label: 'Kelola Akun', href: '/users', routeName: 'users.index', icon: Users, adminOnly: true },
    { label: 'Akses Role', href: '/roles', routeName: 'roles.index', icon: ShieldCheck, adminOnly: true },
]

// ── 2. LOGIKA FILTER MENU YANG BENAR ───────────────────────────────────────
const filteredNavItems = computed(() => {
    const currentUser = page.props.auth.user as unknown as AuthUser
    
    const userPermissions = Array.isArray(page.props.auth.permissions) 
        ? page.props.auth.permissions 
        : []

    return navItems.filter(item => {
        // 1. Jika Admin, tampilkan SEMUA
        if (currentUser.role === 'admin') {
            return true
        }

        // 2. Jika ini menu khusus Admin, sembunyikan dari Kasir
        if (item.adminOnly) {
            return false
        }

        // 3. JIKA MENU ADA GEMBOK-NYA (permission), cek kunci Kasir
        if (item.permission) {
            return userPermissions.includes(item.permission)
        }

        // 4. Jika menu publik, biarkan tampil
        return true
    })
})

function isActive(routeName: string) {
    return page.url.startsWith('/' + routeName.replace('.index', '').replace('.', '/'))
}

function logout() {
    router.post('/logout')
}

const initials = user.name.split(' ').map((n: string) => n[0]).join('').toUpperCase().slice(0, 2)
</script>

<template>
    <aside class="relative z-20 w-64 shrink-0 bg-white border-r border-neutral-40 flex flex-col h-screen overflow-y-auto shadow-sm">

<div class="px-3 py-3 border-b border-neutral-40 flex items-center gap-3">
    <img src="/logo.png" alt="Logo" class="h-9 w-auto object-contain shrink-0" />
    <div class="w-px h-8 bg-neutral-40 shrink-0"></div>
    <div class="min-w-0">
        <p class="text-body1 font-bold text-neutral-100 leading-tight">SmartPOS</p>
        <p class="text-body2 text-neutral-60">Dany Mart</p>
    </div>
</div>

        <div class="px-5 py-4 border-b border-neutral-40 bg-primary-surface/30">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-primary flex items-center justify-center shrink-0">
                    <span class="text-body2 font-bold text-white">{{ initials }}</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-body1 font-semibold text-neutral-90 truncate">{{ user.name }}</p>
                    <span
                        class="inline-block text-body2 px-2 py-0.5 rounded-full font-medium"
                        :class="user.role === 'admin'
                            ? 'bg-primary/10 text-primary'
                            : 'bg-success-light text-success-dark'"
                    >
                        {{ user.role === 'admin' ? 'Admin' : 'Kasir' }}
                    </span>
                </div>
            </div>

            <div class="mt-3 px-3 py-2 bg-white rounded-lg shadow-xs">
                <p class="text-h4 font-bold text-primary text-center tabular-nums">{{ currentTime }}</p>
                <p class="text-body2 text-neutral-60 text-center mt-0.5 leading-tight">{{ currentDate }}</p>
            </div>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1">
            <Link
                v-for="item in filteredNavItems"
                :key="item.routeName"
                :href="item.href"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-150 group cursor-pointer"
                :class="isActive(item.routeName)
                    ? 'bg-primary/10 text-primary'
                    : 'text-neutral-70 hover:bg-neutral-20 hover:text-neutral-90'"
            >
                <component
                    :is="item.icon"
                    class="w-5 h-5 shrink-0 transition-colors duration-150"
                    :class="isActive(item.routeName) ? 'text-primary' : 'text-neutral-50 group-hover:text-neutral-80'"
                />
                
                <span class="text-body1 font-medium">{{ item.label }}</span>

                <span
                    v-if="isActive(item.routeName)"
                    class="ml-auto w-1.5 h-1.5 rounded-full bg-primary"
                />
            </Link>
        </nav>

        <div class="px-3 pb-4 pt-2 border-t border-neutral-40 space-y-1">
            <button
                @click="logout"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-danger hover:bg-danger-light transition-all duration-150 group cursor-pointer"
            >
                <LogOut class="w-5 h-5 shrink-0" />
                <span class="text-body1 font-medium">Keluar</span>
            </button>
        </div>
    </aside>
</template>