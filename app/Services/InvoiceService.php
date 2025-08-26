<?php

namespace App\Services;

use App\Exports\DiscountCodesExport;
use App\Exports\InvoicesExport;
use App\Repositories\Interfaces\InvoiceRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class InvoiceService extends BaseService
{

    public function __construct(InvoiceRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }

    public function getData(): JsonResponse
    {
        $invoices = $this->repository
        ->query()
            ->with(['order'])
            ->when(request()->filled('search_value'), function ($query) {
                if (hasMeaningfulSearch(request('search_value'))) {
                $search = request('search_value');
                $query->where("invoice_number", 'LIKE', "%{$search}%");
                } else {
                    $query->whereRaw('1 = 0');
                }
            })->when(request()->filled('created_at'), function ($query) {
                $query->orderBy('created_at', request('created_at'));
            })
            ->latest();

         return DataTables::of($invoices)
        ->addColumn('invoice_number', function ($invoice) {
            return $invoice->invoice_number;
        })
             ->addColumn('user_name', function ($invoice) {
                     return $invoice->order?->guest ? 'Guest' : $invoice->order->orderAddress?->name;

             })

             ->addColumn('total_price', function ($invoice) {
            return $invoice->total_price;
        })
        ->addColumn('issued_date', function ($invoice) {
            return $invoice->issued_date;
        })
        ->make(true);
    }
    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $invoices = $this->repository->all();
        return Excel::download(new InvoicesExport($invoices), 'Invoices - Dorra Dashboard .xlsx');
    }

    public function download($id)
    {
        $model = $this->repository->find($id);
        $pdf = Pdf::loadView('dashboard.invoices.pdf', compact('model'))->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);;
        return $pdf->download('invoice.pdf');
    }
}
