@extends('layouts/contentLayoutMaster')

@section('title', 'FAQs')
@section('main-page', 'FAQs')

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
        .faq-list-table th:nth-child(3),
        .faq-list-table th:nth-child(4) {
            display: none !important;
        }

        .faq-list-table tbody tr:not(.details-row) td:nth-child(3),
        .faq-list-table tbody tr:not(.details-row) td:nth-child(4) {
            display: none !important;
        }

        /* Style for clickable rows */
        .faq-list-table tbody tr:not(.details-row) {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        /* Add expand indicator to the role column */
        .faq-list-table tbody tr:not(.details-row) td:nth-child(1) {
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
            background-color: #f8f9fa !important;
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
                <form action="" method="get" class="flex-grow-1 me-1 col-12 col-md-5 position-relative search-form">
                    <i data-feather="search" class="position-absolute top-50 translate-middle-y mx-1 text-muted"></i>
                    <input type="text" class="form-control ps-5 border rounded-3" name="search_value"
                        id="search-faq-form" placeholder="Search here" style="height: 38px;">
                    <button type="button" id="clear-search" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
                   background: transparent; border: none; font-weight: bold;
                   color: #aaa; cursor: pointer; font-size: 18px; line-height: 1;" title="Clear filter">
                        &times;
                    </button>
                </form>
                <div class="col-12 col-md-2">
                    <select name="created_at" class="form-select filter-date">
                        <option value="" selected disabled>Date</option>
                        <option value="asc">Oldest</option>
                        <option value="desc">Newest</option>
                    </select>
                </div>
                <button type="button" class="btn btn-outline-primary col-12 col-md-2" data-bs-toggle="modal"
                    data-bs-target="#addQuestionModal">
                    Add New Question
                </button>
            </div>

            <table class="faq-list-table table">
                <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" id="select-all-checkbox">
                        </th>
                        <th>Question</th>
                        <th>Added Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
            <div id="bulk-delete-container" class="my-2 bulk-delete-container" style="display: none;">
                <div class="delete-container d-flex flex-wrap align-items-center justify-content-center justify-content-md-between"
                    style="z-index: 10;">
                    <p id="selected-count-text">0 Faqs are selected</p>
                    <!-- Trigger button -->
                    <button type="button"
                        class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1"
                        data-bs-toggle="modal" data-bs-target="#deleteFaqsModal">
                        <i data-feather="trash-2"></i> Delete Selected
                    </button>

                    <!-- Hidden bulk delete form -->
                    <form style="display:none;" id="bulk-delete-form" method="POST"
                        action="{{ route('faqs.bulk-delete') }}">
                        @csrf
                    </form>

                    <!-- Bulk delete modal -->



                </div>
            </div>



        </div>
        @include('modals/questions/add-question')
        @include('modals/questions/show-question')
        @include('modals/questions/edit-question')

        @include('modals.delete', [
        'id' => 'deleteFaqsModal',
        'formId' => 'bulk-delete-form',
        'title' => 'Delete Faqs',
        'confirmText' => 'Are you sure you want to delete these items?'
        ])
        @include('modals.delete',[
        'id' => 'deleteFaqModal',
        'formId' => 'deleteFaqForm',
        'title' => 'Delete Faq',
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
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="https://unpkg.com/feather-icons"></script>
<script>
    const faqsDataUrl = "{{ route('faqs.data') }}";
        const locale = "{{ app()->getLocale() }}";

</script>
<script !src="">
    handleAjaxFormSubmit("#deleteFaqForm",{
        successMessage: "Faq deleted Successfully",
        onSuccess:function () {
            $("#deleteFaqModal").modal("hide");
            location.reload()
        }
    })
</script>

{{-- Page js files --}}
<script src="{{ asset('js/scripts/pages/app-question-list.js') }}?v={{ time() }}"></script>

<script>
    // Backup accordion handler in case the main one doesn't work
$(document).ready(function() {
    // Alternative click handler
    $(document).off('click.accordion').on('click.accordion', '.faq-list-table tbody tr:not(.details-row)', function(e) {
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