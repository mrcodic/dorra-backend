<?php

namespace App\Services\Ai;

use App\Enums\Ai\AiGuideQuestionTypeEnum;
use App\Models\AiCategory;
use App\Models\AiGuideQuestion;
use App\Models\AiStudioItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AiCategoryGenerationConfigService
{
    public function sync(int $aiCategoryId, array $questions, array $studioItemIds): void
    {
        DB::transaction(function () use ($aiCategoryId, $questions, $studioItemIds) {
            $aiCategory = AiCategory::query()->lockForUpdate()->findOrFail($aiCategoryId);

            $this->syncStudioItems($aiCategory, $studioItemIds);
            $this->syncQuestions($aiCategory, $questions);
        });
    }

    private function syncStudioItems(AiCategory $aiCategory, array $studioItemIds): void
    {
        $submittedIds = collect($studioItemIds)
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        $validIds = AiStudioItem::query()
            ->where('is_active', true)
            ->whereIn('id', $submittedIds)
            ->pluck('id')
            ->map(fn($id) => (int) $id);

        if ($validIds->count() !== $submittedIds->count()) {
            throw ValidationException::withMessages([
                'studio_items' => ['One or more selected AI Studio Items are unavailable.'],
            ]);
        }

        $sync = [];

        foreach ($submittedIds as $index => $id) {
            $sync[$id] = ['sort_order' => $index];
        }

        $aiCategory->studioItems()->sync($sync);
    }

    private function syncQuestions(AiCategory $aiCategory, array $questions): void
    {
        $selectedRows = collect($questions)
            ->filter(fn($row) => (bool) data_get($row, 'selected', false))
            ->values();

        if ($selectedRows->isEmpty()) {
            $aiCategory->questions()->sync([]);
            $aiCategory->options()->sync([]);
            return;
        }

        $questionIds = $selectedRows
            ->pluck('question_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        $validQuestions = AiGuideQuestion::query()
            ->where('is_active', true)
            ->whereIn('id', $questionIds)
            ->with('options')
            ->get()
            ->keyBy('id');

        if ($validQuestions->count() !== $questionIds->count()) {
            throw ValidationException::withMessages([
                'questions' => ['One or more selected questions are unavailable.'],
            ]);
        }

        $questionSync = [];
        $optionSync = [];

        foreach ($selectedRows as $row) {
            $questionId = (int) $row['question_id'];
            $question = $validQuestions->get($questionId);

            $questionSync[$questionId] = [
                'required' => (bool) data_get($row, 'required', $question->required),
                'is_active' => true,
                'sort_order' => (int) data_get($row, 'sort_order', $question->sort_order ?? 0),
                'options_mode' => null,
            ];

            if (!$this->supportsOptions($question->type)) {
                continue;
            }

            $allowedOptionIds = $question->options
                ->pluck('id')
                ->map(fn($id) => (int) $id);

            $selectedOptionIds = collect(data_get($row, 'options', []))
                ->map(fn($id) => (int) $id)
                ->unique()
                ->intersect($allowedOptionIds)
                ->values();

            foreach ($selectedOptionIds as $sortOrder => $optionId) {
                $optionSync[$optionId] = [
                    'is_active' => true,
                    'sort_order' => $sortOrder,
                    'prompt_value_override' => null,
                ];
            }
        }

        $aiCategory->questions()->sync($questionSync);
        $aiCategory->options()->sync($optionSync);
    }

    private function supportsOptions(AiGuideQuestionTypeEnum $type): bool
    {
        return in_array($type, [
            AiGuideQuestionTypeEnum::SINGLE_SELECT,
            AiGuideQuestionTypeEnum::MULTI_SELECT,
        ], true);
    }
}
