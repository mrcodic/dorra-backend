@extends('layouts/contentLayoutMaster')

@section('title', 'SubProduct List')
@section('main-page', 'SubProducts')

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

        /* Hide the last 2 columns on mobile */
        .sub-category-list-table th:nth-child(4),
        .sub-category-list-table th:nth-child(5) {
            display: none !important;
        }

        .sub-category-list-table tbody tr:not(.details-row) td:nth-child(4),
        .sub-category-list-table tbody tr:not(.details-row) td:nth-child(5) {
            display: none !important;
        }

        /* Style for clickable rows */
        .sub-category-list-table tbody tr:not(.details-row) {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        /* Add expand indicator to the role column */
        .sub-category-list-table tbody tr:not(.details-row) td:nth-child(1) {
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
                        id="search-sub-category-form" placeholder="Search subcategory..." style="height: 38px;">
                    <button type="button" id="clear-search" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
                   background: transparent; border: none; font-weight: bold;
                   color: #aaa; cursor: pointer; font-size: 18px; line-height: 1;" title="Clear filter">
                        &times;
                    </button>
                </form>
                {{-- Filter Select - 10% on md+, half width on sm --}}
                <div class="col-12 col-md-2">
                    <select name="created_at" class="form-select filter-date">
                        <option value="" disabled>Date</option>
                        <option value="asc">Oldest</option>
                        <option value="desc">Newest</option>
                    </select>
                </div>

                {{-- Add Button - 20% on md+, full width on xs --}}
                <div class="col-12 col-md-4 text-md-end">
                    <a class="btn btn-outline-primary w-100 w-md-auto" data-bs-toggle="modal"
                        data-bs-target="#addSubCategoryModal">
                        <i data-feather="plus"></i>
                        Add New Subproduct
                    </a>
                </div>
            </div>

        </div>


        <table class="sub-category-list-table table">
            <thead class="table-light">
                <tr>
                    <th>
                        <input type="checkbox" id="select-all-checkbox">
                    </th>
                    <th>Name</th>
                    <th>NO.of Categories</th>
                    <th>Added Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
        <div id="bulk-delete-container" class="my-2 bulk-delete-container" style="display: none;">
            <div class="delete-container d-flex flex-wrap align-items-center justify-content-center justify-content-md-between"
                style="z-index: 10;">
                <p id="selected-count-text">0 Categories are selected</p>
                <button type="submit" id="delete-selected-btn" data-bs-toggle="modal"
                    data-bs-target="#deleteSubCategoriesModal"
                    class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1 delete-selected-btns open-delete-categories-modal">
                    <i data-feather="trash-2"></i> Delete Selected
                </button>
                <form style="display: none;" id="bulk-delete-form" method="POST"
                    action="{{ route('categories.bulk-delete') }}">
                    @csrf
                    <button type="submit" id="delete-selected-btn"
                        class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1 delete-selected-btns">
                        <i data-feather="trash-2"></i> Delete Selected
                    </button>
                </form>


            </div>
        </div>
        @include('modals.delete',[
        'id' => 'deleteSubCategoryModal',
        'formId' => 'deleteSubCategoryForm',
        'title' => 'Delete SubCategory',
        ])
        @include('modals.delete',[
        'id' => 'deleteSubCategoriesModal',
        'formId' => 'bulk-delete-form',
        'title' => 'Delete Subcategories',
        'confirmText' => 'Are you sure you want to delete this items?',
        ])
        @include('modals.subcategories.show-sub-category')
        @include('modals.subcategories.edit-sub-category')
        @include('modals.subcategories.add-sub-category')

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
    const subCategoriesDataUrl = "{{ route('sub-categories.data') }}";
    const locale = "{{ app()->getLocale() }}";
</script>

<script>
    $(document).ready(function () {
    setupClearInput('roleSelect', 'clearRoleFilter');

    // Select all toggle
    $('#select-all-checkbox').on('change', function () {
        $('.category-checkbox').prop('checked', this.checked);
        updateBulkDeleteVisibility();
    });

    // When individual checkbox changes
    $(document).on('change', '.category-checkbox', function () {
        if (!this.checked) {
            $('#select-all-checkbox').prop('checked', false);
        } else if ($('.category-checkbox:checked').length === $('.category-checkbox').length) {
            $('#select-all-checkbox').prop('checked', true);
        }
        updateBulkDeleteVisibility();
    });

    // Simple accordion toggle function
    function toggleAccordion($row) {
        if ($(window).width() > 768) return; // Only on mobile

        const $detailsRow = $row.next('.details-row');
        const $icon = $row.find('.expand-icon');

        // Close all other details
        $('.details-row.show').removeClass('show');
        $('.expand-icon.expanded').removeClass('expanded');

        // If this row has details and they're not currently shown
        if ($detailsRow.length && !$detailsRow.hasClass('show')) {
            $detailsRow.addClass('show');
            $icon.addClass('expanded');
        }
    }

    // Accordion click handler with event delegation
    $(document).on('click.accordion', '.sub-category-list-table tbody tr:not(.details-row)', function(e) {
        // Prevent accordion when clicking interactive elements
        if ($(e.target).is('input, button, a, .btn') ||
            $(e.target).closest('input, button, a, .btn').length > 0) {
            return;
        }

        e.stopPropagation();
        toggleAccordion($(this));
    });

    // Initialize accordion after DataTable draw
    function initAccordion() {
        if ($(window).width() <= 768) {
            $('.sub-category-list-table tbody tr:not(.details-row)').each(function() {
                const $row = $(this);

                // Remove existing details and icons first
                $row.find('.expand-icon').remove();
                $row.next('.details-row').remove();

                // Add expand icon to role column
                $row.find('td:nth-child(1)').append('<span class="expand-icon"><i class="fa-solid fa-angle-down"></i></span>');

                // Get data for details
                const addedDate = $row.find('td:nth-child(4)').html() || '';
                const actions = $row.find('td:nth-child(5)').html() || '';

                // Create details row
                const detailsHtml = `
                    <tr class="details-row">
                        <td colspan="3">
                            <div class="details-content">
                                <div class="detail-row">
                                    <span class="detail-label">Added Date:</span>
                                    <span class="detail-value">${addedDate}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Actions:</span>
                                    <span class="detail-value">${actions}</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                `;

                $row.after(detailsHtml);
            });
        } else {
            // Remove mobile elements on desktop
            $('.details-row').remove();
            $('.expand-icon').remove();
        }
    }

    // Handle window resize
    $(window).on('resize', function() {
        setTimeout(initAccordion, 100);
    });

    // On DataTable events
    $(document).on('draw.dt', '.sub-category-list-table', function () {
        $('#bulk-delete-container').hide();
        $('#select-all-checkbox').prop('checked', false);

        // Reinitialize accordion after DataTable operations
        setTimeout(initAccordion, 100);
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
            $('#selected-count-text').text(`${count} User${count > 1 ? 's are' : ' is'} selected`);
            $('#bulk-delete-container').show();
        } else {
            $('#bulk-delete-container').hide();
        }
    }

    // Initialize on page load
    setTimeout(function() {
        initAccordion();
    }, 500);
});
</script>

{{-- Page js files --}}
<script src="{{ asset('js/scripts/pages/app-sub-category-list.js') }}?v={{ time() }}"></script>

<script>
    // Backup accordion handler in case the main one doesn't work
$(document).ready(function() {
    // Alternative click handler
    $(document).off('click.accordion').on('click.accordion', '.sub-category-list-table tbody tr:not(.details-row)', function(e) {
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
