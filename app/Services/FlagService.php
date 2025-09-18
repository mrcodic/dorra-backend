<?php

namespace App\Services;

use App\Repositories\Interfaces\FlagRepositoryInterface;
use Illuminate\Http\JsonResponse;


class FlagService extends BaseService
{

    public function __construct(FlagRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }

    public function getAll($relations = [], bool $paginate = false, $columns = ['*'], $perPage = 10, $counts = [])
    {
        $relations = request('type') == 'templates' ? ['templates.products'] : ['products'];
        return $this->repository->query()
            ->select($columns)
            ->with($relations)
            ->get();
    }

    public function getData(): JsonResponse
    {
        $locale = app()->getLocale();
        $tags = $this->repository
            ->query(['id', 'name', 'created_at'])

            ->when(request()->filled('search_value'), function ($query) use ($locale) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", [
                        '%' . strtolower(request('search_value')) . '%'
                    ]);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })->when(request()->filled('created_at'), function ($query) {
                $query->orderBy('created_at', request('created_at'));
            })
            ->latest();
        return \Yajra\DataTables\DataTables::of($tags)
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
                return $tag->templates_count;
            })->make();
    }


}
