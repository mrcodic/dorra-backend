<?php

namespace App\Http\Controllers\Dashboard;


use App\Enums\JobTicket\StatusEnum;
use App\Http\Controllers\Base\DashboardController;
use App\Models\Station;
use App\Repositories\Interfaces\StationRepositoryInterface;
use App\Services\JobTicketService;
use Illuminate\Http\JsonResponse;

class JobTicketController extends DashboardController
{
    public function __construct(public JobTicketService $jobTicketService,
    StationRepositoryInterface $stationRepository,

    )
    {
        parent::__construct($jobTicketService);

        $this->updateRequestClass = new UpdateJobTicketRequest();
        $this->indexView = 'job-tickets.index';
        $this->usePagination = true;
        $this->resourceTable = 'job_tickets';
        $this->assoiciatedData = [
            'index' => [
                'stations' => $stationRepository->query()->select(['id', 'name'])->get(),
            ]

        ];

    }
    public function getData(): JsonResponse
    {
        return $this->jobTicketService->getData();
    }
}
