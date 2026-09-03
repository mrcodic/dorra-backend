@extends('layouts/contentLayoutMaster')

@section('title', 'Configure AI Questions')
@section('main-page', 'AI Products')

@section('vendor-style')
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css"
    >
@endsection

@php
    $assignedQuestions = $aiCategory
        ->questions
        ->keyBy('id');

    $assignedOptionIds = $aiCategory
        ->options
        ->pluck('id')
        ->map(fn($id) => (int) $id)
        ->all();
@endphp

@section('content')

    <div class="card">
        <div class="card-header border-bottom">
            <div>
                <h4 class="card-title mb-25">
                    Configure Questions
                </h4>

                <p class="text-muted mb-0">
                    {{ $aiCategory->category?->name }}
                </p>
            </div>

            <a href="{{ route('ai-categories.index') }}"
               class="btn btn-outline-secondary">
                <i data-feather="arrow-left"></i>
                Back
            </a>
        </div>

        <div class="card-body pt-2">

            <div class="alert alert-primary">
                Select the questions and options that should
                appear when users generate designs for
                <strong>
                    {{ $aiCategory->category?->name }}
                </strong>.
            </div>

            <form
                id="questions-form"
                action="{{ route(
                'ai-categories.questions.update',
                $aiCategory->id
            ) }}"
                method="POST"
            >

                @csrf
                @method('PUT')

                <div class="d-flex justify-content-between align-items-center mb-2">

                    <div>
                        <h5 class="mb-25">
                            Questions & Options
                        </h5>

                        <small class="text-muted">
                            These questions are inherited by
                            products inside this category.
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
                    </div>
                </div>

                @forelse($questions as $question)

                    @php
                        $assigned = $assignedQuestions
                            ->get($question->id);

                        $selected = (bool) $assigned;

                        $required = $assigned
                            ? (bool) $assigned->pivot->required
                            : (bool) $question->required;

                        $sortOrder = $assigned
                            ? $assigned->pivot->sort_order
                            : $question->sort_order;

                        $selectedOptions = $question
                            ->options
                            ->whereIn(
                                'id',
                                $assignedOptionIds
                            )
                            ->pluck('id')
                            ->map(fn($id) => (int) $id)
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
                        class="question-card border rounded mb-2"
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
                                            <div class="fw-bolder">
                                                {{ $question->title }}
                                            </div>

                                            <small class="text-muted">
                                                {{ $question->prompt_label }}
                                            </small>
                                        </label>

                                    </div>

                                </div>

                                <div class="col-md-5">

                                    <div
                                        class="question-settings d-flex justify-content-end align-items-center gap-2"
                                    >

                                    <span
                                        class="badge bg-light-primary text-primary"
                                    >
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

                                            <label class="form-check-label">
                                                Required
                                            </label>

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

                        @if(
                            $supportsOptions
                            && $question->options->isNotEmpty()
                        )

                            <div
                                class="question-options border-top p-1"
                            >

                                <div
                                    class="d-flex justify-content-between align-items-center mb-1"
                                >

                                    <div>
                                        <h6 class="mb-25">
                                            Allowed Options
                                        </h6>

                                        <small class="text-muted">
                                            Choose the options available
                                            for this category.
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

                                    @foreach(
                                        $question->options
                                        as $option
                                    )

                                        <div class="col-md-4 col-lg-3 mb-1">

                                            <label
                                                class="border rounded p-1 w-100 h-100 option-item"
                                                for="option-{{ $option->id }}"
                                                style="cursor:pointer"
                                            >

                                                <div class="form-check mb-0">

                                                    <input
                                                        type="checkbox"
                                                        id="option-{{ $option->id }}"
                                                        name="questions[{{ $question->id }}][options][]"
                                                        value="{{ $option->id }}"
                                                        class="form-check-input option-checkbox"
                                                        @checked(
                                                            in_array(
                                                                (int) $option->id,
                                                                $selectedOptions,
                                                                true
                                                            )
                                                        )
                                                    >

                                                    <span
                                                        class="form-check-label fw-bolder"
                                                    >
                                                    {{ $option->label }}
                                                </span>

                                                </div>

                                                @if($option->prompt_value)

                                                    <small
                                                        class="text-muted d-block mt-50"
                                                    >
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

                    <div class="alert alert-warning">
                        No active AI questions found.
                    </div>

                @endforelse

                <div class="d-flex justify-content-end gap-1 mt-2">

                    <a
                        href="{{ route('ai-categories.index') }}"
                        class="btn btn-outline-secondary"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        id="save-questions"
                        class="btn btn-primary"
                    >
                        <i data-feather="save"></i>
                        Save Questions
                    </button>

                </div>

            </form>

        </div>
    </div>

@endsection

@section('page-script')

    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script>

        $(document).ready(function () {

            feather.replace();

            const form = $('#questions-form');
            const saveButton = $('#save-questions');

            let submitting = false;

            function toggleQuestion(card) {

                const checked = card
                    .find('.question-toggle')
                    .is(':checked');

                card
                    .find('.question-settings')
                    .toggle(checked);

                card
                    .find('.question-options')
                    .toggle(checked);

                if (checked) {
                    card.addClass('border-primary');
                } else {
                    card.removeClass('border-primary');
                }
            }

            $('.question-card').each(function () {
                toggleQuestion($(this));
            });

            $(document).on(
                'change',
                '.question-toggle',
                function () {

                    toggleQuestion(
                        $(this).closest('.question-card')
                    );

                }
            );

            $('#select-all-questions').on(
                'click',
                function () {

                    $('.question-toggle')
                        .prop('checked', true)
                        .trigger('change');

                }
            );

            $('#clear-all-questions').on(
                'click',
                function () {

                    $('.question-toggle')
                        .prop('checked', false)
                        .trigger('change');

                }
            );

            $(document).on(
                'click',
                '.select-question-options',
                function () {

                    $(this)
                        .closest('.question-options')
                        .find('.option-checkbox')
                        .prop('checked', true);

                }
            );

            $(document).on(
                'click',
                '.clear-question-options',
                function () {

                    $(this)
                        .closest('.question-options')
                        .find('.option-checkbox')
                        .prop('checked', false);

                }
            );

            function toast(
                message,
                error = true
            ) {

                Toastify({
                    text: message,
                    duration: 4000,
                    close: true,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor:
                        error
                            ? '#EA5455'
                            : '#28C76F'
                }).showToast();

            }

            function setLoading(state) {

                if (state) {

                    if (!saveButton.data('html')) {
                        saveButton.data(
                            'html',
                            saveButton.html()
                        );
                    }

                    saveButton
                        .prop('disabled', true)
                        .html(`
                    <span
                        class="spinner-border spinner-border-sm me-50"
                    ></span>
                    Saving...
                `);

                    return;
                }

                saveButton
                    .prop('disabled', false)
                    .html(
                        saveButton.data('html')
                    );

                feather.replace();
            }

            function showErrors(xhr) {

                const response =
                    xhr.responseJSON ?? {};

                if (
                    xhr.status === 422
                    && response.errors
                ) {

                    Object.values(
                        response.errors
                    ).forEach(messages => {

                        (
                            Array.isArray(messages)
                                ? messages
                                : [messages]
                        ).forEach(message => {

                            toast(message);

                        });

                    });

                    return;
                }

                toast(
                    response.message
                    ?? 'Something went wrong.'
                );

            }

            form
                .off('submit.aiCategoryQuestions')
                .on(
                    'submit.aiCategoryQuestions',
                    function (e) {

                        e.preventDefault();

                        if (submitting) {
                            return;
                        }

                        submitting = true;

                        setLoading(true);

                        $.ajax({

                            url: form.attr('action'),

                            type: 'POST',

                            data: form.serialize(),

                            success: function () {

                                toast(
                                    'Questions updated successfully.',
                                    false
                                );

                                setTimeout(() => {

                                    window.location.href =
                                        '{{ route('ai-categories.index') }}';

                                }, 500);

                            },

                            error: function (xhr) {

                                showErrors(xhr);

                                submitting = false;

                                setLoading(false);

                            }

                        });

                    }
                );

        });

    </script>

@endsection
