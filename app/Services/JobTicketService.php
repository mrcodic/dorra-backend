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
                $search = request('search_value');
                if (hasMeaningfulSearch($search)) {
                    $query->where('code', 'LIKE', "%{$search}%");
                } else {
                    $query->whereRaw('1=0');
                }
            })


            ->when(request()->boolean('overdue'), function ($query) {
                $query->whereNotNull('due_at')
                    ->where('due_at', '<=', now());
            })


            ->when(request()->boolean('pending'), function ($query) {
                $query->whereNull('current_status_id');
            })

            ->when(!request()->boolean('pending'), function ($query) {
                $query->where('current_status_id', request('status_id'));
            })
             ->when(request()->filled('status') , function ($query) {
                $query->where('current_status_id', request('status'));
            })


            ->when(request()->filled('priority'), function ($query) {
                $query->where('priority', request('priority'));
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
            ->addColumn('order_item_image', function ($job) {
                return $job->orderItem->itemable->getImageUrl();
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
                ->with(['station.statuses', 'currentStatus'])
                ->whereCode($data['code'])
                ->lockForUpdate()
                ->firstOrFail();

            $station   = $ticket->station;
            $statuses  = $station?->statuses?->sortBy('sequence')->values();

            if (!$station || !$statuses || $statuses->isEmpty()) {
                throw new ModelNotFoundException("Station or its statuses not configured.");
            }

            $fromStationName = (string) $station->name;
            $fromStatusName  = $ticket->currentStatus
                ? (string) $ticket->currentStatus->name
                : (string) ($statuses->first()->name ?? '');

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


            $toStationName = $fromStationName;
            $toStatusName  = $fromStatusName;
            $message       = 'OK';
            $resultKey     = null;

            match (true) {
                (bool) $nextStatusInSame => (function () use ($ticket, $station, $nextStatusInSame, &$toStationName, &$toStatusName, &$message, &$resultKey) {
                    $this->eventRepository->create([
                        'job_ticket_id'     => $ticket->id,
                        'station_id'        => $station->id,
                        'station_status_id' => $nextStatusInSame->id,
                        'admin_id'          => auth()->id(),
                        'action'            => 'advance',
                    ]);

                    $ticket->current_status_id = $nextStatusInSame->id;
                    $ticket->save();

                    $toStationName = (string) $station->name;
                    $toStatusName  = (string) $nextStatusInSame->name;
                    $message       = 'Advanced to next status';
                    $resultKey     = 'advanced_status';
                })(),

                $nextStation && $firstStatusOfNext => (function () use ($ticket, $nextStation, $firstStatusOfNext, &$toStationName, &$toStatusName, &$message, &$resultKey) {
                    $this->eventRepository->create([
                        'job_ticket_id'     => $ticket->id,
                        'station_id'        => $nextStation->id,
                        'station_status_id' => $firstStatusOfNext->id,
                        'admin_id'          => auth()->id(),
                        'action'            => 'advance',
                        'notes'             => 'Moved to next station',
                    ]);

                    $ticket->station_id        = $nextStation->id;
                    $ticket->current_status_id = $firstStatusOfNext->id;
                    $ticket->save();

                    $toStationName = (string) $nextStation->name;
                    $toStatusName  = (string) $firstStatusOfNext->name;
                    $message       = 'Moved to next station';
                    $resultKey     = 'advanced_station';
                })(),

                default => (function () use ($ticket, $statuses, &$toStationName, &$toStatusName, &$message, &$resultKey) {
                    $this->eventRepository->create([
                        'job_ticket_id'     => $ticket->id,
                        'station_id'        => $ticket->station_id,
                        'station_status_id' => $ticket->current_status_id ?? $statuses->last()->id,
                        'admin_id'          => auth()->id(),
                        'action'            => 'advance',
                        'notes'             => 'Completed last station',
                    ]);

                    $toStationName = (string) optional($ticket->station)->name ?: '';
                    $toStatusName  = (string) ($ticket->currentStatus->name ?? $statuses->last()->name ?? '');
                    $message       = 'Completed workflow';
                    $resultKey     = 'completed_workflow';
                })(),
            };

            $scanCount = $this->eventRepository->query()
                ->where('job_ticket_id', $ticket->id)
                ->count();


            return [
                'code'          => $ticket->code,
                'message'       => $message,
                'result'        => $resultKey,
                'scan_count'    => $scanCount,
                'from_station'  => $fromStationName,
                'to_station'    => $toStationName,
                'from_status'   => $fromStatusName,
                'to_status'     => $toStatusName,
            ];
        });
    }

}
