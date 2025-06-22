<?php

namespace App\Services;


use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

class CategoryService extends BaseService
{
    public function __construct(CategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }


    public function getSubCategories()
    {
        return $this->repository->getWithFilters();
    }

    public function getData(): JsonResponse
    {
        $categories = $this->repository
            ->query(['id', 'name', 'description', 'created_at'])
            ->with(['products', 'children'])
            ->withCount(['children', 'products'])
            ->when(request()->filled('search_value'), function ($query) {
                $locale = app()->getLocale();
                $search = request('search_value');
                $query->where("name->{$locale}", 'LIKE', "%{$search}%");
            })->when(request()->filled('created_at'), function ($query) {
                $query->orderBy('created_at', request('created_at'));
            })
            ->whereNull('parent_id')
            ->latest();

        return DataTables::of($categories)
            ->addColumn('name', function ($category) {
                return $category->getTranslation('name', app()->getLocale());
            })
            ->addColumn('name_en', function ($category) {
                return $category->getTranslation('name', 'en');
            })
            ->addColumn('name_ar', function ($category) {
                return $category->getTranslation('name', 'ar');
            })
            ->addColumn('description_en', function ($category) {
                return $category->getTranslation('description', 'en');
            })
            ->addColumn('description_ar', function ($category) {
                return $category->getTranslation('description', 'ar');
            })
            ->addColumn('image', function ($category) {
                return $category->getFirstMediaUrl('categories');
            })
            ->addColumn('imageId', function ($category) {
                return $category->getFirstMedia('categories')?->id;
            })
            ->addColumn('added_date', function ($category) {
                return $category->created_at?->format('d/n/Y');
            })
            ->addColumn('show_date', function ($category) {
                return $category->created_at?->format('Y-m-d');
            })
            ->addColumn('sub_categories', function ($category) {
                return $category->children_count;
            })
            ->addColumn('no_of_products', function ($category) {
                return $category->products_count;
            })
            ->make(true);
    }


    public function getSubCategoryData(): JsonResponse
    {
        $categories = $this->repository
            ->query(['id', 'name', 'parent_id',  'created_at'])
            ->with(['parent'])
            ->withCount(['subCategoryProducts'])
            ->whereNotNull('parent_id')
            ->when(request()->filled('search_value'), function ($query) {
                $locale = app()->getLocale();
                $search = request('search_value');
                $query->where("name->{$locale}", 'LIKE', "%{$search}%");
            })->when(request()->filled('created_at'), function ($query) {
                $query->orderBy('created_at', request('created_at'));
            })
            ->latest();
        return DataTables::of($categories)
            ->addColumn('name', function ($category) {
                return $category->getTranslation('name', app()->getLocale());
            })
            ->addColumn('name_en', function ($category) {
                return $category->getTranslation('name', 'en');
            })
            ->addColumn('name_ar', function ($category) {
                return $category->getTranslation('name', 'ar');
            })
            ->addColumn('parent_name', function ($category) {
                return $category->parent->getTranslation('name',app()->getLocale());
            })
            ->addColumn('added_date', function ($category) {
                return $category->created_at?->format('d/n/Y');
            })
            ->addColumn('show_date', function ($category) {
                return $category->created_at?->format('Y-m-d');
            })
            ->addColumn('no_of_products', function ($category) {
                return $category->products_count;
            })->make();
    }
}
