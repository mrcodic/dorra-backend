@extends('layouts/contentLayoutMaster')

@section('title', 'Discount Codes')
@section('main-page', 'Discount Codes')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/rowGroup.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('page-style')
    <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
@endsection

@section('content')
    <div class="container card p-1">
        <section class="app-user-list">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-1">
                        <div class="col-md-4 user_role"></div>
                        <div class="col-md-4 user_plan"></div>
                        <div class="col-md-4 user_status"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-1 px-1">
                        <form method="get" class="flex-grow-1 me-1 position-relative" style="max-width: 75%;">
                            <i data-feather="search" class="position-absolute top-50 translate-middle-y mx-1 text-muted"></i>
                            <input type="text" class="form-control ps-5 border rounded-3" id="search-code-form" placeholder="Search here" style="height: 38px;">
                        </form>

                        <div class="col-6 col-md-2">
                            <select class="form-select filter-date">
                                <option value="">Date</option>
                                <option value="asc">asc</option>
                                <option value="desc">desc</option>
                            </select>
                        </div>
                        <button id="export-excel" type="button" class="btn btn-secondary fs-16 px-2">
                            Export
                        </button>
                        <button type="button" class="btn btn-outline-secondary fs-16 px-1" style="width: 126px;" data-bs-toggle="modal" data-bs-target="#createCodeTemplateModal">
                            Generate Code
                        </button>


                    </div>

                    <table class="code-list-table table">
                        <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="select-all-checkbox"></th>
                            <th>Code Name</th>
                            <th>Type</th>
                            <th>Restrictions</th>
                            <th>Number Of Usage</th>
                            <th>Expiry Date</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                    </table>

                    <div id="bulk-delete-container" class="my-2 bulk-delete-container" style="display: none;">
                        <div class="delete-container">
                        <p id="selected-count-text">0 Discount Codes are selected</p>
                            <button type="submit" id="delete-selected-btn" data-bs-toggle="modal" data-bs-target="#deleteCodesModal" class="btn btn-outline-danger d-flex align-items-center gap-1">
                                <i data-feather="trash-2"></i> Delete Selected
                            </button>
                            <form style="display: none;" id="bulk-delete-form" method="POST" action="{{ route('discount-codes.bulk-delete') }}">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>

                @include('modals.discount-codes.auto-generate')
                @include('modals.discount-codes.show-code')
                @include('modals.discount-codes.edit-code')
                @include('modals.delete', ['id' => 'deleteCodeModal', 'formId' => 'deleteCodeForm', 'title' => 'Delete Code'])
                @include('modals.delete', ['id' => 'deleteCodesModal', 'formId' => 'bulk-delete-form', 'title' => 'Delete Codes', 'confirmText' => 'Are you sure you want to delete this items?'])
            </div>
        </section>
    </div>
@endsection

@section('vendor-script')
    <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap5.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.buttons.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/jszip.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/pdfmake.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/vfs_fonts.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/buttons.html5.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/validation/jquery.validate.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/cleave/cleave.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/cleave/addons/cleave-phone.us.js')) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
@endsection

@section('page-script')
    <script>
        const discountCodeDataUrl = "{{ route('discount-codes.data') }}";
        const locale = "{{ app()->getLocale() }}";
    </script>
    <script src="{{ asset('js/scripts/pages/app-discount-codes-list.js') }}?v={{ time() }}"></script>
    <script src="https://unpkg.com/feather-icons"></script>

    <script>
        $(document).ready(function () {
            // Trigger export from external button
            let dtTable = $('.code-list-table').DataTable();

            $('#export-excel').on('click', function () {
                dtTable.button('.buttons-excel').trigger();
            });
        });
    </script>
@endsection
