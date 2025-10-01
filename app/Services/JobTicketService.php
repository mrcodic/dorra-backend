<?php

namespace App\Services;

use App\Repositories\Interfaces\JobTicketRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class JobTicketService extends BaseService
{
    public function __construct(JobTicketRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getData(): JsonResponse
    {
        $jobs = $this->repository
            ->query()
            ->with(['station','orderItem','currentStatus'])
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

    public function scan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string','exists:job_tickets,code'],
        ]);
        $this->handleTransaction(function () use ($data) {
            $ticket = $this->repository->query()->whereCode($data['code'])->lockForUpdate()->first();
        });

//        $result = DB::transaction(function () use ($data, $request) {
//            $ticket = JobTicket::where('code', $data['code'])->lockForUpdate()->first();
//            if (!$ticket) {
//                abort(Response::HTTP_NOT_FOUND, __('Barcode not found.'));
//            }
//
//            if ($ticket->scan_count >= 2) {
//                abort(Response::HTTP_UNPROCESSABLE_ENTITY, __('Scan limit reached for this barcode.'));
//            }
//
//            $currentStation = $ticket->station()->first();
//            $currentOrder   = $currentStation?->sort_order ?? 0;
//
//            $nextStation = Station::where('sort_order', '>', $currentOrder)
//                ->orderBy('sort_order')
//                ->first();
//
//            if (!$nextStation) {
//                abort(Response::HTTP_UNPROCESSABLE_ENTITY, __('Already at the final station.'));
//            }
//
//            $fromStationId = $ticket->station_id;
//            $ticket->station_id = $nextStation->id;
//            $ticket->scan_count++;
//            $ticket->save();
//
////            JobEvent::create([
////                'job_ticket_id' => $ticket->id,
////                'type'          => 'scan',
////                'from_id'       => $fromStationId,
////                'to_id'         => $nextStation->id,
////                'meta'          => [
////                    'reason'  => 'barcode_scan',
////                    'scan_no' => $ticket->scan_count,
////                ],
////                'performed_by'  => optional($request->user())->id,
////                'performed_at'  => now(),
////            ]);
//
//            return [
//                'ticket_id'     => $ticket->id,
//                'code'          => $ticket->code,
//                'scan_count'    => $ticket->scan_count, // 1 or 2
//                'from_station'  => $fromStationId,
//                'to_station'    => $nextStation->id,
//                'message'       => $ticket->scan_count >= 2
//                    ? __('Moved to next station. This barcode cannot be scanned again.')
//                    : __('Moved to next station. One scan remaining.'),
//            ];
//        });

        // Return JSON so our Blade page (fetch) can render it nicely
        return response()->json("ok");

}
}
