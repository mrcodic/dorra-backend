<?php

use App\Services\BaseService;
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
    ) {

        $requested = request('per_page', $perPage);
        $pageSize = $requested === 'all' ? null : (int)$requested;


        $query = $this->repository
            ->query(['id', 'name', 'product_id', 'created_at'])
            ->with(['product:id,name'])
            ->when(request()->filled('search_value'), function ($q) {
                $locale = app()->getLocale();
                $q->where("name->{$locale}", 'LIKE', '%' . request('search_value') . '%');
            })
            ->when(request()->filled('product_id'), fn($q) => $q->whereProductId(request('product_id')))
            ->latest();

        if (request()->ajax()) {
            return $pageSize === null
                ? $query->get()
                : $query->paginate($pageSize)->withQueryString();
        }

        return $this->repository->all(
            $paginate,
            $columns,
            $relations,
            filters: $this->filters,
            perPage: $pageSize ?? $perPage
        );
    }
}
