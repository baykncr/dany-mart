<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TransactionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Order::with(['user:id,name', 'items.product:id,name,unit'])
            ->withCount('items');

        // Filter tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter metode pembayaran
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Search order number
        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        $orders = $query->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn($o) => [
                'id'             => $o->id,
                'order_number'   => $o->order_number,
                'cashier'        => $o->user->name,
                'items_count'    => $o->items_count,
                'total_amount'   => $o->total_amount,
                'payment_method' => $o->payment_method,
                'payment_amount' => $o->payment_amount,
                'change_amount'  => $o->change_amount,
                'created_at'     => $o->created_at->format('d/m/Y H:i'),
                'items'          => $o->items->map(fn($i) => [
                    'name'     => $i->product->name,
                    'qty'      => $i->quantity,
                    'unit'     => $i->product->unit,
                    'price'    => $i->unit_price,
                    'subtotal' => $i->subtotal,
                ]),
            ]);

        // Summary untuk filter aktif
        $summaryQuery = Order::query();
        if ($request->filled('date_from')) $summaryQuery->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $summaryQuery->whereDate('created_at', '<=', $request->date_to);
        if ($request->filled('payment_method')) $summaryQuery->where('payment_method', $request->payment_method);

        return Inertia::render('Transactions/Index', [
            'orders'  => $orders,
            'filters' => $request->only(['search', 'date_from', 'date_to', 'payment_method']),
            'summary' => [
                'total_orders'  => (clone $summaryQuery)->count(),
                'total_revenue' => (clone $summaryQuery)->sum('total_amount'),
            ],
        ]);
    }

    /**
     * Export ke Excel — hanya Admin.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->only(['date_from', 'date_to', 'payment_method', 'search']);
        $filename = 'transaksi-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(new TransactionsExport($filters), $filename);
    }
}