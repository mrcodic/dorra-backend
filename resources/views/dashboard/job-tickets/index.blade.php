@extends('layouts/contentLayoutMaster')

@section('title', 'Jobs')
@section('main-page', 'Jobs')

@section('vendor-style')
{{-- Page Css files --}}
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/rowGroup.bootstrap5.min.css')) }}">
<style>
    .station-card.selected {
        border-color: #24B094 !important;
        background: #E0F4F0;
    }
</style>


<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-style')
{{-- Page Css files --}}
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">

<style>
    /* Statistics Cards */
    .card-specs {
        border: 1.5px solid #CED5D4;
        border-radius: 20px;
        width: 166px;
        flex: 1;
    }

    .card-specs p {
        color: #424746;
        font-size: 14px;
        margin: 0;
        padding: 0;
    }

    .card-specs .number {
        color: #121212;
        font-size: 20px;
        font-weight: bold;
        padding-right: 5px
    }

    .card-specs .text {
        color: #424746;
        font-size: 16px;
    }

    /* Responsive table accordion styles */
    @media (max-width: 768px) {

        /* Hide the last 4 columns on mobile */
        .job-list-table th:nth-child(4),
        .job-list-table th:nth-child(5),
        .job-list-table th:nth-child(6),
        .job-list-table th:nth-child(7),
        .job-list-table th:nth-child(8),
        .job-list-table th:nth-child(9),
        .job-list-table th:nth-child(10) {
            display: none !important;
        }

        .job-list-table tbody tr:not(.details-row) td:nth-child(4),
        .job-list-table tbody tr:not(.details-row) td:nth-child(5),
        .job-list-table tbody tr:not(.details-row) td:nth-child(6),
        .job-list-table tbody tr:not(.details-row) td:nth-child(7),
        .job-list-table tbody tr:not(.details-row) td:nth-child(8),
        .job-list-table tbody tr:not(.details-row) td:nth-child(9),
        .job-list-table tbody tr:not(.details-row) td:nth-child(10) {
            display: none !important;
        }

        /* Style for clickable rows */
        .job-list-table tbody tr:not(.details-row) {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        /* Add expand indicator to the role column */
        .job-list-table tbody tr:not(.details-row) td:nth-child(1) {
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
            margin: 0;
            padding: 0;
        }

        .card-specs .number {
            color: #121212;
            font-size: 20px;
            font-weight: bold;
            padding-right: 5px
        }

        .card-specs .text {
            color: #424746;
            font-size: 16px;
        }

        /* Responsive table accordion styles */
        @media (max-width: 768px) {

            /* Hide the last 4 columns on mobile */
            .job-list-table th:nth-child(4),
            .job-list-table th:nth-child(5),
            .job-list-table th:nth-child(6),
            .job-list-table th:nth-child(7),
            .job-list-table th:nth-child(8),
            .job-list-table th:nth-child(9),
            .job-list-table th:nth-child(10) {
                display: none !important;
            }

            .job-list-table tbody tr:not(.details-row) td:nth-child(4),
            .job-list-table tbody tr:not(.details-row) td:nth-child(5),
            .job-list-table tbody tr:not(.details-row) td:nth-child(6),
            .job-list-table tbody tr:not(.details-row) td:nth-child(7),
            .job-list-table tbody tr:not(.details-row) td:nth-child(8),
            .job-list-table tbody tr:not(.details-row) td:nth-child(9),
            .job-list-table tbody tr:not(.details-row) td:nth-child(10) {
                display: none !important;
            }

            /* Style for clickable rows */
            .job-list-table tbody tr:not(.details-row) {
                cursor: pointer;
                transition: background-color 0.2s ease;
            }

            /* Add expand indicator to the role column */
            .job-list-table tbody tr:not(.details-row) td:nth-child(1) {
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
            {{-- Statistics Cards --}}
            <div class="mb-3 d-flex flex-wrap gap-1">
                @foreach($associatedData['stations'] as $station)
                <div class="d-flex flex-column justify-content-between gap-1 p-1 card-specs station-card"
                    data-station="{{ $station->id }}">
                    <div class="d-flex align-items-center gap-1">
                        <img src="{{asset("/images/".strtolower($station->name).".svg")}}" alt="users" style="width:
                        28px">
                        <p>{{ $station->name }}</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="number">{{ $station->job_tickets_count }}</span>
                        <span class="text">Status</span>
                    </div>
                </div>
                @endforeach



            </div>
            {{-- Search Input --}}
            <div class="d-flex flex-column gap-1">
                <div class="d-flex flex-wrap gap-1">
                    <form action="" method="get" class="d-flex position-relative search-form col-12 col-md-9">
                        <i data-feather="search"
                            class="position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                        <input type="text" class="form-control ps-5 border rounded-3" name="search_value"
                            id="search-job-form" placeholder="Search item id or order number..." style="height: 38px;">
                        <!-- Clear button -->
                        <button type="button" id="clear-search" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
               background: transparent; border: none; font-weight: bold;
               color: #aaa; cursor: pointer; font-size: 18px; line-height: 1;" title="Clear filter">
                            &times;
                        </button>
                    </form>

                    {{-- Overdue (checkbox is fine as you wrote it) --}}
                    <div class=" d-flex gap-1 justify-content-evenly align-items-center col-12 col-md-2">
                        <div class="form-check m-0">
                            <input class="form-check-input" type="checkbox" id="overdue" name="overdue" value="1" {{
                                request('overdue') ? 'checked' : '' }}>
                            <label class="form-check-label" for="overdue">OverDue</label>
                        </div>

                        <div class="form-check m-0">
                            <input class="form-check-input" type="checkbox" id="pending" name="pending" value="1" {{
                                request()->boolean('pending') ? 'checked' : '' }}>
                            <label class="form-check-label" for="pending">Pending</label>
                        </div>
                    </div>


                    {{-- Date --}}
                    <div class="col-12 col-md-2">
                        <input type="date" class="form-control due_date" name="due_at" value="{{ request('due_at') }}">
                    </div>

                    {{-- Priority (ensure value is the enum value) --}}
                    <div class="col-12 col-md-3">
                        <select class="form-select filter-priority">
                            <option value="" selected disabled>Priority</option>
                            <option value="">All</option>

                            @foreach(\App\Enums\JobTicket\PriorityEnum::cases() as $priority)
                            <option value="{{ $priority->value }}" {{ request('priority')===$priority->value ?
                                'selected' :
                                '' }}>
                                {{ $priority->label() }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Station --}}
                    <div class="col-12 col-md-3">
                        <select class="form-select filter-station" data-statuses-url="{{ route('station-statuses') }}">
                            <option value="" selected>All Stations</option>
                            @foreach(\App\Models\Station::all() as $station)
                            <option value="{{ $station->id }}" {{ (string)request('station_id')===(string)$station->
                                id ?
                                'selected' : '' }}>
                                {{ $station->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="col-12 col-md-2">
                        <select class="form-select filter-status">
                            <option value="">All Statuses</option>
                        </select>

                    </div>
                    <div class="col-12 col-md-1">
                        <a href="{{ route('scan.kiosk') }}" class="btn btn-primary">
                            <i data-feather="camera"></i>
                            <span>Scan</span></a>

                    </div>
                </div>

                <table class="job-list-table table">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all-checkbox" class="form-check-input">
                            </th>
                            <th>Image</th>
                            <th>Item Id</th>
                            <th>Item Name</th>
                            <th>Item Quantity</th>
                            <th>Order Number</th>
                            <th>Priority</th>
                            <th>Current Station</th>
                            <th>Status</th>
                            <th>Due Date</th>


                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
                <div id="bulk-delete-container" class="my-2 bulk-delete-container" style="display: none;">
                    <div class="delete-container d-flex flex-wrap align-items-center justify-content-center justify-content-md-between"
                        style="z-index: 10;">
                        <p id="selected-count-text">0 Jobs are selected</p>
                        <button type="submit" data-bs-toggle="modal" data-bs-target="#deleteJobsModal"
                            class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1 delete-selected-btns">
                            <i data-feather="trash-2"></i> Delete Selected
                        </button>
                        </form>
                    </div>
                </div>
            </div>
            @include('modals.delete',[
            'id' => 'deleteInvoiceModal',
            'formId' => 'deleteInvoiceForm',
            'title' => 'Delete Invoice',
            ])
            @include('modals.delete',[
            'id' => 'deleteJobsModal',
            'formId' => 'bulk-delete-form',
            'title' => 'Delete Jobs',
            'confirmText' => 'Are you sure you want to delete this items?',
            ])
            @include("modals.job-tickets.edit-job-ticket",['stations'=>$associatedData['stations']])


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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script>
    const jobsDataUrl = "{{ route('job-tickets.data') }}";
</script>

{{-- Page js files --}}
<script src="{{ asset('js/scripts/pages/app-jobs-list.js') }}?v={{ time() }}"></script>
<script src="https://unpkg.com/feather-icons"></script>
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
                $(document).on('click.accordion', '.job-list-table tbody tr:not(.details-row)', function(e) {
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
                        $('.job-list-table tbody tr:not(.details-row)').each(function() {
                            const $row = $(this);

                            // Remove existing details and icons first
                            $row.find('.expand-icon').remove();
                            $row.next('.details-row').remove();

                            // Add expand icon to role column
                            $row.find('td:nth-child(1)').append('<span class="expand-icon"><i class="fa-solid fa-angle-down"></i></span>');

                            // Get data for details
                            const priority = $row.find('td:nth-child(4)').html() || '';
                            const currentStation = $row.find('td:nth-child(5)').html() || '';
                            const status = $row.find('td:nth-child(6)').html() || '';
                            const dueDate = $row.find('td:nth-child(7)').html() || '';
                            const orderNumber = $row.find('td:nth-child(8)').html() || '';
                            const orderItemName = $row.find('td:nth-child(9)').html() || '';
                            const actions = $row.find('td:nth-child(10)').html() || '';

                            // Create details row
                            const detailsHtml = `
                                <tr class="details-row">
                                    <td colspan="4">
                                        <div class="details-content">
                                            <div class="detail-row">
                                                <span class="detail-label">Priority:</span>
                                                <span class="detail-value">${priority}</span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">Current Station:</span>
                                                <span class="detail-value">${currentStation}</span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">Status:</span>
                                                <span class="detail-value">${status}</span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">Due Date:</span>
                                                <span class="detail-value">${dueDate}</span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">Order Number:</span>
                                                <span class="detail-value">${orderNumber}</span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">Order Item Name:</span>
                                                <span class="detail-value">${orderItemName}</span>
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
                $(document).on('draw.dt', '.job-list-table', function () {
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
                        $('#selected-count-text').text(`${count} Order${count > 1 ? 's are' : ' is'} selected`);
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

<script>
    // Backup accordion handler in case the main one doesn't work
$(document).ready(function() {
    // Alternative click handler
    $(document).off('click.accordion').on('click.accordion', '.job-list-table tbody tr:not(.details-row)', function(e) {
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
