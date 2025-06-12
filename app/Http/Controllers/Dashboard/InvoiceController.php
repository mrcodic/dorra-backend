<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Services\InvoiceService;
use App\Http\Requests\Category\{StoreOrderRequest, UpdateOrderRequest};
use Illuminate\Http\JsonResponse;


class InvoiceController extends DashboardController
{
    public function __construct(public InvoiceService $invoiceService)
    {
        parent::__construct($invoiceService);
        $this->storeRequestClass = new StoreOrderRequest();
        $this->updateRequestClass = new UpdateOrderRequest();
        $this->indexView = 'invoices.index';
        $this->createView = 'invoices.create';
        $this->editView = 'invoices.edit';
        $this->usePagination = true;
        $this->resourceTable = 'invoices';
    }

    public function getData(): JsonResponse
    {
        return $this->invoiceService->getData();
    }


}
