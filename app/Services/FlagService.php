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
        $relations = request('type') == 'templates' ? ['templates.products'] : ['products.media'];
        return $this->repository->query()
            ->select($columns)
            ->with($relations)
            ->get();
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = $this->repository->create($validatedData);
        $model->templates()->attach($validatedData['templates']);
        $model->products()->attach($validatedData['products']);
        return $model->load($relationsToLoad);

    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $model = $this->repository->update($validatedData, $id);
        $model->templates()->sync($validatedData['templates']);
        $model->products()->sync($validatedData['products']);
        return $model->load($relationsToLoad);

    }

    public function getData(): JsonResponse
    {
        $locale = app()->getLocale();
        $flags = $this->repository
            ->query(['id', 'name', 'created_at'])
            ->withCount(['products', 'templates'])
            ->with(['products', 'templates'])
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
        return \Yajra\DataTables\DataTables::of($flags)
            ->addColumn('name', function ($flags) {
                return $flags->getTranslation('name', app()->getLocale());
            })
            ->addColumn('name_en', function ($flags) {
                return $flags->getTranslation('name', 'en');
            })
            ->addColumn('name_ar', function ($flags) {
                return $flags->getTranslation('name', 'ar');
            })
            ->addColumn('added_date', function ($flags) {
                return $flags->created_at?->format('d/n/Y');
            })
            ->addColumn('show_date', function ($flags) {
                return $flags->created_at?->format('Y-m-d');
            })
            ->addColumn('no_of_products', function ($flags) {
                return $flags->products_count;

            })->addColumn('template_ids', function ($flags) {
                return $flags->templates->pluck('id')->toArray();
            })->addColumn('product_ids', function ($flags) {
                return $flags->products->pluck('id')->toArray();
            })
            ->addColumn('no_of_templates', function ($tag) {
                return $tag->templates_count;
            })->addColumn('action', function () {
                return [
                    'can_show' => (bool) auth()->user()->can('tags_show'),
                    'can_edit' => (bool) auth()->user()->can('tags_update'),
                    'can_delete' => (bool) auth()->user()->can('tags_delete'),
                ];
            })->make();
    }


}
