<?php

namespace App\Services;


use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\MockupRepositoryInterface;

class MockupService extends BaseService
{
    public BaseRepositoryInterface $repository;

    public function __construct(MockupRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }


    public function getAll(
        $relations = [],
        bool $paginate = false,
        $columns = ['*'],
        $perPage = 16
    )
    {

        $requested = request('per_page', $perPage);
        $pageSize = $requested === 'all' ? null : (int)$requested;

        $query = $this->repository
            ->query()
            ->with(['product:id,name'])
            ->when(request()->filled('search_value'), function ($q) {
                $q->where("name", 'LIKE', '%' . request('search_value') . '%');
            })
            ->when(request()->filled('product_id'), fn($q) => $q->whereProductId(request('product_id')))
            ->when(request()->filled('type'), fn($q) => $q->whereType(request('type')))
            ->when(request()->filled('search'), function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%');
            })
            ->latest();

        if (request()->ajax()) {
            return $pageSize === null
                ? $query->get()
                : $query->paginate($pageSize)->withQueryString();
        }
        if (request()->expectsJson()) {
            return $query->paginate($pageSize);
        }
        return $this->repository->all(
            $paginate,
            $columns,
            $relations,
            filters: $this->filters,
            perPage: $pageSize ?? $perPage
        );
    }

    public function showAndUpdateRecent($id)
    {
        $mockup = $this->repository->find($id);
        return auth('web')->user()->recentMockups()->syncWithoutDetaching([$mockup->id]);
    }

    public function recentMockups()
    {
        return auth('web')->user()->recentMockups()->take(5)->get();
    }

    public function destroyRecentMockup($id)
    {
        return auth('web')->user()->recentMockups()->detach($id);
    }
}
