<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Controllers\Base\DashboardController;
use App\Http\Requests\JobTicket\UpdateJobTicketRequest;
use App\Repositories\Interfaces\StationRepositoryInterface;
use App\Services\JobTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class JobTicketController extends DashboardController
{
    public function __construct(public JobTicketService    $jobTicketService,
                                StationRepositoryInterface $stationRepository,

    )
    {
        parent::__construct($jobTicketService);

        $this->updateRequestClass = new UpdateJobTicketRequest();
        $this->indexView = 'job-tickets.index';
        $this->showView = 'job-tickets.show';
        $this->usePagination = true;
        $this->resourceTable = 'job_tickets';
        $this->assoiciatedData = [
            'index' => [
                'stations' => $stationRepository->query()->withCount('jobTickets')
                    ->get(),
            ],
        ];
        $this->methodRelations['show'] = ['jobEvents.admin'];
    }

    public function getData(): JsonResponse
    {
        return $this->jobTicketService->getData();
    }

    public function scan(Request $request): JsonResponse
    {
        $data = $this->jobTicketService->scan($request);
        return Response::api(data: $data);

    }
}
