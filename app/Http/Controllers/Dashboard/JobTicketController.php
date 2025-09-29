<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Controllers\Base\DashboardController;
use App\Services\JobTicketService;
use Illuminate\Http\JsonResponse;

class JobTicketController extends DashboardController
{
    public function __construct(public JobTicketService $jobTicketService,

    )
    {
        parent::__construct($jobTicketService);
//        $this->storeRequestClass = new StoreLocationRequest();
//        $this->updateRequestClass = new UpdateLocationRequest();
        $this->indexView = 'job-tickets.index';
        $this->usePagination = true;
        $this->resourceTable = 'job_tickets';

    }
    public function getData(): JsonResponse
    {
        return $this->jobTicketService->getData();
    }
}
