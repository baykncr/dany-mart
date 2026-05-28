<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $today     = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $year      = Carbon::now()->year;

        return Inertia::render('Dashboard', [
            'summary'       => $this->buildSummary($today, $thisMonth),
            'monthlySales'  => $this->buildMonthlySales($year),
            'revenueVsExpense' => $this->buildRevenueVsExpense($year),
            'paymentMethods'   => $this->buildPaymentMethodBreakdown($thisMonth),
            'topProducts'      => $this->buildTopProducts($thisMonth),
            'recentOrders'     => $this->buildRecentOrders(),
            'currentPeriod'    => [
                'today'      => $today->translatedFormat('l, d F Y'),
                'month'      => $today->translatedFormat('F Y'),
                'year'       => $year,
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // SUMMARY CARDS
    // ──────────────────────────────────────────────────────────────────────

    private function buildSummary(Carbon $today, Carbon $thisMonth): array
    {
        // Hari ini
        $todayOrders = Order::whereDate('created_at', $today)->get();
        $todayRevenue       = $todayOrders->sum('total_amount');
        $todayTransactions  = $todayOrders->count();

        // Biaya modal hari ini
        $todayCOGS = OrderItem::whereHas('order', fn($q) => $q->whereDate('created_at', $today))
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->selectRaw('SUM(order_items.quantity * products.purchase_price) as cogs')
            ->value('cogs') ?? 0;

        $todayGrossProfit = $todayRevenue - $todayCOGS;

        // Bulan ini
        $monthOrders = Order::whereDate('created_at', '>=', $thisMonth)->get();
        $monthRevenue      = $monthOrders->sum('total_amount');
        $monthTransactions = $monthOrders->count();

        $monthCOGS = OrderItem::whereHas('order', fn($q) =>
                $q->whereDate('created_at', '>=', $thisMonth)
            )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->selectRaw('SUM(order_items.quantity * products.purchase_price) as cogs')
            ->value('cogs') ?? 0;

        $monthExpenses    = Expense::whereDate('date', '>=', $thisMonth)->sum('amount');
        $monthGrossProfit = $monthRevenue - $monthCOGS;
        $monthNetProfit   = $monthGrossProfit - $monthExpenses;

        // Total produk & stok kritis (stok ≤ 10)
        $totalProducts    = Product::count();
        $lowStockProducts = Product::where('stock', '<=', 10)->where('stock', '>', 0)->count();
        $outOfStock       = Product::where('stock', 0)->count();

        // Kemarin untuk delta %
        $yesterday        = Carbon::yesterday();
        $yesterdayRevenue = Order::whereDate('created_at', $yesterday)->sum('total_amount');
        $revenueDelta     = $yesterdayRevenue > 0
            ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100, 1)
            : null;

        return [
            // Hari ini
            'today_transactions'   => $todayTransactions,
            'today_revenue'        => $todayRevenue,
            'today_gross_profit'   => $todayGrossProfit,
            'today_cogs'           => $todayCOGS,
            'revenue_delta'        => $revenueDelta,

            // Bulan ini
            'month_transactions'   => $monthTransactions,
            'month_revenue'        => $monthRevenue,
            'month_gross_profit'   => $monthGrossProfit,
            'month_net_profit'     => $monthNetProfit,
            'month_expenses'       => $monthExpenses,

            // Inventory
            'total_products'       => $totalProducts,
            'low_stock_products'   => $lowStockProducts,
            'out_of_stock'         => $outOfStock,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // LINE CHART: Tren Penjualan 12 Bulan
    // ──────────────────────────────────────────────────────────────────────

    private function buildMonthlySales(int $year): array
    {
        // Revenue per bulan
        $revenues = Order::selectRaw('MONTH(created_at) as month, SUM(total_amount) as total')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->pluck('total', 'month');

        // Transaksi per bulan
        $counts = Order::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->pluck('total', 'month');

        // COGS per bulan
        $cogs = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereYear('orders.created_at', $year)
            ->selectRaw('MONTH(orders.created_at) as month, SUM(order_items.quantity * products.purchase_price) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        $months = collect(range(1, 12))->map(fn($m) => [
            'month'      => Carbon::create($year, $m, 1)->locale('id')->monthName,
            'month_short'=> Carbon::create($year, $m, 1)->locale('id')->shortMonthName,
            'revenue'    => (int) ($revenues[$m] ?? 0),
            'transactions'=> (int) ($counts[$m] ?? 0),
            'profit'     => (int) ($revenues[$m] ?? 0) - (int) ($cogs[$m] ?? 0),
        ]);

        return $months->toArray();
    }

    // ──────────────────────────────────────────────────────────────────────
    // BAR CHART: Revenue vs Expense
    // ──────────────────────────────────────────────────────────────────────

    private function buildRevenueVsExpense(int $year): array
    {
        $revenues = Order::selectRaw('MONTH(created_at) as month, SUM(total_amount) as total')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->pluck('total', 'month');

        $expenses = Expense::selectRaw('MONTH(date) as month, SUM(amount) as total')
            ->whereYear('date', $year)
            ->groupBy('month')
            ->pluck('total', 'month');

        return collect(range(1, 12))->map(fn($m) => [
            'month'   => Carbon::create($year, $m, 1)->locale('id')->shortMonthName,
            'revenue' => (int) ($revenues[$m] ?? 0),
            'expense' => (int) ($expenses[$m] ?? 0),
            'profit'  => (int) ($revenues[$m] ?? 0) - (int) ($expenses[$m] ?? 0),
        ])->toArray();
    }

    // ──────────────────────────────────────────────────────────────────────
    // DONUT CHART: Metode Pembayaran
    // ──────────────────────────────────────────────────────────────────────

    private function buildPaymentMethodBreakdown(Carbon $thisMonth): array
    {
        return Order::selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
            ->whereDate('created_at', '>=', $thisMonth)
            ->groupBy('payment_method')
            ->get()
            ->map(fn($row) => [
                'method' => $row->payment_method,
                'label'  => $row->payment_method === 'cash' ? 'Tunai' : 'QRIS',
                'count'  => $row->count,
                'total'  => $row->total,
            ])
            ->toArray();
    }

    // ──────────────────────────────────────────────────────────────────────
    // TOP PRODUCTS TABLE
    // ──────────────────────────────────────────────────────────────────────

    private function buildTopProducts(Carbon $thisMonth): array
    {
        return OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereDate('orders.created_at', '>=', $thisMonth)
            ->selectRaw('
                products.id,
                products.name,
                products.unit,
                products.stock,
                categories.name as category_name,
                SUM(order_items.quantity) as total_qty,
                SUM(order_items.subtotal) as total_revenue
            ')
            ->groupBy('products.id', 'products.name', 'products.unit', 'products.stock', 'categories.name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get()
            ->toArray();
    }

    // ──────────────────────────────────────────────────────────────────────
    // RECENT ORDERS
    // ──────────────────────────────────────────────────────────────────────

    private function buildRecentOrders(): array
    {
        return Order::with('user:id,name')
            ->withCount('items')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(fn($o) => [
                'id'             => $o->id,
                'order_number'   => $o->order_number,
                'cashier'        => $o->user->name,
                'total_amount'   => $o->total_amount,
                'payment_method' => $o->payment_method,
                'items_count'    => $o->items_count,
                'created_at'     => $o->created_at->diffForHumans(),
                'created_at_fmt' => $o->created_at->format('d/m H:i'),
            ])
            ->toArray();
    }
}