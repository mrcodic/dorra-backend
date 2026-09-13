@extends('layouts/contentLayoutMaster')

@section('title', 'Edit AI Product')
@section('main-page', 'AI Products')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('admin/vendors/css/forms/select/select2.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Edit AI Product</h4>
        </div>

        <div class="card-body">
            <form id="ai-product-form" action="{{ route('ai-categories.update', $model->id) }}" method="POST">
                @csrf
                @method('PUT')

                @include('dashboard.ai-categories._form')

                <div class="d-flex justify-content-end gap-1 mt-2">
                    <a href="{{ route('ai-categories.index') }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>

                    <button type="submit" id="submit-button" class="btn btn-primary">
                        <i data-feather="save"></i>
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('vendor-script')
    <script src="{{ asset('admin/vendors/js/forms/select/select2.full.min.js') }}"></script>
@endsection

@section('page-script')
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @include('dashboard.ai-categories._submit')

    <script>
        $(function () {
            const modal = $('#quick-studio-item-modal');
            const questionsSelect = $('#quick-studio-question-ids');

            function initStudioQuestionsSelect2() {
                if (!questionsSelect.length) return;

                if (typeof $.fn.select2 !== 'function') {
                    console.error('Select2 is NOT loaded');
                    return;
                }

                if (questionsSelect.hasClass('select2-hidden-accessible')) {
                    questionsSelect.select2('destroy');
                }

                questionsSelect.select2({
                    width: '100%',
                    placeholder: 'Select Questions',
                    allowClear: true,
                    closeOnSelect: false,
                    dropdownParent: modal
                });
            }

            modal.on('shown.bs.modal', function () {
                initStudioQuestionsSelect2();

                const id = Number($('#quick-studio-item-id').val() || 0);

                if (
                    id &&
                    typeof studioItemData !== 'undefined' &&
                    studioItemData[id]
                ) {
                    questionsSelect
                        .val((studioItemData[id].question_ids ?? []).map(String))
                        .trigger('change');
                }
            });
        });
    </script>
@endsection
