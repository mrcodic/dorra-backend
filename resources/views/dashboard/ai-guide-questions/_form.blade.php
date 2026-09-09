@php
    $question = $model ?? null;

    $selectedType = old(
        'type',
        $question?->type?->value
            ?? \App\Enums\Ai\AiGuideQuestionTypeEnum::SINGLE_SELECT->value
    );

    $savedOptions = $question?->options?->map(function ($option) {
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
            'is_active' => (bool) $option->is_active,
        ];
    })->toArray() ?? [];

    $options = old('options', $savedOptions);

    $isColorPalette = collect($options)->contains(
        fn($option) => !empty(data_get($option, 'ui_data.colors', []))
    );
@endphp

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

                    <div class="col-md-11 normal-option-fields">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Label English</label>

                                <input
                                    type="text"
                                    name="options[{{ $index }}][label][en]"
                                    value="{{ data_get($option, 'label.en', '') }}"
                                    class="form-control option-label-en"
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
                                >
                            </div>

                            <div class="col-md-6 mt-1">
                                <label class="form-label">Prompt Value English</label>

                                <textarea
                                    name="options[{{ $index }}][prompt_value][en]"
                                    class="form-control option-prompt-en"
                                    rows="2"
                                >{{ data_get($option, 'prompt_value.en', '') }}</textarea>
                            </div>

                            <div class="col-md-6 mt-1">
                                <label class="form-label">Prompt Value Arabic</label>

                                <textarea
                                    name="options[{{ $index }}][prompt_value][ar]"
                                    class="form-control option-prompt-ar"
                                    rows="2"
                                    dir="rtl"
                                >{{ data_get($option, 'prompt_value.ar', '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-1 option-actions">
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
                    </div>

                    <div class="col-md-11 color-palette-section">
                        <div class="border rounded p-1 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <label class="form-label mb-0">
                                        Color Palette
                                    </label>

                                    <small class="text-muted d-block">
                                        Add the colors displayed to the user.
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

                $('.normal-option-fields').hide();
                $('.color-palette-section').show();
                $('.palette-value').prop('disabled', false);

                updateAllPaletteValues();

                return;
            }

            typeSelect.find('option').prop('disabled', false);

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

                    <div class="col-md-11 normal-option-fields">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Label English</label>

                                <input
                                    type="text"
                                    name="options[${index}][label][en]"
                                    class="form-control option-label-en"
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Label Arabic</label>

                                <input
                                    type="text"
                                    name="options[${index}][label][ar]"
                                    class="form-control option-label-ar"
                                    dir="rtl"
                                >
                            </div>

                            <div class="col-md-6 mt-1">
                                <label class="form-label">Prompt Value English</label>

                                <textarea
                                    name="options[${index}][prompt_value][en]"
                                    class="form-control option-prompt-en"
                                    rows="2"
                                ></textarea>
                            </div>

                            <div class="col-md-6 mt-1">
                                <label class="form-label">Prompt Value Arabic</label>

                                <textarea
                                    name="options[${index}][prompt_value][ar]"
                                    class="form-control option-prompt-ar"
                                    rows="2"
                                    dir="rtl"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-1 option-actions">
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
                    </div>

                    <div class="col-md-11 color-palette-section">
                        <div class="border rounded p-1 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <label class="form-label mb-0">Color Palette</label>

                                    <small class="text-muted d-block">
                                        Add the colors displayed to the user.
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

            if (colorPaletteToggle.is(':checked')) {
                newRow.find('.normal-option-fields').hide();
                newRow.find('.color-palette-section').show();
                newRow.find('.palette-value').prop('disabled', false);
            } else {
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

            const optionNumber = optionRow.index() + 1;
            const prompt = colors.length
                ? `Use this exact color palette: ${colors.join(', ')}`
                : '';

            /*
             * These fields remain in the form, but are hidden while
             * palette mode is enabled. The backend can also generate
             * the same values as a fallback.
             */
            optionRow
                .find('.option-label-en')
                .val(`Color Palette ${optionNumber}`);

            optionRow
                .find('.option-label-ar')
                .val(`لوحة ألوان ${optionNumber}`);

            optionRow
                .find('.option-prompt-en')
                .val(prompt);

            optionRow
                .find('.option-prompt-ar')
                .val(prompt);
        }

        function updateAllPaletteValues() {
            $('.option-row').each(function () {
                updatePaletteValues($(this));
            });
        }

        $('#add-option')
            .off('click.aiOption')
            .on('click.aiOption', function () {
                addOption();
            });

        optionsContainer
            .off('click.aiOption')
            .on('click.aiOption', '.remove-option', function () {
                $(this).closest('.option-row').remove();

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

                    $('.option-row').each(function () {
                        const validColors = $(this)
                            .find('.palette-value')
                            .filter(function () {
                                return /^#[0-9A-Fa-f]{6}$/.test(
                                    $(this).val().trim()
                                );
                            });

                        if (!validColors.length) {
                            hasEmptyPalette = true;
                        }
                    });

                    if (hasEmptyPalette) {
                        showToast(
                            'Each color palette option must contain at least one color.'
                        );

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
            updatePalettePreview($(this));
        });

        toggleColorPaletteMode();
    });
</script>
