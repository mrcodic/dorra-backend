<?php

namespace App\Services\Ai;

use App\Repositories\Interfaces\AiCategoryRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class AiCategoryService extends BaseService
{
    public function __construct(AiCategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getData(): JsonResponse
    {
        $aiCategories = $this->repository
            ->query()
            ->with(['category', 'promptTemplate'])
            ->when(request()->filled('search_value'), function ($query) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $search = request('search_value');

                    $query->whereHas('category', function ($query) use ($search) {
                        $query->where('name', 'LIKE', "%{$search}%");
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->when(request()->filled('generation_type'), function ($query) {
                $query->where('generation_type', request('generation_type'));
            })
            ->when(request()->filled('enabled'), function ($query) {
                $query->where('enabled', request('enabled'));
            })
            ->orderBy('sort_order')
            ->orderBy('id');

        return DataTables::of($aiCategories)
            ->addColumn('category_name', function ($aiCategory) {
                return $aiCategory->category?->name;
            })
            ->addColumn('prompt_template_name', function ($aiCategory) {
                return $aiCategory->promptTemplate?->name ?: '-';
            })
            ->editColumn('generation_type', function ($aiCategory) {
                return $aiCategory->generation_type->value;
            })
            ->addColumn('generation_type_label', function ($aiCategory) {
                return $aiCategory->generation_type->label();
            })
            ->addColumn('action', function () {
                return [
                    'can_edit' => (bool) auth()->user()->hasPermissionTo('ai-categories_update'),
                    'can_delete' => (bool) auth()->user()->hasPermissionTo('ai-categories_delete'),
                ];
            })
            ->make(true);
    }
}
