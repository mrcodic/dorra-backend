@extends('layouts/contentLayoutMaster')

@section('title', 'Edit AI Studio Item')
@section('main-page', 'AI Studio Items')
@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('vendors/css/forms/select/select2.min.css') }}">
@endsection
@section('content')
    <div class="card">
        <div class="card-header border-bottom">
            <div>
                <h4 class="card-title mb-25">Edit AI Studio Item</h4>
                <p class="text-muted mb-0">{{ $model?->name ?? 'AI Studio Item' }}</p>
            </div>

            <a href="{{ route('ai-studio-items.index') }}"
               class="btn btn-outline-secondary">
                <i data-feather="arrow-left"></i>
                Back
            </a>
        </div>

        <div class="card-body pt-2">
            @include('dashboard.ai-studio-items._form')
        </div>
    </div>
@endsection
@section('vendor-script')
    <script src="{{ asset('vendors/js/forms/select/select2.full.min.js') }}"></script>
@endsection
