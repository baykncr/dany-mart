<script setup lang="ts">
import { Chart, registerables } from 'chart.js'
import type { TooltipItem } from 'chart.js'
import { ref, onMounted, watch, onUnmounted } from 'vue'


Chart.register(...registerables)

// ── Types ──────────────────────────────────────────────────────────────────
interface ChartData {
    month: string
    revenue: number
    expense: number
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

    const labels   = props.data.map(d => d.month)
    const revenues = props.data.map(d => d.revenue)
    const expenses = props.data.map(d => d.expense)

    const ctx = canvasRef.value.getContext('2d')

    if (!ctx) {
        return
    }

    chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label:           'Pendapatan',
                    data:            revenues,
                    backgroundColor: '#073C64',
                    borderRadius:    6,
                    borderSkipped:   false,
                    barPercentage:   0.6,
                },
                {
                    label:           'Pengeluaran',
                    data:            expenses,
                    backgroundColor: '#DC2626',
                    borderRadius:    6,
                    borderSkipped:   false,
                    barPercentage:   0.6,
                },
            ],
        },
        options: {
            responsive:          true,
            maintainAspectRatio: false,
            interaction:         { mode: 'index', intersect: false },
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
                    backgroundColor: '#0A0A0A',
                    titleColor:      '#BDBDBD',
                    bodyColor:       '#FFFFFF',
                    padding:         12,
                    cornerRadius:    12,
                    callbacks: {
                        label: (ctx) => ` ${ctx.dataset.label}: ${formatRupiah(ctx.raw as number)}`,
                        afterBody: (items: TooltipItem<'bar'>[]) => {
                            const rev = items.find(i => i.datasetIndex === 0)?.raw as number ?? 0
                            const exp = items.find(i => i.datasetIndex === 1)?.raw as number ?? 0
                            const profit = rev - exp

                            return [
                                '',
                                ` Laba Bersih: ${formatRupiah(profit)}`,
                            ]
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid:   { display: false },
                    ticks:  { color: '#9E9E9E', font: { size: 11 } },
                    border: { display: false },
                },
                y: {
                    grid:   { color: '#EDEDED' },
                    ticks:  { 
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
                <h3 class="text-h5 text-neutral-90">Pendapatan vs Pengeluaran</h3>
                <p class="text-body2 text-neutral-50 mt-0.5">Perbandingan per bulan tahun ini</p>
            </div>
        </div>
        <div class="relative h-64">
            <canvas ref="canvasRef" />
        </div>
    </div>
</template>