@php
    $question = $model ?? null;

    $selectedType = old(
        'type',
        $question?->type?->value
            ?? \App\Enums\Ai\AiGuideQuestionTypeEnum::SINGLE_SELECT->value
    );

    $savedOptions = $question?->options?->map(function ($option) {
        $media = $option->getFirstMedia('option_image');

        return [
            'id' => $option->id,
            'label' => [
                'en' => $option->getTranslation('label', 'en'),
                'ar' => $option->getTranslation('label', 'ar'),
            ],
            'prompt_value' => [
                'en' => $option->getTranslation('prompt_value', 'en'),
                'ar' => $option->getTranslation('prompt_value', 'ar'),
            ],
            'ui_data' => [
                'colors' => array_values(
                    data_get($option->ui_data, 'colors', [])
                ),
            ],
             'media_id' => $media?->id,
             'image_url' => $media ? $media->getFullUrl() : null,
            'is_active' => (bool) $option->is_active,
        ];
    })->toArray() ?? [];

    $options = old('options', $savedOptions);

    /*
     * If validation redirects back, old('options') contains media_id but not image_url.
     * Restore the saved image URL using the option id so Edit preview still works.
     */
    if (old('options') !== null) {
        $savedOptionsById = collect($savedOptions)->keyBy(fn($option) => (int) ($option['id'] ?? 0));

        $options = collect($options)->map(function ($option) use ($savedOptionsById) {
            $optionId = (int) ($option['id'] ?? 0);
            $saved = $optionId ? $savedOptionsById->get($optionId) : null;

            if ($saved) {
                $option['media_id'] = $option['media_id'] ?? $saved['media_id'] ?? null;
                $option['image_url'] = $option['image_url'] ?? $saved['image_url'] ?? null;
            }

            return $option;
        })->values()->all();
    }

    /*
     * Option-level conditional popup.
     *
     * If the controller already passes $conditionalQuestions, use it.
     * Otherwise load active questions here so this Blade works standalone.
     * The current question is excluded because an option must not reveal
     * its own parent question.
     */
    if (isset($conditionalQuestions)) {
        $conditionalQuestions = collect($conditionalQuestions)
            ->filter(fn($item) => (bool) ($item->is_active ?? true))
            ->reject(fn($item) => $question && (int) $item->id === (int) $question->id)
            ->sortBy([
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->values();
    } else {
        $conditionalQuestions = \App\Models\AiGuideQuestion::query()
            ->where('is_active', true)
            ->when(
                $question?->id,
                fn($query, $questionId) => $query->where('id', '!=', $questionId)
            )
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /*
     * Optional edit-state map:
     * [option_id => [question_id, question_id, ...]]
     */
//    $optionConditionalQuestionIds = collect($optionConditionalQuestionIds ?? []);

    $isColorPalette = collect($options)->contains(
        fn($option) => !empty(data_get($option, 'ui_data.colors', []))
    );
@endphp

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">
<link rel="stylesheet" href="{{ asset('admin/vendors/css/forms/select/select2.min.css') }}">

<style>
    .option-image-dropzone {
        min-height: 150px;
        border: 1px dashed #d8d6de;
        border-radius: .357rem;
        background: #fff;
        padding: 12px;
        overflow: hidden;
        position: relative;
    }

    .option-image-dropzone .dz-message {
        margin: 2rem 0;
        color: #6e6b7b;
        text-align: center;
    }

    .option-image-dropzone.dz-started .dz-message {
        display: none;
    }

    .option-image-dropzone .dz-preview {
        position: relative !important;
        display: inline-flex !important;
        flex-direction: column;
        align-items: flex-start;
        width: 140px !important;
        min-height: 0 !important;
        margin: 0 !important;
        vertical-align: top;
    }

    .option-image-dropzone .dz-preview .dz-image {
        width: 140px !important;
        height: 110px !important;
        border-radius: 8px !important;
        overflow: hidden !important;
        background: #f8f8f8;
    }

    .option-image-dropzone .dz-preview .dz-image img {
        display: block !important;
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: contain !important;
    }

    .option-image-dropzone .dz-preview .dz-details,
    .option-image-dropzone .dz-preview .dz-success-mark,
    .option-image-dropzone .dz-preview .dz-error-mark {
        display: none !important;
    }

    .option-image-dropzone .dz-preview .dz-remove {
        display: inline-block;
        margin-top: 8px;
        font-size: 12px;
        color: #ea5455;
        text-decoration: none;
    }
</style>

<div class="row">
    <div class="col-md-6 mb-1">
        <label class="form-label">Question English</label>
        <input
            type="text"
            name="title[en]"
            value="{{ old('title.en', $question?->getTranslation('title', 'en')) }}"
            class="form-control"
        >
    </div>

    <div class="col-md-6 mb-1">
        <label class="form-label">Question Arabic</label>
        <input
            type="text"
            name="title[ar]"
            value="{{ old('title.ar', $question?->getTranslation('title', 'ar')) }}"
            class="form-control"
            dir="rtl"
        >
    </div>

    <div class="col-md-4 mb-1">
        <label class="form-label">Type</label>

        <select
            name="type"
            id="question-type"
            class="form-select"
        >
            @foreach(\App\Enums\Ai\AiGuideQuestionTypeEnum::cases() as $type)
                <option
                    value="{{ $type->value }}"
                    @selected($selectedType === $type->value)
                >
                    {{ $type->label() }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 mb-1">
        <label class="form-label d-block">
            Color Palette Question
        </label>

        <div class="form-check form-switch mt-50">
            <input
                type="checkbox"
                id="color-palette-question"
                class="form-check-input"
                @checked($isColorPalette)
            >

            <label
                for="color-palette-question"
                class="form-check-label"
            >
                Enable Color Palette
            </label>
        </div>

        <small class="text-muted">
            Forces this question to Single Select.
        </small>
    </div>

    <div class="col-md-4 mb-1">
        <label class="form-label">Sort Order</label>

        <input
            type="number"
            name="sort_order"
            min="0"
            value="{{ old('sort_order', $question?->sort_order ?? 0) }}"
            class="form-control"
        >
    </div>

    <div class="col-md-6 mb-1">
        <label class="form-label">Prompt Label English</label>

        <input
            type="text"
            name="prompt_label[en]"
            value="{{ old('prompt_label.en', $question?->getTranslation('prompt_label', 'en')) }}"
            class="form-control"
        >
    </div>

    <div class="col-md-6 mb-1">
        <label class="form-label">Prompt Label Arabic</label>

        <input
            type="text"
            name="prompt_label[ar]"
            value="{{ old('prompt_label.ar', $question?->getTranslation('prompt_label', 'ar')) }}"
            class="form-control"
            dir="rtl"
        >
    </div>

    <div class="col-md-6 mb-1">
        <label class="form-label">Placeholder English</label>

        <input
            type="text"
            name="placeholder[en]"
            value="{{ old('placeholder.en', $question?->getTranslation('placeholder', 'en')) }}"
            class="form-control"
        >
    </div>

    <div class="col-md-6 mb-1">
        <label class="form-label">Placeholder Arabic</label>

        <input
            type="text"
            name="placeholder[ar]"
            value="{{ old('placeholder.ar', $question?->getTranslation('placeholder', 'ar')) }}"
            class="form-control"
            dir="rtl"
        >
    </div>
</div>

<div class="d-flex gap-3 my-1">
    <div class="form-check form-switch">
        <input type="hidden" name="required" value="0">

        <input
            type="checkbox"
            name="required"
            id="required"
            value="1"
            class="form-check-input"
            @checked(old('required', $question?->required ?? false))
        >

        <label for="required" class="form-check-label">
            Required
        </label>
    </div>

    <div class="form-check form-switch">
        <input type="hidden" name="is_active" value="0">

        <input
            type="checkbox"
            name="is_active"
            id="is-active"
            value="1"
            class="form-check-input"
            @checked(old('is_active', $question?->is_active ?? true))
        >

        <label for="is-active" class="form-check-label">
            Active
        </label>
    </div>
</div>

<div
    id="options-section"
    class="border rounded p-1 mt-2"
>
    <div class="d-flex justify-content-between align-items-center mb-1">
        <div>
            <h5 class="mb-0">Options</h5>

            <small class="text-muted">
                Add normal options or color palette options.
            </small>
        </div>

        <button
            type="button"
            id="add-option"
            class="btn btn-sm btn-outline-primary"
        >
            <i data-feather="plus"></i>
            Add Option
        </button>
    </div>

    <div id="options-container">
        @foreach($options as $index => $option)
            @php
                $colors = array_values(
                    data_get($option, 'ui_data.colors', [])
                );

//                $conditionalQuestionIds = collect(
//                    old(
//                        "options.$index.conditional_question_ids",
//                        !empty($option['id'])
//                            ? ($optionConditionalQuestionIds->get((int) $option['id'], []))
//                            : []
//                    )
//                )
//                    ->map(fn($id) => (int) $id)
//                    ->filter()
//                    ->unique()
//                    ->values()
//                    ->all();
            @endphp

            <div
                class="option-row border rounded p-1 mb-1"
                data-option-index="{{ $index }}"
            >
                @if(!empty($option['id']))
                    <input
                        type="hidden"
                        name="options[{{ $index }}][id]"
                        value="{{ $option['id'] }}"
                    >
                @endif

                <div class="row align-items-end">

                    <div class="col-md-10 mb-2">
                        <div class="row option-label-fields">
                            <div class="col-md-6">
                                <label class="form-label">Label English *</label>

                                <input
                                    type="text"
                                    name="options[{{ $index }}][label][en]"
                                    value="{{ data_get($option, 'label.en', '') }}"
                                    class="form-control option-label-en"
                                    placeholder="e.g. Warm Sunset"
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Label Arabic</label>

                                <input
                                    type="text"
                                    name="options[{{ $index }}][label][ar]"
                                    value="{{ data_get($option, 'label.ar', '') }}"
                                    class="form-control option-label-ar"
                                    dir="rtl"
                                    placeholder="مثال: غروب دافئ"
                                >
                            </div>
                        </div>

                        <div class="row mt-1 normal-option-fields">
                            <div class="col-md-6">
                                <label class="form-label">Prompt Value English</label>

                                <textarea
                                    name="options[{{ $index }}][prompt_value][en]"
                                    class="form-control option-prompt-en"
                                    rows="2"
                                >{{ data_get($option, 'prompt_value.en', '') }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Prompt Value Arabic</label>

                                <textarea
                                    name="options[{{ $index }}][prompt_value][ar]"
                                    class="form-control option-prompt-ar"
                                    rows="2"
                                    dir="rtl"
                                >{{ data_get($option, 'prompt_value.ar', '') }}</textarea>
                            </div>

                            <div class="col-12 mt-1 option-image-wrapper">
                                <label class="form-label">Option Image</label>

                                <div
                                    class="option-image-dropzone"
                                    data-media-id="{{ $option['media_id'] ?? '' }}"
                                    data-image-url="{{ $option['image_url'] ?? '' }}"
                                >
                                    <div class="dz-message">Drop image here or click to upload</div>
                                    <div class="text-muted small mt-50">
                                        JPG, PNG, WEBP - Max 2MB (48x48)
                                    </div>
                                </div>

                                <input
                                    type="hidden"
                                    name="options[{{ $index }}][media_id]"
                                    value="{{ $option['media_id'] ?? '' }}"
                                    class="option-media-id"
                                >

                                <input
                                    type="hidden"
                                    name="options[{{ $index }}][remove_media]"
                                    value="0"
                                    class="option-remove-media"
                                >

                                <small class="text-muted d-block mt-50">Optional. One image per option.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 option-actions">
                        <div class="form-check form-switch mb-1">
                            <input
                                type="hidden"
                                name="options[{{ $index }}][is_active]"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                name="options[{{ $index }}][is_active]"
                                value="1"
                                class="form-check-input"
                                @checked(data_get($option, 'is_active', true))
                            >
                        </div>

                        <button
                            type="button"
                            class="btn btn-outline-danger w-100 remove-option"
                        >
                            <i data-feather="trash-2"></i>
                        </button>

{{--                        <div class="option-conditional-question-inputs">--}}
{{--                            @foreach($conditionalQuestionIds as $conditionalQuestionId)--}}
{{--                                <input--}}
{{--                                    type="hidden"--}}
{{--                                    name="options[{{ $index }}][conditional_question_ids][]"--}}
{{--                                    value="{{ $conditionalQuestionId }}"--}}
{{--                                >--}}
{{--                            @endforeach--}}
{{--                        </div>--}}

{{--                        <button--}}
{{--                            type="button"--}}
{{--                            class="btn btn-outline-info w-100 mt-50 configure-option-condition"--}}
{{--                            data-option-index="{{ $index }}"--}}
{{--                        >--}}
{{--                            <i data-feather="git-branch"></i>--}}
{{--                            Conditional--}}
{{--                            <span--}}
{{--                                class="badge bg-info text-white option-condition-count ms-25 {{ count($conditionalQuestionIds) ? '' : 'd-none' }}"--}}
{{--                            >--}}
{{--                                {{ count($conditionalQuestionIds) }}--}}
{{--                            </span>--}}
{{--                        </button>--}}
                    </div>

                    <div class="col-md-10 color-palette-section">
                        <div class="border rounded p-1 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <label class="form-label mb-0">
                                        Color Palette
                                    </label>

                                    <small class="text-muted d-block">
                                        Write the palette label above, then add the colors displayed to the user.
                                    </small>
                                </div>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary add-palette-color"
                                >
                                    <i data-feather="plus"></i>
                                    Add Color
                                </button>
                            </div>

                            <div class="palette-preview d-flex flex-wrap gap-50 mb-1"></div>

                            <div class="palette-colors">
                                @foreach($colors as $color)
                                    <div class="palette-color-row d-flex align-items-center gap-1 mb-1">
                                        <input
                                            type="color"
                                            value="{{ $color }}"
                                            class="form-control form-control-color palette-picker"
                                            style="width:50px"
                                        >

                                        <input
                                            type="text"
                                            name="options[{{ $index }}][ui_data][colors][]"
                                            value="{{ $color }}"
                                            class="form-control palette-value"
                                            placeholder="#000000"
                                            maxlength="7"
                                        >

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger remove-palette-color"
                                        >
                                            <i data-feather="x"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Option-level conditional questions popup --}}
<div
    class="modal fade"
    id="option-condition-modal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-25">
                        Conditional Questions
                    </h5>

                    <small class="text-muted">
                        Choose the questions that should appear when this option is selected.
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
                <div class="alert alert-light-info border mb-2">
                    <strong>Selected option:</strong>
                    <span id="option-condition-option-label">-</span>
                </div>

                <label
                    for="option-condition-question-ids"
                    class="form-label"
                >
                    Show Questions
                </label>

                <select
                    id="option-condition-question-ids"
                    class="form-select select2"
                    multiple
                    data-placeholder="Search and select questions"
                >
                    @foreach($conditionalQuestions as $conditionalQuestion)
                        <option value="{{ $conditionalQuestion->id }}">
                            {{ $conditionalQuestion->title }}
                        </option>
                    @endforeach
                </select>

                @if($conditionalQuestions->isEmpty())
                    <small class="text-warning d-block mt-50">
                        No other active questions are available yet.
                    </small>
                @else
                    <small class="text-muted d-block mt-50">
                        Search and select one or more questions.
                    </small>
                @endif
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    id="clear-option-condition"
                    class="btn btn-outline-danger me-auto"
                >
                    Clear
                </button>

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    id="save-option-condition"
                    class="btn btn-primary"
                    @disabled($conditionalQuestions->isEmpty())
                >
                    Save
                </button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('admin/vendors/js/forms/select/select2.full.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>

<script>
    if (window.Dropzone) {
        Dropzone.autoDiscover = false;
    }
</script>

<script>
    $(document).ready(function () {
        feather.replace();

        const form = $('#question-form');
        const submitButton = $('#submit-button');
        const typeSelect = $('#question-type');
        const colorPaletteToggle = $('#color-palette-question');
        const optionsSection = $('#options-section');
        const optionsContainer = $('#options-container');

        const singleSelectType =
            '{{ \App\Enums\Ai\AiGuideQuestionTypeEnum::SINGLE_SELECT->value }}';

        const optionTypes = [
            '{{ \App\Enums\Ai\AiGuideQuestionTypeEnum::SINGLE_SELECT->value }}',
            '{{ \App\Enums\Ai\AiGuideQuestionTypeEnum::MULTI_SELECT->value }}'
        ];

        let optionIndex = {{ count($options) }};
        let isSubmitting = false;
        let activeConditionalOptionRow = null;

        const mediaDeleteBaseUrl = @json(url('api/v1/media'));

        function optionConditionalQuestionIds(row) {
            return row
                .find('.option-conditional-question-inputs input[type="hidden"]')
                .map(function () {
                    return String($(this).val());
                })
                .get()
                .filter(Boolean);
        }

        function setOptionConditionalQuestionIds(row, ids) {
            const container = row.find('.option-conditional-question-inputs');
            const index = row.data('option-index');

            container.empty();

            [...new Set((ids ?? []).map(String).filter(Boolean))]
                .forEach(questionId => {
                    container.append(`
                        <input
                            type="hidden"
                            name="options[${index}][conditional_question_ids][]"
                            value="${questionId}"
                        >
                    `);
                });

            updateOptionConditionCount(row);
        }

        function updateOptionConditionCount(row) {
            const count = optionConditionalQuestionIds(row).length;
            const badge = row.find('.option-condition-count');

            badge
                .text(count)
                .toggleClass('d-none', count === 0);

            row
                .find('.configure-option-condition')
                .toggleClass('btn-info', count > 0)
                .toggleClass('btn-outline-info', count === 0);
        }

        function currentOptionLabel(row) {
            const label = String(
                row.find('.option-label-en').val()
                || row.find('.option-label-ar').val()
                || `Option ${Number(row.data('option-index')) + 1}`
            ).trim();

            return label || 'Option';
        }

        function closeOptionConditionModal() {
            const modalElement = document.getElementById('option-condition-modal');
            const instance = bootstrap.Modal.getInstance(modalElement);

            instance?.hide();
        }

        function deleteMedia(mediaId) {
            if (!mediaId) {
                return Promise.resolve();
            }

            return fetch(`${mediaDeleteBaseUrl}/${mediaId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': @json(csrf_token()),
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

        function initOptionDropzone(element) {
            if (!element) return null;

            if (!window.Dropzone) {
                console.error('Dropzone is not loaded.');
                return null;
            }

            if (element.dropzone) {
                return element.dropzone;
            }

            const dropzoneElement = $(element);

            if (dropzoneElement.data('dz-initialized')) {
                return element.dropzone ?? null;
            }

            dropzoneElement.data('dz-initialized', true);
            const row = dropzoneElement.closest('.option-row');
            const hiddenInput = row.find('.option-media-id');
            const removeInput = row.find('.option-remove-media');

            const existingMediaId = String(
                dropzoneElement.attr('data-media-id') ?? ''
            ).trim();

            const existingImageUrl = String(
                dropzoneElement.attr('data-image-url') ?? ''
            ).trim();

            const REQUIRED_WIDTH = 48;
            const REQUIRED_HEIGHT = 48;
            const MAX_FILESIZE_MB = 2;

            let dz;

            try {
                dz = new Dropzone(element, {
                    url: @json(route('media.store')),
                    paramName: 'file',
                    maxFiles: 1,
                    acceptedFiles: 'image/*',
                    maxFilesize: MAX_FILESIZE_MB, // MB, Dropzone rejects anything larger automatically
                    addRemoveLinks: true,
                    clickable: true,
                    thumbnailWidth: 160,
                    thumbnailHeight: 160,

                    headers: {
                        'X-CSRF-TOKEN': @json(csrf_token()),
                        'Accept': 'application/json'
                    },

                    accept: function (file, done) {
                        // Skip dimension check for the pre-existing mock file on init
                        if (file._isExisting) {
                            done();
                            return;
                        }

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

                    init: function () {
                        const instance = this;

                        if (!existingMediaId || !existingImageUrl) {
                            return;
                        }

                        const mockFile = {
                            name: 'Current image',
                            size: 1,
                            type: 'image/*',
                            accepted: true,
                            status: Dropzone.SUCCESS,
                            _mediaId: existingMediaId,
                            _isExisting: true
                        };

                        instance.files.push(mockFile);
                        instance.emit('addedfile', mockFile);
                        instance.emit('thumbnail', mockFile, existingImageUrl);
                        instance.emit('success', mockFile, {
                            data: {
                                id: existingMediaId,
                                original_url: existingImageUrl
                            }
                        });
                        instance.emit('complete', mockFile);

                        element.classList.add('dz-started');

                        hiddenInput.val(existingMediaId);
                        removeInput.val(0);
                    },

                    success: function (file, response) {
                        const mediaId = response?.data?.id ?? null;
                        const imageUrl = response?.data?.original_url ?? response?.data?.url ?? null;

                        if (!mediaId) {
                            showToast('Image uploaded but media ID was not returned.');
                            return;
                        }

                        hiddenInput.val(mediaId);
                        removeInput.val(0);

                        file._mediaId = String(mediaId);
                        file._imageUrl = imageUrl;
                        file._isExisting = false;

                        dropzoneElement.attr('data-media-id', mediaId);

                        if (imageUrl) {
                            dropzoneElement.attr('data-image-url', imageUrl);
                        }

                        element.classList.add('dz-started');
                    },

                    removedfile: function (file) {
                        const removedMediaId = String(file._mediaId ?? '');
                        const currentMediaId = String(hiddenInput.val() ?? '');

                        if (file.previewElement) {
                            file.previewElement.remove();
                        }

                        if (!removedMediaId || removedMediaId === currentMediaId) {
                            hiddenInput.val('');
                            removeInput.val(1);
                            dropzoneElement.attr('data-media-id', '');
                            dropzoneElement.attr('data-image-url', '');
                        }

                        if (removedMediaId) {
                            deleteMedia(removedMediaId).catch(error => {
                                showToast(error.message ?? 'Unable to remove image.');
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

                        showToast(message);

                        if (file.previewElement) {
                            file.previewElement.classList.add('dz-error');
                        }
                    }
                });
            } catch (error) {
                dropzoneElement.removeData('dz-initialized');

                if (element.dropzone) {
                    return element.dropzone;
                }

                console.error('Unable to initialize option Dropzone:', error);
                return null;
            }

            return dz;
        }
        function destroyOptionDropzone(row) {
            const element = row.find('.option-image-dropzone')[0];

            if (!element) return;

            if (element.dropzone) {
                element.dropzone.destroy();
            }

            $(element).removeData('dz-initialized');
        }

        function supportsOptions() {
            return optionTypes.includes(typeSelect.val());
        }

        function toggleOptions() {
            if (colorPaletteToggle.is(':checked')) {
                typeSelect.val(singleSelectType);
                optionsSection.show();

                if (!optionsContainer.children('.option-row').length) {
                    addOption();
                }

                return;
            }

            if (supportsOptions()) {
                optionsSection.show();

                if (!optionsContainer.children('.option-row').length) {
                    addOption();
                }

                return;
            }

            optionsSection.hide();
        }

        function toggleColorPaletteMode() {
            const enabled = colorPaletteToggle.is(':checked');

            if (enabled) {
                typeSelect.val(singleSelectType);

                typeSelect.find('option').each(function () {
                    $(this).prop(
                        'disabled',
                        $(this).val() !== singleSelectType
                    );
                });

                optionsSection.show();

                if (!optionsContainer.children('.option-row').length) {
                    addOption();
                }

                $('.option-label-fields').show();
                $('.normal-option-fields').hide();
                $('.color-palette-section').show();
                $('.palette-value').prop('disabled', false);

                optionsContainer.children('.option-row').each(function () {
                    const row = $(this);

                    if (!row.find('.palette-color-row').length) {
                        addPaletteColor(row, '#000000');
                    }
                });

                updateAllPaletteValues();

                return;
            }

            typeSelect.find('option').prop('disabled', false);

            $('.option-label-fields').show();
            $('.normal-option-fields').show();
            $('.color-palette-section').hide();

            /*
             * Disabled inputs are not submitted, so old palette colors
             * are removed if the admin converts the question back to
             * a normal select question.
             */
            $('.palette-value').prop('disabled', true);

            toggleOptions();
        }

        function addOption() {
            const index = optionIndex++;

            optionsContainer.append(`
            <div
                class="option-row border rounded p-1 mb-1"
                data-option-index="${index}"
            >
                <div class="row align-items-end">

                    <div class="col-md-10">
                        <div class="row option-label-fields">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Label English *</label>

                                <input
                                    type="text"
                                    name="options[${index}][label][en]"
                                    class="form-control option-label-en"
                                    placeholder="e.g. Warm Sunset"
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Label Arabic</label>

                                <input
                                    type="text"
                                    name="options[${index}][label][ar]"
                                    class="form-control option-label-ar"
                                    dir="rtl"
                                    placeholder="مثال: غروب دافئ"
                                >
                            </div>
                        </div>

                        <div class="row mt-1 normal-option-fields">
                            <div class="col-md-6">
                                <label class="form-label">Prompt Value English</label>

                                <textarea
                                    name="options[${index}][prompt_value][en]"
                                    class="form-control option-prompt-en"
                                    rows="2"
                                ></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Prompt Value Arabic</label>

                                <textarea
                                    name="options[${index}][prompt_value][ar]"
                                    class="form-control option-prompt-ar"
                                    rows="2"
                                    dir="rtl"
                                ></textarea>
                            </div>

                            <div class="col-12 mt-1 option-image-wrapper">
                                <label class="form-label">Option Image</label>

                                <div
                                    class="option-image-dropzone"
                                    data-media-id=""
                                    data-image-url=""
                                >
                                    <div class="dz-message">Drop image here or click to upload</div>
 <div class="text-muted small mt-50">
                                        JPG, PNG, WEBP - Max 2MB (48x48)
                                    </div>
                                </div>

                                <input
                                    type="hidden"
                                    name="options[${index}][media_id]"
                                    value=""
                                    class="option-media-id"
                                >

                                <input
                                    type="hidden"
                                    name="options[${index}][remove_media]"
                                    value="0"
                                    class="option-remove-media"
                                >

                                <small class="text-muted d-block mt-50">Optional. One image per option.</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2 option-actions">
                        <div class="form-check form-switch mb-1">
                            <input
                                type="hidden"
                                name="options[${index}][is_active]"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                name="options[${index}][is_active]"
                                value="1"
                                class="form-check-input"
                                checked
                            >
                        </div>

                        <button
                            type="button"
                            class="btn btn-outline-danger w-100 remove-option"
                        >
                            <i data-feather="trash-2"></i>
                        </button>

                        <div class="option-conditional-question-inputs"></div>

                        <button
                            type="button"
                            class="btn btn-outline-info w-100 mt-50 configure-option-condition"
                            data-option-index="${index}"
                        >
                            <i data-feather="git-branch"></i>
                            Conditional
                            <span
                                class="badge bg-info text-white option-condition-count ms-25 d-none"
                            >
                                0
                            </span>
                        </button>
                    </div>

                    <div class="col-md-10 color-palette-section">
                        <div class="border rounded p-1 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <label class="form-label mb-0">Color Palette</label>

                                    <small class="text-muted d-block">
                                        Write the palette label above, then add the colors displayed to the user.
                                    </small>
                                </div>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary add-palette-color"
                                >
                                    <i data-feather="plus"></i>
                                    Add Color
                                </button>
                            </div>

                            <div class="palette-preview d-flex flex-wrap gap-50 mb-1"></div>
                            <div class="palette-colors"></div>
                        </div>
                    </div>
                </div>
            </div>
        `);


            const newRow = optionsContainer.children('.option-row').last();
            initOptionDropzone(
                newRow.find('.option-image-dropzone')[0]
            );
            if (colorPaletteToggle.is(':checked')) {
                newRow.find('.option-label-fields').show();
                newRow.find('.normal-option-fields').hide();
                newRow.find('.color-palette-section').show();
                newRow.find('.palette-value').prop('disabled', false);

                if (!newRow.find('.palette-color-row').length) {
                    addPaletteColor(newRow, '#000000');
                }
            } else {
                newRow.find('.option-label-fields').show();
                newRow.find('.normal-option-fields').show();
                newRow.find('.color-palette-section').hide();
                newRow.find('.palette-value').prop('disabled', true);
            }

            feather.replace();
        }

        function addPaletteColor(optionRow, color = '#000000') {
            const index = optionRow.data('option-index');

            optionRow.find('.palette-colors').append(`
            <div class="palette-color-row d-flex align-items-center gap-1 mb-1">
                <input
                    type="color"
                    value="${color}"
                    class="form-control form-control-color palette-picker"
                    style="width:50px"
                >

                <input
                    type="text"
                    name="options[${index}][ui_data][colors][]"
                    value="${color}"
                    class="form-control palette-value"
                    placeholder="#000000"
                    maxlength="7"
                >

                <button
                    type="button"
                    class="btn btn-sm btn-outline-danger remove-palette-color"
                >
                    <i data-feather="x"></i>
                </button>
            </div>
        `);

            updatePalettePreview(optionRow);
            updatePaletteValues(optionRow);

            feather.replace();
        }

        function updatePalettePreview(optionRow) {
            const preview = optionRow.find('.palette-preview');

            preview.empty();

            optionRow.find('.palette-value').each(function () {
                const color = $(this).val().trim().toUpperCase();

                if (!/^#[0-9A-F]{6}$/.test(color)) {
                    return;
                }

                preview.append(`
                <div
                    title="${color}"
                    style="
                        width:40px;
                        height:40px;
                        border-radius:6px;
                        background:${color};
                        border:1px solid rgba(0,0,0,.08);
                    "
                ></div>
            `);
            });
        }

        function updatePaletteValues(optionRow) {
            if (!colorPaletteToggle.is(':checked')) {
                return;
            }

            const colors = [];

            optionRow.find('.palette-value').each(function () {
                const color = $(this).val().trim().toUpperCase();

                if (/^#[0-9A-F]{6}$/.test(color)) {
                    colors.push(color);
                }
            });

            const prompt = colors.length
                ? `Use this exact color palette: ${colors.join(', ')}`
                : '';

            /*
             * Admin writes the palette label manually.
             * Only the AI prompt value is generated from selected colors.
             */
            optionRow.find('.option-prompt-en').val(prompt);
            optionRow.find('.option-prompt-ar').val(prompt);
        }

        function updateAllPaletteValues() {
            $('.option-row').each(function () {
                updatePaletteValues($(this));
            });
        }

        function initConditionalQuestionsSelect2() {
            const select = $('#option-condition-question-ids');

            if (!select.length || !$.fn.select2) {
                return;
            }

            if (select.hasClass('select2-hidden-accessible')) {
                return;
            }

            select.select2({
                width: '100%',
                placeholder: 'Search and select questions',
                allowClear: true,
                closeOnSelect: false,
                dropdownParent: $('#option-condition-modal')
            });
        }

        initConditionalQuestionsSelect2();

        $(document)
            .off('click.optionCondition')
            .on('click.optionCondition', '.configure-option-condition', function () {
                const row = $(this).closest('.option-row');

                activeConditionalOptionRow = row;

                const selectedIds = optionConditionalQuestionIds(row);

                $('#option-condition-option-label')
                    .text(currentOptionLabel(row));

                $('#option-condition-question-ids')
                    .val(selectedIds)
                    .trigger('change');

                bootstrap.Modal
                    .getOrCreateInstance(
                        document.getElementById('option-condition-modal')
                    )
                    .show();
            });

        $('#save-option-condition')
            .off('click.optionCondition')
            .on('click.optionCondition', function () {
                if (!activeConditionalOptionRow) {
                    return;
                }

                const selectedIds =
                    $('#option-condition-question-ids').val() ?? [];

                setOptionConditionalQuestionIds(
                    activeConditionalOptionRow,
                    selectedIds
                );

                closeOptionConditionModal();
            });

        $('#clear-option-condition')
            .off('click.optionCondition')
            .on('click.optionCondition', function () {
                if (!activeConditionalOptionRow) {
                    return;
                }

                $('#option-condition-question-ids')
                    .val([])
                    .trigger('change');

                setOptionConditionalQuestionIds(
                    activeConditionalOptionRow,
                    []
                );

                closeOptionConditionModal();
            });

        $('#option-condition-modal')
            .on('hidden.bs.modal', function () {
                activeConditionalOptionRow = null;
                $('#option-condition-question-ids')
                    .val([])
                    .trigger('change');
                $('#option-condition-option-label').text('-');
            });

        $('#add-option')
            .off('click.aiOption')
            .on('click.aiOption', function () {
                addOption();
            });

        optionsContainer
            .off('click.aiOption')
            .on('click.aiOption', '.remove-option', function () {
                const row = $(this).closest('.option-row');

                destroyOptionDropzone(row);
                row.remove();

                if (colorPaletteToggle.is(':checked')) {
                    updateAllPaletteValues();
                }
            });

        $(document).on('click', '.add-palette-color', function () {
            addPaletteColor(
                $(this).closest('.option-row')
            );
        });

        $(document).on('click', '.remove-palette-color', function () {
            const optionRow = $(this).closest('.option-row');

            $(this).closest('.palette-color-row').remove();

            updatePalettePreview(optionRow);
            updatePaletteValues(optionRow);
        });

        $(document).on('input', '.palette-picker', function () {
            const row = $(this).closest('.palette-color-row');
            const optionRow = $(this).closest('.option-row');

            row.find('.palette-value')
                .val($(this).val().toUpperCase());

            updatePalettePreview(optionRow);
            updatePaletteValues(optionRow);
        });

        $(document).on('input', '.palette-value', function () {
            const value = $(this).val().trim().toUpperCase();
            const row = $(this).closest('.palette-color-row');
            const optionRow = $(this).closest('.option-row');

            if (/^#[0-9A-F]{6}$/.test(value)) {
                $(this).val(value);

                row.find('.palette-picker').val(value);

                updatePalettePreview(optionRow);
                updatePaletteValues(optionRow);
            }
        });

        colorPaletteToggle
            .off('change.colorPalette')
            .on('change.colorPalette', function () {
                toggleColorPaletteMode();
            });

        typeSelect
            .off('change.aiQuestion')
            .on('change.aiQuestion', function () {
                toggleOptions();
            });

        function setLoading(loading) {
            if (loading) {
                if (!submitButton.data('original-html')) {
                    submitButton.data(
                        'original-html',
                        submitButton.html()
                    );
                }

                submitButton.prop('disabled', true);

                submitButton.html(`
                <span class="spinner-border spinner-border-sm me-50"></span>
                Saving...
            `);

                return;
            }

            submitButton.prop('disabled', false);
            submitButton.html(submitButton.data('original-html'));

            feather.replace();
        }

        function showToast(message, type = 'error') {
            Toastify({
                text: message,
                duration: 4000,
                close: true,
                gravity: 'top',
                position: 'right',
                stopOnFocus: true,
                backgroundColor:
                    type === 'success'
                        ? '#28C76F'
                        : '#EA5455'
            }).showToast();
        }

        function fieldToInputName(field) {
            const parts = field.split('.');

            return parts.shift()
                + parts.map(part => `[${part}]`).join('');
        }

        function showErrors(xhr) {
            const response = xhr.responseJSON ?? {};

            form.find('.is-invalid')
                .removeClass('is-invalid');

            if (
                xhr.status === 422
                && response.errors
            ) {
                Object.entries(response.errors)
                    .forEach(([field, messages]) => {
                        const inputName =
                            fieldToInputName(field);

                        form.find(`[name="${inputName}"]`)
                            .addClass('is-invalid');

                        const list = Array.isArray(messages)
                            ? messages
                            : [messages];

                        list.forEach(message => {
                            showToast(message);
                        });
                    });

                return;
            }

            showToast(
                response.message
                ?? 'Something went wrong. Please try again.'
            );
        }

        form.on(
            'input change',
            '.is-invalid',
            function () {
                $(this).removeClass('is-invalid');
            }
        );

        form
            .off('submit.aiQuestion')
            .on('submit.aiQuestion', function (e) {
                e.preventDefault();

                if (isSubmitting) {
                    return;
                }

                if (colorPaletteToggle.is(':checked')) {
                    updateAllPaletteValues();

                    let hasEmptyPalette = false;
                    let hasEmptyPaletteLabel = false;

                    $('.option-row').each(function () {
                        const row = $(this);
                        const validColors = row
                            .find('.palette-value')
                            .filter(function () {
                                return /^#[0-9A-Fa-f]{6}$/.test($(this).val().trim());
                            });

                        if (!row.find('.option-label-en').val().trim()) {
                            hasEmptyPaletteLabel = true;
                            row.find('.option-label-en').addClass('is-invalid');
                        }

                        if (!validColors.length) {
                            hasEmptyPalette = true;
                        }
                    });

                    if (hasEmptyPaletteLabel) {
                        showToast('Each color palette option must have an English label.');
                        return;
                    }

                    if (hasEmptyPalette) {
                        showToast('Each color palette option must contain at least one color.');
                        return;
                    }
                }

                isSubmitting = true;
                setLoading(true);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),

                    success: function () {
                        showToast(
                            'Question saved successfully.',
                            'success'
                        );

                        setTimeout(() => {
                            window.location.href =
                                '{{ route('ai-guide-questions.index') }}';
                        }, 500);
                    },

                    error: function (xhr) {
                        showErrors(xhr);

                        isSubmitting = false;
                        setLoading(false);
                    }
                });
            });

        /*
         * Edit mode:
         * render previews from saved ui_data.colors first,
         * then detect palette mode and show the selected colors.
         */
        $('.option-row').each(function () {
            const row = $(this);

            updatePalettePreview(row);
            updateOptionConditionCount(row);
        });

        toggleColorPaletteMode();

        /*
         * Initialize after the final mode is applied.
         * This is especially important on Edit where Dropzone must render
         * the already attached Media Library image.
         */
        $('.option-row').each(function () {
            initOptionDropzone($(this).find('.option-image-dropzone')[0]);
        });
    });
</script>
