<?php

namespace App\Services;


use App\Repositories\Interfaces\IndustryRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

class IndustryService extends BaseService
{
    public function __construct(IndustryRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }

    public function getData(): JsonResponse
    {
        $locale = app()->getLocale();
        $tags = $this->repository
            ->query(['id', 'name', 'created_at'])
            ->whereNull('parent_id')
            ->withCount(['templates'])
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
            ->addColumn('no_of_templates', function ($tag) {
                return $tag->templates_count;
            })
            ->addColumn('action', function () {
                return [
                    'can_show' => (bool) auth()->user()->can('industries_show'),
                    'can_edit' => (bool) auth()->user()->can('industries_update'),
                    'can_delete' => (bool) auth()->user()->can('industries_delete'),
                ];
            })->make();
    }

    public function getSubIndustryData(): JsonResponse
    {
        $locale = app()->getLocale();
        $tags = $this->repository
            ->query(['id', 'name','parent_id', 'created_at'])
            ->withCount(['templates'])
            ->whereNotNull('parent_id')
            ->with(['parent'])
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
            ->addColumn('action', function () {
                return [
                    'can_show' => (bool) auth()->user()->can('sub-industries_show'),
                    'can_edit' => (bool) auth()->user()->can('sub-industries_update'),
                    'can_delete' => (bool) auth()->user()->can('sub-industries_delete'),
                ];
            })->make();
    }

    public function getAll($relations = [], bool $paginate = false, $columns = ['*'], $perPage = 10, $counts = [])
    {
        return $this->repository->query()
                ->with(['children'])
            ->withCount(['templates'])
            ->whereNull('parent_id')
           ->latest()->get();
    }

    public function getSubIndustries($request)
    {
        return $this->repository->query()
            ->withCount(['templates'])
            ->whereNotNull('parent_id')
            ->when($request->filled('industry_id'), function ($query) use ($request) {
                return $query->where('parent_id', $request->industry_id);
            })->latest()->get();
    }

}
