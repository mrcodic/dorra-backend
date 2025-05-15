@extends('layouts/contentLayoutMaster')

@section('title', 'Templates')
@section('main-page', 'Templates')

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
                <div class="row gx-2 gy-2 align-items-center px-1">

                    {{-- Search Input - 70% on md+, full width on xs --}}
                    <div class="col-12 col-md-4">
                        <form action="" method="get" class="position-relative">
                            <i data-feather="search"
                               class="position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                            <input
                                type="text"
                                class="form-control ps-5 border rounded-3"
                                name="search_value"
                                id="search-category-form"
                                placeholder="Search product..."
                                style="height: 38px;">
                        </form>
                    </div>
                    {{-- Filter Select - 10% on md+, half width on sm --}}
                    <div class="col">
                        <select name="created_at" class="form-select filter-date">
                            <option value="">Drafts</option>
                            <option value="asc">asc</option>
                            <option value="desc">desc</option>
                        </select>
                    </div>
                    {{-- Filter Select - 10% on md+, half width on sm --}}
                    <div class="col">
                        <select name="created_at" class="form-select filter-date">
                            <option value="">Category</option>
                            <option value="asc">asc</option>
                            <option value="desc">desc</option>
                        </select>
                    </div>
                    {{-- Filter Select - 10% on md+, half width on sm --}}
                    <div class="col">
                        <select name="created_at" class="form-select filter-date">
                            <option value="">Subcategory </option>
                            <option value="asc">asc</option>
                            <option value="desc">desc</option>
                        </select>
                    </div>

                    {{-- Filter Select - 10% on md+, half width on sm --}}
                    <div class="col">
                        <select name="created_at" class="form-select filter-date">
                            <option value="">Tags</option>
                            <option value="asc">asc</option>
                            <option value="desc">desc</option>
                        </select>
                    </div>

                    {{-- Add Button - 20% on md+, full width on xs --}}
                    <div class="col-6 col-md-2 text-md-end">
                        <a class="btn btn-outline-primary w-100 w-md-auto" data-bs-toggle="modal"
                           data-bs-target="#addCategoryModal">
                            <i data-feather="upload"></i>
                            Upload Templates
                        </a>
                    </div>

                </div>


                <table class="template-list-table table">
                    <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" id="select-all-checkbox">
                        </th>
                        <th>Name</th>
                        <th>Thumb</th>
                        <th>Product</th>
                        <th>Status</th>
                        <th>Updated Date</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                </table>
                <div id="bulk-delete-container" class="my-2 bulk-delete-container" style="display: none;">
                    <div class="delete-container">
                        <p id="selected-count-text">0 Templates are selected</p>
                        <form id="bulk-delete-form" method="POST" action="{{ route('categories.bulk-delete') }}">
                            @csrf
                            <button type="submit" id="delete-selected-btn"
                                    class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1 delete-selected-btns">
                                <i data-feather="trash-2"></i> Delete Selected
                            </button>
                        </form>


                    </div>
                </div>


            </div>
            @include('modals.categories.show-category')
            @include('modals.categories.edit-category')
            @include('modals.categories.add-category')

        </div>
        <!-- list and filter end -->
    </section>
    <!-- users list ends -->
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
        const templatesDataUrl = "{{ route('templates.data') }}";
        const templatesCreateUrl = "{{ route('templates.create') }}";
        const locale = "{{ app()->getLocale() }}";
    </script>


    {{-- Page js files --}}
    <script src="{{ asset('js/scripts/pages/app-template-list.js') }}?v={{ time() }}"></script>
@endsection
