@extends('layouts/contentLayoutMaster')

@section('title', 'Edit AI Question')
@section('main-page', 'AI Questions')

@section('content')
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Edit AI Question</h4>
        </div>

        <div class="card-body">
            <form id="question-form" action="{{ route('ai-guide-questions.update', $model->id) }}" method="POST">
                @csrf
                @method('PUT')

                @include('dashboard.ai-guide-questions._form')

                <div class="d-flex justify-content-end gap-1 mt-2">
                    <a href="{{ route('ai-guide-questions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button class="btn btn-primary" type="submit">Update Question</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page-script')
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        $(document).ready(function () {
            feather.replace();

            $('#question-form').on('submit', function (e) {
                e.preventDefault();

                $.ajax({
                    url: this.action,
                    method: 'POST',
                    data: $(this).serialize(),
                    success: () => window.location.href = '{{ route('ai-guide-questions.index') }}',
                    error: function (xhr) {
                        console.error(xhr.responseJSON);
                    }
                });
            });
        });
    </script>
@endsection
