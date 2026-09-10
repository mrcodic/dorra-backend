<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\Ai\AiGenerationTypeEnum;
use App\Http\Controllers\Base\DashboardController;
use App\Http\Requests\Ai\QuickStoreAiGuideQuestionRequest;
use App\Http\Requests\Ai\QuickStoreAiStudioItemRequest;
use App\Http\Requests\Ai\QuickUpdateAiStudioItemRequest;
use App\Http\Requests\AiCategory\StoreAiCategoryRequest;
use App\Http\Requests\AiCategory\UpdateAiCategoryRequest;
use App\Models\AiStudioItem;
use App\Repositories\Interfaces\AiGuideQuestionRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Services\Ai\AiCategoryService;
use App\Services\Ai\AiGuideQuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class AiCategoryController extends DashboardController
{
    public function __construct(
        public AiCategoryService $aiCategoryService,
        public CategoryRepositoryInterface $categoryRepository,
        public AiGuideQuestionRepositoryInterface $aiGuideQuestionRepository,
    ) {
        parent::__construct($aiCategoryService);

        $this->storeRequestClass = new StoreAiCategoryRequest();
        $this->updateRequestClass = new UpdateAiCategoryRequest();

        $this->indexView = 'ai-categories.index';
        $this->createView = 'ai-categories.create';
        $this->editView = 'ai-categories.edit';

        $this->usePagination = true;
        $this->resourceTable = 'ai_categories';

        $this->methodRelations = [
            'edit' => ['category', 'questions', 'options', 'studioItems'],
            'store' => ['category', 'questions', 'options', 'studioItems'],
            'update' => ['category', 'questions', 'options', 'studioItems'],
        ];

        $categories = $this->categoryRepository->query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        $questions = $this->aiGuideQuestionRepository->query()
            ->where('is_active', true)
            ->with([
                'options' => fn($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // Do not filter by key or active status here anymore.
        // The same Add/Edit screen is now also the Studio Item management surface.
        $studioItems = AiStudioItem::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $associatedData = [
            'categories' => $categories,
            'questions' => $questions,
            'studioItems' => $studioItems,
        ];

        $this->assoiciatedData = [
            'create' => $associatedData,
            'edit' => $associatedData,
        ];
    }

    public function getData(): JsonResponse
    {
        return $this->aiCategoryService->getData();
    }

    public function quickStoreQuestion(
        QuickStoreAiGuideQuestionRequest $request,
        AiGuideQuestionService $aiGuideQuestionService
    ): JsonResponse {
        $question = $aiGuideQuestionService->storeResource(
            $request->validated(),
            relationsToLoad: ['options']
        );

        $question->load('options');

        $isColorPalette = $question->options->contains(
            fn($option) => !empty(data_get($option->ui_data, 'colors', []))
        );

        return Response::api(data: [
            'id' => $question->id,
            'key' => $question->key,
            'title' => $question->title,
            'prompt_label' => $question->prompt_label,
            'type' => $question->type?->value ?? $question->type,
            'type_label' => $question->type?->label() ?? (string) $question->type,
            'required' => (bool) $question->required,
            'sort_order' => (int) ($question->sort_order ?? 0),
            'isColorPalette' => $isColorPalette,
            'options' => $question->options->map(fn($option) => [
                'id' => $option->id,
                'value' => $option->value,
                'label' => $option->label,
                'colors' => data_get($option->ui_data, 'colors', []),
            ])->values()->all(),
        ]);
    }

    public function quickStoreStudioItem(QuickStoreAiStudioItemRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['key'] = $this->generateStudioItemKey((string) data_get($data, 'name.en', 'studio-item'));

        $studioItem = AiStudioItem::query()->create($data);

        return Response::api(
            data: $this->studioItemPayload($studioItem),
            message: 'Studio Item created successfully.'
        );
    }

    public function quickUpdateStudioItem(
        QuickUpdateAiStudioItemRequest $request,
        AiStudioItem $studioItem
    ): JsonResponse {
        $data = $request->validated();

        if (array_key_exists('settings', $data)) {
            $data['settings'] = array_replace(
                $studioItem->settings ?? [],
                $data['settings'] ?? []
            );
        }

        // Key is intentionally never changed on edit because it is an API/system identifier.
        unset($data['key']);

        $studioItem->update($data);
        $studioItem->refresh();

        return Response::api(
            data: $this->studioItemPayload($studioItem),
            message: 'Studio Item updated successfully.'
        );
    }

    private function studioItemPayload(AiStudioItem $studioItem): array
    {
        $generationType = $studioItem->generation_type;
        $generationTypeValue = $generationType instanceof AiGenerationTypeEnum
            ? $generationType->value
            : (string) $generationType;
        $generationTypeLabel = $generationType instanceof AiGenerationTypeEnum
            ? $generationType->label()
            : Str::headline($generationTypeValue);

        return [
            'id' => $studioItem->id,
            'key' => $studioItem->key,
            'name' => $studioItem->name,
            'name_en' => $studioItem->getTranslation('name', 'en', false),
            'name_ar' => $studioItem->getTranslation('name', 'ar', false),
            'description' => $studioItem->description,
            'description_en' => $studioItem->getTranslation('description', 'en', false),
            'description_ar' => $studioItem->getTranslation('description', 'ar', false),
            'generation_type' => $generationTypeValue,
            'generation_type_label' => $generationTypeLabel,
            'credits_cost' => (int) $studioItem->credits_cost,
            'sort_order' => (int) $studioItem->sort_order,
            'is_active' => (bool) $studioItem->is_active,
            'settings' => $studioItem->settings ?? [],
        ];
    }

    private function generateStudioItemKey(string $name): string
    {
        $base = Str::slug($name, '_') ?: 'studio_item';
        $key = $base;
        $counter = 2;

        while (AiStudioItem::query()->where('key', $key)->exists()) {
            $key = $base . '_' . $counter;
            $counter++;
        }

        return $key;
    }
}
