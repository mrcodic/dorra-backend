@extends('layouts/contentLayoutMaster')

@section('title', 'Tags List')

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
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 user_role"></div>
                <div class="col-md-4 user_plan"></div>
                <div class="col-md-4 user_status"></div>
            </div>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <div class="row gx-2 gy-2 align-items-center px-1">

                {{-- Search Input --}}
                <div class="col-12 col-md-7">
                    <form action="" method="get" class="position-relative">
                        <i data-feather="search" class="position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                        <input
                            type="text"
                            class="form-control ps-5 border rounded-3"
                            name="search_value"
                            id="search-tag-form"
                            placeholder="Search tag..."
                            style="height: 38px;">
                    </form>
                </div>

                {{-- Filter Dropdown --}}
                <div class="col-6 col-md-2">
                    <select name="created_at" class="form-select filter-date">
                        <option value="">Date</option>
                        <option value="asc">asc</option>
                        <option value="desc">desc</option>
                    </select>
                </div>

                {{-- Add Button --}}
                <div class="col-6 col-md-3 text-md-end">
                    <a class="btn btn-outline-primary w-100 w-md-auto d-flex align-items-center justify-content-center "
                        data-bs-toggle="modal"
                        data-bs-target="#addTagModal">
                        <i data-feather="plus"></i>
                        Add New Tag
                    </a>
                </div>

            </div>


            <table class="tag-list-table table">
                <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" id="select-all-checkbox">
                        </th>
                        <th>Name</th>
                        <th>NO.of Products</th>
                        <th>NO.of Templates</th>
                        <th>Added Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
            <div id="bulk-delete-container" class="my-2 bulk-delete-container" style="display: none;">

                <div class="delete-container">
                    <p id="selected-count-text">0 Tags are selected</p>
                    <button type="submit"  id="delete-selected-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteTagsModal"
                            class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1 delete-selected-btns open-delete-categories-modal">
                        <i data-feather="trash-2"></i> Delete Selected
                    </button>
                    <form style="display: none;" id="bulk-delete-form" method="POST" action="{{ route('tags.bulk-delete') }}">
                        @csrf
                        <button  type="submit"  id="delete-selected-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteTagsModal"
                                class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1 delete-selected-btns open-delete-categories-modal">
                            <i data-feather="trash-2"></i> Delete Selected
                        </button>
                    </form>


                </div>
            </div>

            @include('modals.delete',[
          'id' => 'deleteTagModal',
          'formId' => 'deleteTagForm',
          'title' => 'Delete Tag',
          ])
            @include('modals.delete',[
            'id' => 'deleteTagsModal',
            'formId' => 'bulk-delete-form',
            'title' => 'Delete Tags',
            'confirmText' => 'Are you sure you want to delete this items?',
            ])
            @include('modals.tags.add-tag')
            @include('modals.tags.show-tag')
            @include('modals.tags.edit-tag')


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
    const tagsDataUrl = "{{ route('tags.data') }}";
    const locale = "{{ app()->getLocale() }}";
</script>

<script>
    $(document).ready(function() {
        // Select all toggle
        $('#select-all-checkbox').on('change', function() {
            $('.category-checkbox').prop('checked', this.checked);
            updateBulkDeleteVisibility();
        });

        // When individual checkbox changes
        $(document).on('change', '.category-checkbox', function() {
            // If any is unchecked, uncheck "Select All"
            if (!this.checked) {
                $('#select-all-checkbox').prop('checked', false);
            } else if ($('.category-checkbox:checked').length === $('.category-checkbox').length) {
                $('#select-all-checkbox').prop('checked', true);
            }
            updateBulkDeleteVisibility();
        });


        // On table redraw (e.g. pagination, search)
        $(document).on('draw.dt', function() {
            $('#bulk-delete-container').hide();
            $('#select-all-checkbox').prop('checked', false);
        });

        // Close bulk delete container
        $(document).on('click', '#close-bulk-delete', function() {
            $('#bulk-delete-container').hide();
            $('.category-checkbox').prop('checked', false);
            $('#select-all-checkbox').prop('checked', false);
        });

        // Update the bulk delete container visibility
        function updateBulkDeleteVisibility() {
            const selectedCheckboxes = $('.category-checkbox:checked');
            const count = selectedCheckboxes.length;

            if (count > 0) {
                $('#selected-count-text').text(`${count} Tag${count > 1 ? 's' : ''} are selected`);
                $('#bulk-delete-container').show();
            } else {
                $('#bulk-delete-container').hide();
            }
        }

    });
</script>
{{-- Page js files --}}
<script src="{{ asset('js/scripts/pages/app-tag-list.js') }}?v={{ time() }}"></script>
@endsection
