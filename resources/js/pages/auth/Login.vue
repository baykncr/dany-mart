<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';

// Import Icon
import { 
    Package2, Zap, Package, Bot, FileSpreadsheet, ShieldCheck, Mail, Lock 
} from 'lucide-vue-next';

defineOptions({
    layout: null,
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Masuk - SmartPOS" />

    <div class="fixed inset-0 z-[999] flex font-sans overflow-hidden bg-white">

        <div class="hidden lg:flex lg:w-[50%] xl:w-[55%] relative flex-col overflow-hidden bg-[#073C64]">
            
            <img
                src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?q=80&w=2000&auto=format&fit=crop"
                class="absolute inset-0 w-full h-full object-cover mix-blend-overlay opacity-30 transition-transform duration-[20s] hover:scale-110"
                alt="Store POS"
            />

            <div
                class="absolute inset-0 opacity-[0.15]"
                style="background-image:radial-gradient(circle,#ffffff 1px,transparent 1px);background-size:32px 32px"
            />

            <div
                class="absolute -top-48 -right-48 w-[600px] h-[600px] rounded-full pointer-events-none"
                style="background:radial-gradient(circle,rgba(255,255,255,0.15) 0%,transparent 60%)"
            />
            <div
                class="absolute -bottom-36 -left-36 w-[500px] h-[500px] rounded-full pointer-events-none"
                style="background:radial-gradient(circle,rgba(205,216,224,0.15) 0%,transparent 60%)"
            />

            <div class="relative z-10 flex flex-col h-full px-12 xl:px-20 py-12 justify-between">
                

                <div class="flex flex-col justify-center">
                    <div class="max-w-xl">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 mb-8 shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse" />
                            <span class="text-white text-[11px] font-bold tracking-[0.15em] uppercase">
                                Sistem Kasir Pintar
                            </span>
                        </div>

                        <h1 class="text-[40px] xl:text-[48px] font-extrabold text-white leading-[1.15] tracking-tight mb-6">
                            Kasir Lebih <span class="text-blue-300">Cerdas,</span><br>
                            Bisnis Lebih Efisien.
                        </h1>

                        <p class="text-white/80 text-[14px] leading-relaxed max-w-sm font-medium">
                            Solusi manajemen ritel modern dengan rekomendasi produk cerdas, pencatatan stok real-time, dan analisis laporan otomatis.
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-white/50 text-[11px] font-bold uppercase tracking-[0.15em] mb-4">Fitur Unggulan Sistem</p>
                    <div class="flex flex-wrap gap-3">
                        <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-md border border-white/15 shadow-sm">
                            <Zap class="w-4 h-4 text-orange-400" />
                            <span class="text-white text-[13px] font-medium">Transaksi Instan</span>
                        </div>
                        <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-md border border-white/15 shadow-sm">
                            <Package class="w-4 h-4 text-amber-500" />
                            <span class="text-white text-[13px] font-medium">Auto-Sync Stok</span>
                        </div>
                        <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-md border border-white/15 shadow-sm">
                            <Bot class="w-4 h-4 text-purple-400" />
                            <span class="text-white text-[13px] font-medium">Smart Cross-Selling</span>
                        </div>
                        <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-md border border-white/15 shadow-sm">
                            <FileSpreadsheet class="w-4 h-4 text-emerald-400" />
                            <span class="text-white text-[13px] font-medium">Export Excel</span>
                        </div>
                        <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-md border border-white/15 shadow-sm">
                            <ShieldCheck class="w-4 h-4 text-blue-400" />
                            <span class="text-white text-[13px] font-medium">RBAC Security</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col items-center justify-center px-6 py-8 lg:px-16 xl:px-24 overflow-y-auto bg-white shadow-[-20px_0_40px_-10px_rgba(0,0,0,0.05)] z-10 relative">

            <div class="flex items-center justify-center mb-14 w-full max-w-[420px]">
                <img src="/logo.png" alt="Logo" class="h-14 w-auto object-contain" />
            </div>

            <div class="w-full max-w-[420px]">

                <div class="mb-8">
                    <h2 class="text-[32px] font-bold text-[#0A192F] leading-tight tracking-tight">Masuk ke Akun</h2>
                    <p class="text-[15px] text-slate-500 font-normal mt-2">Selamat datang kembali! Silakan masukkan kredensial Anda.</p>
                </div>

                <div
                    v-if="status"
                    class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-start gap-3 shadow-sm"
                >
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-[13px] text-green-700 font-bold">{{ status }}</p>
                </div>

                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="flex flex-col gap-5"
                >
                    <div class="flex flex-col gap-2">
                        <Label for="email" class="text-[14px] font-bold text-[#0A192F]">
                            Alamat Email
                        </Label>
                        
                        <div class="relative w-full">
                            <Mail class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 stroke-[1.5] pointer-events-none z-10" />
                            
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="email"
                                placeholder="nama@email.com"
                                style="width: 100%;"
                                class="!block !h-[48px] !w-full !pl-11 !pr-4 !rounded-xl !bg-white !border !border-slate-300 !shadow-none placeholder:!text-slate-400 hover:!border-slate-400 focus:!bg-white focus:!border-[#0F4259] focus:!ring-1 focus:!ring-[#0F4259] transition-all !text-[15px] !text-slate-900 [&:-webkit-autofill]:!bg-white"
                            />
                        </div>
                        <InputError :message="errors.email" class="text-[12px]" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="password" class="text-[14px] font-bold text-[#0A192F]">
                            Password
                        </Label>
                        
                        <div class="relative w-full">
                            <Lock class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 stroke-[1.5] pointer-events-none z-10" />
                            
                            <PasswordInput
                                id="password"
                                name="password"
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                placeholder="••••••••"
                                style="width: 100%;"
                                class="!block !h-[48px] !w-full !pl-11 !pr-10 !rounded-xl !bg-white !border !border-slate-300 !shadow-none placeholder:!text-slate-400 hover:!border-slate-400 focus:!bg-white focus:!border-[#0F4259] focus:!ring-1 focus:!ring-[#0F4259] transition-all !text-[15px] !text-slate-900 [&:-webkit-autofill]:!bg-white"
                            />
                        </div>
                        <InputError :message="errors.password" class="text-[12px]" />
                    </div>

                    <div class="pt-4">
                        <Button
                            type="submit"
                            class="!h-[52px] w-full !text-[15px] !font-bold !rounded-xl !bg-[#123647] hover:!bg-[#0A192F] active:!bg-[#050D18] transition-colors !text-white flex items-center justify-center gap-2 !border-none !shadow-md"
                            :tabindex="4"
                            :disabled="processing"
                            data-test="login-button"
                        >
                            <Spinner v-if="processing" class="w-5 h-5" />
                            Masuk Sekarang
                        </Button>
                    </div>
                </Form>

            </div>
        </div>
    </div>
</template>