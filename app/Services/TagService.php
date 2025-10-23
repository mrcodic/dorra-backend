<?php

namespace App\Services;

use App\Enums\Template\StatusEnum;
use App\Models\Category;
use App\Models\Product;
use App\Models\Template;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

class TagService extends BaseService
{
    public function __construct(TagRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }

    public function getAll($relations = [], bool $paginate = false, $columns = ['*'], $perPage = 10, $counts = [])
    {
        $taggableType = request('taggable_type');
        $taggableId   = request('taggable_id');
        $locale       = app()->getLocale();
        $search       = trim(strtolower((string) request('search')));

        $mapModels = [
            'product'  => Product::class,
            'category' => Category::class,
        ];
        $mapRelations = [
            'product'  => 'products',
            'category' => 'categories',
        ];
        $relation = $mapRelations[$taggableType] ?? null;

        $query = $this->repository->query()
            ->with($relations)
            ->when($search !== '', function ($q) use ($locale, $search) {
                $q->whereRaw(
                    "LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?",
                    ["%{$search}%"]
                );
            });

        if ($taggableType && $taggableId && isset($mapModels[$taggableType]) && $relation) {
            $query->whereHas('templates', function ($t) use ($relation, $taggableId) {
                $t->whereStatus(StatusEnum::LIVE)
                    ->whereHas($relation, fn ($r) => $r->where("{$relation}.id", $taggableId));
            });

            $query->withCount([
                'templates as templates_count' => function ($t) use ($relation, $taggableId) {
                    $t->whereStatus(StatusEnum::LIVE)
                        ->whereHas($relation, fn ($r) => $r->where("{$relation}.id", $taggableId));
                },
            ]);
        } else {
            $query->withCount('templates');
        }

        return $paginate
            ? $query->paginate($perPage, $columns)
            : $query->get($columns);
    }


    public function getData(): JsonResponse
    {
        $locale = app()->getLocale();
        $tags = $this->repository
            ->query(['id', 'name', 'created_at'])
            ->withCount(['templates', 'products','categories'])
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
                return $tag->templates_count;
            })
            ->addColumn('action', function () {
                return [
                    'can_show' => (bool) auth()->user()->can('tags_show'),
                    'can_edit' => (bool) auth()->user()->can('tags_update'),
                    'can_delete' => (bool) auth()->user()->can('tags_delete'),
                ];
            })->make();
    }


}
