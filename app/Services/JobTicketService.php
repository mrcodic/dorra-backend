<?php

namespace App\Services;

use Yajra\DataTables\DataTables;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\Interfaces\{JobEventRepositoryInterface, JobTicketRepositoryInterface, StationRepositoryInterface};

class JobTicketService extends BaseService
{
    public function __construct(JobTicketRepositoryInterface       $repository,
                                public StationRepositoryInterface  $stationRepository,
                                public JobEventRepositoryInterface $eventRepository,
    )
    {
        parent::__construct($repository);
    }

    public function getData(): JsonResponse
    {
        $jobs = $this->repository
            ->query()
            ->with(['station', 'orderItem', 'currentStatus'])
            ->when(request()->filled('search_value'), function ($query) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $search = request('search_value');
                    $query->where("code", 'LIKE', "%{$search}%");
                } else {
                    $query->whereRaw('1 = 0');
                }
            })->when(request()->filled('created_at'), function ($query) {
                $query->orderBy('created_at', request('created_at'));
            })
            ->latest();

        return DataTables::of($jobs)
            ->addColumn('code', function ($job) {
                return $job->code;
            })
            ->editColumn('priority_label', function ($job) {
                return $job->priority?->label() ?? "-";
            })
            ->editColumn('status_label', function ($job) {
                return $job->currentStatus?->name ?? "-";
            })
            ->editColumn('due_at', function ($job) {
                return $job->due_at?->format('Y-m-d') ?? "-";
            })
            ->addColumn('current_station', function ($job) {
                return $job->station?->name ?? "-";
            })
            ->addColumn('order_number', function ($job) {
                return $job->orderItem->order->order_number ?? "-";
            })
            ->addColumn('order_item_name', function ($job) {
                return $job->orderItem->orderable->name ?? "-";
            })
            ->make(true);
    }

    /**
     * @throws \Exception
     */
    public function scan(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'exists:job_tickets,code'],
        ]);

        return $this->handleTransaction(function () use ($data) {
            $ticket = $this->repository->query()
                ->with(['station.statuses' => fn($q) => $q->orderBy('sequence')])
                ->whereCode($data['code'])
                ->lockForUpdate()
                ->firstOrFail();

            $station  = $ticket->station;
            $statuses = $station?->statuses?->values();

            if (!$station || !$statuses || $statuses->isEmpty()) {
                throw new ModelNotFoundException("Station or its statuses not configured.");
            }

            if (!$ticket->current_status_id) {
                $ticket->current_status_id = $statuses->first()->id;
                $ticket->save();
            } else {

                if (!$statuses->firstWhere('id', (int) $ticket->current_status_id)) {
                    $ticket->current_status_id = $statuses->first()->id;
                    $ticket->save();
                }
            }

            $currentIndex = $statuses->search(fn ($s) => (int) $s->id === (int) $ticket->current_status_id);
            $nextStatusInSame = $statuses->get($currentIndex + 1);


            $nextStation = $this->stationRepository->query()
                ->where('workflow_order', '>', $station->workflow_order)
                ->orderBy('workflow_order')
                ->with(['statuses' => fn($q) => $q->orderBy('sequence')->limit(1)])
                ->first();

            $firstStatusOfNext = $nextStation?->statuses?->first();


            if ($nextStatusInSame) {
                dd("sfdf");
                $this->eventRepository->create([
                    'job_ticket_id'     => $ticket->id,
                    'station_id'        => $station->id,
                    'station_status_id' => $nextStatusInSame->id,
                    'admin_id'          => auth()->id(),
                    'action'            => 'advance',
                ]);

                $ticket->current_status_id = $nextStatusInSame->id;
                $ticket->save();

            }

            if ($nextStation && $firstStatusOfNext) {
                $this->eventRepository->create([
                    'job_ticket_id'     => $ticket->id,
                    'station_id'        => $station->id,
                    'station_status_id' => $ticket->current_status_id,
                    'admin_id'          => auth()->id(),
                    'action'            => 'handoff',
                    'notes'             => 'Ready to move to next station',
                ]);
                $ticket->current_status_id = $firstStatusOfNext->id;
                $ticket->station_id = $nextStation->id;
                $ticket->save();

            }
            $this->eventRepository->create([
                'job_ticket_id'     => $ticket->id,
                'station_id'        => $ticket->station_id,
                'station_status_id' => $ticket->current_status_id ?? $statuses->last()->id,
                'admin_id'          => auth()->id(),
                'action'            => 'complete',
                'notes'             => 'Completed last station',
            ]);


        });
    }


}
