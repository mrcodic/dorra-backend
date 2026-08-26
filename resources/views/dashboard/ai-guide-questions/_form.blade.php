@php
    $question = $model ?? null;
    $selectedType = old('type', $question?->type?->value ?? \App\Enums\Ai\AiGuideQuestionTypeEnum::SINGLE_SELECT->value);
    $options = old('options', $question?->options?->map(fn($option) => [
        'value' => $option->value,
        'label' => $option->label,
        'prompt_value' => $option->prompt_value
    ])->toArray() ?? []);
@endphp

<div class="row">

    <div class="col-md-6 mb-1">
        <label class="form-label">Question</label>
        <input type="text" name="title" value="{{ old('title', $question?->title) }}" class="form-control" placeholder="What do you want to create?">
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

    <div class="col-md-6 mb-1">
        <label class="form-label">Prompt Label</label>
        <input type="text" name="prompt_label" value="{{ old('prompt_label', $question?->prompt_label) }}" class="form-control" placeholder="Create">
    </div>

    <div class="col-md-6 mb-1">
        <label class="form-label">Placeholder</label>
        <input type="text" name="placeholder" value="{{ old('placeholder', $question?->placeholder) }}" class="form-control">
    </div>

    <div class="col-md-6 mb-1">
        <label class="form-label">Sort Order</label>
        <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $question?->sort_order ?? 0) }}" class="form-control">
    </div>
</div>

<div class="d-flex gap-3 my-1">
    <div class="form-check form-switch">
        <input type="hidden" name="required" value="0">
        <input type="checkbox" name="required" value="1" class="form-check-input" id="required"
            @checked(old('required', $question?->required ?? false))>
        <label for="required" class="form-check-label">Required</label>
    </div>

    <div class="form-check form-switch">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is-active"
            @checked(old('is_active', $question?->is_active ?? true))>
        <label for="is-active" class="form-check-label">Active</label>
    </div>
</div>

<div id="options-section" class="border rounded p-1 mt-2">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <div>
            <h5 class="mb-0">Options</h5>
            <small class="text-muted">Only used with Single Select.</small>
        </div>
        <button type="button" id="add-option" class="btn btn-sm btn-outline-primary">
            <i data-feather="plus"></i>
            Add Option
        </button>
    </div>

    <div id="options-container">
        @foreach($options as $index => $option)
            <div class="option-row row align-items-end mb-1">
                <div class="col-md-3">
                    <label class="form-label">Value</label>
                    <input type="text" name="options[{{ $index }}][value]" value="{{ $option['value'] }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Label</label>
                    <input type="text" name="options[{{ $index }}][label]" value="{{ $option['label'] }}" class="form-control">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Prompt Value</label>
                    <input type="text" name="options[{{ $index }}][prompt_value]" value="{{ $option['prompt_value'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger remove-option">
                        <i data-feather="trash-2"></i>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const type = document.getElementById('question-type');
        const section = document.getElementById('options-section');
        const container = document.getElementById('options-container');
        let index = {{ count($options) }};

        function renderType() {
            section.style.display = type.value === '{{ \App\Enums\Ai\AiGuideQuestionTypeEnum::SINGLE_SELECT->value }}' ? '' : 'none';
        }

        function addOption() {
            container.insertAdjacentHTML('beforeend', `
            <div class="option-row row align-items-end mb-1">
                <div class="col-md-3">
                    <label class="form-label">Value</label>
                    <input type="text" name="options[${index}][value]" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Label</label>
                    <input type="text" name="options[${index}][label]" class="form-control">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Prompt Value</label>
                    <input type="text" name="options[${index}][prompt_value]" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger remove-option">
                        <i data-feather="trash-2"></i>
                    </button>
                </div>
            </div>
        `);
            index++;
            feather.replace();
        }

        type.addEventListener('change', renderType);
        document.getElementById('add-option').addEventListener('click', addOption);

        container.addEventListener('click', function (e) {
            const button = e.target.closest('.remove-option');
            if (button) button.closest('.option-row').remove();
        });

        renderType();
        if (!container.children.length) addOption();
    });
</script>
