<script setup lang="ts">
import { ArrowDown, ArrowUp, Banknote, DollarSign, Package, TrendingDown, TrendingUp } from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps<{
    title: string
    value: string | number
    subtitle?: string
    icon: 'revenue' | 'transaction' | 'profit' | 'expense' | 'product'
    color?: 'blue' | 'green' | 'red' | 'yellow'
    delta?: number | null
    prefix?: string
    isLoading?: boolean
}>()

const colorMap = {
    blue:   { icon: 'bg-primary/10 text-primary' },
    green:  { icon: 'bg-success-light text-success-dark' },
    red:    { icon: 'bg-danger-light text-danger' },
    yellow: { icon: 'bg-warning-light text-warning-dark' },
}

const colors = computed(() => colorMap[props.color ?? 'blue'])

const iconMap = {
    revenue: DollarSign,
    transaction: Banknote,
    profit: TrendingUp,
    expense: TrendingDown,
    product: Package,
}
</script>

<template>
    <div class="bg-white rounded-2xl shadow-sm border border-neutral-30 p-5 flex flex-col gap-4 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
        
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <p class="text-body2 font-semibold text-neutral-60 uppercase tracking-wide">{{ title }}</p>
                <p v-if="subtitle" class="text-body2 text-neutral-50 mt-0.5">{{ subtitle }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" :class="colors.icon">
                <component :is="iconMap[icon] ?? DollarSign" class="w-5 h-5" />
            </div>
        </div>

        <div>
            <div v-if="isLoading" class="h-8 bg-neutral-20 rounded-lg animate-pulse w-3/4" />
            <p v-else class="text-h2 font-bold text-neutral-100 leading-none tabular-nums">
                <span v-if="prefix" class="text-h5 font-semibold text-neutral-50 mr-1">{{ prefix }}</span>
                {{ value }}
            </p>
        </div>

        <div v-if="typeof delta === 'number'" class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-body2 font-semibold"
                :class="delta >= 0 ? 'bg-success-light text-success-dark' : 'bg-danger-light text-danger'"
            >
                <component :is="delta >= 0 ? ArrowUp : ArrowDown" class="w-3 h-3" />
                {{ Math.abs(delta) }}%
            </span>
            <span class="text-body2 text-neutral-50">vs kemarin</span>
        </div>
    </div>
</template>