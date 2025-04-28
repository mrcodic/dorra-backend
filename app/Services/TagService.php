<?php

namespace App\Services;

use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

class TagService extends BaseService
{
    public function __construct(TagRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }

    public function getData(): JsonResponse
    {
        $categories = $this->repository
            ->query(['id', 'name','created_at'])
//            ->withCount(['templates', 'products'])
            ->withCount(['products'])
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
            ->addColumn('added_date', function ($category) {
                return $category->created_at?->format('d/n/Y');
            })
            ->addColumn('show_date', function ($category) {
                return $category->created_at?->format('Y-m-d');
            })

            ->addColumn('no_of_products', function ($category) {
                return $category->products_count;

            })
            ->addColumn('no_of_templates', function ($category) {
                return 5;
//                return $category->templates_count;

            })->make();
    }


}
