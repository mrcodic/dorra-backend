@extends('layouts/contentLayoutMaster')

@section('title', 'Campaigns')
@section('main-page', 'Campaigns')

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
        /* Responsive table accordion stylefs */
        @media (max-width: 768px) {

            /* Hide the last 4 columns on mobile */
            .user-list-table th:nth-child(4),
            .user-list-table th:nth-child(5),
            .user-list-table th:nth-child(6),
            .user-list-table th:nth-child(7),
            .user-list-table th:nth-child(8) {
                display: none !important;
            }

            .user-list-table tbody tr:not(.details-row) td:nth-child(4),
            .user-list-table tbody tr:not(.details-row) td:nth-child(5),
            .user-list-table tbody tr:not(.details-row) td:nth-child(6),
            .user-list-table tbody tr:not(.details-row) td:nth-child(7),
            .user-list-table tbody tr:not(.details-row) td:nth-child(8) {
                display: none !important;
            }

            /* Style for clickable rows */
            .user-list-table tbody tr:not(.details-row) {
                cursor: pointer;
                transition: background-color 0.2s ease;
            }

            /* Add expand indicator to the role column */
            .user-list-table tbody tr:not(.details-row) td:nth-child(1) {
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
                    <div class="col-md-4 user_status bg-succes"></div>
                </div>
            </div>
            <div class="card-datatable table-responsive pt-0">
{{--                <div class="px-1 d-flex flex-wrap justify-content-between align-items-center gap-1">--}}
{{--                    <div class="col-12 col-md-3">--}}
{{--                        <select name="created_at" class="form-select filter-date">--}}
{{--                            <option value="" disabled></option>--}}
{{--                            <option value="desc">Newest</option>--}}
{{--                            <option value="asc">Oldest</option>--}}
{{--                        </select>--}}
{{--                    </div>--}}

{{--                </div>--}}
                <table class="user-list-table table">
                    <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" id="select-all-checkbox" class="form-check-input">
                        </th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Phone</th>

                    </tr>
                    </thead>
                </table>
                <div id="bulk-delete-container" class="my-2 bulk-delete-container" style="display: none;">
                    <div
                        class="delete-container d-flex flex-wrap align-items-center justify-content-center justify-content-md-between"
                        style="z-index: 10;">
                        <p id="selected-count-text">0 Users are selected</p>
                        <button type="button" id="open-sms-modal-btn" data-bs-toggle="modal"
                                data-bs-target="#smsModal"
                                class="btn btn-outline-primary d-flex justify-content-center align-items-center gap-1 delete-selected-btns">
                            <i data-feather="message-square"></i> Send SMS
                        </button>



                    </div>
                </div>

            </div>
        </div>
        {{-- SMS Modal --}}
        <div class="modal fade" id="smsModal" tabindex="-1" aria-labelledby="smsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="send-sms-form" method="POST" action="{{ route("users.campaigns.send.sms") }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="smsModalLabel">Send SMS to selected users</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

                        </div>

                        <div class="modal-body">
                            {{-- Hidden field for selected user IDs (will be filled from JS) --}}
                            <input type="hidden" name="numbers[]" id="sms-user-ids">

                            <div class="mb-1">
                                <label for="sms-message" class="form-label">Message</label>
                                <textarea class="form-control" id="sms-message" name="message" rows="4"
                                          placeholder="Write your SMS message here..." required></textarea>
                            </div>

                            <small class="text-muted d-block">
                                The message will be sent to all selected users.
                            </small>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Send SMS
                            </button>
                        </div>
                    </form>
                </div>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.repeater/1.2.1/jquery.repeater.min.js"></script>

    <script !src="">
        $(document).on('click', '#open-sms-modal-btn', function () {
            const selected = $('.category-checkbox:checked').map(function () {
                return this.value;
            }).get();

            if (selected.length === 0) {
                Toastify({
                    text: "Please select at least one user.",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                }).showToast();
                return false;
            }


            $('#send-sms-form input[name="numbers[]"]').remove();


            selected.forEach(number => {
                $('#send-sms-form').append(
                    `<input type="hidden" name="numbers[]" value="${number}">`
                );
            });
        });

        handleAjaxFormSubmit("#send-sms-form",{
            successMessage :"SMS send successfully",
            onSuccess:function () {
                location.reload();
            }
        })
    </script>
    <script>
        const usersDataUrl = "{{ route('users.campaigns.data') }}";

        $('#address-repeater').repeater({
            initEmpty: false,
            defaultValues: {
                'label': '',
                'line': '',
                'country_id': '',
                'state_id': ''
            },
            show: function () {
                $(this).slideDown();
                feather.replace();
            },
            hide: function (deleteElement) {
                $(this).slideUp(deleteElement);
            }
        });
        feather.replace();
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
            $(document).on('click.accordion', '.user-list-table tbody tr:not(.details-row)', function (e) {
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
                    $('.user-list-table tbody tr:not(.details-row)').each(function () {
                        const $row = $(this);

                        // Remove existing details and icons first
                        $row.find('.expand-icon').remove();
                        $row.next('.details-row').remove();

                        // Add expand icon to role column
                        $row.find('td:nth-child(1)').append('<span class="expand-icon"><i class="fa-solid fa-angle-down"></i></span>');

                        // Get data for details
                        const email = $row.find('td:nth-child(4)').html() || '';
                        const status = $row.find('td:nth-child(5)').html() || '';
                        const joinedDate = $row.find('td:nth-child(6)').html() || '';
                        const ordersCount = $row.find('td:nth-child(7)').html() || '';
                        const actions = $row.find('td:nth-child(8)').html() || '';

                        // Create details row
                        const detailsHtml = `
                    <tr class="details-row">
                        <td colspan="4">
                            <div class="details-content">
                                <div class="detail-row">
                                    <span class="detail-label">Email:</span>
                                    <span class="detail-value">${email}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Status:</span>
                                    <span class="detail-value">${status}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Joined Date:</span>
                                    <span class="detail-value">${joinedDate}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Orders Count:</span>
                                    <span class="detail-value">${ordersCount}</span>
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
            $(window).on('resize', function () {
                setTimeout(initAccordion, 100);
            });

            // On DataTable events
            $(document).on('draw.dt', '.user-list-table', function () {
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
            setTimeout(function () {
                initAccordion();
            }, 500);
        });
    </script>

    <script src="{{ asset('js/scripts/pages/app-campaign-list.js') }}?v={{ time() }}"></script>

    <script>
        // Backup accordion handler in case the main one doesn't work
        $(document).ready(function () {
            // Alternative click handler
            $(document).off('click.accordion').on('click.accordion', '.user-list-table tbody tr:not(.details-row)', function (e) {
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
