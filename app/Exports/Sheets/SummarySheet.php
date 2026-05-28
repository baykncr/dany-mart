<?php

namespace App\Exports\Sheets;

use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SummarySheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(private array $filters = []) {}

    public function title(): string { return 'Ringkasan'; }

    public function array(): array
    {
        $query = Order::query();
        $this->applyFilters($query);

        $totalRevenue     = $query->sum('total_amount');
        $totalTransactions = $query->count();

        $cogsQuery = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id');
        $this->applyFiltersToJoin($cogsQuery);
        $totalCOGS = $cogsQuery->selectRaw('SUM(order_items.quantity * products.purchase_price) as c')->value('c') ?? 0;

        $expQuery = Expense::query();
        if (!empty($this->filters['date_from'])) $expQuery->whereDate('date', '>=', $this->filters['date_from']);
        if (!empty($this->filters['date_to']))   $expQuery->whereDate('date', '<=', $this->filters['date_to']);
        $totalExpenses = $expQuery->sum('amount');

        $grossProfit = $totalRevenue - $totalCOGS;
        $netProfit   = $grossProfit - $totalExpenses;

        $cashRevenue = (clone $query)->where('payment_method', 'cash')->sum('total_amount');
        $qrisRevenue = (clone $query)->where('payment_method', 'qris')->sum('total_amount');

        $period = trim(
            ($this->filters['date_from'] ?? '') . ' s/d ' . ($this->filters['date_to'] ?? now()->format('Y-m-d'))
        );

        return [
            ['LAPORAN KEUANGAN - DANY MART', '', ''],
            ['Diekspor pada:', now()->format('d/m/Y H:i:s'), ''],
            ['Periode:', $period, ''],
            ['', '', ''],
            ['METRIK', 'NILAI', 'KETERANGAN'],
            ['Total Transaksi',    $totalTransactions,   'Order'],
            ['Total Pendapatan',   $totalRevenue,         'Rp'],
            ['Total HPP (COGS)',   $totalCOGS,            'Rp'],
            ['Laba Kotor',         $grossProfit,          'Rp'],
            ['Total Pengeluaran',  $totalExpenses,        'Rp'],
            ['Laba Bersih',        $netProfit,            'Rp'],
            ['', '', ''],
            ['PEMBAYARAN', 'NILAI', ''],
            ['Tunai (Cash)',       $cashRevenue,          'Rp'],
            ['QRIS',              $qrisRevenue,          'Rp'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Title
        $sheet->mergeCells('A1:C1');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '073C64']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Header row
        $sheet->getStyle('A5:C5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '073C64']],
        ]);
        $sheet->getStyle('A13:B13')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '073C64']],
        ]);

        // Number format for money rows
        $sheet->getStyle('B6:B15')->getNumberFormat()->setFormatCode('"Rp "#,##0');

        // Laba Bersih highlight
        $sheet->getStyle('A11:C11')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '15803D']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCFCE7']],
        ]);

        return [];
    }

    private function applyFilters($query): void
    {
        if (!empty($this->filters['date_from'])) $query->whereDate('created_at', '>=', $this->filters['date_from']);
        if (!empty($this->filters['date_to']))   $query->whereDate('created_at', '<=', $this->filters['date_to']);
        if (!empty($this->filters['payment_method'])) $query->where('payment_method', $this->filters['payment_method']);
    }

    private function applyFiltersToJoin($query): void
    {
        if (!empty($this->filters['date_from'])) $query->whereDate('orders.created_at', '>=', $this->filters['date_from']);
        if (!empty($this->filters['date_to']))   $query->whereDate('orders.created_at', '<=', $this->filters['date_to']);
    }
}