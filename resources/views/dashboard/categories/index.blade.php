@extends('layouts/contentLayoutMaster')

@section('title', 'Categories')
@section('main-page', 'Categories')

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
            <table class="category-list-table table">
                <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" id="select-all-checkbox">
                        </th>
                        <th>Name</th>
                        <th>SubCategories</th>
                        <th>NO.of Products</th>
                        <th>Added Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
            <div id="bulk-delete-container" class="my-2 bulk-delete-container" style="display: none;">
                <div class="delete-container">
                    <p id="selected-count-text">0 Products are selected</p>
                    <button id="delete-selected-btn" class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1">
                            <i data-feather="trash-2"></i> Delete Selected
                        </button>
                </div>
            </div>


        </div>
        @include('modals/modal-show-category')
        @include('modals/modal-edit-category')
        @include('modals/modal-add-category')

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
    const categoriesDataUrl = "{{ route('categories.data') }}";
    const categoriesCreateUrl = "{{ route('categories.create') }}";
    const locale = "{{ app()->getLocale() }}";
</script>
<script src="">
    // Function to update bulk delete display
    function updateBulkDeleteVisibility() {
        const selectedCheckboxes = $('.category-checkbox:checked');
        const count = selectedCheckboxes.length;

        if (count > 0) {
            $('#selected-count-text').text(`${count} Product${count > 1 ? 's' : ''} are selected`);
            $('#bulk-delete-container').show();
        } else {
            $('#bulk-delete-container').hide();
        }
    }

    // When checkbox is changed
    $(document).on('change', '.category-checkbox', function() {
        updateBulkDeleteVisibility();
    });

    // Optional: Hide bulk action on table redraw
    dt_user_table.on("draw", function() {
        $('#bulk-delete-container').hide();
    });

    // Close icon handler
    $(document).on('click', '#close-bulk-delete', function() {
        $('#bulk-delete-container').hide();
        $('.category-checkbox').prop('checked', false); // uncheck all
    });
</script>
{{-- Page js files --}}
<script src="{{ asset('js/scripts/pages/app-category-list.js') }}?v={{ time() }}"></script>
@endsection