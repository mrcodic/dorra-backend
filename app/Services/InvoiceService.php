<?php

namespace App\Services;

use App\Repositories\Interfaces\InvoiceRepositoryInterface;
use Illuminate\Http\JsonResponse;
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
                    $locale = app()->getLocale();
                $search = request('search_value');
                $query->where("invoice_number->{$locale}", 'LIKE', "%{$search}%");
                } else {
                    $query->whereRaw('1 = 0');
                }
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
}
