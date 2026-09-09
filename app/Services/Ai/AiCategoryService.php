<?php

namespace App\Services\Ai;

use App\Repositories\Interfaces\AiCategoryRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AiCategoryService extends BaseService
{
    public function __construct(
        AiCategoryRepositoryInterface $repository,
        private readonly AiCategoryGenerationConfigService $generationConfigService
    ) {
        parent::__construct($repository);
    }

    public function getData(): JsonResponse
    {
        $aiCategories = $this->repository->query()
            ->with(['category', 'studioItems'])
            ->withCount('questions')
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
            ->when(request()->filled('enabled'), fn($query) => $query->where('enabled', request('enabled')))
            ->orderBy('sort_order')
            ->orderBy('id');

        return DataTables::of($aiCategories)
            ->addColumn('category_name', fn($aiCategory) => $aiCategory->category?->name)
            ->addColumn('studio_items', fn($aiCategory) => $aiCategory->studioItems
                ->pluck('name')
                ->filter()
                ->values()
                ->all()
            )
            ->addColumn('action', fn() => [
                'can_edit' => (bool) auth()->user()->hasPermissionTo('ai-categories_update'),
                'can_delete' => (bool) auth()->user()->hasPermissionTo('ai-categories_delete'),
            ])
            ->make(true);
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        return DB::transaction(function () use ($validatedData, $relationsToLoad) {
            $questions = Arr::pull($validatedData, 'questions', []);
            $studioItems = Arr::pull($validatedData, 'studio_items', []);

            $aiCategory = $this->repository->create($validatedData);

            $this->generationConfigService->sync(
                $aiCategory->id,
                $questions,
                $studioItems
            );

            return $aiCategory->load(array_unique(array_merge(
                $relationsToLoad,
                ['category', 'questions', 'options', 'studioItems']
            )));
        });
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        return DB::transaction(function () use ($validatedData, $id, $relationsToLoad) {
            $questions = Arr::pull($validatedData, 'questions', []);
            $studioItems = Arr::pull($validatedData, 'studio_items', []);

            $aiCategory = $this->repository->update($validatedData, $id);

            $this->generationConfigService->sync(
                $aiCategory->id,
                $questions,
                $studioItems
            );

            return $aiCategory->load(array_unique(array_merge(
                $relationsToLoad,
                ['category', 'questions', 'options', 'studioItems']
            )));
        });
    }

    public function getActiveCategories(bool $paginate = false, int $perPage = 15)
    {
        $query = $this->repository->query()
            ->where('enabled', true)
            ->with(['category', 'studioItems'])
            ->orderBy('sort_order')
            ->orderBy('id');

        return $paginate
            ? $query->paginate($perPage)
            : $query->get();
    }
}
