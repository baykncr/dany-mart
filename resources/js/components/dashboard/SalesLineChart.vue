<script setup lang="ts">
import { Chart, registerables } from 'chart.js'
import { ref, onMounted, watch, onUnmounted } from 'vue'


Chart.register(...registerables)

// ── Types ──────────────────────────────────────────────────────────────────
interface ChartData {
    month: string
    month_short: string
    revenue: number
    transactions: number
    profit: number
}

const props = defineProps<{
    data: ChartData[]
}>()

// ── State ──────────────────────────────────────────────────────────────────
const canvasRef = ref<HTMLCanvasElement | null>(null)
let chartInstance: Chart | null = null

// ── Helpers ────────────────────────────────────────────────────────────────
function formatRupiah(val: number | string) {
    const num = Number(val)

    if (num >= 1_000_000) {
        return 'Rp ' + (num / 1_000_000).toFixed(1) + 'Jt'
    }

    if (num >= 1_000) {
        return 'Rp ' + (num / 1_000).toFixed(0) + 'Rb'
    }

    return 'Rp ' + num
}

// ── Core Logic ─────────────────────────────────────────────────────────────
function buildChart() {
    if (chartInstance) {
        chartInstance.destroy()
    }

    if (!canvasRef.value) {
        return
    }

    const labels   = props.data.map(d => d.month_short)
    const revenues = props.data.map(d => d.revenue)
    const profits  = props.data.map(d => d.profit)

    const ctx = canvasRef.value.getContext('2d')

    if (!ctx) {
        return
    }

    // Revenue gradient fill
    const revenueGrad = ctx.createLinearGradient(0, 0, 0, 300)
    
    revenueGrad.addColorStop(0, 'rgba(7, 60, 100, 0.15)')
    revenueGrad.addColorStop(1, 'rgba(7, 60, 100, 0)')

    // Profit gradient fill
    const profitGrad = ctx.createLinearGradient(0, 0, 0, 300)
    
    profitGrad.addColorStop(0, 'rgba(22, 163, 74, 0.12)')
    profitGrad.addColorStop(1, 'rgba(22, 163, 74, 0)')

    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label:                'Pendapatan',
                    data:                 revenues,
                    borderColor:          '#073C64',
                    backgroundColor:      revenueGrad,
                    borderWidth:          2.5,
                    pointBackgroundColor: '#073C64',
                    pointRadius:          4,
                    pointHoverRadius:     7,
                    fill:                 true,
                    tension:              0.4,
                },
                {
                    label:                'Laba Kotor',
                    data:                 profits,
                    borderColor:          '#16A34A',
                    backgroundColor:      profitGrad,
                    borderWidth:          2,
                    pointBackgroundColor: '#16A34A',
                    pointRadius:          4,
                    pointHoverRadius:     7,
                    fill:                 true,
                    tension:              0.4,
                    borderDash:           [5, 3],
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    align:    'end',
                    labels: {
                        boxWidth:        10,
                        boxHeight:       10,
                        borderRadius:    5,
                        useBorderRadius: true,
                        color:           '#616161',
                        font:            { size: 12, family: 'Inter' },
                    },
                },
                tooltip: {
                    backgroundColor: '#073C64',
                    titleColor:      '#CDD8E0',
                    bodyColor:       '#FFFFFF',
                    padding:         12,
                    cornerRadius:    12,
                    callbacks: {
                        label: (context) => ` ${context.dataset.label}: ${formatRupiah(context.raw as number)}`,
                    },
                },
            },
            scales: {
                x: {
                    grid:   { display: false },
                    ticks:  { color: '#9E9E9E', font: { size: 11 } },
                    border: { display: false },
                },
                yy: {
                    grid: { color: '#EDEDED' },
                    ticks: {
                        color:    '#9E9E9E',
                        font:     { size: 11 },
                        callback: (val) => formatRupiah(val),
                    },
                    border: { display: false, dash: [4, 4] },
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
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-h5 text-neutral-90">Tren Penjualan</h3>
                <p class="text-body2 text-neutral-50 mt-0.5">Pendapatan & Laba Kotor per bulan</p>
            </div>
        </div>
        <div class="relative h-64">
            <canvas ref="canvasRef" />
        </div>
    </div>
</template>