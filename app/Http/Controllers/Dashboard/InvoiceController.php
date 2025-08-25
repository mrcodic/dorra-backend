<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class InvoiceController extends DashboardController
{
    public function __construct(public InvoiceService $invoiceService)
    {
        parent::__construct($invoiceService);
        $this->indexView = 'invoices.index';
        $this->showView = 'invoices.show';
        $this->usePagination = true;
        $this->resourceTable = 'invoices';
        $this->methodRelations = [

            'edit' => ['order' , 'user' , 'designs'],
        ];

    }

    public function getData(): JsonResponse
    {
        return $this->invoiceService->getData();
    }

    public function export(): BinaryFileResponse
    {
        return $this->invoiceService->export();
    }


}
