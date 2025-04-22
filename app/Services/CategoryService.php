<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Yajra\DataTables\DataTables;

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

    public function getData()
    {
        $categories = $this->repository
            ->query(['id', 'name', 'created_at'])
            ->with(['products', 'children'])
            ->withCount(['children', 'products'])
            ->whereNull('parent_id')
            ->latest();
        return DataTables::of($categories)
            ->addColumn('name', function ($category) {
                return $category->getTranslation('name', app()->getLocale());
            })
            ->addColumn('added_date', function ($category) {
                return $category->created_at?->format('j/n/Y');
            })
            ->addColumn('sub_categories', function ($category) {
                return $category->children_count;
            })
            ->addColumn('no_of_products', function ($category) {
                return $category->products_count;

            })->make();
    }
}
