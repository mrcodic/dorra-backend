@extends('layouts/contentLayoutMaster')

@section('title', 'Add AI Studio Item')
@section('main-page', 'AI Studio Items')

@section('content')
    <div class="card">
        <div class="card-header border-bottom">
            <h4 class="card-title mb-0">Add AI Studio Item</h4>

            <a href="{{ route('ai-studio-items.index') }}"
               class="btn btn-outline-secondary">
                <i data-feather="arrow-left"></i>
                Back
            </a>
        </div>

        <div class="card-body pt-2">
            @include('dashboard.ai-studio-items._form', [
                'item' => null
            ])
        </div>
    </div>
@endsection
