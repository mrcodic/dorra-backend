@extends('layouts/contentLayoutMaster')

@section('title', 'AI Questions')
@section('main-page', 'AI Questions')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')
    @php
        $aiCategories = collect($associatedData['aiCategories'] ?? []);

        if ($aiCategories->isEmpty()) {
            $aiCategories = \App\Models\AiCategory::query()
                ->where('enabled', true)
                ->with('category')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        }
    @endphp

    <div class="card p-1">
        <section class="app-user-list">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-1">
                        <div>
                            <h4 class="mb-25">AI Guide Questions</h4>
                            <p class="text-muted mb-0">
                                Manage global questions. Select an AI Product to manage its question order and conditional visibility.
                            </p>
                        </div>

                        @can('ai-guide-questions_create')
                            <a href="{{ route('ai-guide-questions.create') }}" class="btn btn-primary">
                                <i data-feather="plus"></i>
                                Add Question
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="card-datatable table-responsive pt-0">
                    <div class="px-1 mb-2 d-flex flex-wrap align-items-center gap-1">
                        <div class="position-relative flex-grow-1 col-12 col-md-3">
                            <i data-feather="search"
                               class="position-absolute top-50 translate-middle-y ms-2 text-muted"></i>

                            <input type="text"
                                   id="search-ai-question-form"
                                   class="form-control ps-5 pe-3 border rounded-3"
                                   placeholder="Search question...">

                            <button type="button"
                                    id="clear-search"
                                    style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:transparent;border:none;color:#aaa;font-size:18px">
                                &times;
                            </button>
                        </div>

                        <div class="col-12 col-md-3">
                            <select
                                id="filter-ai-category"
                                class="form-select select2 filter-ai-category"
                                data-placeholder="AI Product"
                            >
                                <option value=""></option>

                                @foreach($aiCategories as $aiCategory)
                                    <option value="{{ $aiCategory->id }}">
                                        {{ $aiCategory->category?->name ?? ('AI Product #' . $aiCategory->id) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-2">
                            <select class="form-select filter-type">
                                <option value="">All Types</option>

                                @foreach(\App\Enums\Ai\AiGuideQuestionTypeEnum::cases() as $type)
                                    <option value="{{ $type->value }}">
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-2">
                            <select class="form-select filter-status">
                                <option value="">All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div id="product-config-hint" class="px-1 mb-1">
                        <div class="alert alert-info py-1 mb-0">
                            Select an AI Product to edit product-specific <strong>Order</strong> and <strong>Conditional</strong> rules.
                        </div>
                    </div>

                    <table class="ai-question-list-table table">
                        <thead class="table-light">
                        <tr>
                            <th>Question</th>
                            <th>Type</th>
                            <th>Prompt Label</th>
                            <th>Options</th>
                            <th>Required</th>
                            <th>Order</th>
                            <th>Conditional</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="question-conditions-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Conditional Visibility</h5>
                        <small class="text-muted">
                            Multiple answers in one rule are OR. Different parent rules are AND.
                        </small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="condition-question-id">
                    <div class="mb-1">
                        <strong id="condition-question-title"></strong>
                    </div>

                    <div id="condition-rules-container"></div>

                    <button
                        type="button"
                        id="add-condition-rule"
                        class="btn btn-sm btn-outline-primary mt-1"
                    >
                        <i data-feather="plus"></i>
                        Add Parent Rule
                    </button>

                    <small class="text-muted d-block mt-1">
                        The parent question must already be attached to the selected AI Product.
                    </small>
                </div>

                <div class="modal-footer d-flex justify-content-between">
                    <button
                        type="button"
                        id="clear-question-conditions"
                        class="btn btn-outline-danger"
                    >
                        Clear Conditions
                    </button>

                    <div class="d-flex gap-1">
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            id="save-question-conditions"
                            class="btn btn-primary"
                        >
                            Save Conditions
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('vendor-script')
    <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap5.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
@endsection

@section('page-script')
    <script>
        const aiGuideQuestionsDataUrl = "{{ route('ai-guide-questions.data') }}";
        const aiGuideQuestionsBaseUrl = "{{ url('ai-guide-questions') }}";
        const aiGuideQuestionProductConfigUrl = "{{ route('ai-guide-questions.product-config') }}";
        const aiGuideQuestionConditionsUrlTemplate =
            "{{ route('ai-guide-questions.conditions.show', ['question' => '__QUESTION_ID__']) }}";
        const aiGuideQuestionConditionsUpdateUrlTemplate =
            "{{ route('ai-guide-questions.conditions.update', ['question' => '__QUESTION_ID__']) }}";
        const aiGuideQuestionSortOrderUrlTemplate =
            "{{ route('ai-guide-questions.sort-order.update', ['question' => '__QUESTION_ID__']) }}";
        const csrfToken = "{{ csrf_token() }}";
    </script>

    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="{{ asset('js/scripts/pages/app-ai-guide-question-list.js') }}?v={{ time() }}"></script>
@endsection
