@extends('layouts/contentLayoutMaster')

@section('title', 'Add AI Question')
@section('main-page', 'AI Questions')

@section('content')
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Add AI Question</h4>
        </div>

        <div class="card-body">
            <form id="question-form" action="{{ route('ai-guide-questions.store') }}" method="POST">
                @csrf
                @include('dashboard.ai-guide-questions._form')

                <div class="d-flex justify-content-end gap-1 mt-2">
                    <a href="{{ route('ai-guide-questions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button class="btn btn-primary" type="submit">Save Question</button>
                </div>
            </form>
        </div>
    </div>
@endsection

