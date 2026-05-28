<?php

namespace App\Exports\Sheets;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersSheet implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(private array $filters = []) {}

    public function title(): string { return 'Transaksi'; }

    public function query()
    {
        $query = Order::with('user:id,name')->withCount('items');

        if (!empty($this->filters['date_from'])) $query->whereDate('created_at', '>=', $this->filters['date_from']);
        if (!empty($this->filters['date_to']))   $query->whereDate('created_at', '<=', $this->filters['date_to']);
        if (!empty($this->filters['payment_method'])) $query->where('payment_method', $this->filters['payment_method']);
        if (!empty($this->filters['search'])) $query->where('order_number', 'like', '%' . $this->filters['search'] . '%');

        return $query->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'No. Transaksi',
            'Tanggal & Waktu',
            'Kasir',
            'Jumlah Item',
            'Total Belanja',
            'Metode Bayar',
            'Dibayar',
            'Kembalian',
        ];
    }

    public function map($order): array
    {
        return [
            $order->order_number,
            $order->created_at->format('d/m/Y H:i:s'),
            $order->user->name,
            $order->items_count,
            $order->total_amount,
            strtoupper($order->payment_method),
            $order->payment_amount,
            $order->change_amount,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '073C64']],
        ]);
        $sheet->getStyle('E:E')->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle('G:G')->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle('H:H')->getNumberFormat()->setFormatCode('"Rp "#,##0');

        return [];
    }
}