<?php

namespace App\Services\Ai;

use App\Enums\Ai\AiGuideQuestionTypeEnum;
use App\Repositories\Interfaces\AiCategoryRepositoryInterface;
use App\Repositories\Interfaces\AiStudioItemRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AiPromptBuilderService
{
    public function __construct(
        private readonly AiCategoryRepositoryInterface $aiCategoryRepository,
        private readonly AiStudioItemRepositoryInterface $aiStudioItemRepository,
        private readonly AiGenerationConfigService $aiGenerationConfigService,
    ) {
    }

    public function build(int $aiCategoryId, int $aiStudioItemId, array $answers = []): array
    {
        $aiCategory = $this->aiCategoryRepository
            ->query()
            ->where('enabled', true)
            ->with('category')
            ->findOrFail($aiCategoryId);

        $studioItem = $this->aiStudioItemRepository
            ->query()
            ->where('is_active', true)
            ->findOrFail($aiStudioItemId);

        $questions = $this->aiGenerationConfigService
            ->getAssignedQuestions(
                $aiCategoryId,
                $aiStudioItemId
            );

        $resolvedAnswers = $this->resolveAnswers(
            $questions,
            $answers
        );

        $categorySettings = $aiCategory->settings ?? [];
        $studioSettings = $studioItem->settings ?? [];

        $categoryName = $aiCategory->category?->name ?? '';
        $studioName = $studioItem->name ?? '';

        $prompt = $this->buildPrompt(
            categoryName: $categoryName,
            studioName: $studioName,
            resolvedAnswers: $resolvedAnswers,
            categorySettings: $categorySettings,
            studioSettings: $studioSettings,
        );

        $negativePrompt = $this->buildNegativePrompt(
            $categorySettings,
            $studioSettings
        );

        return [
            'prompt' => $prompt,
            'negative_prompt' => $negativePrompt,

            'generation' => [
                'type' =>
                    $studioItem->generation_type?->value
                    ?? $studioItem->generation_type,

                'resolution' =>
                    $aiCategory->default_resolution
                        ?: $studioItem->default_resolution,

                'aspect_ratio' =>
                    $aiCategory->aspect_ratio
                        ?: $studioItem->aspect_ratio,

                'provider' =>
                    $aiCategory->provider
                        ?: $studioItem->provider,

                'model' =>
                    $aiCategory->model
                        ?: $studioItem->model,

                'credits_cost' =>
                    $aiCategory->credits_cost
                        ?: $studioItem->credits_cost,

                'transparent_background' => (bool) data_get(
                    $categorySettings,
                    'transparent_background',
                    data_get(
                        $studioSettings,
                        'transparent_background',
                        false
                    )
                ),

                'print_ready' => (bool) data_get(
                    $categorySettings,
                    'print_ready',
                    false
                ),
            ],

            'context' => [
                'ai_category_id' => $aiCategory->id,
                'ai_studio_item_id' => $studioItem->id,
                'category_name' => $categoryName,
                'studio_item_name' => $studioName,
            ],

            'resolved_answers' =>
                $resolvedAnswers->values()->all(),
        ];
    }

    private function buildPrompt(
        string $categoryName,
        string $studioName,
        Collection $resolvedAnswers,
        array $categorySettings,
        array $studioSettings
    ): string {
        $sections = [];

        $sections[] = trim("
Create a professional {$studioName} design.

The design is intended for:
{$categoryName}.
        ");

        if ($resolvedAnswers->isNotEmpty()) {
            $sections[] =
                "Design requirements:\n"
                . $resolvedAnswers
                    ->map(
                        fn($answer) =>
                            '- '
                            . $answer['prompt_label']
                            . ': '
                            . $answer['prompt_value']
                    )
                    ->implode("\n");
        }

        $studioInstructions = trim(
            (string) data_get(
                $studioSettings,
                'prompt_instructions',
                ''
            )
        );

        if ($studioInstructions !== '') {
            $sections[] =
                "Studio instructions:\n"
                . $studioInstructions;
        }

        $productContext = trim(
            (string) data_get(
                $categorySettings,
                'product_context',
                ''
            )
        );

        if ($productContext !== '') {
            $sections[] =
                "Product context:\n"
                . $productContext;
        }

        $productionRequirements =
            $this->buildProductionRequirements(
                $categorySettings
            );

        if ($productionRequirements !== '') {
            $sections[] =
                "Production requirements:\n"
                . $productionRequirements;
        }

        $sections[] = implode("\n", [
            'Create one coherent finished artwork.',
            'Follow the requested style and user choices precisely.',
            'Do not introduce unrelated text, objects, decorations, or concepts.',
            'Do not generate a product mockup unless explicitly requested.',
        ]);

        return collect($sections)
            ->map(fn($section) => trim($section))
            ->filter()
            ->implode("\n\n");
    }

    private function resolveAnswers(Collection $questions, array $answers): Collection
    {
        $errors = [];
        $resolved = collect();

        foreach ($questions as $question) {
            $key = $question->key;
            $value = $answers[$key] ?? null;

            $required = (bool) (
                $question->resolved_required
                ?? $question->required
            );

            if (
                $required
                && $this->isEmptyAnswer($value)
            ) {
                $errors["answers.{$key}"][] =
                    "{$question->title} is required.";

                continue;
            }

            if ($this->isEmptyAnswer($value)) {
                continue;
            }

            $promptLabel = trim(
                (string) (
                $question->prompt_label
                    ?: $question->title
                )
            );

            switch ($question->type) {
                case AiGuideQuestionTypeEnum::TEXT:
                case AiGuideQuestionTypeEnum::TEXTAREA:

                    if (
                        !is_string($value)
                        && !is_numeric($value)
                    ) {
                        $errors["answers.{$key}"][] =
                            'Invalid answer.';

                        continue 2;
                    }

                    $resolvedValue = trim(
                        (string) $value
                    );

                    break;

                case AiGuideQuestionTypeEnum::SINGLE_SELECT:

                    if (
                        !is_string($value)
                        && !is_numeric($value)
                    ) {
                        $errors["answers.{$key}"][] =
                            'Please select one valid option.';

                        continue 2;
                    }

                    $option = $this->resolveOption(
                        $question,
                        (string) $value
                    );

                    if (!$option) {
                        $errors["answers.{$key}"][] =
                            'The selected option is not available.';

                        continue 2;
                    }

                    $resolvedValue =
                        $option->prompt_value
                            ?: $option->label;

                    break;

                case AiGuideQuestionTypeEnum::MULTI_SELECT:

                    if (!is_array($value)) {
                        $errors["answers.{$key}"][] =
                            'Please select valid options.';

                        continue 2;
                    }

                    $selectedValues = collect($value)
                        ->filter(
                            fn($item) =>
                                is_string($item)
                                || is_numeric($item)
                        )
                        ->map(
                            fn($item) => (string) $item
                        )
                        ->unique()
                        ->values();

                    $optionValues = [];

                    foreach ($selectedValues as $selectedValue) {
                        $option = $this->resolveOption(
                            $question,
                            $selectedValue
                        );

                        if (!$option) {
                            $errors["answers.{$key}"][] =
                                "Invalid option: {$selectedValue}.";

                            continue;
                        }

                        $optionValues[] =
                            $option->prompt_value
                                ?: $option->label;
                    }

                    if (
                        isset(
                            $errors["answers.{$key}"]
                        )
                    ) {
                        continue 2;
                    }

                    $resolvedValue = implode(
                        ', ',
                        $optionValues
                    );

                    break;

                default:

                    $errors["answers.{$key}"][] =
                        'Unsupported question type.';

                    continue 2;
            }

            $resolved->push([
                'question_id' => $question->id,
                'question_key' => $question->key,
                'title' => $question->title,
                'prompt_label' => $promptLabel,
                'prompt_value' => $resolvedValue,
            ]);
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages(
                $errors
            );
        }

        return $resolved;
    }

    private function resolveOption(
        $question,
        string $value
    ) {
        $assignedOptionIds = collect(
            $question->assigned_option_ids ?? []
        )
            ->map(fn($id) => (int) $id);

        return $question
            ->options
            ->filter(
                fn($option) =>
                $assignedOptionIds->contains(
                    (int) $option->id
                )
            )
            ->first(
                fn($option) =>
                    (string) $option->value
                    === $value
            );
    }

    private function buildProductionRequirements(
        array $settings
    ): string {
        $requirements = [];

        $custom = trim(
            (string) data_get(
                $settings,
                'production_requirements',
                ''
            )
        );

        if ($custom !== '') {
            $requirements[] = $custom;
        }

        if (
            data_get(
                $settings,
                'print_ready',
                false
            )
        ) {
            $requirements[] =
                'Create production-ready artwork suitable for printing.';

            $requirements[] =
                'Keep clean printable edges.';

            $requirements[] =
                'Avoid unnecessary tiny details that may not reproduce well in print.';

            $requirements[] =
                'Generate the artwork itself, not a product mockup.';
        }

        if (
            data_get(
                $settings,
                'transparent_background',
                false
            )
        ) {
            $requirements[] =
                'Keep the artwork isolated from its background for transparent output processing.';
        }

        $orientation = data_get(
            $settings,
            'orientation'
        );

        if ($orientation) {
            $requirements[] =
                "Composition orientation: {$orientation}.";
        }

        return implode(
            "\n",
            $requirements
        );
    }

    private function buildNegativePrompt(
        array $categorySettings,
        array $studioSettings
    ): string {
        return collect([
            data_get(
                $studioSettings,
                'negative_rules'
            ),

            data_get(
                $categorySettings,
                'negative_rules'
            ),

            'low quality',
            'distorted composition',
            'unrelated elements',
            'unwanted product mockup',
        ])
            ->map(fn($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->implode("\n");
    }

    private function isEmptyAnswer(
        mixed $value
    ): bool {
        if ($value === null) {
            return true;
        }

        if (
            is_string($value)
            && trim($value) === ''
        ) {
            return true;
        }

        return is_array($value)
            && empty($value);
    }
}
