<?php

namespace App\Services;

use App\Repositories\Interfaces\JobTicketRepositoryInterface;
use Illuminate\Http\JsonResponse;
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
                return $job->status?->label() ?? "-";
            })
            ->editColumn('due_at', function ($job) {
                return $job->due_at?->format('Y-m-d') ?? "-";
            })
            ->addColumn('current_station', function ($job) {
                return $job->station?->name ?? "-";
            }) ->addColumn('order_number', function ($job) {
                return $job->orderItem->order->order_number ?? "-";
            })
            ->addColumn('order_item_name', function ($job) {
                return $job->orderItem->orderable->name ?? "-";
            })
            ->make(true);
    }


}
