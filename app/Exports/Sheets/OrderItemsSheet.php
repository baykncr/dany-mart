<?php

namespace App\Exports\Sheets;

use App\Models\OrderItem;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrderItemsSheet implements FromQuery, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(private array $filters = []) {}

    public function title(): string { return 'Detail Item'; }

    public function query()
    {
        $query = OrderItem::with([
                'order:id,order_number,created_at,payment_method',
                'order.user:id,name',
                'product:id,name,unit,category_id',
                'product.category:id,name',
            ]);

        if (!empty($this->filters['date_from'])) {
            $query->whereHas('order', fn($q) => $q->whereDate('created_at', '>=', $this->filters['date_from']));
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereHas('order', fn($q) => $q->whereDate('created_at', '<=', $this->filters['date_to']));
        }

        return $query->orderByDesc('id');
    }

    public function headings(): array
    {
        return [
            'No. Transaksi',
            'Tanggal',
            'Kasir',
            'Produk',
            'Kategori',
            'Satuan',
            'Qty',
            'Harga Satuan',
            'Subtotal',
            'Metode Bayar',
        ];
    }

   public function map($item): array
    {
        return [
            $item->order?->order_number ?? 'Tidak Diketahui',
            $item->order?->created_at ? $item->order->created_at->format('d/m/Y H:i') : '-',
            $item->order?->user?->name ?? 'Kasir Dihapus',
            $item->product?->name ?? 'Produk Dihapus',
            $item->product?->category?->name ?? '-',
            $item->product?->unit ?? '-',
            
            $item->quantity,
            $item->unit_price,
            $item->subtotal,
            
            $item->order?->payment_method ? strtoupper($item->order->payment_method) : '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '073C64']],
        ]);
        $sheet->getStyle('H:H')->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle('I:I')->getNumberFormat()->setFormatCode('"Rp "#,##0');

        return [];
    }
}