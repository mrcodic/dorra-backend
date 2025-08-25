<?php

namespace App\Services;

use App\Exports\DiscountCodesExport;
use App\Exports\InvoicesExport;
use App\Repositories\Interfaces\InvoiceRepositoryInterface;
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
                 if ($invoice->order && $invoice->order->user) {
                     return trim($invoice->order->user->first_name . ' ' . $invoice->order->user->last_name);
                 }
                 if ($invoice->order && $invoice->order->guest) {
                     return trim($invoice->order->guest->first_name . ' ' . $invoice->order->guest->last_name);
                 }
                 return 'Unknown';
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
}
