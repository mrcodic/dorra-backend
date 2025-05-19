@extends('layouts/contentLayoutMaster')

@section('title', 'Discount Codes')
@section('main-page', 'Discount Codes')

@section('vendor-style')
{{-- Page Css files --}}
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/rowGroup.bootstrap5.min.css')) }}">


<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-style')
{{-- Page Css files --}}
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
@endsection

@section('content')
<div class="container card p-1">


    <!-- users list start -->
    <section class="app-user-list">

        <!-- list and filter start -->
        <div class="card">
            <div class="card-body ">
                <div class="row">
                    <div class="col-md-4 user_role"></div>
                    <div class="col-md-4 user_plan"></div>
                    <div class="col-md-4 user_status"></div>
                </div>
            </div>
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center gap-1 px-1">
                    <form action="" method="get" class="flex-grow-1 me-1 position-relative" style="max-width: 75%;">
                        <i data-feather="search"
                            class="position-absolute top-50 translate-middle-y mx-1 text-muted"></i>
                        <input
                            type="text"
                            class="form-control ps-5 border rounded-3"
                            name="search_value"
                            id="search-code-form"
                            placeholder="Search here"
                            style="height: 38px;">
                    </form>

                    {{-- Filter Select - 10% on md+, half width on sm --}}
                    <div class="col-6 col-md-2">
                        <select name="created_at" class="form-select filter-date">
                            <option value="">Date</option>
                            <option value="asc">asc</option>
                            <option value="desc">desc</option>
                        </select>
                    </div>
                    <button type="date" class="btn btn-secondary fs-16" style="width: 96px; ">Export</button>
                    <button type="button" class="btn btn-outline-secondary fs-16 px-1" style="width: 126px;" data-bs-toggle="modal"
                        data-bs-target="#autoGenerateCodeTemplateModal">Auto
                        Generate
                    </button>
                    <button type="button" class="btn btn-outline-primary fs-16" data-bs-toggle="modal"
                        data-bs-target="#createCodeTemplateModal">
                        Create New Code
                    </button>
                </div>
                <table class="code-list-table table">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all-checkbox">
                            </th>
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
                        <button id="delete-selected-btn"
                            class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1">
                            <i data-feather="trash-2"></i> Delete Selected
                        </button>
                    </div>
                </div>
            </div>

            @include('modals.discount-codes.auto-generate')
            @include('modals.discount-codes.create-code')
            @include('modals.delete',[
                    'id' => 'deleteCodeModal',
                    'formId' => 'deleteCodeForm',
                    'title' => 'Delete Code',
                    ])
            @include('modals.delete',[
            'id' => 'deleteCodesModal',
            'formId' => 'bulk-delete-form',
            'title' => 'Delete Codes',
            'confirmText' => 'Are you sure you want to delete this items?',
            ])
        </div>
        <!-- list and filter end -->
    </section>
    <!-- users list ends -->
</div>

@endsection

@section('vendor-script')
{{-- Vendor js files --}}
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
<script src="{{ asset(mix('vendors/js/tables/datatable/buttons.print.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.rowGroup.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/validation/jquery.validate.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/cleave/cleave.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/cleave/addons/cleave-phone.us.js')) }}"></script>
@endsection

@section('page-script')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script>
    const discountCodeDataUrl = "{{ route('discount-codes.data') }}";
    const discountCodeCreateUrl = "{{ route('discount-codes.create') }}";
</script>

{{-- Page js files --}}
<script src="{{ asset('js/scripts/pages/app-discount-codes-list.js') }}?v={{ time() }}"></script>
<script src="https://unpkg.com/feather-icons"></script>

@endsection
