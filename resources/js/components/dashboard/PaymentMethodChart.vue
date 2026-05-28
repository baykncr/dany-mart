<script setup lang="ts">
import { Chart, registerables } from 'chart.js'
import { ref, onMounted, watch, onUnmounted, computed } from 'vue'


Chart.register(...registerables)

// ── Types ──────────────────────────────────────────────────────────────────
interface PaymentData {
    method: string
    label: string
    count: number
    total: number
}

const props = defineProps<{
    data: PaymentData[]
}>()

// ── State ──────────────────────────────────────────────────────────────────
const canvasRef = ref<HTMLCanvasElement | null>(null)
let chartInstance: Chart | null = null

const totalCount = computed(() => {
    return props.data.reduce((sum, item) => sum + item.count, 0)
})

// ── Helpers ────────────────────────────────────────────────────────────────
function rupiah(val: number | string) {
    return 'Rp ' + Number(val).toLocaleString('id-ID')
}

// ── Core Logic ─────────────────────────────────────────────────────────────
function buildChart() {
    if (chartInstance) {
        chartInstance.destroy()
    }

    if (!props.data.length || !canvasRef.value) {
        return
    }

    const ctx = canvasRef.value.getContext('2d')

    if (!ctx) {
        return
    }

    chartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: props.data.map(d => d.label),
            datasets: [{
                data:                 props.data.map(d => d.count),
                backgroundColor:      ['#073C64', '#16A34A'],
                hoverBackgroundColor: ['#063253', '#15803D'],
                borderWidth:          0,
                hoverOffset:          6,
            }],
        },
        options: {
            responsive:          true,
            maintainAspectRatio: false,
            cutout:              '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#073C64',
                    bodyColor:       '#FFFFFF',
                    padding:         10,
                    cornerRadius:    10,
                    callbacks: {
                        label: (context) => ` ${context.label}: ${context.raw} transaksi`,
                    },
                },
            },
        },
    })
}

// ── Lifecycle ──────────────────────────────────────────────────────────────
onMounted(buildChart)
watch(() => props.data, buildChart, { deep: true })

onUnmounted(() => {
    if (chartInstance) {
        chartInstance.destroy()
    }
})
</script>

<template>
    <div class="bg-white rounded-2xl shadow-sm border border-neutral-30 p-5">
        <h3 class="text-h5 text-neutral-90 mb-1">Metode Pembayaran</h3>
        <p class="text-body2 text-neutral-50 mb-4">Distribusi bulan ini</p>

        <div class="flex items-center gap-6">
            <div class="relative w-32 h-32 shrink-0">
                <canvas ref="canvasRef" />
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <p class="text-h4 font-bold text-neutral-90 leading-none">{{ totalCount }}</p>
                    <p class="text-body2 text-neutral-50 mt-0.5">transaksi</p>
                </div>
            </div>

            <div class="flex-1 space-y-3">
                <div v-if="!data.length" class="text-body2 text-neutral-50">Belum ada data bulan ini.</div>
                
                <div
                    v-for="(item, i) in data"
                    :key="item.method"
                    class="flex items-center gap-3"
                >
                    <div
                        class="w-3 h-3 rounded-full shrink-0"
                        :style="{ background: i === 0 ? '#073C64' : '#16A34A' }"
                    />
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-body1 font-semibold text-neutral-80">{{ item.label }}</span>
                            <span class="text-body2 text-neutral-60 tabular-nums">{{ item.count }}x</span>
                        </div>
                        <p class="text-body2 text-neutral-50 tabular-nums">{{ rupiah(item.total) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>