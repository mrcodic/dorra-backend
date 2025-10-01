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
                ->with(['station.statuses'])
                ->whereCode($data['code'])
                ->lockForUpdate()
                ->firstOrFail();

            $station  = $ticket->station;
            $statuses = $station?->statuses?->sortBy('sequence')->values();

            if (!$station || !$statuses || $statuses->isEmpty()) {
                throw new ModelNotFoundException("Station or its statuses not configured.");
            }

            $currentIndex = 0;
            if ($ticket->current_status_id) {
                $found = $statuses->search(fn ($s) => (int)$s->id === (int)$ticket->current_status_id);
                if ($found !== false) {
                    $currentIndex = (int) $found;
                } else {
                    $ticket->current_status_id = $statuses->first()->id;
                    $ticket->save();
                }
            } else {
                $ticket->current_status_id = $statuses->first()->id;
                $ticket->save();

            }

            $nextStatusInSame = $statuses->get($currentIndex + 1);
            $nextStation = $this->stationRepository->query()
                ->where('workflow_order', '>', $station->workflow_order)
                ->orderBy('workflow_order')
                ->first();
            $firstStatusOfNext = $nextStation
                ? $nextStation->statuses()->orderBy('sequence')->first()
                : null;
//dd($nextStatusInSame || !($nextStation && $firstStatusOfNext), $nextStation && $firstStatusOfNext);
          match (true) {
              $nextStatusInSame => (function () use ($ticket, $station, $nextStatusInSame) {

                    $this->eventRepository->create([
                        'job_ticket_id'      => $ticket->id,
                        'station_id'         => $station->id,
                        'station_status_id'  => $nextStatusInSame->id,
                        'admin_id'           => auth()->id(),
                        'action'             => 'advance',
                    ]);
                    $ticket->update([
                        'current_status_id' => $nextStatusInSame->id,
                    ]);

                    return 'advanced_status';
                })(),

                $nextStation && $firstStatusOfNext => (function () use ($ticket, $nextStation, $firstStatusOfNext) {
//                    dd($nextStation, $firstStatusOfNext);
                    $this->eventRepository->create([
                        'job_ticket_id'      => $ticket->id,
                        'station_id'         => $nextStation->id,
                        'station_status_id'  => $firstStatusOfNext->id,
                        'admin_id'           => auth()->id(),
                        'action'             => 'advance',
                        'notes'              => 'Moved to next station',
                    ]);
                    $ticket->update([
                        'current_status_id' => $firstStatusOfNext->id,
                        'station_id'         => $nextStation->id,
                    ]);
                    return 'advanced_station';
                })(),

                default => (function () use ($ticket, $statuses) {
                    $this->eventRepository->create([
                        'job_ticket_id'      => $ticket->id,
                        'station_id'         => $ticket->station_id,
                        'station_status_id'  => $ticket->current_status_id ?? $statuses->last()->id,
                        'admin_id'           => auth()->id(),
                        'action'             => 'advance',
                        'notes'              => 'Completed last station',
                    ]);
                    return 'completed_workflow';
                })(),
            };


        });
    }

}
