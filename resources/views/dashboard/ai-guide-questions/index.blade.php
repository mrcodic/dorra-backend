@extends('layouts/contentLayoutMaster')

@section('title', 'AI Questions')
@section('main-page', 'AI Questions')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
@endsection

@section('content')
    <div class="card p-1">
        <section class="app-user-list">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-1">
                        <div>
                            <h4 class="mb-25">AI Guide Questions</h4>
                            <p class="text-muted mb-0">Manage AI guide questions and their options.</p>
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
                        <div class="position-relative flex-grow-1 col-12 col-md-4">
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

                    <table class="ai-question-list-table table">
                        <thead class="table-light">
                        <tr>
                            <th>Question</th>
                            <th>Type</th>
                            <th>Prompt Label</th>
                            <th>Options</th>
                            <th>Required</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('vendor-script')
    <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap5.js')) }}"></script>
@endsection

@section('page-script')
    <script>
        const aiGuideQuestionsDataUrl = "{{ route('ai-guide-questions.data') }}";
        const aiGuideQuestionsBaseUrl = "{{ url('ai-guide-questions') }}";
        const csrfToken = "{{ csrf_token() }}";
    </script>

    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="{{ asset('js/scripts/pages/app-ai-guide-question-list.js') }}?v={{ time() }}"></script>
@endsection
