@extends('layouts/contentLayoutMaster')

@section('title', 'User List')
@section('main-page', 'Users')

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
            <div class="px-1 d-flex flex-wrap justify-content-between align-items-center gap-1">
                <form action="" method="get" class="position-relative flex-grow-1 me-1 col-12 col-md-5">
                    <i data-feather="search" class="position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                    <input type="text" class="form-control ps-5 border rounded-3" name="search_value"
                        id="search-user-form" placeholder="Search user..." style="height: 38px;">
                </form>

                <div class="col-12 col-md-3">
                    <select name="created_at" class="form-select filter-date">
                        <option value="" disabled>Date</option>
                        <option value="desc">Newest</option>
                        <option value="asc">Oldest</option>
                    </select>
                </div>

                <div class="col-12 col-md-3 text-md-end">

                    <a class="btn btn-outline-primary w-100" href="{{ route('users.create') }}"> <i
                            data-feather="plus"></i>Add New User</a>
                </div>
            </div>
            <table class="user-list-table table">
                <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" id="select-all-checkbox" class="form-check-input">
                        </th>
                        <th>Name</th>
                        <th>IMAGE</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Joined Date</th>
                        <th>Orders Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
            <div id="bulk-delete-container" class="my-2 bulk-delete-container" style="display: none;">
                <div class="delete-container">
                    <p id="selected-count-text">0 Users are selected</p>
                    <button type="submit" id="delete-selected-btn" data-bs-toggle="modal"
                        data-bs-target="#deleteUsersModal"
                        class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1 delete-selected-btns open-delete-users-modal">
                        <i data-feather="trash-2"></i> Delete Selected
                    </button>
                    <form style="display: none;" id="bulk-delete-form" method="POST"
                        action="{{ route('users.bulk-delete') }}">
                        @csrf
                        <button type="submit" id="delete-selected-btn"
                            class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1 delete-selected-btns">
                            <i data-feather="trash-2"></i> Delete Selected
                        </button>
                    </form>


                </div>
            </div>

        </div>
        <!-- Modal to add new user starts-->
        <div class="modal modal-slide-in new-user-modal fade" id="modals-slide-in">
            <div class="modal-dialog">
                <form class="add-new-user modal-content pt-0" method="post" action="{{ route('users.create') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                    <div class="modal-header mb-1">
                        <h5 class="modal-title" id="exampleModalLabel">Add User</h5>
                    </div>
                    <div class="modal-body flex-grow-1">
                        <div class="mb-1">
                            <label class="form-label" for="first_name">First Name</label>
                            <input type="text" class="form-control dt-full-name" id="first_name" placeholder="John"
                                name="first_name" />
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="last_name">Last Name</label>
                            <input type="text" class="form-control dt-full-name" id="last_name" placeholder="Doe"
                                name="last_name" />
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="email">Email</label>
                            <input type="text" id="email" class="form-control dt-email"
                                placeholder="john.doe@example.com" name="email" />
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="phone_number">Phone Number</label>
                            <div class="input-group">
                                <!-- Phone Code Select -->
                                <select class="form-select" id="phone-code" name="country_code_id">
                                    @foreach($associatedData['country_codes'] as $countryCode)
                                    <option value="{{ $countryCode->id }}"
                                        data-phone-code="{{ $countryCode->phone_code }}">{{ $countryCode->phone_code }}
                                        ({{ $countryCode->iso_code }})
                                    </option>
                                    @endforeach

                                    <!-- Add more countries as needed -->
                                </select>

                                <!-- Phone Number Input -->
                                <input type="text" id="phone_number" class="form-control dt-contact"
                                    placeholder="(609) 933-44-22" name="phone_number" />
                                <input type="hidden" name="full_phone_number" id="full_phone_number" />
                            </div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="image">Image</label>
                            <input type="file" id="image" name="image" class="form-control" />
                        </div>

                        <div class="mb-1">
                            <label class="form-label" for="password">Password</label>
                            <input type="password" id="password" class="form-control dt-contact"
                                placeholder="**********" name="password" />
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="password_confirmation">Confirm Password</label>
                            <input type="password" id="password_confirmation" class="form-control dt-contact"
                                placeholder="**********" name="password_confirmation" />
                        </div>
                        <div class="mb-1 form-check form-switch">
                            <label class="form-label form-check-label" for="status">Account Status</label>
                            <input type="checkbox" class="form-check-input" id="status" name="status" checked />

                        </div>

                        <div id="address-repeater" class="mb-1">
                            <label class="form-label">Addresses</label>
                            <div data-repeater-list="addresses">
                                <div data-repeater-item class="row g-2 mb-4 align-items-end border p-2 rounded">
                                    <div class="col-md-10">
                                        <!-- Add margin-bottom to the inputs for spacing -->
                                        <input type="text" name="label" id="addresses.*.label" class="form-control mb-1"
                                            placeholder="Enter Address Label" />
                                        <input type="text" name="line" id="addresses.*.line" class="form-control mb-1"
                                            placeholder="Enter Address Line" />

                                        <!-- Country select with margin-bottom -->
                                        <select class="form-control mb-1 country-select">
                                            <option value="">Select Country</option>
                                            @foreach($associatedData['countries'] as $country)
                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                        <div id="state-url" data-url="{{ route('states') }}"></div>

                                        <!-- State select with margin-bottom -->
                                        <select name="state_id" id="addresses.*.state_id"
                                            class="form-control mb-1 state-select">
                                            <option value="">Select State</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" data-repeater-delete class="btn btn-outline-danger">
                                            <i data-feather="trash-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" data-repeater-create class="btn btn-outline-primary">
                                <i class="fas fa-plus"></i> Add Address
                            </button>
                        </div>


                        <button type="submit" class="btn btn-primary me-1 mt-2 data-submit">Submit</button>
                        <button type="reset" class="btn btn-outline-secondary mt-2" data-bs-dismiss="modal">Cancel
                        </button>
                    </div>

                </form>
            </div>
        </div>
        <!-- Modal to add new user Ends-->
    </div>
    @include('modals.delete',[
    'id' => 'deleteUserModal',
    'formId' => 'deleteUserForm',
    'title' => 'Delete User',
    ])
    @include('modals.delete',[
    'id' => 'deleteUsersModal',
    'formId' => 'bulk-delete-form',
    'title' => 'Delete Users',
    'confirmText' => 'Are you sure you want to delete this items?',
    ])
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
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.repeater/1.2.1/jquery.repeater.min.js"></script>


<script>
    const usersDataUrl = "{{ route('users.data') }}";

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
    $(document).on('click.accordion', '.user-list-table tbody tr:not(.details-row)', function(e) {
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
            $('.user-list-table tbody tr:not(.details-row)').each(function() {
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
    $(window).on('resize', function() {
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
    setTimeout(function() {
        initAccordion();
    }, 500);
});
</script>

<script src="{{ asset('js/scripts/pages/app-user-list.js') }}?v={{ time() }}"></script>

<script>
    // Backup accordion handler in case the main one doesn't work
$(document).ready(function() {
    // Alternative click handler
    $(document).off('click.accordion').on('click.accordion', '.user-list-table tbody tr:not(.details-row)', function(e) {
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