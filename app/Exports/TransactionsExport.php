<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TransactionsExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private readonly array $filters = []
    ) {}

    public function sheets(): array
    {
        return [
            'Ringkasan'   => new Sheets\SummarySheet($this->filters),
            'Transaksi'   => new Sheets\OrdersSheet($this->filters),
            'Detail Item' => new Sheets\OrderItemsSheet($this->filters),
        ];
    }
}