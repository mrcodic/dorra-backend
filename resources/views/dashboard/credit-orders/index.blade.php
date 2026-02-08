@extends('layouts/contentLayoutMaster')

@section('title', 'Credit Orders')
@section('main-page', 'Credit Orders')

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

<style>
    /* Responsive table accordion styles */
    @media (max-width: 768px) {

        /* Hide the last 4 columns on mobile */
        .category-list-table th:nth-child(4),
        .category-list-table th:nth-child(5),
        .category-list-table th:nth-child(6),
        .category-list-table th:nth-child(7),
        .category-list-table th:nth-child(8) {
            display: none !important;
        }

        .category-list-table tbody tr:not(.details-row) td:nth-child(4),
        .category-list-table tbody tr:not(.details-row) td:nth-child(5),
        .category-list-table tbody tr:not(.details-row) td:nth-child(6),
        .category-list-table tbody tr:not(.details-row) td:nth-child(7),
        .category-list-table tbody tr:not(.details-row) td:nth-child(8) {
            display: none !important;
        }

        /* Style for clickable rows */
        .category-list-table tbody tr:not(.details-row) {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        /* Add expand indicator to the role column */
        .category-list-table tbody tr:not(.details-row) td:nth-child(1) {
            position: relative;
            padding-left: 20px !important;
        }

        .expand-icon {
            position: absolute;
            left: 70%;
            top: 50%;
            transform: translateY(-50%);
            transition: transform 0.3s ease;
            color: #666;
            font-size: 14px;
            pointer-events: none;
        }

        .expand-icon.expanded {
            transform: translateY(-50%) rotate(180deg);
        }

        /* Details row styling */
        .details-row {
            background-color: #F9FDFC !important;
            display: none;
        }

        .details-row.show {
            display: table-row !important;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .detail-label {
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }

        .detail-value {
            color: #212529;
            font-size: 14px;
        }
    }

    /* Ensure normal behavior on desktop */
    @media (min-width: 769px) {
        .details-row {
            display: none !important;
        }

        .expand-icon {
            display: none !important;
        }
    }
</style>
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
            <div class="px-1 d-flex flex-wrap justify-content-between align-items-center gap-1">

                {{-- Search Input --}}
                <form action="" method="get" class="position-relative flex-grow-1 me-1 col-12 col-md-5 search-form">
                    <i data-feather="search" class="position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                    <input type="text" class="form-control ps-5 border rounded-3" name="search_value"
                           id="search-category-form" placeholder="Search product..." style="height: 38px;">
                    <!-- Clear button -->
                    <button type="button" id="clear-search" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
                   background: transparent; border: none; font-weight: bold;
                   color: #aaa; cursor: pointer; font-size: 18px; line-height: 1;" title="Clear filter">
                        &times;
                    </button>
                </form>

                {{-- Filter Select - 10% on md+, half width on sm --}}
                <div class="col-12 col-md-3">
                    <select name="created_at" class="form-select filter-date">
                        <option value="" disabled>Date</option>
                        <option value="desc">Newest</option>
                        <option value="asc">Oldest</option>
                    </select>
                </div>

                {{-- Add Button - 20% on md+, full width on xs --}}
                @can("categories_create")
                <div class="col-12 col-md-3">
                    <a class="btn btn-outline-primary w-100 w-md-auto" data-bs-toggle="modal"
                       data-bs-target="#categoryModal">
                        <i data-feather="plus"></i>
                        Add New Product
                    </a>
                </div>
                @endcan
            </div>


            <table class="category-list-table table">
                <thead class="table-light">
                <tr>
                    <th>
                        <input type="checkbox" id="select-all-checkbox" class="form-check-input" @disabled(!auth()->user()->hasPermissionTo('categories_delete'))>
                    </th>
                    <th>Plan Name</th>
                    <th>Plan Amount</th>
                    <th>Plan Credit Number</th>
                    <th>User Name</th>
                    <th>Added Date</th>
                    <th>Actions</th>
                </tr>
                </thead>
            </table>

        </div>


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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script>
    const categoriesDataUrl = "{{ route('credit-orders.data') }}";
    const categoriesCreateUrl = "{{ route('credit-orders.create') }}";
    const locale = "{{ app()->getLocale() }}";
</script>


{{-- Page js files --}}
<script src="{{ asset('js/scripts/pages/app-category-list.js') }}?v={{ time() }}"></script>

<script>
    // Backup accordion handler in case the main one doesn't work
    $(document).ready(function() {
        // Alternative click handler
        $(document).off('click.accordion').on('click.accordion', '.category-list-table tbody tr:not(.details-row)', function(e) {
            console.log('Accordion clicked'); // Debug log

            if ($(window).width() <= 768) {
                // Skip if clicking on interactive elements
                if ($(e.target).is('input, button, a') || $(e.target).closest('input, button, a').length) {
                    return;
                }

                const $currentRow = $(this);
                const $detailsRow = $currentRow.next('.details-row');
                const $icon = $currentRow.find('.expand-icon');

                // Toggle logic
                if ($detailsRow.hasClass('show')) {
                    // Close this one
                    $detailsRow.removeClass('show');
                    $icon.removeClass('expanded');
                } else {
                    // Close all others first
                    $('.details-row.show').removeClass('show');
                    $('.expand-icon.expanded').removeClass('expanded');

                    // Open this one
                    $detailsRow.addClass('show');
                    $icon.addClass('expanded');
                }
            }
        });
    });
</script>
@endsection
