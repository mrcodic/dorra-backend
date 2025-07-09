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
        ->query(['id', 'invoice_number', 'created_at', 'user_id', 'order_id', 'design_id' , 
        'quantity', 'subtotal', 'discount_amount', 'delivery_amount', 'tax_amount', 
        'total_price', 'status', 'issued_date'])
            ->with(['order', 'user', 'design'])
            ->when(request()->filled('search_value'), function ($query) {
                $locale = app()->getLocale();
                $search = request('search_value');
                $query->where("invoice_number->{$locale}", 'LIKE', "%{$search}%");
            })
            ->latest();

         return DataTables::of($invoices)
        ->addColumn('invoice_number', function ($invoice) {
            return $invoice->invoice_number;
        })
        ->addColumn('user_name', function ($invoice) {
            return optional($invoice->user)->first_name . ' ' . optional($invoice->user)->last_name;
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
