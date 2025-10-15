<?php

namespace App\Services;

use App\Enums\Template\StatusEnum;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\Interfaces\StationStatusRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

class StationStatusService extends BaseService
{
    public function __construct(StationStatusRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }

    public function getData(): JsonResponse
    {
        $stationStatuses = $this->repository
            ->query()
            ->whereNotNull('parent_id')
            ->when(request()->filled('search_value'), function ($query)  {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $query->where('name', 'like', '%' . request('search_value') . '%');
                } else {
                    $query->whereRaw('1 = 0');
                }
            })->when(request()->filled('created_at'), function ($query) {
                $query->orderBy('created_at', request('created_at'));
            })
            ->latest();
        return DataTables::of($stationStatuses)
            ->make();
    }
    

}
