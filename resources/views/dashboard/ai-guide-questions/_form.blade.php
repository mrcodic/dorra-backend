@php
    $question = $model ?? null;
    $selectedType = old(
        'type',
        $question?->type?->value ?? \App\Enums\Ai\AiGuideQuestionTypeEnum::SINGLE_SELECT->value
    );

    $options = old(
        'options',
        $question?->options?->map(fn($option) => [
            'id' => $option->id,
            'label' => [
                'en' => $option->getTranslation('label', 'en'),
                'ar' => $option->getTranslation('label', 'ar'),
            ],
            'prompt_value' => [
                'en' => $option->getTranslation('prompt_value', 'en'),
                'ar' => $option->getTranslation('prompt_value', 'ar'),
            ],
        ])->toArray() ?? []
    );
@endphp

<div class="row">
    <div class="col-md-6 mb-1">
        <label class="form-label">Question English</label>
        <input type="text"
               name="title[en]"
               value="{{ old('title.en', $question?->getTranslation('title', 'en')) }}"
               class="form-control">
    </div>

    <div class="col-md-6 mb-1">
        <label class="form-label">Question Arabic</label>
        <input type="text"
               name="title[ar]"
               value="{{ old('title.ar', $question?->getTranslation('title', 'ar')) }}"
               class="form-control"
               dir="rtl">
    </div>

    <div class="col-md-6 mb-1">
        <label class="form-label">Type</label>
        <select name="type" id="question-type" class="form-select">
            @foreach(\App\Enums\Ai\AiGuideQuestionTypeEnum::cases() as $type)
                <option value="{{ $type->value }}" @selected($selectedType === $type->value)>
                    {{ $type->label() }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3 mb-1">
        <label class="form-label">Prompt Label English</label>
        <input type="text"
               name="prompt_label[en]"
               value="{{ old('prompt_label.en', $question?->getTranslation('prompt_label', 'en')) }}"
               class="form-control">
    </div>

    <div class="col-md-3 mb-1">
        <label class="form-label">Prompt Label Arabic</label>
        <input type="text"
               name="prompt_label[ar]"
               value="{{ old('prompt_label.ar', $question?->getTranslation('prompt_label', 'ar')) }}"
               class="form-control"
               dir="rtl">
    </div>

    <div class="col-md-6 mb-1">
        <label class="form-label">Placeholder English</label>
        <input type="text"
               name="placeholder[en]"
               value="{{ old('placeholder.en', $question?->getTranslation('placeholder', 'en')) }}"
               class="form-control">
    </div>

    <div class="col-md-6 mb-1">
        <label class="form-label">Placeholder Arabic</label>
        <input type="text"
               name="placeholder[ar]"
               value="{{ old('placeholder.ar', $question?->getTranslation('placeholder', 'ar')) }}"
               class="form-control"
               dir="rtl">
    </div>

    <div class="col-md-6 mb-1">
        <label class="form-label">Sort Order</label>
        <input type="number"
               name="sort_order"
               min="0"
               value="{{ old('sort_order', $question?->sort_order ?? 0) }}"
               class="form-control">
    </div>
</div>

<div class="d-flex gap-3 my-1">
    <div class="form-check form-switch">
        <input type="hidden" name="required" value="0">
        <input type="checkbox"
               name="required"
               id="required"
               value="1"
               class="form-check-input"
            @checked(old('required', $question?->required ?? false))>
        <label for="required" class="form-check-label">Required</label>
    </div>

    <div class="form-check form-switch">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox"
               name="is_active"
               id="is-active"
               value="1"
               class="form-check-input"
            @checked(old('is_active', $question?->is_active ?? true))>
        <label for="is-active" class="form-check-label">Active</label>
    </div>
</div>

<div id="options-section" class="border rounded p-1 mt-2">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <div>
            <h5 class="mb-0">Options</h5>
            <small class="text-muted">Value is generated automatically from English label.</small>
        </div>

        <button type="button" id="add-option" class="btn btn-sm btn-outline-primary">
            <i data-feather="plus"></i>
            Add Option
        </button>
    </div>

    <div id="options-container">
        @foreach($options as $index => $option)
            <div class="option-row border rounded p-1 mb-1">
                @if(!empty($option['id']))
                    <input type="hidden" name="options[{{ $index }}][id]" value="{{ $option['id'] }}">
                @endif

                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Label English</label>
                        <input type="text"
                               name="options[{{ $index }}][label][en]"
                               value="{{ $option['label']['en'] ?? '' }}"
                               class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Label Arabic</label>
                        <input type="text"
                               name="options[{{ $index }}][label][ar]"
                               value="{{ $option['label']['ar'] ?? '' }}"
                               class="form-control"
                               dir="rtl">
                    </div>

                    <div class="col-md-6 mt-1">
                        <label class="form-label">Prompt Value English</label>
                        <input type="text"
                               name="options[{{ $index }}][prompt_value][en]"
                               value="{{ $option['prompt_value']['en'] ?? '' }}"
                               class="form-control">
                    </div>

                    <div class="col-md-5 mt-1">
                        <label class="form-label">Prompt Value Arabic</label>
                        <input type="text"
                               name="options[{{ $index }}][prompt_value][ar]"
                               value="{{ $option['prompt_value']['ar'] ?? '' }}"
                               class="form-control"
                               dir="rtl">
                    </div>

                    <div class="col-md-1 mt-1">
                        <button type="button" class="btn btn-outline-danger w-100 remove-option">
                            <i data-feather="trash-2"></i>
                        </button>
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
        const optionsSection = $('#options-section');
        const optionsContainer = $('#options-container');
        const singleSelect = '{{ \App\Enums\Ai\AiGuideQuestionTypeEnum::SINGLE_SELECT->value }}';

        let optionIndex = {{ count($options) }};
        let isSubmitting = false;

        function toggleOptions() {
            if (typeSelect.val() === singleSelect) {
                optionsSection.show();

                if (!optionsContainer.children('.option-row').length) {
                    addOption();
                }

                return;
            }

            optionsSection.hide();
        }

        function addOption() {
            const index = optionIndex++;

            optionsContainer.append(`
            <div class="option-row border rounded p-1 mb-1">
                <div class="row align-items-end">

                    <div class="col-md-6">
                        <label class="form-label">Label English</label>
                        <input type="text"
                               name="options[${index}][label][en]"
                               class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Label Arabic</label>
                        <input type="text"
                               name="options[${index}][label][ar]"
                               class="form-control"
                               dir="rtl">
                    </div>

                    <div class="col-md-6 mt-1">
                        <label class="form-label">Prompt Value English</label>
                        <input type="text"
                               name="options[${index}][prompt_value][en]"
                               class="form-control">
                    </div>

                    <div class="col-md-5 mt-1">
                        <label class="form-label">Prompt Value Arabic</label>
                        <input type="text"
                               name="options[${index}][prompt_value][ar]"
                               class="form-control"
                               dir="rtl">
                    </div>

                    <div class="col-md-1 mt-1">
                        <button type="button"
                                class="btn btn-outline-danger w-100 remove-option">
                            <i data-feather="trash-2"></i>
                        </button>
                    </div>

                </div>
            </div>
        `);

            feather.replace();
        }

        $('#add-option').on('click', function () {
            addOption();
        });

        optionsContainer.on('click', '.remove-option', function () {
            $(this).closest('.option-row').remove();
        });

        typeSelect.on('change', function () {
            toggleOptions();
        });

        function setLoading(loading) {
            if (loading) {
                submitButton.prop('disabled', true);

                if (!submitButton.data('original-html')) {
                    submitButton.data('original-html', submitButton.html());
                }

                submitButton.html(`
                <span class="spinner-border spinner-border-sm me-50" role="status"></span>
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
                style: {
                    background: type === 'success' ? '#28c76f' : '#ea5455'
                }
            }).showToast();
        }

        function showErrors(xhr) {
            const response = xhr.responseJSON ?? {};

            if (xhr.status === 422 && response.errors) {
                Object.values(response.errors).forEach(messages => {
                    const errors = Array.isArray(messages) ? messages : [messages];

                    errors.forEach(message => {
                        showToast(message);
                    });
                });

                return;
            }

            showToast(
                response.message ?? 'Something went wrong. Please try again.'
            );
        }

        form.off('submit.aiQuestion').on('submit.aiQuestion', function (e) {
            e.preventDefault();

            if (isSubmitting) return;

            isSubmitting = true;
            setLoading(true);

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),

                success: function () {
                    showToast('Question saved successfully.', 'success');

                    setTimeout(() => {
                        window.location.href = '{{ route('ai-guide-questions.index') }}';
                    }, 500);
                },

                error: function (xhr) {
                    showErrors(xhr);
                    isSubmitting = false;
                    setLoading(false);
                }
            });
        });

        toggleOptions();
    });
</script>
