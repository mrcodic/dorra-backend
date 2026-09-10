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
            ],
        ];
    })->all();
@endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

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
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary quick-edit-studio-item"
                                        data-id="{{ $studioItem->id }}"
                                        title="Edit Studio Item"
                                    >
                                        <i data-feather="edit-2"></i>
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
            @forelse($questions as $question)
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
                    No active AI questions found. Use “Add New Question”.
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

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>

                    <button type="button" id="quick-save-studio-item" class="btn btn-primary">
                        <i data-feather="save"></i>
                        Save Studio Item
                    </button>
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
                    ></button>
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

<script>
    $(function () {
        feather.replace();

        const singleSelect = @json(\App\Enums\Ai\AiGuideQuestionTypeEnum::SINGLE_SELECT->value);
        const multiSelect = @json(\App\Enums\Ai\AiGuideQuestionTypeEnum::MULTI_SELECT->value);
        const quickStoreUrl = @json(route('ai-categories.questions.quick-store'));
        const csrfToken = @json(csrf_token());
        const quickStudioStoreUrl = @json(route('ai-categories.studio-items.quick-store'));
        const quickStudioUpdateUrlTemplate = @json(route('ai-categories.studio-items.quick-update', ['studioItem' => '__STUDIO_ITEM_ID__']));

        let studioItemData = @json($studioItemsPayload);

        let quickOptionIndex = 0;

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

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary quick-edit-studio-item"
                                    data-id="${item.id}"
                                    title="Edit Studio Item"
                                >
                                    <i data-feather="edit-2"></i>
                                </button>
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

        function toggleQuestion(card) {
            const checked = card.find('.question-toggle').is(':checked');

            card.toggleClass('is-selected', checked);
            card.find('.question-settings').toggle(checked);
            card.find('.question-options').toggle(checked);
        }

        $('.question-card').each(function () {
            toggleQuestion($(this));
        });

        $(document).on('change', '.question-toggle', function () {
            toggleQuestion($(this).closest('.question-card'));
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

                    <div class="quick-normal-option-fields">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Label English *</label>
                                <input
                                    type="text"
                                    class="form-control quick-option-label-en"
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Label Arabic</label>
                                <input
                                    type="text"
                                    class="form-control quick-option-label-ar"
                                    dir="rtl"
                                >
                            </div>

                            <div class="col-md-6 mt-1">
                                <label class="form-label">Prompt Value English</label>
                                <textarea
                                    class="form-control quick-option-prompt-en"
                                    rows="2"
                                ></textarea>
                            </div>

                            <div class="col-md-6 mt-1">
                                <label class="form-label">Prompt Value Arabic</label>
                                <textarea
                                    class="form-control quick-option-prompt-ar"
                                    rows="2"
                                    dir="rtl"
                                ></textarea>
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
                                        Add the colors that belong to this palette.
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
            const optionNumber = optionRow.index() + 1;
            const prompt = colors.length
                ? `Use this exact color palette: ${colors.join(', ')}`
                : '';

            optionRow
                .find('.quick-option-label-en')
                .val(`Color Palette ${optionNumber}`);

            optionRow
                .find('.quick-option-label-ar')
                .val(`لوحة ألوان ${optionNumber}`);

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
            $(this).closest('.quick-option-row').remove();

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

        function resetQuickQuestionModal() {
            $('#quick-title-en, #quick-title-ar, #quick-prompt-label-en, #quick-prompt-label-ar, #quick-placeholder-en, #quick-placeholder-ar').val('');
            $('#quick-sort-order').val(0);
            $('#quick-required').prop('checked', false);
            $('#quick-color-palette-question').prop('checked', false);

            $('#quick-question-type')
                .find('option')
                .prop('disabled', false);

            $('#quick-question-type').val(singleSelect);

            $('#quick-options-container').empty();

            quickOptionIndex = 0;

            toggleQuickPaletteMode();
            toggleQuickOptions();
        }

        function buildOptionVisual(option) {
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
                    const colors = getQuickPaletteColors(row);

                    if (!colors.length) {
                        error = 'Each color palette option must contain at least one valid color.';
                        return;
                    }

                    updateQuickPaletteValues(row);

                    options.push({
                        label: {
                            en: row.find('.quick-option-label-en').val().trim(),
                            ar: row.find('.quick-option-label-ar').val().trim()
                        },
                        prompt_value: {
                            en: row.find('.quick-option-prompt-en').val().trim(),
                            ar: row.find('.quick-option-prompt-ar').val().trim()
                        },
                        ui_data: {
                            colors: colors
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

                    $('#questions-container').append(
                        buildQuestionCard(question)
                    );

                    const modalElement =
                        document.getElementById(
                            'quick-question-modal'
                        );

                    bootstrap.Modal
                        .getOrCreateInstance(modalElement)
                        .hide();

                    resetQuickQuestionModal();
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
            resetQuickQuestionModal();
        });

        resetQuickQuestionModal();

        // Safety check before the parent AI Product form submits.
        $(document).on('submit', 'form', function (event) {
            const form = $(this);

            if (!form.find('[name="category_id"]').length) {
                return;
            }

            if (!form.find('.studio-item-checkbox:checked').length) {
                event.preventDefault();
                event.stopImmediatePropagation();

                toast(
                    'Select at least one Studio Item.'
                );
            }
        });
    });
</script>
