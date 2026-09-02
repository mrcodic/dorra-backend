@extends('layouts/contentLayoutMaster')

@section('title', 'Edit AI Studio Item')
@section('main-page', 'AI Studio Items')

@section('content')
    <div class="card">
        <div class="card-header border-bottom">
            <div>
                <h4 class="card-title mb-25">Edit AI Studio Item</h4>
                <p class="text-muted mb-0">{{ $item->name }}</p>
            </div>

            <a href="{{ route('ai-studio-items.index') }}"
               class="btn btn-outline-secondary">
                <i data-feather="arrow-left"></i>
                Back
            </a>
        </div>

        <div class="card-body pt-2">
            @include('dashboard.ai-studio-items._form', [
                'item' => $item
            ])
        </div>
    </div>
@endsection
