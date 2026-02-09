<?php

namespace App\Services;

use App\Repositories\Interfaces\CreditOrderRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;


class CreditOrderService extends BaseService
{
    public function __construct(CreditOrderRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getData(): JsonResponse
    {

        $creditOrders = $this->repository
            ->query()
            ->with(['plan', 'user'])
            ->when(request()->filled('search_value'), function ($query) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $search = request('search_value');
                    $query->where("order_number", 'LIKE', "%{$search}%");
                } else {
                    $query->whereRaw('1 = 0');
                }
            })->when(request()->filled('created_at'), function ($query) {
                $query->orderBy('created_at', request('created_at'));
            })
            ->latest();

        return DataTables::of($creditOrders)
            ->addColumn('added_date', function ($creditOrder) {
                return $creditOrder->created_at?->format('d/n/Y');
            })
            ->make(true);
    }

}
