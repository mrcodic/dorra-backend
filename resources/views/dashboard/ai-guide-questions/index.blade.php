@extends('layouts/contentLayoutMaster')

@section('title', 'AI Questions')
@section('main-page', 'AI Questions')

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
                        <a href="{{ route('ai-guide-questions.create') }}" class="btn btn-primary">
                            <i data-feather="plus"></i>
                            Add Question
                        </a>
                    </div>
                </div>

                <div class="card-datatable table-responsive pt-0">
                    <form method="GET" class="px-1 mb-2">
                        <div class="d-flex flex-wrap align-items-center gap-1">
                            <div class="position-relative flex-grow-1 col-12 col-md-4">
                                <i data-feather="search" class="position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                                <input type="text"
                                       name="search_value"
                                       value="{{ request('search_value') }}"
                                       class="form-control ps-5 border rounded-3"
                                       placeholder="Search question...">
                            </div>

                            <div class="col-12 col-md-2">
                                <select name="type" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Types</option>
                                    @foreach(\App\Enums\Ai\AiGuideQuestionTypeEnum::cases() as $type)
                                        <option value="{{ $type->value }}" @selected(request('type') === $type->value)>
                                            {{ $type->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-2">
                                <select name="is_active" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Status</option>
                                    <option value="1" @selected(request('is_active') === '1')>Active</option>
                                    <option value="0" @selected(request('is_active') === '0')>Inactive</option>
                                </select>
                            </div>

                            <button class="btn btn-outline-primary" type="submit">Search</button>

                            @if(request()->hasAny(['search_value', 'type', 'is_active']))
                                <a href="{{ route('ai-guide-questions.index') }}" class="btn btn-outline-secondary">Clear</a>
                            @endif
                        </div>
                    </form>

                    <table class="table">
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
                        <tbody>
                        @forelse($data as $question)
                            <tr>
                                <td>
                                    <div class="fw-bolder">{{ $question->title }}</div>
                                    <small class="text-muted">{{ $question->key }}</small>
                                </td>
                                <td>
                                <span class="badge bg-light-primary text-primary">
                                    {{ $question->type->label() }}
                                </span>
                                </td>
                                <td>{{ $question->prompt_label }}</td>
                                <td>
                                    @if($question->type === \App\Enums\Ai\AiGuideQuestionTypeEnum::SINGLE_SELECT)
                                        {{ $question->options_count }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                <span class="badge {{ $question->required ? 'bg-light-danger text-danger' : 'bg-light-secondary text-secondary' }}">
                                    {{ $question->required ? 'Required' : 'Optional' }}
                                </span>
                                </td>
                                <td>{{ $question->sort_order }}</td>
                                <td>
                                <span class="badge {{ $question->is_active ? 'bg-light-success text-success' : 'bg-light-danger text-danger' }}">
                                    {{ $question->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('ai-guide-questions.edit', $question->id) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i data-feather="edit-2"></i>
                                        </a>

                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger delete-question"
                                                data-id="{{ $question->id }}">
                                            <i data-feather="trash-2"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-3">No questions found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                    <div class="px-1 pb-1">
                        {{ $data->links() }}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('page-script')
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            feather.replace();

            $(document).on('click', '.delete-question', function () {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Delete Question?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete'
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: `{{ url('ai-guide-questions') }}/${id}`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: () => window.location.reload(),
                        error: () => Swal.fire('Error', 'Could not delete question.', 'error')
                    });
                });
            });
        });
    </script>
@endsection
