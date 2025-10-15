<?php

namespace App\Services;

use App\Models\JobTicket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;
use Illuminate\Http\{JsonResponse, Request};
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
            ->with(['station','orderItem','currentStatus'])

            ->when(request()->filled('search_value'), function ($q) {
                $search = request('search_value');
                hasMeaningfulSearch($search)
                    ? $q->where(function ($q) use ($search) {
                    $q->whereHas('orderItem', function ($q) use ($search) {
                                $q->where('id',$search);
                            })->orWhereHas('orderItem', function ($q) use ($search) {
                                $q->whereHas('order',function ($q) use ($search){
                                    $q->where('order_number', 'like', '%' . $search . '%');

                                });
                            });
                })
                    : $q->whereRaw('1=0');
            })
            ->when(request()->boolean('overdue'), function ($q) {
                $q->whereNotNull('due_at')->where('due_at', '<', today())
                ->whereHas('station', function ($q) {
                    $q->whereIsTerminal(false);
                })
                ;
            })

            ->when(request()->boolean('pending'), function ($q) {
                $q->whereNull('current_status_id');
            })

            ->when(!request()->boolean('pending') && request()->filled('status_id'), function ($q) {
                $q->where('current_status_id', request('status_id'));
            })
            ->when(request()->filled('due_at'), fn($q) => $q->whereDate('due_at', request('due_at')))
            ->when(request()->filled('station_id'), fn($q) => $q->where('station_id', request('station_id')))
            ->when(request()->filled('priority'), fn($q) => $q->where('priority', request('priority')))
            ->latest();


        return DataTables::of($jobs)
            ->addColumn('code', fn($job) => $job->code)
            ->editColumn('priority_label', fn($job) => $job->priority?->label() ?? '-')
            ->editColumn('status_label', fn($job) => $job->currentStatus?->name ?? '-')
            ->editColumn('due_at', fn($job) => $job->due_at?->format('Y-m-d') ?? '-')
            ->addColumn('current_station', fn($job) => $job->station?->name ?? '-')
            ->addColumn('order_number', fn($job) => $job->orderItem->order->order_number ?? '-')
            ->addColumn('order_item_name', fn($job) => $job->orderItem->orderable?->name ?? '-')
            ->addColumn('order_item_quantity', fn($job) => $job->orderItem?->quantity ?? '-')
            ->addColumn('order_item_id', fn($job) => $job->orderItem?->id ?? '-')
            ->addColumn('order_item_image', fn($job) => $job->orderItem->itemable?->getFrontImageUrl())
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

            $statuses = $ticket->stationStatuses()
                ->whereStationId($station->id)
                ->get()
                ->isNotEmpty() ?
                $ticket->stationStatuses()
                    ->whereStationId($station->id)
                    ->get()->sortBy('sequence')->values()
                : $station?->statuses?->sortBy('sequence')->values();


            if (!$station || !$statuses || $statuses->isEmpty()) {
               throw ValidationException::withMessages(["station" => "Job ticket still pending."]);
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



    public function downloadPdf(JobTicket $ticket)
    {
        $model= $ticket->load([
            'orderItem.order',
            'orderItem.itemable.types',
            'orderItem.orderable',
            'jobEvents.admin.roles',
            'station','currentStatus',
        ]);

        $pdf = Pdf::loadView('dashboard.job-tickets.pdf', compact('model'));
        $pdf->set_option('isRemoteEnabled', true);
        $pdf->set_option('isHtml5ParserEnabled', true);


        return $pdf->download('job_ticket_'.$ticket->code.'.pdf');
    }

}
