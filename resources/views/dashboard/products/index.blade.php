
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
                <div class="row gx-2 gy-2 align-items-center px-1">
                    <div class="col-12 col-md-6">
                        <form action="" method="get" class="position-relative">
                            <i data-feather="search" class="position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                            <input
                                type="text"
                                class="form-control ps-5 border rounded-3"
                                name="search_value"
                                id="search-product-form"
                                placeholder="Search category..."
                                style="height: 38px;">
                        </form>
                    </div>

                    <div class="col-6 col-md-2 col-lg-2">
                        <select name="category_id" class="form-select category-select">
                            <option value=""  selected disabled>Product</option>
                            @foreach($associatedData['categories'] as $category)
                                <option value="{{ $category->id }}">{{ $category->name}}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-6 col-md-2 col-lg-2">
                        <div style="position: relative;">
                            <select name="tag_id" class="tag-select form-select pe-5" id="tagSelect">
                                <option value="" selected disabled>Tag</option>
                                @foreach($associatedData['tags'] as $tag)
                                    <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" id="clearTagFilter"
                                    style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
                       background: transparent; border: none; font-weight: bold;
                       color: #aaa; cursor: pointer; font-size: 18px; line-height: 1;"
                                    title="Clear filter">
                                &times;
                            </button>
                        </div>
                    </div>

                    <div class="col-12 col-md-2 text-md-end">
                        <a class="btn btn-outline-primary w-100 w-md-auto" href="{{ route('products.create') }}">
                            Add New Category
                        </a>
                    </div>
                </div>


                <table class="product-list-table table">
                    <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" id="select-all-checkbox">
                        </th>
                        <th>Image</th>
                        <th>Name</th>
{{--                        <th>Category</th>--}}
                        <th>Tags</th>
                        <th>NO.of Purchas</th>
                        <th>Added Date</th>
                        <th>Rating</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                </table>
                <div id="bulk-delete-container" class="my-2 bulk-delete-container" style="display: none;">
                    <div class="delete-container">
                        <p id="selected-count-text">0 Products are selected</p>
                        <button type="submit" id="delete-selected-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteProductsModal"
                                class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1 delete-selected-btns">
                            <i data-feather="trash-2"></i> Delete Selected
                        </button>
                        <form style="display: none;" id="bulk-delete-form" method="POST" action="{{ route('products.bulk-delete') }}">
                            @csrf
                            <button type="submit" id="delete-selected-btn"
                                    class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1 delete-selected-btns">
                                <i data-feather="trash-2"></i> Delete Selected
                            </button>
                        </form>


                    </div>
                    </div>
                </div>
            </div>
            @include('modals.delete',[
                     'id' => 'deleteProductModal',
                     'formId' => 'deleteProductForm',
                     'title' => 'Delete Product',
                     ])
            @include('modals.delete',[
            'id' => 'deleteProductsModal',
            'formId' => 'bulk-delete-form',
            'title' => 'Delete Categories',
            'confirmText' => 'Are you sure you want to delete this items?',
            ])
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
    <script src="https://unpkg.com/feather-icons"></script>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        const productsDataUrl = "{{ route('products.data') }}";
        const productsCreateUrl = "{{ route('products.create') }}";
    </script>
    <script>
        document.getElementById('clearTagFilter').addEventListener('click', function () {
            const select = document.getElementById('tagSelect');
            select.selectedIndex = 0; // Select the first option (placeholder)
            select.dispatchEvent(new Event('change')); // If you have JS that listens to this
        });
    </script>

    <script>
        $(document).ready(function () {
            // Select all toggle
            $('#select-all-checkbox').on('change', function () {
                $('.category-checkbox').prop('checked', this.checked);
                updateBulkDeleteVisibility();
            });

            // When individual checkbox changes
            $(document).on('change', '.category-checkbox', function () {
                // If any is unchecked, uncheck "Select All"
                if (!this.checked) {
                    $('#select-all-checkbox').prop('checked', false);
                } else if ($('.category-checkbox:checked').length === $('.category-checkbox').length) {
                    $('#select-all-checkbox').prop('checked', true);
                }
                updateBulkDeleteVisibility();
            });


            // On table redraw (e.g. pagination, search)
            $(document).on('draw.dt', function () {
                $('#bulk-delete-container').hide();
                $('#select-all-checkbox').prop('checked', false);
            });

            // Close bulk delete container
            $(document).on('click', '#close-bulk-delete', function () {
                $('#bulk-delete-container').hide();
                $('.category-checkbox').prop('checked', false);
                $('#select-all-checkbox').prop('checked', false);
            });

            // Update the bulk delete container visibility
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

        });
    </script>




    {{-- Page js files --}}
    <script src="{{ asset('js/scripts/pages/app-product-list.js') }}?v={{ time() }}"></script>
@endsection
