<?php

namespace App\Services;


use App\Repositories\Interfaces\PlanRepositoryInterface;
use Yajra\DataTables\Facades\DataTables;

class PlanService extends BaseService
{

    public function __construct(PlanRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }

    public function activePlans()
    {
        return $this->repository->query()->whereIsActive(true)->get();
    }

    public function getData()
    {
        $plans = $this->repository
            ->query()
            ->when(request()->filled('search_value'), function ($query) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $search = request('search_value');
                    $words = preg_split('/\s+/', $search);
                    $query->where(function ($query) use ($words) {
                        foreach ($words as $word) {
                            $query->where('name', 'like', '%' . $word . '%');
                        }
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->orderBy('created_at', request('created_at', 'desc'));

        return DataTables::of($plans)
            ->editColumn('created_at', function ($plan) {
                return $plan->created_at->format('d/m/Y');
            })
            ->addColumn('action', function ($plan) {
                return [
                    'can_edit' => (bool)auth()->user()->hasPermissionTo('plans_update'),
                    'can_delete' => (bool)auth()->user()->hasPermissionTo('plans_delete'),
                ];
            })
            ->make();
    }

}
