@php
    $studioItem = $model ?? null;
    $isEdit = (bool) $studioItem;

    $selectedGenerationType = old(
        'generation_type',
        $studioItem?->generation_type?->value
            ?? $studioItem?->generation_type
    );

      $selectedResolution = old(
        'default_resolution',
        $studioItem?->default_resolution ?? '1024x1024'
    );

    $selectedAspectRatio = old(
        'aspect_ratio',
        $studioItem?->aspect_ratio
    );

    /*
     * Studio Item question configuration.
     *
     * Show ALL active global/general questions here. Attachment, required,
     * order and conditions belong to this Studio Item assignment only.
     */
    $generalQuestions = \App\Models\AiGuideQuestion::query()
        ->where('is_active', true)
        ->with([
            'options' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id'),
        ])
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get();

    $studioQuestionAssignments = collect();
    $studioQuestionConditions = collect();

    if ($isEdit) {
        $studioMorphTypes = array_values(array_unique([
            $studioItem->getMorphClass(),
            'ai_studio_item',
            \App\Models\AiStudioItem::class,
        ]));

        $studioQuestionAssignments = \Illuminate\Support\Facades\DB::table(
            'ai_guide_question_assignments'
        )
            ->where('assignable_id', $studioItem->id)
            ->whereIn('assignable_type', $studioMorphTypes)
            ->where(function ($query) {
                $query
                    ->whereNull('is_active')
                    ->orWhere('is_active', true);
            })
            ->get()
            ->keyBy(fn ($row) => (int) $row->ai_guide_question_id);

        if (
            $studioQuestionAssignments->isNotEmpty()
            && \Illuminate\Support\Facades\Schema::hasTable(
                'ai_guide_question_conditions'
            )
        ) {
            $questionIdByAssignmentId = $studioQuestionAssignments
                ->keyBy(fn ($assignment) => (int) $assignment->id)
                ->map(
                    fn ($assignment) =>
                        (int) $assignment->ai_guide_question_id
                );

            $studioQuestionConditions =
                \Illuminate\Support\Facades\DB::table(
                    'ai_guide_question_conditions'
                )
                    ->whereIn(
                        'ai_guide_question_assignment_id',
                        $studioQuestionAssignments->pluck('id')
                    )
                    ->orderBy('id')
                    ->get()
                    ->groupBy(function ($condition) use ($questionIdByAssignmentId) {
                        return (int) (
                            $questionIdByAssignmentId->get(
                                (int) $condition->ai_guide_question_assignment_id
                            )
                            ?? 0
                        );
                    })
                    ->filter(
                        fn ($rows, $questionId) =>
                            (int) $questionId > 0
                    )
                    ->map(function ($rows) {
                        return $rows
                            ->groupBy(fn ($condition) => implode(':', [
                                (int) $condition->parent_question_id,
                                (string) (
                                    $condition->operator
                                    ?: 'selected'
                                ),
                            ]))
                            ->map(function ($conditions) {
                                $first = $conditions->first();

                                return [
                                    'parent_question_id' =>
                                        (int) $first->parent_question_id,

                                    'parent_option_ids' =>
                                        $conditions
                                            ->pluck('parent_option_id')
                                            ->map(fn ($id) => (int) $id)
                                            ->unique()
                                            ->values()
                                            ->all(),

                                    'operator' =>
                                        (string) (
                                            $first->operator
                                            ?: 'selected'
                                        ),
                                ];
                            })
                            ->values()
                            ->all();
                    });
        }
    }

    $studioQuestionPayload = $generalQuestions
        ->mapWithKeys(function ($question) {
            return [
                (int) $question->id => [
                    'id' => (int) $question->id,
                    'title' => $question->title,
                    'type' => $question->type?->value ?? $question->type,
                    'options' => $question->options
                        ->map(fn ($option) => [
                            'id' => (int) $option->id,
                            'label' => $option->label,
                        ])
                        ->values()
                        ->all(),
                ],
            ];
        })
        ->all();

    $initialStudioQuestionConfig = $generalQuestions
        ->mapWithKeys(function ($question) use (
            $studioQuestionAssignments,
            $studioQuestionConditions
        ) {
            $assignment = $studioQuestionAssignments->get(
                (int) $question->id
            );

            return [
                (int) $question->id => [
                    'selected' => (bool) $assignment,
                    'required' => $assignment?->required !== null
                        ? (bool) $assignment->required
                        : (bool) $question->required,
                    'sort_order' => $assignment
                        ? (int) ($assignment->sort_order ?? 0)
                        : (int) ($question->sort_order ?? 0),
                    'conditions' => $studioQuestionConditions->get(
                        (int) $question->id,
                        []
                    ),
                ],
            ];
        })
        ->all();
@endphp

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css"
>


<style>
    .studio-question-card {
        transition:
            border-color .2s ease,
            background-color .2s ease,
            box-shadow .2s ease;
    }

    .studio-question-card.is-attached {
        border-color: rgba(115, 103, 240, .45) !important;
        background: rgba(115, 103, 240, .025);
        box-shadow: 0 0 0 1px rgba(115, 103, 240, .04);
    }

    .studio-question-condition-panel {
        background: #fafafa;
    }

    .studio-condition-rule {
        background: #fff;
    }

    .studio-condition-answer-select {
        min-height: 90px;
    }
</style>

<form
    id="studio-item-form"
    action="{{ $isEdit
        ? route('ai-studio-items.update', $studioItem->id)
        : route('ai-studio-items.store') }}"
    method="POST"
    enctype="multipart/form-data"
>
    @csrf

    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">

        <div class="col-md-6 mb-1">
            <label class="form-label">
                Name English *
            </label>

            <input
                type="text"
                name="name[en]"
                class="form-control"
                value="{{ old(
                    'name.en',
                    $isEdit
                        ? $studioItem->getTranslation('name', 'en')
                        : ''
                ) }}"
            >
        </div>

        <div class="col-md-6 mb-1">
            <label class="form-label">
                Name Arabic *
            </label>

            <input
                type="text"
                name="name[ar]"
                class="form-control"
                value="{{ old(
                    'name.ar',
                    $isEdit
                        ? $studioItem->getTranslation('name', 'ar')
                        : ''
                ) }}"
            >
        </div>

        <div class="col-md-6 mb-1">
            <label class="form-label">
                Description English
            </label>

            <textarea
                name="description[en]"
                class="form-control"
                rows="3"
            >{{ old(
                'description.en',
                $isEdit
                    ? $studioItem->getTranslation('description', 'en')
                    : ''
            ) }}</textarea>
        </div>

        <div class="col-md-6 mb-1">
            <label class="form-label">
                Description Arabic
            </label>

            <textarea
                name="description[ar]"
                class="form-control"
                rows="3"
            >{{ old(
                'description.ar',
                $isEdit
                    ? $studioItem->getTranslation('description', 'ar')
                    : ''
            ) }}</textarea>
        </div>

        <div class="col-md-4 mb-1">
            <label class="form-label">
                Generation Type *
            </label>

            <select
                name="generation_type"
                class="form-select"
            >
                <option value="">
                    Select Type
                </option>

                @foreach(\App\Enums\Ai\AiGenerationTypeEnum::cases() as $type)
                    <option
                        value="{{ $type->value }}"
                        @selected(
                            $selectedGenerationType === $type->value
                        )
                    >
                        {{ $type->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4 mb-1">
            <label class="form-label">
                Default Resolution
            </label>

            <select
                name="default_resolution"
                class="form-select"
            >
                <option value="">
                    Select Resolution
                </option>

                <option
                    value="512x512"
                    @selected($selectedResolution === '512x512')
                >
                    512x512
                </option>

                <option
                    value="768x768"
                    @selected($selectedResolution === '768x768')
                >
                    768x768
                </option>

                <option
                    value="1024x1024"
                    @selected($selectedResolution === '1024x1024')
                >
                    1024x1024
                </option>
            </select>
        </div>

        <div class="col-md-4 mb-1">
            <label class="form-label">
                Aspect Ratio
            </label>

            <select
                name="aspect_ratio"
                class="form-select"
            >
                <option value="">
                    Default
                </option>

                <option
                    value="1:1"
                    @selected($selectedAspectRatio === '1:1')
                >
                    1:1
                </option>

                <option
                    value="4:5"
                    @selected($selectedAspectRatio === '4:5')
                >
                    4:5
                </option>

                <option
                    value="3:4"
                    @selected($selectedAspectRatio === '3:4')
                >
                    3:4
                </option>

                <option
                    value="16:9"
                    @selected($selectedAspectRatio === '16:9')
                >
                    16:9
                </option>

                <option
                    value="9:16"
                    @selected($selectedAspectRatio === '9:16')
                >
                    9:16
                </option>
            </select>
        </div>

        <div class="col-md-4 mb-1">
            <label class="form-label">
                Credits Cost *
            </label>

            <input
                type="number"
                name="credits_cost"
                min="0"
                class="form-control"
                value="{{ old(
                    'credits_cost',
                    $studioItem?->credits_cost ?? 1
                ) }}"
            >
        </div>

        <div class="col-md-4 mb-1">
            <label class="form-label">
                Sort Order
            </label>

            <input
                type="number"
                name="sort_order"
                min="0"
                class="form-control"
                value="{{ old(
                    'sort_order',
                    $studioItem?->sort_order ?? 0
                ) }}"
            >
        </div>

        <div class="col-md-6 mb-1">
            <label class="form-label">
                Image
            </label>

            <input
                type="file"
                name="image"
                class="form-control"
                accept="image/*"
            >

            @if(
                $isEdit
                && $studioItem->getFirstMediaUrl('image')
            )
                <div class="mt-1">
                    <img
                        src="{{ $studioItem->getFirstMediaUrl('image') }}"
                        width="120"
                        height="120"
                        class="rounded border"
                        style="object-fit:cover"
                    >
                </div>
            @endif
        </div>

        <div class="col-md-6 mb-1">
            <label class="form-label d-block">
                Status
            </label>

            <input
                type="hidden"
                name="is_active"
                value="0"
            >

            <div class="form-check form-switch">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    id="is-active"
                    class="form-check-input"
                    @checked(
                        old(
                            'is_active',
                            $studioItem?->is_active ?? true
                        )
                    )
                >

                <label
                    for="is-active"
                    class="form-check-label"
                >
                    Active
                </label>
            </div>
        </div>

        <div class="col-12">
            <hr>

            <h5 class="mb-1">
                Generation Settings
            </h5>
        </div>

        @php
            $settings = old(
                'settings',
                $studioItem?->settings ?? []
            );

            $orientation = data_get(
                $settings,
                'orientation'
            );

            $transparentBackground = (bool) data_get(
                $settings,
                'transparent_background',
                false
            );

            $printReady = (bool) data_get(
                $settings,
                'print_ready',
                false
            );
        @endphp

        <div class="col-md-4 mb-1">
            <label class="form-label">
                Orientation
            </label>

            <select
                name="settings[orientation]"
                class="form-select"
            >
                <option value="">
                    Default
                </option>

                <option
                    value="square"
                    @selected($orientation === 'square')
                >
                    Square
                </option>

                <option
                    value="portrait"
                    @selected($orientation === 'portrait')
                >
                    Portrait
                </option>

                <option
                    value="landscape"
                    @selected($orientation === 'landscape')
                >
                    Landscape
                </option>
            </select>
        </div>

        <div class="col-md-4 mb-1">
            <label class="form-label d-block">
                Transparent Background
            </label>

            <input
                type="hidden"
                name="settings[transparent_background]"
                value="0"
            >

            <div class="form-check form-switch">
                <input
                    type="checkbox"
                    name="settings[transparent_background]"
                    value="1"
                    id="transparent-background"
                    class="form-check-input"
                    @checked($transparentBackground)
                >

                <label
                    for="transparent-background"
                    class="form-check-label"
                >
                    Enable
                </label>
            </div>
        </div>

        <div class="col-md-4 mb-1">
            <label class="form-label d-block">
                Print Ready
            </label>

            <input
                type="hidden"
                name="settings[print_ready]"
                value="0"
            >

            <div class="form-check form-switch">
                <input
                    type="checkbox"
                    name="settings[print_ready]"
                    value="1"
                    id="print-ready"
                    class="form-check-input"
                    @checked($printReady)
                >

                <label
                    for="print-ready"
                    class="form-check-label"
                >
                    Enable
                </label>
            </div>
        </div>

    </div>


    <div class="col-12 mt-2">
        <hr>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 mb-1">
            <div>
                <h5 class="mb-25">Questions & Conditional Visibility</h5>
                <p class="text-muted mb-0">
                    All active general AI questions are available here.
                    Attach only the questions this Studio Item needs.
                </p>
            </div>

            <div class="d-flex gap-50">
                <button
                    type="button"
                    id="studio-select-all-questions"
                    class="btn btn-sm btn-outline-primary"
                    @disabled(!$isEdit)
                >
                    Select All
                </button>

                <button
                    type="button"
                    id="studio-clear-questions"
                    class="btn btn-sm btn-outline-secondary"
                    @disabled(!$isEdit)
                >
                    Clear
                </button>

                @if($isEdit)
                    <button
                        type="button"
                        id="save-studio-question-config"
                        class="btn btn-sm btn-primary"
                    >
                        <i data-feather="save"></i>
                        Save Questions
                    </button>
                @endif
            </div>
        </div>

        @if(!$isEdit)
            <div class="alert alert-info">
                Create the Studio Item first, then edit it to attach questions,
                set their order and configure conditional visibility.
            </div>
        @endif

        <div id="studio-question-list" class="d-flex flex-column gap-1">
            @forelse($generalQuestions as $question)
                @php
                    $config = $initialStudioQuestionConfig[
                        (int) $question->id
                    ] ?? [];

                    $selected = (bool) data_get(
                        $config,
                        'selected',
                        false
                    );

                    $required = (bool) data_get(
                        $config,
                        'required',
                        $question->required
                    );

                    $sortOrder = (int) data_get(
                        $config,
                        'sort_order',
                        $question->sort_order ?? 0
                    );

                    $conditionCount = count(
                        data_get($config, 'conditions', [])
                    );

                    $typeValue =
                        $question->type?->value
                        ?? $question->type;

                    $canBeConditionParent = in_array(
                        $typeValue,
                        ['single_select', 'multi_select'],
                        true
                    );
                @endphp

                <div
                    class="studio-question-card border rounded"
                    data-question-id="{{ $question->id }}"
                    data-question-type="{{ $typeValue }}"
                    data-can-parent="{{ $canBeConditionParent ? 1 : 0 }}"
                >
                    <div class="p-1 d-flex flex-wrap align-items-center gap-1">
                        <div class="form-check mb-0">
                            <input
                                type="checkbox"
                                class="form-check-input studio-question-toggle"
                                id="studio-question-{{ $question->id }}"
                                value="{{ $question->id }}"
                                @checked($selected)
                                @disabled(!$isEdit)
                            >
                        </div>

                        <label
                            for="studio-question-{{ $question->id }}"
                            class="flex-grow-1 mb-0"
                        >
                            <div class="fw-bolder">
                                {{ $question->title }}
                            </div>

                            <small class="text-muted">
                                {{ $question->prompt_label ?: $question->key }}
                            </small>
                        </label>

                        <span class="badge bg-light-primary text-primary">
                            {{ $question->type?->label() ?? $typeValue }}
                        </span>

                        <div class="d-flex align-items-center gap-50">
                            <input
                                type="checkbox"
                                class="form-check-input studio-question-required"
                                @checked($required)
                                @disabled(!$isEdit || !$selected)
                            >

                            <small>Required</small>
                        </div>

                        <div style="width:95px">
                            <input
                                type="number"
                                min="0"
                                class="form-control form-control-sm studio-question-order"
                                value="{{ $sortOrder }}"
                                title="Studio Item question order"
                                @disabled(!$isEdit || !$selected)
                            >
                        </div>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary studio-question-condition-toggle"
                            @disabled(!$isEdit || !$selected)
                        >
                            <i data-feather="git-branch"></i>
                            Conditional

                            <span
                                class="studio-condition-count badge bg-light-warning text-warning ms-25 {{ $conditionCount ? '' : 'd-none' }}"
                            >
                                {{ $conditionCount }}
                            </span>
                        </button>
                    </div>

                    <div
                        class="studio-question-condition-panel border-top p-1 d-none"
                    >
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <div class="fw-bolder">
                                    Conditional Visibility
                                </div>

                                <small class="text-muted">
                                    Answers inside one rule are OR.
                                    Different parent rules are AND.
                                </small>
                            </div>

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary add-studio-condition-rule"
                            >
                                <i data-feather="plus"></i>
                                Add Rule
                            </button>
                        </div>

                        <div class="studio-condition-rules"></div>

                        <div class="studio-no-conditions text-muted small">
                            No conditions. This question is always visible when
                            the Studio Item is selected.
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-warning mb-0">
                    No active general AI questions found.
                </div>
            @endforelse
        </div>
    </div>

    <div class="d-flex justify-content-end gap-1 mt-2">
        <a
            href="{{ route('ai-studio-items.index') }}"
            class="btn btn-outline-secondary"
        >
            Cancel
        </a>

        <button
            type="submit"
            id="save-studio-item"
            class="btn btn-primary"
        >
            <i data-feather="save"></i>
            {{ $isEdit ? 'Update' : 'Create' }}
        </button>
    </div>
</form>

<script src="https://unpkg.com/feather-icons"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script>
    $(function () {
        feather.replace();

        const form = $('#studio-item-form');
        const submitButton = $('#save-studio-item');
        const originalHtml = submitButton.html();

        const studioQuestionPayload = @json($studioQuestionPayload);
        const initialStudioQuestionConfig = @json($initialStudioQuestionConfig);
        const studioItemId = Number(@json($studioItem?->id ?? 0));
        const studioQuestionConfigUrl = studioItemId
            ? `{{ url('ai-studio-items') }}/${studioItemId}/question-config`
            : null;

        let isSubmitting = false;
        let conditionRuleSequence = 0;

        function showToast(message, isError = true) {
            Toastify({
                text: message,
                duration: 4000,
                close: true,
                gravity: 'top',
                position: 'right',
                backgroundColor: isError
                    ? '#EA5455'
                    : '#28C76F'
            }).showToast();
        }

        function resetSubmitButton() {
            isSubmitting = false;

            submitButton
                .prop('disabled', false)
                .html(originalHtml);

            feather.replace();
        }


        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        function questionCard(questionId) {
            return $(
                `.studio-question-card[data-question-id="${Number(questionId)}"]`
            );
        }

        function attachedQuestionIds() {
            return $('.studio-question-toggle:checked')
                .map(function () {
                    return Number($(this).val());
                })
                .get()
                .filter(Boolean);
        }

        function refreshQuestionCardState(card) {
            const selected = card
                .find('.studio-question-toggle')
                .is(':checked');

            card.toggleClass(
                'is-attached',
                selected
            );

            card
                .find(
                    '.studio-question-required, '
                    + '.studio-question-order, '
                    + '.studio-question-condition-toggle'
                )
                .prop(
                    'disabled',
                    !studioItemId || !selected
                );

            if (!selected) {
                card
                    .find('.studio-question-condition-panel')
                    .addClass('d-none');
            }
        }

        function refreshAllQuestionCards() {
            $('.studio-question-card').each(function () {
                refreshQuestionCardState($(this));
            });
        }

        function conditionParentOptionsHtml(
            childQuestionId,
            selectedParentId = null
        ) {
            const childCard = questionCard(
                childQuestionId
            );

            const childSort = Number(
                childCard
                    .find('.studio-question-order')
                    .val()
                || 0
            );

            return attachedQuestionIds()
                .filter(parentId => {
                    if (
                        Number(parentId)
                        === Number(childQuestionId)
                    ) {
                        return false;
                    }

                    const parent =
                        studioQuestionPayload[
                            Number(parentId)
                            ];

                    if (!parent) {
                        return false;
                    }

                    if (
                        ![
                            'single_select',
                            'multi_select'
                        ].includes(
                            String(parent.type)
                        )
                    ) {
                        return false;
                    }

                    const parentSort = Number(
                        questionCard(parentId)
                            .find(
                                '.studio-question-order'
                            )
                            .val()
                        || 0
                    );

                    /*
                     * The visibility resolver is order-sensitive.
                     * Parent must appear before the child.
                     */
                    return parentSort < childSort;
                })
                .map(parentId => {
                    const parent =
                        studioQuestionPayload[
                            Number(parentId)
                            ];

                    return `
                        <option
                            value="${Number(parentId)}"
                            ${
                        Number(selectedParentId)
                        === Number(parentId)
                            ? 'selected'
                            : ''
                    }
                        >
                            ${escapeHtml(parent.title)}
                        </option>
                    `;
                })
                .join('');
        }

        function conditionAnswerOptionsHtml(
            parentQuestionId,
            selectedOptionIds = []
        ) {
            const parent =
                studioQuestionPayload[
                    Number(parentQuestionId)
                    ];

            if (!parent) {
                return '';
            }

            const selected = new Set(
                (
                    Array.isArray(selectedOptionIds)
                        ? selectedOptionIds
                        : [selectedOptionIds]
                )
                    .map(String)
            );

            return (parent.options ?? [])
                .map(option => `
                    <option
                        value="${Number(option.id)}"
                        ${
                    selected.has(
                        String(option.id)
                    )
                        ? 'selected'
                        : ''
                }
                    >
                        ${escapeHtml(option.label)}
                    </option>
                `)
                .join('');
        }

        function buildConditionRule(
            childQuestionId,
            condition = {}
        ) {
            const index =
                conditionRuleSequence++;

            const parentQuestionId =
                Number(
                    condition.parent_question_id
                    || 0
                );

            const parentOptionIds =
                condition.parent_option_ids
                ?? [];

            const operator =
                condition.operator
                || 'selected';

            return `
                <div
                    class="studio-condition-rule border rounded p-1 mb-1"
                    data-rule-index="${index}"
                >
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-1">
                            <label class="form-label">
                                Parent Question *
                            </label>

                            <select
                                class="form-select studio-condition-parent"
                            >
                                <option value="">
                                    Select Parent
                                </option>

                                ${conditionParentOptionsHtml(
                childQuestionId,
                parentQuestionId
            )}
                            </select>
                        </div>

                        <div class="col-md-4 mb-1">
                            <label class="form-label">
                                Answers *
                                <small class="text-muted">
                                    (OR)
                                </small>
                            </label>

                            <select
                                class="form-select studio-condition-answer-select"
                                multiple
                            >
                                ${conditionAnswerOptionsHtml(
                parentQuestionId,
                parentOptionIds
            )}
                            </select>
                        </div>

                        <div class="col-md-2 mb-1">
                            <label class="form-label">
                                Operator
                            </label>

                            <select
                                class="form-select studio-condition-operator"
                            >
                                <option
                                    value="selected"
                                    ${
                operator
                === 'selected'
                    ? 'selected'
                    : ''
            }
                                >
                                    Selected
                                </option>

                                <option
                                    value="not_selected"
                                    ${
                operator
                === 'not_selected'
                    ? 'selected'
                    : ''
            }
                                >
                                    Not Selected
                                </option>
                            </select>
                        </div>

                        <div class="col-md-2 mb-1">
                            <button
                                type="button"
                                class="btn btn-outline-danger w-100 remove-studio-condition-rule"
                            >
                                <i data-feather="trash-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        function refreshConditionPanelState(card) {
            const count = card
                .find('.studio-condition-rule')
                .length;

            card
                .find('.studio-no-conditions')
                .toggleClass(
                    'd-none',
                    count > 0
                );

            const badge = card.find(
                '.studio-condition-count'
            );

            badge
                .text(count)
                .toggleClass(
                    'd-none',
                    count === 0
                );

            feather.replace();
        }

        function appendConditionRule(
            card,
            condition = {}
        ) {
            const childQuestionId =
                Number(
                    card.data('question-id')
                );

            card
                .find(
                    '.studio-condition-rules'
                )
                .append(
                    buildConditionRule(
                        childQuestionId,
                        condition
                    )
                );

            refreshConditionPanelState(
                card
            );
        }

        function hydrateInitialConditions() {
            Object.entries(
                initialStudioQuestionConfig
                ?? {}
            ).forEach(
                ([questionId, config]) => {
                    const card =
                        questionCard(
                            Number(questionId)
                        );

                    (
                        config.conditions
                        ?? []
                    ).forEach(
                        condition =>
                            appendConditionRule(
                                card,
                                condition
                            )
                    );

                    refreshConditionPanelState(
                        card
                    );
                }
            );
        }

        function refreshConditionParentSelects() {
            $('.studio-condition-rule').each(function () {
                const rule = $(this);
                const card = rule.closest(
                    '.studio-question-card'
                );

                const childQuestionId =
                    Number(
                        card.data('question-id')
                    );

                const parentSelect =
                    rule.find(
                        '.studio-condition-parent'
                    );

                const currentParent =
                    Number(
                        parentSelect.val()
                        || 0
                    );

                parentSelect.html(`
                    <option value="">
                        Select Parent
                    </option>

                    ${conditionParentOptionsHtml(
                    childQuestionId,
                    currentParent
                )}
                `);

                if (currentParent) {
                    parentSelect.val(
                        String(
                            currentParent
                        )
                    );
                }
            });
        }

        $(document).on(
            'change',
            '.studio-question-toggle',
            function () {
                const card = $(this).closest(
                    '.studio-question-card'
                );

                refreshQuestionCardState(
                    card
                );

                refreshConditionParentSelects();
            }
        );

        $(document).on(
            'change',
            '.studio-question-order',
            function () {
                refreshConditionParentSelects();
            }
        );

        $(document).on(
            'click',
            '.studio-question-condition-toggle',
            function () {
                const card = $(this).closest(
                    '.studio-question-card'
                );

                card
                    .find(
                        '.studio-question-condition-panel'
                    )
                    .toggleClass('d-none');

                refreshConditionPanelState(
                    card
                );
            }
        );

        $(document).on(
            'click',
            '.add-studio-condition-rule',
            function () {
                const card = $(this).closest(
                    '.studio-question-card'
                );

                appendConditionRule(
                    card
                );
            }
        );

        $(document).on(
            'click',
            '.remove-studio-condition-rule',
            function () {
                const card = $(this).closest(
                    '.studio-question-card'
                );

                $(this)
                    .closest(
                        '.studio-condition-rule'
                    )
                    .remove();

                refreshConditionPanelState(
                    card
                );
            }
        );

        $(document).on(
            'change',
            '.studio-condition-parent',
            function () {
                const rule = $(this).closest(
                    '.studio-condition-rule'
                );

                const parentQuestionId =
                    Number(
                        $(this).val()
                        || 0
                    );

                rule
                    .find(
                        '.studio-condition-answer-select'
                    )
                    .html(
                        conditionAnswerOptionsHtml(
                            parentQuestionId,
                            []
                        )
                    );
            }
        );

        $('#studio-select-all-questions').on(
            'click',
            function () {
                if (!studioItemId) {
                    return;
                }

                $('.studio-question-toggle')
                    .prop('checked', true)
                    .trigger('change');
            }
        );

        $('#studio-clear-questions').on(
            'click',
            function () {
                if (!studioItemId) {
                    return;
                }

                $('.studio-question-toggle')
                    .prop('checked', false)
                    .trigger('change');
            }
        );

        function collectStudioQuestionConfig() {
            const questions = [];
            let error = null;

            $('.studio-question-card').each(function () {
                if (error) {
                    return;
                }

                const card = $(this);

                const questionId =
                    Number(
                        card.data('question-id')
                    );

                const selected =
                    card
                        .find(
                            '.studio-question-toggle'
                        )
                        .is(':checked');

                if (!selected) {
                    return;
                }

                const required =
                    card
                        .find(
                            '.studio-question-required'
                        )
                        .is(':checked');

                const sortOrder =
                    Number(
                        card
                            .find(
                                '.studio-question-order'
                            )
                            .val()
                        || 0
                    );

                const conditions = [];

                card
                    .find(
                        '.studio-condition-rule'
                    )
                    .each(function () {
                        if (error) {
                            return;
                        }

                        const rule = $(this);

                        const parentQuestionId =
                            Number(
                                rule
                                    .find(
                                        '.studio-condition-parent'
                                    )
                                    .val()
                                || 0
                            );

                        const parentOptionIds =
                            (
                                rule
                                    .find(
                                        '.studio-condition-answer-select'
                                    )
                                    .val()
                                ?? []
                            )
                                .map(Number)
                                .filter(Boolean);

                        const operator =
                            String(
                                rule
                                    .find(
                                        '.studio-condition-operator'
                                    )
                                    .val()
                                || 'selected'
                            );

                        if (
                            !parentQuestionId
                            || !parentOptionIds.length
                        ) {
                            error =
                                'Choose a parent question and at least one answer for every conditional rule.';

                            return;
                        }

                        conditions.push({
                            parent_question_id:
                            parentQuestionId,

                            parent_option_ids:
                            parentOptionIds,

                            operator:
                            operator
                        });
                    });

                questions.push({
                    question_id:
                    questionId,

                    required:
                    required,

                    sort_order:
                    sortOrder,

                    conditions:
                    conditions
                });
            });

            return {
                questions,
                error
            };
        }

        $('#save-studio-question-config').on(
            'click',
            function () {
                if (
                    !studioItemId
                    || !studioQuestionConfigUrl
                ) {
                    showToast(
                        'Create the Studio Item first.'
                    );

                    return;
                }

                const button = $(this);
                const config =
                    collectStudioQuestionConfig();

                if (config.error) {
                    showToast(
                        config.error
                    );

                    return;
                }

                button.prop(
                    'disabled',
                    true
                );

                $.ajax({
                    url:
                    studioQuestionConfigUrl,

                    type:
                        'POST',

                    data: {
                        _method:
                            'PUT',

                        _token:
                            '{{ csrf_token() }}',

                        questions:
                        config.questions
                    },

                    success:
                        function (response) {
                            showToast(
                                response.message
                                ?? 'Studio Item questions saved successfully.',
                                false
                            );
                        },

                    error:
                        function (xhr) {
                            const response =
                                xhr.responseJSON
                                ?? {};

                            if (
                                xhr.status === 422
                                && response.errors
                            ) {
                                Object.values(
                                    response.errors
                                )
                                    .flat()
                                    .forEach(
                                        message =>
                                            showToast(
                                                message
                                            )
                                    );
                            } else {
                                showToast(
                                    response.message
                                    ?? 'Unable to save Studio Item questions.'
                                );
                            }
                        },

                    complete:
                        function () {
                            button.prop(
                                'disabled',
                                false
                            );
                        }
                });
            }
        );

        refreshAllQuestionCards();
        hydrateInitialConditions();
        refreshConditionParentSelects();

        form
            .off('submit.aiStudioItem')
            .on('submit.aiStudioItem', function (e) {
                e.preventDefault();

                if (isSubmitting) {
                    return;
                }

                isSubmitting = true;

                submitButton
                    .prop('disabled', true)
                    .html(`
                    <span class="spinner-border spinner-border-sm me-50"></span>
                    Saving...
                `);

                const formData = new FormData(this);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,

                    success: function (response) {
                        showToast(
                            response.message ?? 'Saved successfully.',
                            false
                        );

                        setTimeout(() => {
                            window.location.href =
                                "{{ route('ai-studio-items.index') }}";
                        }, 500);
                    },

                    error: function (xhr) {
                        const response = xhr.responseJSON ?? {};

                        if (
                            xhr.status === 422
                            && response.errors
                        ) {
                            Object.values(response.errors)
                                .flat()
                                .forEach(message => {
                                    showToast(message);
                                });
                        } else {
                            showToast(
                                response.message
                                ?? 'Something went wrong.'
                            );
                        }

                        resetSubmitButton();
                    }
                });
            });
    });
</script>
