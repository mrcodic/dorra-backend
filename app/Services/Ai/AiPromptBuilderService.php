<?php

namespace App\Services\Ai;

use App\Enums\Ai\AiGuideQuestionTypeEnum;
use App\Repositories\Interfaces\AiCategoryRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AiPromptBuilderService
{
    public function __construct(
        private readonly AiCategoryRepositoryInterface $aiCategoryRepository,
        private readonly AiGenerationConfigService $aiGenerationConfigService,
    ) {
    }

    public function build(int $aiCategoryId, ?int $aiStudioItemId = null, array $answers = []): array
    {
        $aiCategory = $this->aiCategoryRepository
            ->query()
            ->where('enabled', true)
            ->with([
                'category',
                'studioItems' => fn($query) => $query->where('ai_studio_items.is_active', true),
            ])
            ->findOrFail($aiCategoryId);

        $studioItem = null;

        if ($aiStudioItemId !== null) {
            $studioItem = $aiCategory->studioItems
                ->firstWhere('id', $aiStudioItemId);

            if (!$studioItem) {
                throw ValidationException::withMessages([
                    'ai_studio_item_id' => [
                        'The selected AI Studio Item is not enabled for this AI Product.',
                    ],
                ]);
            }
        }

        $questions = $this->aiGenerationConfigService->getAssignedQuestions(
            $aiCategoryId,
            $aiStudioItemId
        );

        /*
         * Keep only questions that should currently be visible
         * according to their assignment-level condition.
         */
        $visibleQuestions = $this->filterVisibleQuestions(
            $questions,
            $answers
        );

        /*
         * Required validation and prompt building now operate only
         * on questions that are currently visible.
         *
         * Hidden question answers are ignored automatically.
         */
        $resolvedAnswers = $this->resolveAnswers(
            $visibleQuestions,
            $answers
        );

        $categorySettings = $aiCategory->settings ?? [];
        $studioSettings = $studioItem?->settings ?? [];

        $categoryName = $aiCategory->category?->name ?? '';
        $studioName = $studioItem?->name ?? $categoryName;

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
                'type' => $studioItem
                    ? ($studioItem->generation_type?->value ?? $studioItem->generation_type)
                    : null,

                'resolution' => $aiCategory->default_resolution,
                'aspect_ratio' => $aiCategory->aspect_ratio,
                'provider' => $aiCategory->provider,
                'model' => $aiCategory->model,

                'credits_cost' => (int) ($studioItem?->credits_cost ?? 1),

                'transparent_background' => (bool) data_get(
                    $categorySettings,
                    'transparent_background',
                    false
                ),

                'print_ready' => (bool) data_get(
                    $categorySettings,
                    'print_ready',
                    false
                ),
            ],

            'context' => [
                'ai_category_id' => $aiCategory->id,
                'ai_studio_item_id' => $studioItem?->id,
                'category_name' => $categoryName,
                'studio_item_name' => $studioItem?->name,
            ],

            'resolved_answers' => $resolvedAnswers
                ->values()
                ->all(),
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

    /**
     * Resolve which questions are visible based on:
     *
     * resolved_condition = [
     *     'parent_question_id' => 10,
     *     'parent_option_id' => 50,
     *     'operator' => 'selected',
     * ]
     *
     * Questions without a condition are always visible.
     *
     * If the parent conditional question itself is hidden,
     * its child questions are also hidden.
     */
    private function filterVisibleQuestions(
        Collection $questions,
        array $answers
    ): Collection {
        $visibleQuestionIds = collect();

        return $questions
            ->filter(function ($question) use (
                $questions,
                $answers,
                $visibleQuestionIds
            ) {
                $condition = $question->resolved_condition ?? null;

                /*
                 * Normal question:
                 * always visible.
                 */
                if (!$condition) {
                    $visibleQuestionIds->push(
                        (int) $question->id
                    );

                    return true;
                }

                $parentQuestionId = (int) data_get(
                    $condition,
                    'parent_question_id'
                );

                /*
                 * Invalid condition or parent question is itself hidden.
                 */
                if (
                    !$parentQuestionId
                    || !$visibleQuestionIds->contains($parentQuestionId)
                ) {
                    return false;
                }

                $parentQuestion = $questions->first(
                    fn($item) =>
                        (int) $item->id === $parentQuestionId
                );

                if (!$parentQuestion) {
                    return false;
                }

                $visible = $this->conditionMatches(
                    $condition,
                    $parentQuestion,
                    $answers
                );

                if ($visible) {
                    $visibleQuestionIds->push(
                        (int) $question->id
                    );
                }

                return $visible;
            })
            ->values();
    }

    /**
     * Determine whether one conditional rule matches.
     */
    private function conditionMatches(
        array $condition,
              $parentQuestion,
        array $answers
    ): bool {
        $parentKey = (string) $parentQuestion->key;

        /*
         * Parent has no answer yet.
         *
         * Child stays hidden.
         */
        if (!array_key_exists($parentKey, $answers)) {
            return false;
        }

        $answer = $answers[$parentKey];

        if ($this->isEmptyAnswer($answer)) {
            return false;
        }

        $parentOptionId = (int) data_get(
            $condition,
            'parent_option_id'
        );

        if (!$parentOptionId) {
            return false;
        }

        /*
         * The condition stores option ID,
         * but frontend answers use option VALUE.
         *
         * Resolve the option first so we can compare correctly.
         */
        $parentOption = $parentQuestion
            ->options
            ->first(
                fn($option) =>
                    (int) $option->id === $parentOptionId
            );

        if (!$parentOption) {
            return false;
        }

        $expectedValue = (string) $parentOption->value;

        /*
         * Supports both:
         *
         * SINGLE_SELECT:
         * "logo"
         *
         * MULTI_SELECT:
         * ["logo", "print"]
         */
        $selected = is_array($answer)
            ? collect($answer)
                ->filter(
                    fn($value) =>
                        is_string($value)
                        || is_numeric($value)
                )
                ->map(
                    fn($value) =>
                    (string) $value
                )
                ->contains($expectedValue)
            : (string) $answer === $expectedValue;

        return match (
        data_get(
            $condition,
            'operator',
            'selected'
        )
        ) {
            'not_selected' => !$selected,
            default => $selected,
        };
    }

    private function resolveAnswers(
        Collection $questions,
        array $answers
    ): Collection {
        $errors = [];
        $resolved = collect();

        foreach ($questions as $question) {
            $key = $question->key;
            $value = $answers[$key] ?? null;

            $required = (bool) (
                $question->resolved_required
                ?? $question->required
            );

            /*
             * Because this method receives only visible questions,
             * hidden required questions do not trigger validation.
             */
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
                            fn($item) =>
                            (string) $item
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
