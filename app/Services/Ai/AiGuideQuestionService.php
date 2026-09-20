<?php

namespace App\Services\Ai;

use App\Enums\Ai\AiGuideQuestionTypeEnum;
use App\Models\Media;
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
            ->when(
                request()->filled('search_value'),
                function ($query) use ($locale) {
                    if (hasMeaningfulSearch(request('search_value'))) {
                        $search = strtolower(
                            request('search_value')
                        );

                        $query->where(
                            function ($query) use (
                                $search,
                                $locale
                            ) {
                                $query->whereRaw(
                                    "LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.\"{$locale}\"'))) LIKE ?",
                                    ["%{$search}%"]
                                )->orWhereRaw(
                                    "LOWER(JSON_UNQUOTE(JSON_EXTRACT(prompt_label, '$.\"{$locale}\"'))) LIKE ?",
                                    ["%{$search}%"]
                                );
                            }
                        );
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                }
            )
            ->when(
                request()->filled('type'),
                fn($query) => $query->where(
                    'type',
                    request('type')
                )
            )
            ->when(
                request()->filled('is_active'),
                fn($query) => $query->where(
                    'is_active',
                    request('is_active')
                )
            )
            ->orderBy('sort_order')
            ->orderBy('id');

        return DataTables::of($questions)
            ->editColumn(
                'title',
                fn($question) => $question->title
            )
            ->editColumn(
                'prompt_label',
                fn($question) => $question->prompt_label
            )
            ->editColumn(
                'type',
                fn($question) => $question->type->value
            )
            ->addColumn(
                'type_label',
                fn($question) => $question->type->label()
            )
            ->addColumn(
                'action',
                fn() => [
                    'can_edit' => (bool) auth()
                        ->user()
                        ->hasPermissionTo(
                            'ai-guide-questions_update'
                        ),

                    'can_delete' => (bool) auth()
                        ->user()
                        ->hasPermissionTo(
                            'ai-guide-questions_delete'
                        ),
                ]
            )
            ->make(true);
    }

    public function storeResource(
        $validatedData,
        $relationsToStore = [],
        $relationsToLoad = []
    ) {
        return $this->handleTransaction(
            function () use (
                $validatedData,
                $relationsToLoad
            ) {
                $options = Arr::pull(
                    $validatedData,
                    'options',
                    []
                );

                $validatedData['key'] = (string) Str::ulid();

                $question = $this->repository->create(
                    $validatedData
                );

                $this->syncOptions(
                    $question->id,
                    $question->type,
                    $options
                );

                return $question->load(
                    $relationsToLoad
                );
            }
        );
    }

    public function updateResource(
        $validatedData,
        $id,
        $relationsToLoad = []
    ) {
        return $this->handleTransaction(
            function () use (
                $validatedData,
                $id,
                $relationsToLoad
            ) {
                $options = Arr::pull(
                    $validatedData,
                    'options',
                    []
                );

                $question = $this->repository->update(
                    $validatedData,
                    $id
                );

                $this->syncOptions(
                    $question->id,
                    $question->type,
                    $options
                );

                return $question->load(
                    $relationsToLoad
                );
            }
        );
    }

    private function syncOptions(
        int $questionId,
        AiGuideQuestionTypeEnum $type,
        array $options
    ): void {
        if (!$this->supportsOptions($type)) {
            $this->deleteQuestionOptions(
                $questionId
            );

            return;
        }

        $existingOptions = $this->optionRepository
            ->query()
            ->where(
                'ai_guide_question_id',
                $questionId
            )
            ->get()
            ->keyBy('id');

        $submittedIds = [];

        foreach (array_values($options) as $index => $option) {
            $optionId = isset($option['id'])
                ? (int) $option['id']
                : null;

            $existing = $optionId
                ? $existingOptions->get($optionId)
                : null;

            /*
             * media_id is not stored on the options table.
             *
             * It is only used to sync the Spatie Media Library
             * collection after the option itself is created/updated.
             */
            $mediaId = !empty($option['media_id'])
                ? (int) $option['media_id']
                : null;

            $removeMedia = filter_var(
                $option['remove_media'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );

            unset(
                $option['media_id'],
                $option['remove_media']
            );

            $data = $this->prepareOptionData(
                questionId: $questionId,
                option: $option,
                index: $index,
                existing: $existing
            );

            /*
             * Existing option.
             */
            if ($existing) {
                $existing->update($data);

                $this->syncOptionMedia(
                    option: $existing,
                    mediaId: $mediaId,
                    removeMedia: $removeMedia
                );

                $submittedIds[] = $existing->id;

                continue;
            }

            /*
             * New option.
             */
            $model = $this->optionRepository->create(
                $data
            );

            $this->syncOptionMedia(
                option: $model,
                mediaId: $mediaId,
                removeMedia: $removeMedia
            );

            $submittedIds[] = $model->id;
        }

        /*
         * Delete options removed from the submitted form.
         */
        $query = $this->optionRepository
            ->query()
            ->where(
                'ai_guide_question_id',
                $questionId
            );

        if ($submittedIds) {
            $query->whereNotIn(
                'id',
                $submittedIds
            );
        }

        $this->deleteOptionModels(
            $query->get()
        );
    }

    private function prepareOptionData(
        int $questionId,
        array $option,
        int $index,
        $existing = null
    ): array {
        $uiData = $this->normalizeUiData(
            $option['ui_data'] ?? null
        );

        $colors = $uiData['colors'] ?? [];

        $isPalette = !empty($colors);

        $label = is_array(
            $option['label'] ?? null
        )
            ? $option['label']
            : [];

        $promptValue = is_array(
            $option['prompt_value'] ?? null
        )
            ? $option['prompt_value']
            : [];

        $englishLabel = trim(
            (string) ($label['en'] ?? '')
        );

        if ($englishLabel === '') {
            throw ValidationException::withMessages([
                "options.{$index}.label.en" => [
                    'English label is required for every option.',
                ],
            ]);
        }

        $label['en'] = $englishLabel;

        $label['ar'] = trim(
            (string) ($label['ar'] ?? '')
        );

        if ($isPalette) {
            [
                $label,
                $promptValue,
            ] = $this->preparePaletteContent(
                label: $label,
                promptValue: $promptValue,
                colors: $colors
            );
        }

        /*
         * Existing API option value must remain stable.
         *
         * Changing the label on Edit must NOT regenerate value.
         */
        $value = $existing?->value
            ?: $this->generateOptionValue(
                $questionId,
                $label['en']
            );

        return [
            'ai_guide_question_id' => $questionId,
            'value' => $value,
            'label' => $label,

            'prompt_value' => $promptValue
                ?: null,

            'ui_data' => $uiData,

            'is_active' => (bool) (
                $option['is_active']
                ?? true
            ),

            'sort_order' => $index,
        ];
    }

    private function preparePaletteContent(
        array $label,
        array $promptValue,
        array $colors
    ): array {
        $colorsText = implode(
            ', ',
            $colors
        );

        $defaultPrompt =
            "Use this exact color palette: {$colorsText}";

        /*
         * Admin writes the palette label manually.
         *
         * Only the AI prompt value is generated from
         * the selected palette colors.
         */
        $promptValue['en'] = trim(
            (string) (
                $promptValue['en']
                ?? ''
            )
        ) ?: $defaultPrompt;

        $promptValue['ar'] = trim(
            (string) (
                $promptValue['ar']
                ?? ''
            )
        ) ?: $defaultPrompt;

        return [
            $label,
            $promptValue,
        ];
    }

    private function syncOptionMedia(
        $option,
        ?int $mediaId,
        bool $removeMedia = false
    ): void {
        $collectionName = 'option_image';

        $currentMedia = $option->getFirstMedia(
            $collectionName
        );

        /*
         * Same media already attached.
         */
        if (
            $mediaId
            && $currentMedia
            && (int) $currentMedia->id === $mediaId
        ) {
            return;
        }

        /*
         * Attach new uploaded media.
         */
        if ($mediaId) {
            $media = Media::query()->find($mediaId);

            if (!$media) {
                throw ValidationException::withMessages([
                    'media_id' => [
                        'Uploaded media was not found.',
                    ],
                ]);
            }

            /*
             * Remove previous option image only when
             * we're attaching a different one.
             */
            if ($currentMedia) {
                $option->clearMediaCollection(
                    $collectionName
                );
            }

            /*
             * IMPORTANT:
             * use getMorphClass(), not get_class().
             *
             * This keeps Spatie polymorphic relation
             * compatible with Laravel morph maps.
             */
            $media->model_type = $option->getMorphClass();
            $media->model_id = $option->getKey();
            $media->collection_name = $collectionName;

            $media->save();

            /*
             * Clear cached relation so getFirstMedia()
             * immediately sees the newly attached media.
             */
            $option->unsetRelation('media');

            return;
        }

        /*
         * Keep current image.
         */
        if (!$removeMedia) {
            return;
        }

        /*
         * Explicit remove.
         */
        $option->clearMediaCollection(
            $collectionName
        );

        $option->unsetRelation('media');
    }

    private function normalizeUiData(
        ?array $uiData
    ): ?array {
        if (!$uiData) {
            return null;
        }

        /*
         * ui_data is only for supported visual
         * metadata such as color palettes.
         *
         * Option images are stored through
         * Spatie Media Library.
         */
        $colors = collect(
            $uiData['colors'] ?? []
        )
            ->filter(
                fn($color) => is_string($color)
            )
            ->map(
                fn($color) => strtoupper(
                    trim($color)
                )
            )
            ->filter(
                fn($color) =>
                preg_match(
                    '/^#[0-9A-F]{6}$/',
                    $color
                )
            )
            ->unique()
            ->values()
            ->all();

        return $colors
            ? [
                'colors' => $colors,
            ]
            : null;
    }

    private function supportsOptions(
        AiGuideQuestionTypeEnum $type
    ): bool {
        return in_array(
            $type,
            [
                AiGuideQuestionTypeEnum::SINGLE_SELECT,
                AiGuideQuestionTypeEnum::MULTI_SELECT,
            ],
            true
        );
    }

    private function deleteQuestionOptions(
        int $questionId
    ): void {
        $options = $this->optionRepository
            ->query()
            ->where(
                'ai_guide_question_id',
                $questionId
            )
            ->get();

        $this->deleteOptionModels(
            $options
        );
    }

    private function deleteOptionModels(
        $options
    ): void {
        foreach ($options as $option) {
            /*
             * Use the literal collection name.
             *
             * Do not use getMediaCollectionName().
             */
            $option->clearMediaCollection(
                'option_image'
            );

            $option->delete();
        }
    }

    private function generateOptionValue(
        int $questionId,
        string $label
    ): string {
        $base = Str::slug(
            $label,
            '_'
        ) ?: 'option';

        $value = $base;

        $counter = 2;

        while (
        $this->optionValueExists(
            $questionId,
            $value
        )
        ) {
            $value = "{$base}_{$counter}";

            $counter++;
        }

        return $value;
    }

    private function optionValueExists(
        int $questionId,
        string $value
    ): bool {
        return $this->optionRepository
            ->query()
            ->where(
                'ai_guide_question_id',
                $questionId
            )
            ->where(
                'value',
                $value
            )
            ->exists();
    }
}
