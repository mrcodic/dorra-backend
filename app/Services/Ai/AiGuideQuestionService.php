<?php

namespace App\Services\Ai;

use App\Enums\Ai\AiGuideQuestionTypeEnum;
use App\Repositories\Interfaces\AiGuideQuestionOptionRepositoryInterface;
use App\Repositories\Interfaces\AiGuideQuestionRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class AiGuideQuestionService extends BaseService
{
    public function __construct(
        AiGuideQuestionRepositoryInterface $repository,
        public AiGuideQuestionOptionRepositoryInterface $optionRepository
    ) {
        parent::__construct($repository);
    }

    public function getData(): JsonResponse
    {
        $locale = app()->getLocale();

        $questions = $this->repository->query()
            ->withCount('options')
            ->when(request()->filled('search_value'), function ($query) use ($locale) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $search = strtolower(request('search_value'));

                    $query->where(function ($query) use ($search, $locale) {
                        $query->whereRaw(
                            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.\"{$locale}\"'))) LIKE ?",
                            ["%{$search}%"]
                        )->orWhereRaw(
                            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(prompt_label, '$.\"{$locale}\"'))) LIKE ?",
                            ["%{$search}%"]
                        );
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->when(request()->filled('type'), fn($query) => $query->where('type', request('type')))
            ->when(request()->filled('is_active'), fn($query) => $query->where('is_active', request('is_active')))
            ->orderBy('sort_order')
            ->orderBy('id');

        return DataTables::of($questions)
            ->editColumn('title', fn($question) => $question->title)
            ->editColumn('prompt_label', fn($question) => $question->prompt_label)
            ->editColumn('type', fn($question) => $question->type->value)
            ->addColumn('type_label', fn($question) => $question->type->label())
            ->addColumn('action', fn() => [
                'can_edit' => (bool) auth()->user()->hasPermissionTo('ai-guide-questions_update'),
                'can_delete' => (bool) auth()->user()->hasPermissionTo('ai-guide-questions_delete'),
            ])
            ->make(true);
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        return $this->handleTransaction(function () use ($validatedData, $relationsToLoad) {
            $options = Arr::pull($validatedData, 'options', []);
            $validatedData['key'] = (string) Str::ulid();

            $question = $this->repository->create($validatedData);

            $this->syncOptions($question->id, $question->type, $options);

            return $question->load($relationsToLoad);
        });
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        return $this->handleTransaction(function () use ($validatedData, $id, $relationsToLoad) {
            $options = Arr::pull($validatedData, 'options', []);

            $question = $this->repository->update($validatedData, $id);

            $this->syncOptions($question->id, $question->type, $options);

            return $question->load($relationsToLoad);
        });
    }

    private function syncOptions(int $questionId, AiGuideQuestionTypeEnum $type, array $options): void
    {
        if (!$this->supportsOptions($type)) {
            $this->deleteQuestionOptions($questionId);
            return;
        }

        $existingOptions = $this->optionRepository->query()
            ->where('ai_guide_question_id', $questionId)
            ->get()
            ->keyBy('id');

        $submittedIds = [];

        foreach (array_values($options) as $index => $option) {
            $optionId = isset($option['id']) ? (int) $option['id'] : null;
            $existing = $optionId ? $existingOptions->get($optionId) : null;

            $data = $this->prepareOptionData(
                questionId: $questionId,
                option: $option,
                index: $index,
                existing: $existing
            );

            if ($existing) {
                $existing->update($data);
                $submittedIds[] = $existing->id;
                continue;
            }

            $model = $this->optionRepository->create($data);
            $submittedIds[] = $model->id;
        }

        $query = $this->optionRepository->query()
            ->where('ai_guide_question_id', $questionId);

        if ($submittedIds) {
            $query->whereNotIn('id', $submittedIds);
        }

        $query->delete();
    }

    private function prepareOptionData(int $questionId, array $option, int $index, $existing = null): array
    {
        $uiData = $this->normalizeUiData($option['ui_data'] ?? null);
        $colors = $uiData['colors'] ?? [];
        $isPalette = !empty($colors);

        $label = is_array($option['label'] ?? null) ? $option['label'] : [];
        $promptValue = is_array($option['prompt_value'] ?? null) ? $option['prompt_value'] : [];

        if ($isPalette) {
            [$label, $promptValue] = $this->preparePaletteContent(
                $label,
                $promptValue,
                $colors,
                $index
            );
        }

        $englishLabel = trim((string) ($label['en'] ?? ''));

        if (!$isPalette && $englishLabel === '') {
            throw ValidationException::withMessages([
                "options.{$index}.label.en" => ['English label is required.'],
            ]);
        }

        /*
         * Existing values must remain stable.
         * Changing an admin label must not change the API identifier.
         */
        $value = $existing?->value ?: $this->generateOptionValue(
            $questionId,
            $englishLabel ?: "color_palette_" . ($index + 1)
        );

        return [
            'ai_guide_question_id' => $questionId,
            'value' => $value,
            'label' => $label,
            'prompt_value' => $promptValue ?: null,
            'ui_data' => $uiData,
            'is_active' => (bool) ($option['is_active'] ?? true),
            'sort_order' => $index,
        ];
    }

    private function preparePaletteContent(array $label, array $promptValue, array $colors, int $index): array
    {
        $number = $index + 1;
        $colorsText = implode(', ', $colors);

        /*
         * Frontend does not need to send label/prompt_value for palettes.
         * Backend generates safe defaults.
         */
        $label['en'] = trim((string) ($label['en'] ?? '')) ?: "Color Palette {$number}";
        $label['ar'] = trim((string) ($label['ar'] ?? '')) ?: "لوحة ألوان {$number}";

        $defaultPrompt = "Use this exact color palette: {$colorsText}";

        $promptValue['en'] = trim((string) ($promptValue['en'] ?? '')) ?: $defaultPrompt;
        $promptValue['ar'] = trim((string) ($promptValue['ar'] ?? '')) ?: $defaultPrompt;

        return [$label, $promptValue];
    }

    private function normalizeUiData(?array $uiData): ?array
    {
        if (!$uiData) {
            return null;
        }

        /*
         * Whitelist supported UI metadata.
         * Do not store arbitrary frontend JSON.
         */
        $colors = collect($uiData['colors'] ?? [])
            ->filter(fn($color) => is_string($color))
            ->map(fn($color) => strtoupper(trim($color)))
            ->filter(fn($color) => preg_match('/^#[0-9A-F]{6}$/', $color))
            ->unique()
            ->values()
            ->all();

        return $colors ? ['colors' => $colors] : null;
    }

    private function supportsOptions(AiGuideQuestionTypeEnum $type): bool
    {
        return in_array($type, [
            AiGuideQuestionTypeEnum::SINGLE_SELECT,
            AiGuideQuestionTypeEnum::MULTI_SELECT,
        ], true);
    }

    private function deleteQuestionOptions(int $questionId): void
    {
        $this->optionRepository->query()
            ->where('ai_guide_question_id', $questionId)
            ->delete();
    }

    private function generateOptionValue(int $questionId, string $label): string
    {
        $base = Str::slug($label, '_') ?: 'option';
        $value = $base;
        $counter = 2;

        while ($this->optionValueExists($questionId, $value)) {
            $value = "{$base}_{$counter}";
            $counter++;
        }

        return $value;
    }

    private function optionValueExists(int $questionId, string $value): bool
    {
        return $this->optionRepository->query()
            ->where('ai_guide_question_id', $questionId)
            ->where('value', $value)
            ->exists();
    }
}
