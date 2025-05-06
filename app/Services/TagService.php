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
        $tags = $this->repository
            ->query(['id', 'name','created_at'])
//            ->withCount(['templates', 'products'])
            ->withCount(['products'])
            ->when(request()->filled('search_value'), function ($query) {
                $locale = app()->getLocale();
                $search = request('search_value');
                $query->where("name->{$locale}", 'LIKE', "%{$search}%");
            })
            ->latest();
        return DataTables::of($tags)
            ->addColumn('name', function ($tag) {
                return $tag->getTranslation('name', app()->getLocale());
            })
            ->addColumn('name_en', function ($tag) {
                return $tag->getTranslation('name', 'en');
            })
            ->addColumn('name_ar', function ($tag) {
                return $tag->getTranslation('name', 'ar');
            })
            ->addColumn('added_date', function ($tag) {
                return $tag->created_at?->format('d/n/Y');
            })
            ->addColumn('show_date', function ($tag) {
                return $tag->created_at?->format('Y-m-d');
            })

            ->addColumn('no_of_products', function ($tag) {
                return $tag->products_count;

            })
            ->addColumn('no_of_templates', function ($tag) {
                return 0;
//                return $tag->templates_count;

            })->make();
    }


}
