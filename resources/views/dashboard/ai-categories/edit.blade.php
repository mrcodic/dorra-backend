@extends('layouts/contentLayoutMaster')

@section('title', 'Edit AI Product')
@section('main-page', 'AI Products')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Edit AI Product</h4>
        </div>

        <div class="card-body">
            <form id="ai-product-form"
                  action="{{ route('ai-categories.update', $model->id) }}"
                  method="POST">

                @csrf
                @method('PUT')

                @include('dashboard.ai-categories._form')

                <div class="d-flex justify-content-end gap-1 mt-2">
                    <a href="{{ route('ai-categories.index') }}"
                       class="btn btn-outline-secondary">
                        Cancel
                    </a>

                    <button type="submit"
                            id="submit-button"
                            class="btn btn-primary">
                        <i data-feather="save"></i>
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page-script')
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    @include('dashboard.ai-categories._submit')
@endsection
