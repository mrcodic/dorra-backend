<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class InvoicesExport implements FromCollection
{
    public function __construct(public $invoices = null)
    {
    }

    public function collection()
    {
        $invoices = $this->invoices;
        return $invoices->map(function ($invoice) {
            return [
                $invoice->invoice_number,
                $invoice->order?->user?->name ?? $invoice->order?->guest?->name,
                $invoice->subtotal,
                $invoice->issued_date,

            ];
        });
    }

    public function headings(): array
    {
        return [
            'Invoice Number',
            'Client',
            'Price',
            'Issued Date',
        ];
    }
}
