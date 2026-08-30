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

