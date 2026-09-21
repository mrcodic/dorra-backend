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
Create a professional custom design.

Context (for understanding only — do not render this as text in the artwork):
- Product type: {$studioName}
- Category: {$categoryName}
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
            'Do not write, spell out, or render the product type or category name (or any letters from them) as text anywhere in the artwork.',
            'Do not include any written text in the artwork unless explicitly requested in the design requirements above.',
        ]);

        return collect($sections)
            ->map(fn($section) => trim($section))
            ->filter()
            ->implode("\n\n");
    }

    /**
     * Resolve which questions are visible.
     *
     * resolved_conditions uses:
     *
     * [
     *     [
     *         'parent_question_id' => 10,
     *         'parent_option_ids' => [50, 51],
     *         'operator' => 'selected',
     *     ],
     *     [
     *         'parent_question_id' => 20,
     *         'parent_option_ids' => [80, 81],
     *         'operator' => 'selected',
     *     ],
     * ]
     *
     * Same rule:
     *     option IDs are OR.
     *
     * Different rules:
     *     rules are AND.
     *
     * The existing sort-order rule still guarantees that parent
     * questions are evaluated before their child question.
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
                $conditions = collect(
                    $question->resolved_conditions ?? []
                );

                /*
                 * Backward compatibility with the old single condition.
                 */
                if (
                    $conditions->isEmpty()
                    && !empty($question->resolved_condition)
                ) {
                    $legacy = $question->resolved_condition;

                    $conditions = collect([[
                        'parent_question_id' => (int) data_get(
                            $legacy,
                            'parent_question_id'
                        ),
                        'parent_option_ids' => array_values(
                            array_filter([
                                (int) data_get(
                                    $legacy,
                                    'parent_option_id'
                                ),
                            ])
                        ),
                        'operator' => (string) data_get(
                            $legacy,
                            'operator',
                            'selected'
                        ),
                    ]]);
                }

                /*
                 * Normal question: always visible.
                 */
                if ($conditions->isEmpty()) {
                    $visibleQuestionIds->push(
                        (int) $question->id
                    );

                    return true;
                }

                /*
                 * Different parent rules are AND.
                 */
                $visible = $conditions->every(
                    function ($condition) use (
                        $questions,
                        $answers,
                        $visibleQuestionIds
                    ) {
                        $parentQuestionId = (int) data_get(
                            $condition,
                            'parent_question_id'
                        );

                        if (
                            !$parentQuestionId
                            || !$visibleQuestionIds->contains(
                                $parentQuestionId
                            )
                        ) {
                            return false;
                        }

                        $parentQuestion = $questions->first(
                            fn($item) =>
                                (int) $item->id
                                === $parentQuestionId
                        );

                        if (!$parentQuestion) {
                            return false;
                        }

                        return $this->conditionMatches(
                            $condition,
                            $parentQuestion,
                            $answers
                        );
                    }
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
     * Determine whether one conditional parent rule matches.
     *
     * Multiple parent_option_ids inside the rule are OR.
     */
    private function conditionMatches(
        array $condition,
              $parentQuestion,
        array $answers
    ): bool {
        $parentKey = (string) $parentQuestion->key;

        if (!array_key_exists($parentKey, $answers)) {
            return false;
        }

        $answer = $answers[$parentKey];

        if ($this->isEmptyAnswer($answer)) {
            return false;
        }

        $parentOptionIds = collect(
            data_get($condition, 'parent_option_ids', [])
        )
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        /*
         * Backward compatibility with the old parent_option_id.
         */
        if ($parentOptionIds->isEmpty()) {
            $legacyOptionId = (int) data_get(
                $condition,
                'parent_option_id'
            );

            if ($legacyOptionId) {
                $parentOptionIds = collect([
                    $legacyOptionId,
                ]);
            }
        }

        if ($parentOptionIds->isEmpty()) {
            return false;
        }

        $expectedValues = $parentQuestion
            ->options
            ->whereIn('id', $parentOptionIds)
            ->pluck('value')
            ->map(fn($value) => (string) $value)
            ->unique()
            ->values();

        if ($expectedValues->isEmpty()) {
            return false;
        }

        $answerValues = is_array($answer)
            ? collect($answer)
                ->filter(
                    fn($value) =>
                        is_string($value)
                        || is_numeric($value)
                )
                ->map(fn($value) => (string) $value)
                ->unique()
                ->values()
            : collect([(string) $answer]);

        /*
         * selected:
         *     any expected answer selected => true.
         *
         * not_selected:
         *     none of the expected answers selected => true.
         */
        $hasAnySelected = $answerValues
            ->intersect($expectedValues)
            ->isNotEmpty();

        return match (
        (string) data_get(
            $condition,
            'operator',
            'selected'
        )
        ) {
            'not_selected' => !$hasAnySelected,
            default => $hasAnySelected,
        };
    }

    private function isEmptyAnswer(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_array($value)) {
            return collect($value)
                ->filter(function ($item) {
                    if ($item === null) {
                        return false;
                    }

                    if (is_string($item)) {
                        return trim($item) !== '';
                    }

                    return true;
                })
                ->isEmpty();
        }

        return false;
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
            data_get($studioSettings, 'negative_rules'),
            data_get($categorySettings, 'negative_rules'),

            'low quality',
            'distorted composition',
            'unrelated elements',
            'unwanted product mockup',
            'product name as text',
            'category name as text',
            'written labels or captions',
        ])
            ->map(fn($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->implode("\n");
    }
}
