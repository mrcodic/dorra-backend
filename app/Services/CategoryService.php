<?php

namespace App\Services;

use App\Repositories\Interfaces\CategoryRepositoryInterface;

class CategoryService extends BaseService
{
    public function __construct(CategoryRepositoryInterface $repository)
    {
        $this->relations = ['products', 'children'];
        parent::__construct($repository);

    }

    public function getAll(bool $paginate = false, $columns = ['*'])
    {
        return $this->repository->query($columns)->with($this->relations)
            ->whereNull('parent_id')
            ->get();
    }

    public function getSubCategories()
    {
        return $this->repository->getWithFilters();
    }

}
