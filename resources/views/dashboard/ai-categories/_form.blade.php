@php
    $aiCategory = $model ?? null;

    $settings = old('settings', $aiCategory?->settings ?? []);

    $selectedResolution = old(
        'default_resolution',
        $aiCategory?->default_resolution ?? '1024x1024'
    );

    $selectedAspectRatio = old(
        'aspect_ratio',
        $aiCategory?->aspect_ratio
    );

    $studioItems = collect($associatedData['studioItems'] ?? []);
    $questions = collect($associatedData['questions'] ?? []);

    $selectedStudioItemIds = collect(
        old(
            'studio_items',
            $aiCategory?->studioItems?->pluck('id')->all() ?? []
        )
    )
        ->map(fn ($id) => (int) $id)
        ->values()
        ->all();

    $assignedQuestions = $aiCategory?->questions?->keyBy('id') ?? collect();

    $assignedOptionIds = $aiCategory?->options
        ?->pluck('id')
        ->map(fn ($id) => (int) $id)
        ->all() ?? [];

    $oldQuestions = old('questions');

    /*
     * AI Product edit:
     * show only questions already attached to this Product.
     *
     * On create there is no Product assignment yet, so keep the full active
     * question list available for the initial attachment.
     *
     * If validation fails, also keep any newly selected question from old()
     * so it does not disappear from the form.
     */
    $displayQuestions = $questions;

    if ($aiCategory?->id) {
        $attachedQuestionIds = $assignedQuestions
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $oldSelectedQuestionIds = collect(is_array($oldQuestions) ? $oldQuestions : [])
            ->filter(fn ($row) => (bool) data_get($row, 'selected', false))
            ->map(fn ($row) => (int) data_get($row, 'question_id', 0))
            ->filter()
            ->values()
            ->all();

        $visibleQuestionIds = collect($attachedQuestionIds)
            ->merge($oldSelectedQuestionIds)
            ->unique()
            ->values()
            ->all();

        $displayQuestions = $questions
            ->filter(fn ($question) => in_array(
                (int) $question->id,
                $visibleQuestionIds,
                true
            ))
            ->sortBy(function ($question) use ($assignedQuestions) {
                $assigned = $assignedQuestions->get($question->id);

                return [
                    $assigned
                        ? (int) ($assigned->pivot->sort_order ?? 0)
                        : PHP_INT_MAX,
                    (int) $question->id,
                ];
            })
            ->values();
    }

    /*
     * Conditional visibility belongs to the question assignment, not the global question.
     * Backend can provide:
     * $associatedData['questionConditions'][question_id] = [
     *     'parent_question_id' => 1,
     *     'parent_option_id' => 4,
     *     'operator' => 'selected',
     * ];
     */
    $questionConditions = collect($associatedData['questionConditions'] ?? []);


    /*
     * Multi-condition edit fallback.
     *
     * New structure:
     * questionConditions[child_question_id] = [
     *     [
     *         'parent_question_id' => 10,
     *         'parent_option_ids' => [50, 51],
     *         'operator' => 'selected',
     *     ],
     * ];
     *
     * This fallback keeps the current controller untouched.
     */
    if (
        $aiCategory?->id
        && \Illuminate\Support\Facades\Schema::hasTable('ai_guide_question_conditions')
    ) {
        $aiCategoryMorphTypes = array_values(array_unique([
            $aiCategory->getMorphClass(),
            'ai_category',
            \App\Models\AiCategory::class,
        ]));

        $categoryQuestionAssignments = \Illuminate\Support\Facades\DB::table('ai_guide_question_assignments')
            ->where('assignable_id', $aiCategory->id)
            ->whereIn('assignable_type', $aiCategoryMorphTypes)
            ->get();

        if ($categoryQuestionAssignments->isNotEmpty()) {
            $questionIdByAssignmentId = $categoryQuestionAssignments
                ->keyBy(fn ($assignment) => (int) $assignment->id)
                ->map(fn ($assignment) => (int) $assignment->ai_guide_question_id);

            $questionConditions = \Illuminate\Support\Facades\DB::table('ai_guide_question_conditions')
                ->whereIn(
                    'ai_guide_question_assignment_id',
                    $categoryQuestionAssignments->pluck('id')
                )
                ->orderBy('id')
                ->get()
                ->groupBy(function ($condition) use ($questionIdByAssignmentId) {
                    return (int) ($questionIdByAssignmentId->get(
                        (int) $condition->ai_guide_question_assignment_id
                    ) ?? 0);
                })
                ->filter(fn ($rows, $questionId) => (int) $questionId > 0)
                ->map(function ($rows) {
                    return $rows
                        ->groupBy(fn ($condition) => implode(':', [
                            (int) $condition->parent_question_id,
                            (string) ($condition->operator ?: 'selected'),
                        ]))
                        ->map(function ($group) {
                            $first = $group->first();

                            return [
                                'parent_question_id' => (int) $first->parent_question_id,
                                'parent_option_ids' => $group
                                    ->pluck('parent_option_id')
                                    ->map(fn ($id) => (int) $id)
                                    ->unique()
                                    ->values()
                                    ->all(),
                                'operator' => (string) ($first->operator ?: 'selected'),
                            ];
                        })
                        ->values()
                        ->all();
                });
        }
    }

    $conditionQuestionsPayload = $questions
        ->filter(fn ($question) => in_array(
            $question->type?->value ?? $question->type,
            [
                \App\Enums\Ai\AiGuideQuestionTypeEnum::SINGLE_SELECT->value,
                \App\Enums\Ai\AiGuideQuestionTypeEnum::MULTI_SELECT->value,
            ],
            true
        ))
        ->mapWithKeys(function ($question) {
            return [
                (int) $question->id => [
                    'id' => (int) $question->id,
                    'title' => $question->title,
                    'options' => $question->options
                        ->where('is_active', true)
                        ->values()
                        ->map(fn ($option) => [
                            'id' => (int) $option->id,
                            'label' => $option->label,
                        ])
                        ->all(),
                ],
            ];
        })
        ->all();

    $studioItemsPayload = $studioItems->mapWithKeys(function ($studioItem) {
        return [
            $studioItem->id => [
                'id' => $studioItem->id,
                'key' => $studioItem->key,
                'name' => $studioItem->name,
                'name_en' => $studioItem->getTranslation('name', 'en', false),
                'name_ar' => $studioItem->getTranslation('name', 'ar', false),
                'description' => $studioItem->description,
                'description_en' => $studioItem->getTranslation('description', 'en', false),
                'description_ar' => $studioItem->getTranslation('description', 'ar', false),
                'generation_type' => $studioItem->generation_type?->value ?? $studioItem->generation_type,
                'generation_type_label' => $studioItem->generation_type?->label() ?? $studioItem->generation_type,
                'credits_cost' => (int) $studioItem->credits_cost,
                'sort_order' => (int) $studioItem->sort_order,
                'is_active' => (bool) $studioItem->is_active,
                'settings' => $studioItem->settings ?? [],
                'question_ids' => $studioItem->relationLoaded('questions')
                    ? $studioItem->questions->pluck('id')->map(fn($id) => (int) $id)->values()->all()
                    : collect($associatedData['studioItemQuestionIds'][$studioItem->id] ?? [])->map(fn($id) => (int) $id)->values()->all(),
            ],
        ];
    })->all();
@endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">

<style>
    .ai-config-card {
        transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
    }

    .ai-config-card.is-selected {
        border-color: var(--bs-primary) !important;
        background: rgba(115, 103, 240, .035);
        box-shadow: 0 0 0 1px rgba(115, 103, 240, .08);
    }

    .question-options-panel {
        background: #fafafa;
    }

    .question-condition-settings {
        background: #fcfbff;
    }

    .question-condition-panel {
        border: 1px solid rgba(115, 103, 240, .22);
        background: rgba(115, 103, 240, .035);
        border-radius: .5rem;
    }

    .question-condition-summary {
        border: 1px solid rgba(255, 159, 67, .3);
        background: rgba(255, 159, 67, .08);
        color: #a15c11;
        border-radius: .357rem;
        padding: .65rem .75rem;
        font-size: .85rem;
    }

    .question-condition-badge {
        white-space: nowrap;
    }
    .quick-option-dropzone {
        min-height: 150px;
        border: 1px dashed #d8d6de;
        border-radius: .357rem;
        background: #fff;
        padding: 12px;
        overflow: hidden;
        position: relative;
    }

    .quick-option-dropzone .dz-message {
        margin: 2rem 0;
        color: #6e6b7b;
        text-align: center;
    }

    .quick-option-dropzone.dz-started .dz-message {
        display: none;
    }

    .quick-option-dropzone .dz-preview {
        position: relative !important;
        display: inline-flex !important;
        flex-direction: column;
        align-items: flex-start;
        width: 140px !important;
        min-height: 0 !important;
        margin: 0 !important;
        vertical-align: top;
    }

    .quick-option-dropzone .dz-preview .dz-image {
        width: 140px !important;
        height: 110px !important;
        border-radius: 8px !important;
        overflow: hidden !important;
        background: #f8f8f8;
    }

    .quick-option-dropzone .dz-preview .dz-image img {
        display: block !important;
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: contain !important;
    }

    .quick-option-dropzone .dz-preview .dz-details,
    .quick-option-dropzone .dz-preview .dz-success-mark,
    .quick-option-dropzone .dz-preview .dz-error-mark {
        display: none !important;
    }

    .quick-option-dropzone .dz-preview .dz-remove {
        display: inline-block;
        margin-top: 8px;
        font-size: 12px;
        color: #ea5455;
        text-decoration: none;
    }
</style>

{{-- Product Settings --}}
<div class="card border mb-2">
    <div class="card-header border-bottom">
        <div>
            <h5 class="mb-25">Product AI Settings</h5>
            <small class="text-muted">
                Product context, output size and production behavior.
            </small>
        </div>
    </div>

    <div class="card-body pt-2">
        <div class="row">
            <div class="col-md-6 mb-1">
                <label class="form-label">Product *</label>

                <select name="category_id" class="form-select">
                    <option value="">Select Product</option>

                    @foreach($associatedData['categories'] as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected(old('category_id', $aiCategory?->category_id) == $category->id)
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 mb-1">
                <label class="form-label">Default Resolution</label>

                <select name="default_resolution" class="form-select">
                    <option value="">Select Resolution</option>

                    @foreach(['512x512', '768x768', '1024x1024'] as $resolution)
                        <option
                            value="{{ $resolution }}"
                            @selected($selectedResolution === $resolution)
                        >
                            {{ $resolution }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 mb-1">
                <label class="form-label">Aspect Ratio</label>

                <select name="aspect_ratio" class="form-select">
                    <option value="">Default</option>

                    @foreach(['1:1', '4:5', '3:4', '16:9', '9:16'] as $ratio)
                        <option
                            value="{{ $ratio }}"
                            @selected($selectedAspectRatio === $ratio)
                        >
                            {{ $ratio }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 mb-1">
                <label class="form-label">Orientation</label>

                <select name="settings[orientation]" class="form-select">
                    <option value="">Default</option>

                    @foreach(['square', 'portrait', 'landscape'] as $orientation)
                        <option
                            value="{{ $orientation }}"
                            @selected(($settings['orientation'] ?? null) === $orientation)
                        >
                            {{ ucfirst($orientation) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 mb-1">
                <label class="form-label">Sort Order</label>

                <input
                    type="number"
                    name="sort_order"
                    min="0"
                    value="{{ old('sort_order', $aiCategory?->sort_order ?? 0) }}"
                    class="form-control"
                >
            </div>
        </div>

        <div class="d-flex flex-wrap gap-3 mt-1">
            <div class="form-check form-switch">
                <input type="hidden" name="enabled" value="0">

                <input
                    type="checkbox"
                    name="enabled"
                    id="enabled"
                    value="1"
                    class="form-check-input"
                    @checked(old('enabled', $aiCategory?->enabled ?? true))
                >

                <label for="enabled" class="form-check-label">AI Enabled</label>
            </div>

            <div class="form-check form-switch">
                <input type="hidden" name="settings[transparent_background]" value="0">

                <input
                    type="checkbox"
                    name="settings[transparent_background]"
                    id="transparent-background"
                    value="1"
                    class="form-check-input"
                    @checked($settings['transparent_background'] ?? false)
                >

                <label for="transparent-background" class="form-check-label">
                    Transparent Background
                </label>
            </div>

            <div class="form-check form-switch">
                <input type="hidden" name="settings[print_ready]" value="0">

                <input
                    type="checkbox"
                    name="settings[print_ready]"
                    id="print-ready"
                    value="1"
                    class="form-check-input"
                    @checked($settings['print_ready'] ?? true)
                >

                <label for="print-ready" class="form-check-label">Print Ready</label>
            </div>
        </div>
    </div>
</div>

{{-- Studio Items --}}
<div class="card border mb-2">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-25">Studio Items</h5>
            <small class="text-muted">
                Choose the generation modes available for this product. Add/Edit changes Studio Items globally.
            </small>
        </div>

        @can('ai-studio-items_create')
            <button
                type="button"
                id="add-studio-item"
                class="btn btn-sm btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#quick-studio-item-modal"
            >
                <i data-feather="plus"></i>
                Add Studio Item
            </button>
        @endcan
    </div>

    <div class="card-body pt-2">
        <div id="studio-items-container" class="row">
            @forelse($studioItems as $studioItem)
                @php
                    $studioSelected = (bool) $studioItem->is_active && in_array(
                        (int) $studioItem->id,
                        $selectedStudioItemIds,
                        true
                    );
                @endphp

                <div class="col-md-4 mb-1 studio-item-column" data-studio-item-id="{{ $studioItem->id }}">
                    <div class="ai-config-card studio-item-card border rounded p-1 w-100 h-100 {{ $studioSelected ? 'is-selected' : '' }}">
                        <div class="d-flex justify-content-between align-items-start gap-1">
                            <div class="form-check mb-0 flex-grow-1">
                                <input
                                    type="checkbox"
                                    id="studio-item-{{ $studioItem->id }}"
                                    name="studio_items[]"
                                    value="{{ $studioItem->id }}"
                                    class="form-check-input studio-item-checkbox"
                                    @checked($studioSelected)
                                    @disabled(!$studioItem->is_active)
                                >

                                <label class="form-check-label fw-bolder" for="studio-item-{{ $studioItem->id }}">
                                    {{ $studioItem->name }}
                                </label>
                            </div>

                            <div class="d-flex gap-50 align-items-center">
                                <span class="badge {{ $studioItem->is_active ? 'bg-light-success text-success' : 'bg-light-danger text-danger' }} studio-item-status-badge">
                                    {{ $studioItem->is_active ? 'Active' : 'Inactive' }}
                                </span>

                                @can('ai-studio-items_update')
                                    <button type="button" class="btn btn-sm btn-outline-primary quick-edit-studio-item" data-id="{{ $studioItem->id }}" title="Edit Studio Item">
                                        <i data-feather="edit-2"></i>
                                    </button>
                                @endcan

                                @can('ai-studio-items_delete')
                                    <button type="button" class="btn btn-sm btn-outline-danger quick-delete-studio-item-card" data-id="{{ $studioItem->id }}" title="Delete Studio Item">
                                        <i data-feather="trash-2"></i>
                                    </button>
                                @endcan
                            </div>
                        </div>

                        <div class="mt-1">
                            <span class="badge bg-light-primary text-primary studio-item-key">
                                {{ $studioItem->key }}
                            </span>
                        </div>

                        @if($studioItem->description)
                            <small class="text-muted d-block mt-1 studio-item-description">
                                {{ $studioItem->description }}
                            </small>
                        @else
                            <small class="text-muted d-block mt-1 studio-item-description"></small>
                        @endif

                        <div class="d-flex gap-1 mt-1">
                            <small class="text-muted studio-item-generation-type">
                                {{ $studioItem->generation_type?->label() ?? $studioItem->generation_type }}
                            </small>
                            <small class="text-muted">•</small>
                            <small class="text-muted studio-item-credits">
                                {{ (int) $studioItem->credits_cost }} Credits
                            </small>
                        </div>
                    </div>
                </div>
            @empty
                <div id="no-studio-items-alert" class="col-12">
                    <div class="alert alert-warning mb-0">
                        No Studio Items found. Use “Add Studio Item”.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Questions --}}
<div class="card border mb-2">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-25">Questions & Options</h5>
            <small class="text-muted">
                Configure the guided questions for this product directly from Add/Edit.
            </small>
        </div>

        <div class="d-flex gap-1">
            <button
                type="button"
                id="select-all-questions"
                class="btn btn-sm btn-outline-primary"
            >
                Select All
            </button>

            <button
                type="button"
                id="clear-all-questions"
                class="btn btn-sm btn-outline-secondary"
            >
                Clear
            </button>

            @can('ai-guide-questions_create')
                <button
                    type="button"
                    class="btn btn-sm btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#quick-question-modal"
                >
                    <i data-feather="plus"></i>
                    Add New Question
                </button>
            @endcan
        </div>
    </div>

    <div class="card-body pt-2">
        <div id="questions-container">
            @forelse($displayQuestions as $question)
                @php
                    $assigned = $assignedQuestions->get($question->id);
                    $oldRow = is_array($oldQuestions)
                        ? ($oldQuestions[$question->id] ?? null)
                        : null;

                    $selected = $oldRow !== null
                        ? (bool) data_get($oldRow, 'selected', false)
                        : (bool) $assigned;

                    $required = $oldRow !== null
                        ? (bool) data_get($oldRow, 'required', false)
                        : ($assigned
                            ? (bool) $assigned->pivot->required
                            : (bool) $question->required);

                    $sortOrder = $oldRow !== null
                        ? (int) data_get($oldRow, 'sort_order', $question->sort_order ?? 0)
                        : ($assigned
                            ? (int) $assigned->pivot->sort_order
                            : (int) ($question->sort_order ?? 0));

                    $selectedOptions = $oldRow !== null
                        ? collect(data_get($oldRow, 'options', []))
                            ->map(fn ($id) => (int) $id)
                            ->all()
                        : $question->options
                            ->whereIn('id', $assignedOptionIds)
                            ->pluck('id')
                            ->map(fn ($id) => (int) $id)
                            ->all();

                    $supportsOptions = in_array(
                        $question->type->value,
                        [
                            \App\Enums\Ai\AiGuideQuestionTypeEnum::SINGLE_SELECT->value,
                            \App\Enums\Ai\AiGuideQuestionTypeEnum::MULTI_SELECT->value,
                        ],
                        true
                    );

                    $savedConditionsRaw = $oldRow !== null
                        ? data_get($oldRow, 'conditions', [])
                        : ($questionConditions->get($question->id) ?? []);

                    /*
                     * Backward compatibility with the old single-condition form.
                     */
                    if (
                        empty($savedConditionsRaw)
                        && $oldRow !== null
                        && !empty(data_get($oldRow, 'condition'))
                    ) {
                        $legacyCondition = data_get($oldRow, 'condition', []);

                        $savedConditionsRaw = [[
                            'parent_question_id' => data_get($legacyCondition, 'parent_question_id'),
                            'parent_option_ids' => array_values(array_filter([
                                data_get($legacyCondition, 'parent_option_id'),
                            ])),
                            'operator' => data_get($legacyCondition, 'operator', 'selected'),
                        ]];
                    }

                    if (
                        is_array($savedConditionsRaw)
                        && array_key_exists('parent_question_id', $savedConditionsRaw)
                    ) {
                        $savedConditionsRaw = [[
                            'parent_question_id' => data_get($savedConditionsRaw, 'parent_question_id'),
                            'parent_option_ids' => data_get(
                                $savedConditionsRaw,
                                'parent_option_ids',
                                array_values(array_filter([
                                    data_get($savedConditionsRaw, 'parent_option_id'),
                                ]))
                            ),
                            'operator' => data_get($savedConditionsRaw, 'operator', 'selected'),
                        ]];
                    }

                    $savedConditions = collect($savedConditionsRaw)
                        ->map(function ($condition) {
                            return [
                                'parent_question_id' => (int) data_get($condition, 'parent_question_id', 0),
                                'parent_option_ids' => collect(
                                    data_get(
                                        $condition,
                                        'parent_option_ids',
                                        array_values(array_filter([
                                            data_get($condition, 'parent_option_id'),
                                        ]))
                                    )
                                )
                                    ->map(fn ($id) => (int) $id)
                                    ->filter()
                                    ->unique()
                                    ->values()
                                    ->all(),
                                'operator' => (string) data_get($condition, 'operator', 'selected'),
                            ];
                        })
                        ->filter(fn ($condition) => $condition['parent_question_id'] || !empty($condition['parent_option_ids']))
                        ->values();

                    $conditionEnabled = $oldRow !== null
                        ? (bool) data_get($oldRow, 'condition_enabled', false)
                        : $savedConditions->isNotEmpty();

                    $conditionRows = $savedConditions->isNotEmpty()
                        ? $savedConditions->all()
                        : [[
                            'parent_question_id' => 0,
                            'parent_option_ids' => [],
                            'operator' => 'selected',
                        ]];
                @endphp

                <div
                    class="ai-config-card question-card border rounded mb-2 {{ $selected ? 'is-selected' : '' }}"
                    data-question-id="{{ $question->id }}"
                >
                    <div class="p-1">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <div class="form-check">
                                    <input
                                        type="hidden"
                                        name="questions[{{ $question->id }}][question_id]"
                                        value="{{ $question->id }}"
                                    >

                                    <input
                                        type="hidden"
                                        name="questions[{{ $question->id }}][selected]"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        id="question-{{ $question->id }}"
                                        name="questions[{{ $question->id }}][selected]"
                                        value="1"
                                        class="form-check-input question-toggle"
                                        @checked($selected)
                                    >

                                    <label
                                        class="form-check-label"
                                        for="question-{{ $question->id }}"
                                    >
                                        <div class="fw-bolder">{{ $question->title }}</div>
                                        <small class="text-muted">
                                            {{ $question->prompt_label }}
                                        </small>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="question-settings d-flex justify-content-end align-items-center gap-2">
                                    <span class="badge bg-light-primary text-primary">
                                        {{ $question->type->label() }}
                                    </span>

                                    <span
                                        class="badge bg-light-warning text-warning question-condition-badge {{ $conditionEnabled ? '' : 'd-none' }}"
                                    >
                                        Conditional
                                    </span>

                                    <div class="form-check form-switch">
                                        <input
                                            type="hidden"
                                            name="questions[{{ $question->id }}][required]"
                                            value="0"
                                        >

                                        <input
                                            type="checkbox"
                                            name="questions[{{ $question->id }}][required]"
                                            value="1"
                                            class="form-check-input"
                                            @checked($required)
                                        >

                                        <label class="form-check-label">Required</label>
                                    </div>

                                    <div style="width:85px">
                                        <input
                                            type="number"
                                            name="questions[{{ $question->id }}][sort_order]"
                                            value="{{ $sortOrder }}"
                                            min="0"
                                            class="form-control form-control-sm"
                                            placeholder="Order"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="question-condition-settings border-top p-1">
                        <div class="d-flex justify-content-between align-items-center gap-1">
                            <div>
                                <div class="fw-bolder">Conditional Visibility</div>
                                <small class="text-muted">
                                    Show this question only when a previous answer matches.
                                </small>
                            </div>

                            <div class="form-check form-switch mb-0">
                                <input
                                    type="hidden"
                                    name="questions[{{ $question->id }}][condition_enabled]"
                                    value="0"
                                >

                                <input
                                    type="checkbox"
                                    id="question-condition-{{ $question->id }}"
                                    name="questions[{{ $question->id }}][condition_enabled]"
                                    value="1"
                                    class="form-check-input question-condition-toggle"
                                    @checked($conditionEnabled)
                                >

                                <label
                                    class="form-check-label"
                                    for="question-condition-{{ $question->id }}"
                                >
                                    Conditional
                                </label>
                            </div>
                        </div>

                        <div class="question-condition-panel p-1 mt-1 {{ $conditionEnabled ? '' : 'd-none' }}">
                            <div
                                class="condition-rules"
                                data-next-index="{{ count($conditionRows) }}"
                            >
                                @foreach($conditionRows as $conditionIndex => $savedCondition)
                                    @php
                                        $conditionParentQuestionId = (int) data_get(
                                            $savedCondition,
                                            'parent_question_id',
                                            0
                                        );

                                        $conditionParentOptionIds = collect(
                                            data_get($savedCondition, 'parent_option_ids', [])
                                        )
                                            ->map(fn ($id) => (int) $id)
                                            ->all();

//                                        $conditionOperator = (string) data_get(
//                                            $savedCondition,
//                                            'operator',
//                                            'selected'
//                                        );
                                    @endphp

                                    <div
                                        class="condition-rule border rounded p-1 mb-1"
                                        data-condition-index="{{ $conditionIndex }}"
                                    >
                                        <div class="row align-items-end">
                                            <div class="col-md-4 mb-1">
                                                <label class="form-label">Parent Question *</label>

                                                <select
                                                    name="questions[{{ $question->id }}][conditions][{{ $conditionIndex }}][parent_question_id]"
                                                    class="form-select condition-parent-question"
                                                >
                                                    <option value="">Select parent question</option>

                                                    @foreach($conditionQuestionsPayload as $parentQuestion)
                                                        @continue((int) $parentQuestion['id'] === (int) $question->id)

                                                        <option
                                                            value="{{ $parentQuestion['id'] }}"
                                                            @selected($conditionParentQuestionId === (int) $parentQuestion['id'])
                                                        >
                                                            {{ $parentQuestion['title'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-4 mb-1">
                                                <label class="form-label">Answers * <small class="text-muted">(OR)</small></label>

                                                <select
                                                    name="questions[{{ $question->id }}][conditions][{{ $conditionIndex }}][parent_option_ids][]"
                                                    class="form-select condition-parent-options"
                                                    multiple
                                                    style="width:100%"
                                                >
                                                    @if($conditionParentQuestionId && isset($conditionQuestionsPayload[$conditionParentQuestionId]))
                                                        @foreach($conditionQuestionsPayload[$conditionParentQuestionId]['options'] as $parentOption)
                                                            <option
                                                                value="{{ $parentOption['id'] }}"
                                                                @selected(in_array((int) $parentOption['id'], $conditionParentOptionIds, true))
                                                            >
                                                                {{ $parentOption['label'] }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>

                                            {{--                                            <div class="col-md-2 mb-1">--}}
                                            {{--                                                <label class="form-label">Rule</label>--}}

                                            {{--                                                <select--}}
                                            {{--                                                    name="questions[{{ $question->id }}][conditions][{{ $conditionIndex }}][operator]"--}}
                                            {{--                                                    class="form-select condition-operator"--}}
                                            {{--                                                >--}}
                                            {{--                                                    <option value="selected" @selected($conditionOperator === 'selected')>--}}
                                            {{--                                                        Is selected--}}
                                            {{--                                                    </option>--}}
                                            {{--                                                    <option value="not_selected" @selected($conditionOperator === 'not_selected')>--}}
                                            {{--                                                        Is not selected--}}
                                            {{--                                                    </option>--}}
                                            {{--                                                </select>--}}
                                            {{--                                            </div>--}}

                                            <div class="col-md-2 mb-1">
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-danger w-100 remove-condition-rule"
                                                >
                                                    <i data-feather="trash-2"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="d-flex justify-content-between align-items-center gap-1 mt-50">
                                <small class="text-muted">
                                    Multiple answers in one row are OR. Different rows are AND.
                                </small>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary add-condition-rule"
                                >
                                    <i data-feather="plus"></i>
                                    Add Parent Rule
                                </button>
                            </div>

                            <div class="question-condition-summary mt-1 {{ $conditionEnabled ? '' : 'd-none' }}">
                                All parent rules must match. Inside each rule, any selected answer can match.
                            </div>

                            <small class="text-muted d-block mt-50">
                                If this question is Required, validation applies only while all parent rules are matched.
                            </small>
                        </div>
                    </div>

                    @if($supportsOptions && $question->options->isNotEmpty())
                        <div class="question-options question-options-panel border-top p-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <h6 class="mb-25">Allowed Options</h6>
                                    <small class="text-muted">
                                        Choose which options appear for this product.
                                    </small>
                                </div>

                                <div class="d-flex gap-50">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary select-question-options"
                                    >
                                        Select All
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary clear-question-options"
                                    >
                                        Clear
                                    </button>
                                </div>
                            </div>

                            <div class="row">
                                @foreach($question->options as $option)
                                    <div class="col-md-4 col-lg-3 mb-1">
                                        <label
                                            class="border rounded p-1 w-100 h-100 option-item"
                                            for="question-{{ $question->id }}-option-{{ $option->id }}"
                                            style="cursor:pointer"
                                        >
                                            @php
                                                $optionColors = array_values(
                                                    data_get($option->ui_data, 'colors', [])
                                                );
                                            @endphp

                                            <div class="form-check mb-0">
                                                <input
                                                    type="checkbox"
                                                    id="question-{{ $question->id }}-option-{{ $option->id }}"
                                                    name="questions[{{ $question->id }}][options][]"
                                                    value="{{ $option->id }}"
                                                    class="form-check-input option-checkbox"
                                                    @checked(in_array((int) $option->id, $selectedOptions, true))
                                                >

                                                <span class="form-check-label fw-bolder">
                                                    {{ $option->label }}
                                                </span>
                                            </div>

                                            @if(!empty($optionColors))
                                                <div class="d-flex mt-1 overflow-hidden rounded" style="height:34px">
                                                    @foreach($optionColors as $color)
                                                        <span
                                                            title="{{ $color }}"
                                                            style="background:{{ $color }};flex:1;min-width:24px"
                                                        ></span>
                                                    @endforeach
                                                </div>
                                            @elseif($option->prompt_value)
                                                <small class="text-muted d-block mt-50">
                                                    {{ $option->prompt_value }}
                                                </small>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div id="no-questions-alert" class="alert alert-warning mb-0">
                    @if($aiCategory?->id)
                        No questions are attached to this AI Product yet. Use “Add New Question” to create and attach one.
                    @else
                        No active AI questions found. Use “Add New Question”.
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Quick Studio Item Modal. Add/Edit is global; product attachment is saved with the parent form. --}}
@if(auth()->user()?->can('ai-studio-items_create') || auth()->user()?->can('ai-studio-items_update'))
    <div class="modal fade" id="quick-studio-item-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="quick-studio-item-modal-title">Add Studio Item</h5>
                        <small class="text-muted">
                            Studio Item settings are global and can be used by multiple AI Products.
                        </small>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="quick-studio-item-id">

                    <div class="row">
                        <div class="col-md-6 mb-1">
                            <label class="form-label">Name English *</label>
                            <input type="text" id="quick-studio-name-en" class="form-control">
                        </div>

                        <div class="col-md-6 mb-1">
                            <label class="form-label">Name Arabic</label>
                            <input type="text" id="quick-studio-name-ar" class="form-control" dir="rtl">
                        </div>

                        <div class="col-md-6 mb-1">
                            <label class="form-label">Description English</label>
                            <textarea id="quick-studio-description-en" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="col-md-6 mb-1">
                            <label class="form-label">Description Arabic</label>
                            <textarea id="quick-studio-description-ar" class="form-control" rows="3" dir="rtl"></textarea>
                        </div>

                        <div class="col-md-4 mb-1">
                            <label class="form-label">Generation Type *</label>
                            <select id="quick-studio-generation-type" class="form-select">
                                <option value="">Select Type</option>
                                @foreach(\App\Enums\Ai\AiGenerationTypeEnum::cases() as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-1">
                            <label class="form-label">Credits Cost *</label>
                            <input type="number" id="quick-studio-credits-cost" min="0" value="1" class="form-control">
                        </div>

                        <div class="col-md-4 mb-1">
                            <label class="form-label">Sort Order</label>
                            <input type="number" id="quick-studio-sort-order" min="0" value="0" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-6 mb-1">
                        <label class="form-label">Prompt Instructions</label>
                        <textarea
                            id="quick-studio-prompt-instructions"
                            class="form-control"
                            rows="4"
                            placeholder="Instructions specific to this Studio Item..."
                        ></textarea>
                    </div>

                    <div class="col-md-6 mb-1">
                        <label class="form-label">Negative Rules</label>
                        <textarea
                            id="quick-studio-negative-rules"
                            class="form-control"
                            rows="4"
                            placeholder="Things the generated result should avoid..."
                        ></textarea>
                    </div>

                    <div class="col-md-4 mb-1">
                        <label class="form-label d-block">Status</label>

                        <div class="form-check form-switch mt-50">
                            <input type="checkbox" id="quick-studio-is-active" class="form-check-input" checked>
                            <label for="quick-studio-is-active" class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer d-flex justify-content-between">
                <div>
                    @can('ai-studio-items_delete')
                        <button type="button" id="quick-delete-studio-item" class="btn btn-outline-danger d-none">
                            <i data-feather="trash-2"></i>
                            Delete Studio Item
                        </button>
                    @endcan
                </div>

                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>

                    <button type="button" id="quick-save-studio-item" class="btn btn-primary">
                        <i data-feather="save"></i>
                        Save Studio Item
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>
@endif

{{-- Quick Question Modal. No nested form: values are sent with AJAX manually. --}}
@can('ai-guide-questions_create')
    <div
        class="modal fade"
        id="quick-question-modal"
        tabindex="-1"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Add New Question</h5>
                        <small class="text-muted">
                            The question is created globally, then automatically selected for this product.
                        </small>
                    </div>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    >x</button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-1">
                            <label class="form-label">Question English *</label>
                            <input type="text" id="quick-title-en" class="form-control">
                        </div>

                        <div class="col-md-6 mb-1">
                            <label class="form-label">Question Arabic</label>
                            <input type="text" id="quick-title-ar" class="form-control" dir="rtl">
                        </div>

                        <div class="col-md-4 mb-1">
                            <label class="form-label">Type *</label>

                            <select id="quick-question-type" class="form-select">
                                @foreach(\App\Enums\Ai\AiGuideQuestionTypeEnum::cases() as $type)
                                    <option value="{{ $type->value }}">
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-1">
                            <label class="form-label d-block">Color Palette Question</label>

                            <div class="form-check form-switch mt-50">
                                <input
                                    type="checkbox"
                                    id="quick-color-palette-question"
                                    class="form-check-input"
                                >

                                <label
                                    for="quick-color-palette-question"
                                    class="form-check-label"
                                >
                                    Enable Color Palette
                                </label>
                            </div>

                            <small class="text-muted">
                                Forces question type to Single Select.
                            </small>
                        </div>

                        <div class="col-md-4 mb-1">
                            <label class="form-label">Sort Order</label>
                            <input
                                type="number"
                                id="quick-sort-order"
                                min="0"
                                value="0"
                                class="form-control"
                            >
                        </div>

                        <div class="col-md-6 mb-1">
                            <label class="form-label">Prompt Label English</label>
                            <input type="text" id="quick-prompt-label-en" class="form-control">
                        </div>

                        <div class="col-md-6 mb-1">
                            <label class="form-label">Prompt Label Arabic</label>
                            <input type="text" id="quick-prompt-label-ar" class="form-control" dir="rtl">
                        </div>

                        <div class="col-md-6 mb-1">
                            <label class="form-label">Placeholder English</label>
                            <input type="text" id="quick-placeholder-en" class="form-control">
                        </div>

                        <div class="col-md-6 mb-1">
                            <label class="form-label">Placeholder Arabic</label>
                            <input type="text" id="quick-placeholder-ar" class="form-control" dir="rtl">
                        </div>

                        <div class="col-md-4 mb-1 d-flex align-items-end">
                            <div class="form-check form-switch mb-50">
                                <input
                                    type="checkbox"
                                    id="quick-required"
                                    value="1"
                                    class="form-check-input"
                                >

                                <label for="quick-required" class="form-check-label">
                                    Required by default
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-1 mt-1" id="quick-condition-section">
                        <div class="d-flex justify-content-between align-items-center gap-1">
                            <div>
                                <h6 class="mb-25">Conditional Visibility</h6>
                                <small class="text-muted">
                                    Show this question only when a selected parent question has a specific answer.
                                </small>
                            </div>

                            <div class="form-check form-switch mb-0">
                                <input
                                    type="checkbox"
                                    id="quick-condition-enabled"
                                    class="form-check-input"
                                >

                                <label
                                    for="quick-condition-enabled"
                                    class="form-check-label"
                                >
                                    Conditional
                                </label>
                            </div>
                        </div>

                        <div
                            id="quick-condition-panel"
                            class="question-condition-panel p-1 mt-1 d-none"
                        >
                            <div class="row">
                                <div class="col-md-5 mb-1">
                                    <label class="form-label">Parent Question *</label>

                                    <select
                                        id="quick-condition-parent-question"
                                        class="form-select"
                                    >
                                        <option value="">Select parent question</option>
                                    </select>

                                    <small class="text-muted">
                                        Only selected Single/Multi Select questions can be parents.
                                    </small>
                                </div>

                                <div class="col-md-4 mb-1">
                                    <label class="form-label">Answer *</label>

                                    <select
                                        id="quick-condition-parent-option"
                                        class="form-select"
                                    >
                                        <option value="">Select answer</option>
                                    </select>
                                </div>
                            </div>

                            <div
                                id="quick-condition-summary"
                                class="question-condition-summary d-none"
                            >
                                Show when
                                <strong id="quick-condition-summary-question"></strong>
                                <span id="quick-condition-summary-operator">has</span>
                                answer
                                <strong id="quick-condition-summary-option"></strong>.
                            </div>

                            <small class="text-muted d-block mt-50">
                                Required validation applies only while this condition is matched.
                            </small>
                        </div>
                    </div>

                    <div id="quick-options-section" class="border rounded p-1 mt-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <h6 class="mb-25">Options</h6>
                                <small class="text-muted" id="quick-options-help">
                                    Used for Single Select and Multi Select questions.
                                </small>
                            </div>

                            <button
                                type="button"
                                id="quick-add-option"
                                class="btn btn-sm btn-outline-primary"
                            >
                                <i data-feather="plus"></i>
                                Add Option
                            </button>
                        </div>

                        <div id="quick-options-container"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        id="quick-save-question"
                        class="btn btn-primary"
                    >
                        <i data-feather="save"></i>
                        Create & Select
                    </button>
                </div>
            </div>
        </div>
    </div>
@endcan

<script src="https://unpkg.com/feather-icons"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
<script>
    if (window.Dropzone) {
        Dropzone.autoDiscover = false;
    }
</script>

<script>
    $(function () {
        feather.replace();

        const singleSelect = @json(\App\Enums\Ai\AiGuideQuestionTypeEnum::SINGLE_SELECT->value);
        const multiSelect = @json(\App\Enums\Ai\AiGuideQuestionTypeEnum::MULTI_SELECT->value);
        const quickStoreUrl = @json(route('ai-categories.questions.quick-store'));
        const mediaStoreUrl = @json(route('media.store'));
        const mediaDeleteBaseUrl = @json(url('api/v1/media'));
        const currentAiCategoryId = @json($aiCategory?->id);
        const csrfToken = @json(csrf_token());

        const quickStudioStoreUrl = @json(route('ai-categories.studio-items.quick-store'));
        const quickStudioUpdateUrlTemplate = @json(route('ai-categories.studio-items.quick-update', ['studioItem' => '__STUDIO_ITEM_ID__']));
        const quickStudioDeleteUrlTemplate = @json(route('ai-categories.studio-items.quick-delete', ['studioItem' => '__STUDIO_ITEM_ID__']));
        const canEditStudioItems = @json(auth()->user()?->can('ai-studio-items_update') ?? false);
        const canDeleteStudioItems = @json(auth()->user()?->can('ai-studio-items_delete') ?? false);

        let studioItemData = @json($studioItemsPayload);
        const conditionQuestionData = @json($conditionQuestionsPayload);

        let quickOptionIndex = 0;
        let quickQuestionSaved = false;


        function toast(message, error = true) {
            Toastify({
                text: message,
                duration: 4000,
                close: true,
                gravity: 'top',
                position: 'right',
                backgroundColor: error ? '#EA5455' : '#28C76F'
            }).showToast();
        }

        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        function toggleStudioItemCard(input) {
            input
                .closest('.studio-item-card')
                .toggleClass('is-selected', input.is(':checked'));
        }

        $('.studio-item-checkbox').each(function () {
            toggleStudioItemCard($(this));
        });

        $(document).on('change', '.studio-item-checkbox', function () {
            toggleStudioItemCard($(this));
        });

        function resetStudioItemModal() {
            $('#quick-studio-item-id').val('');
            $('#quick-studio-item-modal-title').text('Add Studio Item');

            $('#quick-studio-name-en, #quick-studio-name-ar, #quick-studio-description-en, #quick-studio-description-ar, #quick-studio-prompt-instructions, #quick-studio-negative-rules').val('');

            $('#quick-studio-generation-type').val('');
            $('#quick-studio-credits-cost').val(1);
            $('#quick-studio-sort-order').val(0);
            $('#quick-studio-is-active').prop('checked', true);
            $('#quick-delete-studio-item').addClass('d-none');
        }
        function fillStudioItemModal(item) {
            $('#quick-studio-item-id').val(item.id);
            $('#quick-studio-item-modal-title').text(`Edit Studio Item: ${item.name ?? item.key ?? ''}`);

            $('#quick-studio-name-en').val(item.name_en ?? '');
            $('#quick-studio-name-ar').val(item.name_ar ?? '');
            $('#quick-studio-description-en').val(item.description_en ?? '');
            $('#quick-studio-description-ar').val(item.description_ar ?? '');

            $('#quick-studio-generation-type').val(item.generation_type ?? '');
            $('#quick-studio-credits-cost').val(Number(item.credits_cost ?? 1));
            $('#quick-studio-sort-order').val(Number(item.sort_order ?? 0));
            $('#quick-studio-is-active').prop('checked', !!item.is_active);

            $('#quick-studio-prompt-instructions').val(item.settings?.prompt_instructions ?? '');
            $('#quick-studio-negative-rules').val(item.settings?.negative_rules ?? '');
            if (canDeleteStudioItems) {
                $('#quick-delete-studio-item').removeClass('d-none');
            }
        }
        function buildStudioItemCard(item, selected = true) {
            const active = !!item.is_active;
            const checked = active && selected;
            const disabled = active ? '' : 'disabled';
            const statusClass = active ? 'bg-light-success text-success' : 'bg-light-danger text-danger';
            const statusText = active ? 'Active' : 'Inactive';
            const description = item.description
                ? `<small class="text-muted d-block mt-1 studio-item-description">${escapeHtml(item.description)}</small>`
                : '<small class="text-muted d-block mt-1 studio-item-description"></small>';

            return `
                <div class="col-md-4 mb-1 studio-item-column" data-studio-item-id="${item.id}">
                    <div class="ai-config-card studio-item-card border rounded p-1 w-100 h-100 ${checked ? 'is-selected' : ''}">
                        <div class="d-flex justify-content-between align-items-start gap-1">
                            <div class="form-check mb-0 flex-grow-1">
                                <input
                                    type="checkbox"
                                    id="studio-item-${item.id}"
                                    name="studio_items[]"
                                    value="${item.id}"
                                    class="form-check-input studio-item-checkbox"
                                    ${checked ? 'checked' : ''}
                                    ${disabled}
                                >

                                <label class="form-check-label fw-bolder" for="studio-item-${item.id}">
                                    ${escapeHtml(item.name)}
                                </label>
                            </div>

                            <div class="d-flex gap-50 align-items-center">
                                <span class="badge ${statusClass} studio-item-status-badge">
                                    ${statusText}
                                </span>

                                ${canEditStudioItems ? `
                                    <button type="button" class="btn btn-sm btn-outline-primary quick-edit-studio-item" data-id="${item.id}" title="Edit Studio Item">
                                        <i data-feather="edit-2"></i>
                                    </button>
                                ` : ''}

                                ${canDeleteStudioItems ? `
                                    <button type="button" class="btn btn-sm btn-outline-danger quick-delete-studio-item-card" data-id="${item.id}" title="Delete Studio Item">
                                        <i data-feather="trash-2"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </div>

                        <div class="mt-1">
                            <span class="badge bg-light-primary text-primary studio-item-key">
                                ${escapeHtml(item.key)}
                            </span>
                        </div>

                        ${description}

                        <div class="d-flex gap-1 mt-1">
                            <small class="text-muted studio-item-generation-type">
                                ${escapeHtml(item.generation_type_label)}
                            </small>
                            <small class="text-muted">•</small>
                            <small class="text-muted studio-item-credits">
                                ${Number(item.credits_cost ?? 0)} Credits
                            </small>
                        </div>
                    </div>
                </div>
            `;
        }

        function upsertStudioItemCard(item, selectNew = false) {
            studioItemData[item.id] = item;

            const current = $(`.studio-item-column[data-studio-item-id="${item.id}"]`);
            const wasSelected = current.length
                ? current.find('.studio-item-checkbox').is(':checked')
                : selectNew;

            const html = buildStudioItemCard(item, wasSelected);

            if (current.length) {
                current.replaceWith(html);
            } else {
                $('#no-studio-items-alert').remove();
                $('#studio-items-container').append(html);
            }

            const checkbox = $(`.studio-item-column[data-studio-item-id="${item.id}"] .studio-item-checkbox`);
            toggleStudioItemCard(checkbox);
            feather.replace();
        }

        $('#add-studio-item').on('click', function () {
            resetStudioItemModal();
        });

        $(document).on('click', '.quick-edit-studio-item', function () {
            const id = Number($(this).data('id'));
            const item = studioItemData[id];

            if (!item) {
                toast('Studio Item data could not be loaded.');
                return;
            }

            fillStudioItemModal(item);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('quick-studio-item-modal')).show();
        });

        function removeStudioItemCard(id) {
            delete studioItemData[id];
            $(`.studio-item-column[data-studio-item-id="${id}"]`).remove();
            if (!$('#studio-items-container .studio-item-column').length) {
                $('#studio-items-container').html(`
                    <div id="no-studio-items-alert" class="col-12">
                        <div class="alert alert-warning mb-0">
                            No Studio Items found. Use “Add Studio Item”.
                        </div>
                    </div>
                `);
            }
        }

        function deleteStudioItem(id) {
            const item = studioItemData[id];

            if (!item) {
                toast('Studio Item data could not be loaded.');
                return;
            }

            Swal.fire({
                title: 'Delete Studio Item?',
                html: `This will delete <strong>${escapeHtml(item.name ?? item.key ?? 'this Studio Item')}</strong> globally and remove it from all AI Products.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-outline-secondary ms-1'
                },
                buttonsStyling: false
            }).then(result => {
                if (!result.isConfirmed) return;

                const url = quickStudioDeleteUrlTemplate.replace('__STUDIO_ITEM_ID__', id);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: csrfToken
                    },
                    success: function (response) {
                        const modalElement = document.getElementById('quick-studio-item-modal');
                        const modal = bootstrap.Modal.getInstance(modalElement);

                        if (modal) modal.hide();

                        removeStudioItemCard(id);
                        resetStudioItemModal();
                        feather.replace();
                        toast(response.message ?? 'Studio Item deleted successfully.', false);
                    },
                    error: function (xhr) {
                        const response = xhr.responseJSON ?? {};
                        toast(response.message ?? 'Unable to delete Studio Item.');
                    }
                });
            });
        }

        $(document).on('click', '.quick-delete-studio-item-card', function () {
            deleteStudioItem(Number($(this).data('id')));
        });

        $('#quick-delete-studio-item').on('click', function () {
            const id = Number($('#quick-studio-item-id').val() || 0);
            if (id) deleteStudioItem(id);
        });

        $('#quick-save-studio-item').on('click', function () {
            const button = $(this);
            const originalHtml = button.html();
            const id = Number($('#quick-studio-item-id').val() || 0);
            const nameEn = $('#quick-studio-name-en').val().trim();
            const generationType = $('#quick-studio-generation-type').val();

            if (!nameEn) {
                toast('Studio Item English name is required.');
                return;
            }

            if (!generationType) {
                toast('Generation Type is required.');
                return;
            }

            const data = {
                name: {
                    en: nameEn,
                    ar: $('#quick-studio-name-ar').val().trim()
                },

                description: {
                    en: $('#quick-studio-description-en').val().trim(),
                    ar: $('#quick-studio-description-ar').val().trim()
                },

                generation_type: generationType,
                credits_cost: Number($('#quick-studio-credits-cost').val() || 0),
                sort_order: Number($('#quick-studio-sort-order').val() || 0),
                is_active: $('#quick-studio-is-active').is(':checked') ? 1 : 0,

                question_ids: id
                    ? (studioItemData[id]?.question_ids ?? [])
                    : [],

                settings: {
                    prompt_instructions: $('#quick-studio-prompt-instructions').val().trim(),
                    negative_rules: $('#quick-studio-negative-rules').val().trim()
                },

                _token: csrfToken
            };
            let url = quickStudioStoreUrl;

            if (id) {
                url = quickStudioUpdateUrlTemplate.replace('__STUDIO_ITEM_ID__', id);
                data._method = 'PUT';
            }

            button
                .prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm me-50"></span>Saving...');

            $.ajax({
                url: url,
                type: 'POST',
                data: data,

                success: function (response) {
                    const item = response.data ?? response;

                    upsertStudioItemCard(item, !id);

                    bootstrap.Modal
                        .getOrCreateInstance(document.getElementById('quick-studio-item-modal'))
                        .hide();

                    resetStudioItemModal();
                    toast(id ? 'Studio Item updated successfully.' : 'Studio Item created and selected.', false);
                },

                error: function (xhr) {
                    const response = xhr.responseJSON ?? {};

                    if (xhr.status === 422 && response.errors) {
                        Object.values(response.errors)
                            .flat()
                            .forEach(message => toast(message));
                        return;
                    }

                    toast(response.message ?? 'Unable to save Studio Item.');
                },

                complete: function () {
                    button.prop('disabled', false).html(originalHtml);
                    feather.replace();
                }
            });
        });

        $('#quick-studio-item-modal').on('hidden.bs.modal', function () {
            resetStudioItemModal();
        });



        function conditionParentOptionsHtml(currentQuestionId, selectedParentId = null) {
            return Object.values(conditionQuestionData)
                .filter(question => Number(question.id) !== Number(currentQuestionId))
                .map(question => `
                    <option
                        value="${Number(question.id)}"
                        ${Number(selectedParentId) === Number(question.id) ? 'selected' : ''}
                    >
                        ${escapeHtml(question.title)}
                    </option>
                `)
                .join('');
        }

        function normalizeConditionSelectedIds(selectedOptionIds = []) {
            const values = Array.isArray(selectedOptionIds)
                ? selectedOptionIds
                : [selectedOptionIds];

            return values
                .map(value => String(value ?? '').trim())
                .filter(Boolean);
        }

        function conditionAnswerOptionsHtml(parentQuestionId, selectedOptionIds = []) {
            const parent = conditionQuestionData[Number(parentQuestionId)];
            const selectedValues = normalizeConditionSelectedIds(selectedOptionIds);
            const selectedIds = new Set(selectedValues);
            const selectedOrder = new Map(
                selectedValues.map((id, index) => [String(id), index])
            );

            if (!parent || !Array.isArray(parent.options)) {
                return '';
            }

            /*
             * Keep selected answers in the same order they were selected/saved.
             * Unselected answers keep their original relative order afterwards.
             */
            const orderedOptions = [...parent.options].sort((a, b) => {
                const aId = String(a.id);
                const bId = String(b.id);
                const aSelected = selectedOrder.has(aId);
                const bSelected = selectedOrder.has(bId);

                if (aSelected && bSelected) {
                    return selectedOrder.get(aId) - selectedOrder.get(bId);
                }

                if (aSelected) return -1;
                if (bSelected) return 1;

                return 0;
            });

            return orderedOptions.map(option => `
                <option
                    value="${Number(option.id)}"
                    ${selectedIds.has(String(option.id)) ? 'selected' : ''}
                >
                    ${escapeHtml(option.label)}
                </option>
            `).join('');
        }

        function initConditionAnswersSelect2(select) {
            if (!select?.length || typeof $.fn.select2 !== 'function') {
                return;
            }

            if (select.hasClass('select2-hidden-accessible')) {
                select.select2('destroy');
            }

            select.select2({
                width: '100%',
                placeholder: 'Select one or more answers',
                allowClear: true,
                closeOnSelect: false
            });
        }
        $(document).on(
            'select2:select',
            '.condition-parent-options',
            function (e) {
                const select = $(this);
                const selectedId = String(e.params.data.id);

                const selectedOption = select
                    .find('option')
                    .filter(function () {
                        return String($(this).val()) === selectedId;
                    });

                /*
                 * Select2 normally renders selected tags using <option> order.
                 * Move the newest selection after the previously selected ones
                 * so the visible tags follow the user's click order.
                 */
                selectedOption.detach().appendTo(select);

                const selectedValues = select
                    .find('option:selected')
                    .map(function () {
                        return String($(this).val());
                    })
                    .get();

                /*
                 * Re-initialize after the event finishes so Select2 redraws
                 * the tags using the new option order.
                 */
                setTimeout(function () {
                    if (select.hasClass('select2-hidden-accessible')) {
                        select.select2('destroy');
                    }

                    initConditionAnswersSelect2(select);

                    select
                        .val(selectedValues)
                        .trigger('change.select2');
                }, 0);
            }
        );

        function populateConditionAnswers(rule, selectedOptionIds = []) {
            const parentQuestionId = Number(
                rule.find('.condition-parent-question').val() || 0
            );

            const optionSelect = rule.find('.condition-parent-options');

            if (optionSelect.hasClass('select2-hidden-accessible')) {
                optionSelect.select2('destroy');
            }

            optionSelect.html(
                conditionAnswerOptionsHtml(
                    parentQuestionId,
                    selectedOptionIds
                )
            );

            initConditionAnswersSelect2(optionSelect);
        }

        function nextConditionIndex(card) {
            const container = card.find('.condition-rules');
            const next = Number(container.attr('data-next-index') || 0);

            container.attr('data-next-index', next + 1);

            return next;
        }

        function buildConditionRuleHtml(
            questionId,
            index,
            condition = {}
        ) {
            const parentQuestionId = Number(
                condition.parent_question_id || 0
            );

            const selectedOptionIds = normalizeConditionSelectedIds(
                condition.parent_option_ids
                ?? condition.parent_option_id
                ?? []
            );

            const operator = condition.operator || 'selected';

            return `
                <div
                    class="condition-rule border rounded p-1 mb-1"
                    data-condition-index="${index}"
                >
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-1">
                            <label class="form-label">Parent Question *</label>

                            <select
                                name="questions[${questionId}][conditions][${index}][parent_question_id]"
                                class="form-select condition-parent-question"
                            >
                                <option value="">Select parent question</option>
                                ${conditionParentOptionsHtml(
                questionId,
                parentQuestionId
            )}
                            </select>
                        </div>

                        <div class="col-md-4 mb-1">
                            <label class="form-label">
                                Answers * <small class="text-muted">(OR)</small>
                            </label>

                            <select
                                name="questions[${questionId}][conditions][${index}][parent_option_ids][]"
                                class="form-select condition-parent-options"
                                multiple
                                style="width:100%"
                            >
                                ${conditionAnswerOptionsHtml(
                parentQuestionId,
                selectedOptionIds
            )}
                            </select>
                        </div>
                        <div class="col-md-2 mb-1">
                            <button
                                type="button"
                                class="btn btn-outline-danger w-100 remove-condition-rule"
                            >
                                <i data-feather="trash-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        function addConditionRule(card, condition = {}) {
            const questionId = Number(card.data('question-id'));
            const index = nextConditionIndex(card);
            const container = card.find('.condition-rules');

            container.append(
                buildConditionRuleHtml(
                    questionId,
                    index,
                    condition
                )
            );

            const rule = container.find('.condition-rule').last();

            initConditionAnswersSelect2(
                rule.find('.condition-parent-options')
            );

            feather.replace();

            return rule;
        }

        function ensureConditionRule(card) {
            if (!card.find('.condition-rule').length) {
                addConditionRule(card);
            }
        }

        function updateConditionSummary(card) {
            const enabled = card.find('.question-condition-toggle').is(':checked');
            const panel = card.find('.question-condition-panel');
            const badge = card.find('.question-condition-badge');
            const summary = card.find('.question-condition-summary');

            panel.toggleClass('d-none', !enabled);
            badge.toggleClass('d-none', !enabled);
            summary.toggleClass('d-none', !enabled);

            if (enabled) {
                ensureConditionRule(card);

                card.find('.condition-parent-options').each(function () {
                    initConditionAnswersSelect2($(this));
                });
            }
        }

        function initializeQuestionCondition(card) {
            card.find('.condition-parent-options').each(function () {
                initConditionAnswersSelect2($(this));
            });

            updateConditionSummary(card);
        }

        function toggleQuestion(card) {
            const checked = card.find('.question-toggle').is(':checked');

            card.toggleClass('is-selected', checked);
            card.find('.question-settings').toggle(checked);
            card.find('.question-options').toggle(checked);
            card.find('.question-condition-settings').toggle(checked);
        }

        $('.question-card').each(function () {
            const card = $(this);

            toggleQuestion(card);
            initializeQuestionCondition(card);
        });

        $(document).on('change', '.question-toggle', function () {
            toggleQuestion($(this).closest('.question-card'));
        });

        $(document).on('change', '.question-condition-toggle', function () {
            updateConditionSummary($(this).closest('.question-card'));
        });

        $(document).on('click', '.add-condition-rule', function () {
            addConditionRule($(this).closest('.question-card'));
        });

        $(document).on('click', '.remove-condition-rule', function () {
            const card = $(this).closest('.question-card');
            const rule = $(this).closest('.condition-rule');
            const optionSelect = rule.find('.condition-parent-options');

            if (optionSelect.hasClass('select2-hidden-accessible')) {
                optionSelect.select2('destroy');
            }

            rule.remove();

            if (card.find('.question-condition-toggle').is(':checked')) {
                ensureConditionRule(card);
            }

            feather.replace();
        });

        $(document).on('change', '.condition-parent-question', function () {
            const rule = $(this).closest('.condition-rule');

            populateConditionAnswers(rule, []);
        });

        $('#select-all-questions').on('click', function () {
            $('.question-toggle')
                .prop('checked', true)
                .trigger('change');
        });

        $('#clear-all-questions').on('click', function () {
            $('.question-toggle')
                .prop('checked', false)
                .trigger('change');
        });

        $(document).on('click', '.select-question-options', function () {
            $(this)
                .closest('.question-options')
                .find('.option-checkbox')
                .prop('checked', true);
        });

        $(document).on('click', '.clear-question-options', function () {
            $(this)
                .closest('.question-options')
                .find('.option-checkbox')
                .prop('checked', false);
        });

        function quickConditionEnabled() {
            return $('#quick-condition-enabled').is(':checked');
        }

        function selectedConditionalParentIds() {
            return $('#questions-container .question-card')
                .filter(function () {
                    return $(this).find('.question-toggle').is(':checked');
                })
                .map(function () {
                    return Number($(this).data('question-id'));
                })
                .get()
                .filter(Boolean);
        }

        function quickConditionParentOptionsHtml(selectedParentId = null) {
            const selectedIds = new Set(selectedConditionalParentIds());

            return Object.values(conditionQuestionData)
                .filter(question => selectedIds.has(Number(question.id)))
                .map(question => `
                    <option
                        value="${Number(question.id)}"
                        ${Number(selectedParentId) === Number(question.id) ? 'selected' : ''}
                    >
                        ${escapeHtml(question.title)}
                    </option>
                `)
                .join('');
        }

        function populateQuickConditionParents(selectedParentId = null) {
            const select = $('#quick-condition-parent-question');

            if (!select.length) return;

            select.html(`
                <option value="">Select parent question</option>
                ${quickConditionParentOptionsHtml(selectedParentId)}
            `);
        }

        function populateQuickConditionAnswers(selectedOptionId = null) {
            const parentQuestionId = Number(
                $('#quick-condition-parent-question').val() || 0
            );

            $('#quick-condition-parent-option').html(`
                <option value="">Select answer</option>
                ${conditionAnswerOptionsHtml(parentQuestionId, selectedOptionId)}
            `);
        }

        function updateQuickConditionSummary() {
            const enabled = quickConditionEnabled();
            const panel = $('#quick-condition-panel');
            const summary = $('#quick-condition-summary');

            panel.toggleClass('d-none', !enabled);

            if (!enabled) {
                summary.addClass('d-none');
                return;
            }

            const parentId = Number(
                $('#quick-condition-parent-question').val() || 0
            );

            const optionId = Number(
                $('#quick-condition-parent-option').val() || 0
            );

            const operator = $('#quick-condition-operator').val();
            const parent = conditionQuestionData[parentId];

            const option = parent?.options?.find(
                item => Number(item.id) === optionId
            );

            if (!parent || !option) {
                summary.addClass('d-none');
                return;
            }

            $('#quick-condition-summary-question').text(parent.title);
            $('#quick-condition-summary-operator').text(
                operator === 'not_selected'
                    ? 'does not have'
                    : 'has'
            );
            $('#quick-condition-summary-option').text(option.label);

            summary.removeClass('d-none');
        }

        function ensureQuickConditionSortOrder() {
            const parentQuestionId = Number(
                $('#quick-condition-parent-question').val() || 0
            );

            if (!parentQuestionId) return;

            const parentCard = $(
                `.question-card[data-question-id="${parentQuestionId}"]`
            );

            if (!parentCard.length) return;

            const parentSort = Number(
                parentCard
                    .find(`input[name="questions[${parentQuestionId}][sort_order]"]`)
                    .val() || 0
            );

            const currentSort = Number(
                $('#quick-sort-order').val() || 0
            );

            if (currentSort <= parentSort) {
                $('#quick-sort-order').val(parentSort + 1);
            }
        }

        $('#quick-condition-enabled').on('change', function () {
            if (quickConditionEnabled()) {
                populateQuickConditionParents(
                    $('#quick-condition-parent-question').val()
                );
            }

            updateQuickConditionSummary();
        });

        $('#quick-condition-parent-question').on('change', function () {
            populateQuickConditionAnswers();
            ensureQuickConditionSortOrder();
            updateQuickConditionSummary();
        });

        $('#quick-condition-parent-option, #quick-condition-operator').on(
            'change',
            updateQuickConditionSummary
        );

        $('#quick-question-modal').on('shown.bs.modal', function () {
            const currentParentId =
                $('#quick-condition-parent-question').val();

            populateQuickConditionParents(currentParentId);
            populateQuickConditionAnswers(
                $('#quick-condition-parent-option').val()
            );
            updateQuickConditionSummary();
        });

        function quickPaletteEnabled() {
            return $('#quick-color-palette-question').is(':checked');
        }

        function quickSupportsOptions() {
            if (quickPaletteEnabled()) {
                return true;
            }

            const type = $('#quick-question-type').val();

            return type === singleSelect || type === multiSelect;
        }

        function toggleQuickOptions() {
            const supportsOptions = quickSupportsOptions();

            $('#quick-options-section').toggle(supportsOptions);

            if (
                supportsOptions
                && !$('#quick-options-container .quick-option-row').length
            ) {
                addQuickOption();
            }
        }

        function toggleQuickPaletteMode() {
            const enabled = quickPaletteEnabled();
            const typeSelect = $('#quick-question-type');

            if (enabled) {
                typeSelect.val(singleSelect);

                typeSelect.find('option').each(function () {
                    $(this).prop(
                        'disabled',
                        $(this).val() !== singleSelect
                    );
                });

                $('#quick-options-help').text(
                    'Each option represents one color palette. Add one or more colors to every palette.'
                );

                $('#quick-options-section').show();

                if (!$('#quick-options-container .quick-option-row').length) {
                    addQuickOption();
                }

                $('#quick-options-container .quick-normal-option-fields').hide();
                $('#quick-options-container .quick-color-palette-section').removeClass('d-none');

                $('#quick-options-container .quick-option-row').each(function () {
                    const row = $(this);

                    clearQuickOptionImage(row);

                    if (!row.find('.quick-palette-color-row').length) {
                        addQuickPaletteColor(row, '#000000');
                    }

                    updateQuickPaletteValues(row);
                    updateQuickPalettePreview(row);
                });

                return;
            }

            typeSelect.find('option').prop('disabled', false);

            $('#quick-options-help').text(
                'Used for Single Select and Multi Select questions.'
            );

            $('#quick-options-container .quick-normal-option-fields').show();
            $('#quick-options-container .quick-color-palette-section').addClass('d-none');

            toggleQuickOptions();
        }

        function deleteQuickOptionMedia(mediaId) {
            if (!mediaId) return Promise.resolve();

            return fetch(`${mediaDeleteBaseUrl}/${mediaId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            }).then(async response => {
                if (response.ok) return;

                let message = 'Unable to remove image.';

                try {
                    const data = await response.json();
                    message = data?.message ?? message;
                } catch (_) {}

                throw new Error(message);
            });
        }

        function initQuickOptionDropzone(optionRow) {
            if (!window.Dropzone) {
                console.error('Dropzone is not loaded.');
                return null;
            }

            const element = optionRow.find('.quick-option-dropzone')[0];

            if (!element) return null;
            if (element.dropzone) return element.dropzone;

            const hiddenInput = optionRow.find('.quick-option-media-id');

            const REQUIRED_WIDTH = 48;
            const REQUIRED_HEIGHT = 48;
            const MAX_FILESIZE_MB = 2;

            const dz = new Dropzone(element, {
                url: mediaStoreUrl,
                paramName: 'file',
                maxFiles: 1,
                acceptedFiles: 'image/*',
                maxFilesize: MAX_FILESIZE_MB,
                addRemoveLinks: true,
                clickable: true,
                thumbnailWidth: 160,
                thumbnailHeight: 160,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },

                accept: function (file, done) {
                    const img = new Image();
                    const objectUrl = URL.createObjectURL(file);

                    img.onload = function () {
                        URL.revokeObjectURL(objectUrl);

                        if (img.width !== REQUIRED_WIDTH || img.height !== REQUIRED_HEIGHT) {
                            done(`Image must be exactly ${REQUIRED_WIDTH}x${REQUIRED_HEIGHT}px (uploaded: ${img.width}x${img.height}px).`);
                            return;
                        }

                        done();
                    };

                    img.onerror = function () {
                        URL.revokeObjectURL(objectUrl);
                        done('Unable to read image dimensions.');
                    };

                    img.src = objectUrl;
                },

                success: function (file, response) {
                    const mediaId = response?.data?.id ?? null;
                    const imageUrl = response?.data?.original_url ?? response?.data?.url ?? null;

                    if (!mediaId) {
                        hiddenInput.val('');
                        toast('Image uploaded but media ID was not returned.');
                        return;
                    }

                    hiddenInput.val(mediaId);
                    file._mediaId = String(mediaId);
                    file._imageUrl = imageUrl;
                    file._deleteOnRemove = true;

                    element.classList.add('dz-started');
                },

                removedfile: function (file) {
                    const mediaId = String(file._mediaId ?? '');
                    const currentMediaId = String(hiddenInput.val() ?? '');

                    if (file.previewElement) {
                        file.previewElement.remove();
                    }

                    if (!mediaId || mediaId === currentMediaId) {
                        hiddenInput.val('');
                    }

                    if (mediaId && file._deleteOnRemove !== false) {
                        deleteQuickOptionMedia(mediaId).catch(error => {
                            toast(error.message ?? 'Unable to remove image.');
                        });
                    }

                    if (!this.files.length) {
                        element.classList.remove('dz-started');
                    }
                },

                maxfilesexceeded: function (file) {
                    this.removeAllFiles(true);
                    this.addFile(file);
                },

                error: function (file, response) {
                    const message = typeof response === 'string'
                        ? response
                        : response?.message ?? 'Unable to upload image.';

                    toast(message);

                    if (file.previewElement) {
                        file.previewElement.classList.add('dz-error');
                    }
                }
            });

            return dz;
        }
        function destroyQuickOptionDropzone(optionRow, deleteMedia = true) {
            const element = optionRow.find('.quick-option-dropzone')[0];

            if (!element?.dropzone) return;

            element.dropzone.files.forEach(file => {
                file._deleteOnRemove = deleteMedia;
            });

            element.dropzone.destroy();
        }

        function clearQuickOptionImage(optionRow) {
            const element = optionRow.find('.quick-option-dropzone')[0];

            if (!element?.dropzone) {
                optionRow.find('.quick-option-media-id').val('');
                return;
            }

            element.dropzone.files.forEach(file => {
                file._deleteOnRemove = true;
            });

            element.dropzone.removeAllFiles(true);
            optionRow.find('.quick-option-media-id').val('');
        }

        function addQuickOption() {
            const index = quickOptionIndex++;

            $('#quick-options-container').append(`
                <div
                    class="quick-option-row border rounded p-1 mb-1"
                    data-option-index="${index}"
                >
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong>Option ${index + 1}</strong>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger quick-remove-option"
                        >
                            <i data-feather="trash-2"></i>
                            Remove
                        </button>
                    </div>

                    <div class="row mb-1 quick-option-label-fields">
                        <div class="col-md-6">
                            <label class="form-label">Label English *</label>
                            <input type="text" class="form-control quick-option-label-en" placeholder="e.g. Warm Sunset">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Label Arabic</label>
                            <input type="text" class="form-control quick-option-label-ar" dir="rtl" placeholder="مثال: غروب دافئ">
                        </div>
                    </div>

                    <div class="quick-normal-option-fields">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Prompt Value English</label>
                                <textarea class="form-control quick-option-prompt-en" rows="2"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Prompt Value Arabic</label>
                                <textarea class="form-control quick-option-prompt-ar" rows="2" dir="rtl"></textarea>
                            </div>

                            <div class="col-12 mt-1">
                                <label class="form-label">Option Image</label>

                                <div class="quick-option-dropzone">
                                    <div class="dz-message">
                                        Drop image here or click to upload
                                    </div>
 <div class="text-center text-muted small mt-50">
                                        JPG, PNG, WEBP - Max 2MB (48x48)
                                    </div>
                                </div>

                                <input type="hidden" class="quick-option-media-id">

                                <small class="text-muted d-block mt-50">
                                    Optional. One image per option.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="quick-color-palette-section d-none">
                        <div class="border rounded p-1 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <label class="form-label mb-0">
                                        Color Palette
                                    </label>

                                    <small class="text-muted d-block">
                                        Write a label above for this palette, then add the colors that belong to it.
                                    </small>
                                </div>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary quick-add-palette-color"
                                >
                                    <i data-feather="plus"></i>
                                    Add Color
                                </button>
                            </div>

                            <div class="quick-palette-preview d-flex flex-wrap gap-50 mb-1"></div>
                            <div class="quick-palette-colors"></div>
                        </div>
                    </div>
                </div>
            `);

            const row = $('#quick-options-container .quick-option-row').last();

            initQuickOptionDropzone(row);

            if (quickPaletteEnabled()) {
                row.find('.quick-normal-option-fields').hide();
                row.find('.quick-color-palette-section').removeClass('d-none');
                addQuickPaletteColor(row, '#000000');
            }

            feather.replace();
        }

        function addQuickPaletteColor(optionRow, color = '#000000') {
            const normalizedColor = normalizeHexColor(color) ?? '#000000';

            optionRow.find('.quick-palette-colors').append(`
                <div class="quick-palette-color-row d-flex align-items-center gap-1 mb-1">
                    <input
                        type="color"
                        value="${normalizedColor}"
                        class="form-control form-control-color quick-palette-picker"
                        style="width:50px"
                    >

                    <input
                        type="text"
                        value="${normalizedColor}"
                        class="form-control quick-palette-value"
                        placeholder="#000000"
                        maxlength="7"
                    >

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-danger quick-remove-palette-color"
                    >
                        <i data-feather="x"></i>
                    </button>
                </div>
            `);

            updateQuickPalettePreview(optionRow);
            updateQuickPaletteValues(optionRow);

            feather.replace();
        }

        function normalizeHexColor(value) {
            const color = String(value ?? '').trim().toUpperCase();

            return /^#[0-9A-F]{6}$/.test(color)
                ? color
                : null;
        }

        function getQuickPaletteColors(optionRow) {
            return optionRow
                .find('.quick-palette-value')
                .map(function () {
                    return normalizeHexColor($(this).val());
                })
                .get()
                .filter(Boolean)
                .filter((color, index, colors) => colors.indexOf(color) === index);
        }

        function updateQuickPalettePreview(optionRow) {
            const preview = optionRow.find('.quick-palette-preview');
            const colors = getQuickPaletteColors(optionRow);

            preview.empty();

            colors.forEach(color => {
                preview.append(`
                    <div
                        title="${color}"
                        style="
                            width:42px;
                            height:42px;
                            border-radius:6px;
                            background:${color};
                            border:1px solid rgba(0,0,0,.1);
                        "
                    ></div>
                `);
            });
        }

        function updateQuickPaletteValues(optionRow) {
            if (!quickPaletteEnabled()) {
                return;
            }

            const colors = getQuickPaletteColors(optionRow);
            const prompt = colors.length
                ? `Use this exact color palette: ${colors.join(', ')}`
                : '';

            optionRow
                .find('.quick-option-prompt-en')
                .val(prompt);

            optionRow
                .find('.quick-option-prompt-ar')
                .val(prompt);
        }

        function updateAllQuickPaletteValues() {
            $('#quick-options-container .quick-option-row').each(function () {
                updateQuickPaletteValues($(this));
                updateQuickPalettePreview($(this));
            });
        }

        $('#quick-question-type').on('change', function () {
            if (!quickPaletteEnabled()) {
                toggleQuickOptions();
            }
        });

        $('#quick-color-palette-question').on('change', function () {
            toggleQuickPaletteMode();
        });

        $('#quick-add-option').on('click', function () {
            addQuickOption();
        });

        $(document).on('click', '.quick-remove-option', function () {
            const row = $(this).closest('.quick-option-row');

            destroyQuickOptionDropzone(row, true);
            row.remove();

            if (quickPaletteEnabled()) {
                updateAllQuickPaletteValues();
            }
        });

        $(document).on('click', '.quick-add-palette-color', function () {
            addQuickPaletteColor(
                $(this).closest('.quick-option-row')
            );
        });

        $(document).on('click', '.quick-remove-palette-color', function () {
            const optionRow = $(this).closest('.quick-option-row');

            $(this).closest('.quick-palette-color-row').remove();

            updateQuickPalettePreview(optionRow);
            updateQuickPaletteValues(optionRow);
        });

        $(document).on('input', '.quick-palette-picker', function () {
            const row = $(this).closest('.quick-palette-color-row');
            const optionRow = $(this).closest('.quick-option-row');
            const color = normalizeHexColor($(this).val()) ?? '#000000';

            row.find('.quick-palette-value').val(color);

            updateQuickPalettePreview(optionRow);
            updateQuickPaletteValues(optionRow);
        });

        $(document).on('input change', '.quick-palette-value', function () {
            const row = $(this).closest('.quick-palette-color-row');
            const optionRow = $(this).closest('.quick-option-row');
            const color = normalizeHexColor($(this).val());

            if (color) {
                $(this).val(color);
                row.find('.quick-palette-picker').val(color);
            }

            updateQuickPalettePreview(optionRow);
            updateQuickPaletteValues(optionRow);
        });

        function resetQuickQuestionModal(deleteTemporaryMedia = true) {
            $('#quick-title-en, #quick-title-ar, #quick-prompt-label-en, #quick-prompt-label-ar, #quick-placeholder-en, #quick-placeholder-ar').val('');
            $('#quick-sort-order').val(0);
            $('#quick-required').prop('checked', false);
            $('#quick-color-palette-question').prop('checked', false);

            $('#quick-condition-enabled').prop('checked', false);
            $('#quick-condition-parent-question').html(
                '<option value="">Select parent question</option>'
            );
            $('#quick-condition-parent-option').html(
                '<option value="">Select answer</option>'
            );
            $('#quick-condition-operator').val('selected');
            $('#quick-condition-panel').addClass('d-none');
            $('#quick-condition-summary').addClass('d-none');

            $('#quick-question-type')
                .find('option')
                .prop('disabled', false);

            $('#quick-question-type').val(singleSelect);

            $('#quick-options-container .quick-option-row').each(function () {
                destroyQuickOptionDropzone($(this), deleteTemporaryMedia);
            });

            $('#quick-options-container').empty();

            quickOptionIndex = 0;

            toggleQuickPaletteMode();
            toggleQuickOptions();
        }

        function buildOptionVisual(option) {
            const imageUrl = option.image ?? option.image_url ?? null;

            if (imageUrl) {
                return `
                    <div class="mt-1">
                        <img
                            src="${escapeHtml(imageUrl)}"
                            alt=""
                            style="width:100%;height:90px;object-fit:cover;border-radius:6px"
                        >
                    </div>
                `;
            }

            const colors = Array.isArray(option.colors)
                ? option.colors
                    .map(normalizeHexColor)
                    .filter(Boolean)
                : [];

            if (colors.length) {
                return `
                    <div class="d-flex mt-1 overflow-hidden rounded" style="height:34px">
                        ${colors.map(color => `
                            <span
                                title="${color}"
                                style="background:${color};flex:1;min-width:24px"
                            ></span>
                        `).join('')}
                    </div>
                `;
            }

            if (option.prompt_value) {
                return `
                    <small class="text-muted d-block mt-50">
                        ${escapeHtml(option.prompt_value)}
                    </small>
                `;
            }

            return '';
        }

        function buildQuestionCard(question) {
            const id = Number(question.id);
            const supportsOptions =
                question.type === singleSelect
                || question.type === multiSelect;

            const options = Array.isArray(question.options)
                ? question.options
                : [];

            let optionsHtml = '';

            if (supportsOptions && options.length) {
                optionsHtml = `
                    <div class="question-options question-options-panel border-top p-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <h6 class="mb-25">Allowed Options</h6>
                                <small class="text-muted">
                                    Choose which options appear for this product.
                                </small>
                            </div>

                            <div class="d-flex gap-50">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary select-question-options"
                                >
                                    Select All
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary clear-question-options"
                                >
                                    Clear
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            ${options.map(option => `
                                <div class="col-md-4 col-lg-3 mb-1">
                                    <label
                                        class="border rounded p-1 w-100 h-100 option-item"
                                        for="question-${id}-option-${option.id}"
                                        style="cursor:pointer"
                                    >
                                        <div class="form-check mb-0">
                                            <input
                                                type="checkbox"
                                                id="question-${id}-option-${option.id}"
                                                name="questions[${id}][options][]"
                                                value="${option.id}"
                                                class="form-check-input option-checkbox"
                                                checked
                                            >

                                            <span class="form-check-label fw-bolder">
                                                ${escapeHtml(option.label)}
                                            </span>
                                        </div>

                                        ${buildOptionVisual(option)}
                                    </label>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            return `
                <div
                    class="ai-config-card question-card border rounded mb-2 is-selected"
                    data-question-id="${id}"
                >
                    <div class="p-1">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <div class="form-check">
                                    <input
                                        type="hidden"
                                        name="questions[${id}][question_id]"
                                        value="${id}"
                                    >

                                    <input
                                        type="hidden"
                                        name="questions[${id}][selected]"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        id="question-${id}"
                                        name="questions[${id}][selected]"
                                        value="1"
                                        class="form-check-input question-toggle"
                                        checked
                                    >

                                    <label
                                        class="form-check-label"
                                        for="question-${id}"
                                    >
                                        <div class="fw-bolder">
                                            ${escapeHtml(question.title)}
                                        </div>

                                        <small class="text-muted">
                                            ${escapeHtml(question.prompt_label)}
                                        </small>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="question-settings d-flex justify-content-end align-items-center gap-2">
                                    <span class="badge bg-light-primary text-primary">
                                        ${escapeHtml(question.type_label)}
                                    </span>

                                    <span class="badge bg-light-warning text-warning question-condition-badge d-none">
                                        Conditional
                                    </span>

                                    ${question.isColorPalette ? `
                                        <span class="badge bg-light-info text-info">
                                            Color Palette
                                        </span>
                                    ` : ''}

                                    <div class="form-check form-switch">
                                        <input
                                            type="hidden"
                                            name="questions[${id}][required]"
                                            value="0"
                                        >

                                        <input
                                            type="checkbox"
                                            name="questions[${id}][required]"
                                            value="1"
                                            class="form-check-input"
                                            ${question.required ? 'checked' : ''}
                                        >

                                        <label class="form-check-label">
                                            Required
                                        </label>
                                    </div>

                                    <div style="width:85px">
                                        <input
                                            type="number"
                                            name="questions[${id}][sort_order]"
                                            value="${Number(question.sort_order ?? 0)}"
                                            min="0"
                                            class="form-control form-control-sm"
                                            placeholder="Order"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="question-condition-settings border-top p-1">
                        <div class="d-flex justify-content-between align-items-center gap-1">
                            <div>
                                <div class="fw-bolder">Conditional Visibility</div>
                                <small class="text-muted">
                                    Show this question only when parent answers match.
                                </small>
                            </div>

                            <div class="form-check form-switch mb-0">
                                <input
                                    type="hidden"
                                    name="questions[${id}][condition_enabled]"
                                    value="0"
                                >

                                <input
                                    type="checkbox"
                                    id="question-condition-${id}"
                                    name="questions[${id}][condition_enabled]"
                                    value="1"
                                    class="form-check-input question-condition-toggle"
                                >

                                <label class="form-check-label" for="question-condition-${id}">
                                    Conditional
                                </label>
                            </div>
                        </div>

                        <div class="question-condition-panel p-1 mt-1 d-none">
                            <div
                                class="condition-rules"
                                data-next-index="1"
                            >
                                ${buildConditionRuleHtml(id, 0)}
                            </div>

                            <div class="d-flex justify-content-between align-items-center gap-1 mt-50">
                                <small class="text-muted">
                                    Multiple answers in one row are OR. Different rows are AND.
                                </small>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary add-condition-rule"
                                >
                                    <i data-feather="plus"></i>
                                    Add Parent Rule
                                </button>
                            </div>

                            <div class="question-condition-summary mt-1 d-none">
                                All parent rules must match. Inside each rule, any selected answer can match.
                            </div>

                            <small class="text-muted d-block mt-50">
                                If this question is Required, validation applies only while all parent rules are matched.
                            </small>
                        </div>
                    </div>

                    ${optionsHtml}
                </div>
            `;
        }

        function collectQuickOptions() {
            const options = [];
            let error = null;

            $('#quick-options-container .quick-option-row').each(function () {
                if (error) {
                    return;
                }

                const row = $(this);

                if (quickPaletteEnabled()) {
                    const labelEn = row.find('.quick-option-label-en').val().trim();
                    const colors = getQuickPaletteColors(row);

                    if (!labelEn) {
                        error = 'Write an English label for every color palette.';
                        return;
                    }

                    if (!colors.length) {
                        error = 'Each color palette option must contain at least one valid color.';
                        return;
                    }

                    updateQuickPaletteValues(row);

                    options.push({
                        label: {
                            en: labelEn,
                            ar: row.find('.quick-option-label-ar').val().trim()
                        },

                        prompt_value: {
                            en: row.find('.quick-option-prompt-en').val().trim(),
                            ar: row.find('.quick-option-prompt-ar').val().trim()
                        },

                        is_active: 1
                    });
                    return;
                }

                const labelEn = row
                    .find('.quick-option-label-en')
                    .val()
                    .trim();

                if (!labelEn) {
                    error = 'English label is required for every option.';
                    return;
                }

                options.push({
                    label: {
                        en: labelEn,
                        ar: row.find('.quick-option-label-ar').val().trim()
                    },
                    prompt_value: {
                        en: row.find('.quick-option-prompt-en').val().trim(),
                        ar: row.find('.quick-option-prompt-ar').val().trim()
                    },
                    media_id: row.find('.quick-option-media-id').val() || null,
                    is_active: 1
                });
            });

            return {
                options,
                error
            };
        }

        $('#quick-save-question').on('click', function () {
            const button = $(this);
            const originalHtml = button.html();
            const titleEn = $('#quick-title-en').val().trim();

            if (!titleEn) {
                toast('Question English is required.');
                return;
            }

            if (quickPaletteEnabled()) {
                $('#quick-question-type').val(singleSelect);
                updateAllQuickPaletteValues();
            }

            const collected = quickSupportsOptions()
                ? collectQuickOptions()
                : {options: [], error: null};

            if (collected.error) {
                toast(collected.error);
                return;
            }

            if (quickSupportsOptions() && !collected.options.length) {
                toast(
                    quickPaletteEnabled()
                        ? 'Add at least one color palette.'
                        : 'Add at least one option.'
                );
                return;
            }

            let pendingCondition = null;

            if (quickConditionEnabled()) {
                const parentQuestionId = Number(
                    $('#quick-condition-parent-question').val() || 0
                );

                const parentOptionId = Number(
                    $('#quick-condition-parent-option').val() || 0
                );

                const operator =
                    $('#quick-condition-operator').val() || 'selected';

                // if (!parentQuestionId) {
                //     toast('Select the parent question for the condition.');
                //     return;
                // }
                //
                // if (!parentOptionId) {
                //     toast('Select the parent answer for the condition.');
                //     return;
                // }

                const parentCard = $(
                    `.question-card[data-question-id="${parentQuestionId}"]`
                );

                if (
                    !parentCard.length
                    || !parentCard.find('.question-toggle').is(':checked')
                ) {
                    toast(
                        'The parent question must be selected for this AI Product.'
                    );
                    return;
                }

                const parent = conditionQuestionData[parentQuestionId];
                const parentOption = parent?.options?.find(
                    item => Number(item.id) === parentOptionId
                );

                if (!parent || !parentOption) {
                    toast(
                        'The selected answer does not belong to the parent question.'
                    );
                    return;
                }

                ensureQuickConditionSortOrder();

                pendingCondition = {
                    parent_question_id: parentQuestionId,
                    parent_option_id: parentOptionId,
                    operator: operator
                };
            }

            button
                .prop('disabled', true)
                .html(
                    '<span class="spinner-border spinner-border-sm me-50"></span>Creating...'
                );

            $.ajax({
                url: quickStoreUrl,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                data: {
                    ai_category_id: currentAiCategoryId,
                    title: {
                        en: titleEn,
                        ar: $('#quick-title-ar').val().trim()
                    },

                    type: $('#quick-question-type').val(),

                    prompt_label: {
                        en: $('#quick-prompt-label-en').val().trim(),
                        ar: $('#quick-prompt-label-ar').val().trim()
                    },

                    placeholder: {
                        en: $('#quick-placeholder-en').val().trim(),
                        ar: $('#quick-placeholder-ar').val().trim()
                    },

                    required: $('#quick-required').is(':checked')
                        ? 1
                        : 0,

                    is_active: 1,

                    sort_order: Number(
                        $('#quick-sort-order').val() || 0
                    ),

                    options: collected.options
                },

                success: function (response) {
                    const question = response.data ?? response;

                    $('#no-questions-alert').remove();

                    conditionQuestionData[Number(question.id)] = {
                        id: Number(question.id),
                        title: question.title,
                        options: Array.isArray(question.options)
                            ? question.options.map(option => ({
                                id: Number(option.id),
                                label: option.label
                            }))
                            : []
                    };

                    $('#questions-container').append(
                        buildQuestionCard(question)
                    );

                    const newQuestionCard = $(
                        `.question-card[data-question-id="${Number(question.id)}"]`
                    );

                    toggleQuestion(newQuestionCard);
                    initializeQuestionCondition(newQuestionCard);

                    if (pendingCondition) {
                        newQuestionCard
                            .find('.question-condition-toggle')
                            .prop('checked', true);

                        const firstConditionRule = newQuestionCard
                            .find('.condition-rule')
                            .first();

                        firstConditionRule
                            .find('.condition-parent-question')
                            .val(
                                String(
                                    pendingCondition.parent_question_id
                                )
                            );

                        populateConditionAnswers(
                            firstConditionRule,
                            [pendingCondition.parent_option_id]
                        );

                        firstConditionRule
                            .find('.condition-operator')
                            .val(pendingCondition.operator);

                        updateConditionSummary(newQuestionCard);
                    }

                    const modalElement =
                        document.getElementById(
                            'quick-question-modal'
                        );

                    quickQuestionSaved = true;

                    bootstrap.Modal
                        .getOrCreateInstance(modalElement)
                        .hide();

                    feather.replace();

                    toast(
                        'Question created and selected.',
                        false
                    );
                },

                error: function (xhr) {
                    const response = xhr.responseJSON ?? {};

                    if (
                        xhr.status === 422
                        && response.errors
                    ) {
                        Object.values(response.errors)
                            .flat()
                            .forEach(message => toast(message));

                        return;
                    }

                    toast(
                        response.message
                        ?? 'Unable to create question.'
                    );
                },

                complete: function () {
                    button
                        .prop('disabled', false)
                        .html(originalHtml);

                    feather.replace();
                }
            });
        });

        $('#quick-question-modal').on('hidden.bs.modal', function () {
            resetQuickQuestionModal(!quickQuestionSaved);
            quickQuestionSaved = false;
        });

        resetQuickQuestionModal(false);

        // Safety check before the parent AI Product form submits.
        $(document).on('submit', 'form', function (event) {
            const form = $(this);

            if (!form.find('[name="category_id"]').length) {
                return;
            }

            if (!form.find('.studio-item-checkbox:checked').length) {
                event.preventDefault();
                event.stopImmediatePropagation();

                toast('Select at least one Studio Item.');
                return;
            }

            let conditionError = null;

            form.find('.question-card').each(function () {
                if (conditionError) return;

                const card = $(this);

                if (
                    !card.find('.question-toggle').is(':checked')
                    || !card.find('.question-condition-toggle').is(':checked')
                ) {
                    return;
                }

                const childQuestionId = Number(card.data('question-id'));
                const rules = card.find('.condition-rule');

                if (!rules.length) {
                    conditionError = 'Add at least one parent rule for every conditional question.';
                    return;
                }

                rules.each(function () {
                    if (conditionError) return false;

                    const rule = $(this);
                    const parentQuestionId = Number(
                        rule.find('.condition-parent-question').val() || 0
                    );

                    const parentOptionIds = (
                        rule.find('.condition-parent-options').val() ?? []
                    ).filter(Boolean);

                    if (!parentQuestionId || !parentOptionIds.length) {
                        conditionError = 'Choose a parent question and at least one answer for every condition rule.';
                        return false;
                    }

                    if (parentQuestionId === childQuestionId) {
                        conditionError = 'A question cannot depend on itself.';
                        return false;
                    }

                    const parentCard = form.find(
                        `.question-card[data-question-id="${parentQuestionId}"]`
                    );

                    if (
                        !parentCard.length
                        || !parentCard.find('.question-toggle').is(':checked')
                    ) {
                        conditionError = 'Every parent question of a conditional question must also be selected.';
                        return false;
                    }
                });
            });

            if (conditionError) {
                event.preventDefault();
                event.stopImmediatePropagation();
                toast(conditionError);
            }
        });
    });
</script>
