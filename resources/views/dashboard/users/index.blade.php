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
            <div class="row gx-2 gy-2 align-items-center px-1">
                <div class="col-12 col-md-6">
                    <form action="" method="get" class="position-relative">
                        <i data-feather="search" class="position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                        <input
                            type="text"
                            class="form-control ps-5 border rounded-3"
                            name="search_value"
                            id="search-user-form"
                            placeholder="Search user..."
                            style="height: 38px;">
                    </form>
                </div>

                <div class="col-3">
                    <select name="created_at" class="form-select filter-date">
                        <option value="">Date</option>
                        <option value="asc">asc</option>
                        <option value="desc">desc</option>
                    </select>
                </div>

                <div class="col-3 text-md-end">
                   
                    <a class="btn btn-outline-primary w-100" href="{{ route("users.create") }}"> <i data-feather="plus"></i>Add New User</a>
                </div>
            </div>
            <table class="user-list-table table">
                <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" id="select-all-checkbox" class="form-check-input">
                        </th>
                        <th>Name</th>
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
                    <p id="selected-count-text">0 Products are selected</p>
                    <button id="delete-selected-btn" class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1">
                        <i data-feather="trash-2"></i> Delete Selected
                    </button>
                </div>
            </div>
        </div>
        <!-- Modal to add new user starts-->
        <div class="modal modal-slide-in new-user-modal fade" id="modals-slide-in">
            <div class="modal-dialog">
                <form class="add-new-user modal-content pt-0" method="post" action="{{ route("users.store") }}" enctype="multipart/form-data">
                    @csrf
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                    <div class="modal-header mb-1">
                        <h5 class="modal-title" id="exampleModalLabel">Add User</h5>
                    </div>
                    <div class="modal-body flex-grow-1">
                        <div class="mb-1">
                            <label class="form-label" for="first_name">First Name</label>
                            <input
                                type="text"
                                class="form-control dt-full-name"
                                id="first_name"
                                placeholder="John"
                                name="first_name" />
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="last_name">Last Name</label>
                            <input
                                type="text"
                                class="form-control dt-full-name"
                                id="last_name"
                                placeholder="Doe"
                                name="last_name" />
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="email">Email</label>
                            <input
                                type="text"
                                id="email"
                                class="form-control dt-email"
                                placeholder="john.doe@example.com"
                                name="email" />
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="phone_number">Phone Number</label>
                            <div class="input-group">
                                <!-- Phone Code Select -->
                                <select class="form-select" id="phone-code" name="country_code_id">
                                    @foreach($associatedData['country_codes'] as $countryCode)
                                    <option value="{{ $countryCode->id }}" data-phone-code="{{ $countryCode->phone_code }}">{{ $countryCode->phone_code }} ({{ $countryCode->iso_code }})</option>
                                    @endforeach

                                    <!-- Add more countries as needed -->
                                </select>

                                <!-- Phone Number Input -->
                                <input
                                    type="text"
                                    id="phone_number"
                                    class="form-control dt-contact"
                                    placeholder="(609) 933-44-22"
                                    name="phone_number" />
                                <input type="hidden" name="full_phone_number" id="full_phone_number" />
                            </div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="image">Image</label>
                            <input
                                type="file"
                                id="image"
                                name="image"
                                class="form-control" />
                        </div>

                        <div class="mb-1">
                            <label class="form-label" for="password">Password</label>
                            <input
                                type="password"
                                id="password"
                                class="form-control dt-contact"
                                placeholder="**********"
                                name="password" />
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="password_confirmation">Confirm Password</label>
                            <input
                                type="password"
                                id="password_confirmation"
                                class="form-control dt-contact"
                                placeholder="**********"
                                name="password_confirmation" />
                        </div>
                        <div class="mb-1 form-check form-switch">
                            <label class="form-label form-check-label" for="status">Account Status</label>
                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="status"
                                name="status"
                                checked />

                        </div>

                        <div id="address-repeater" class="mb-1">
                            <label class="form-label">Addresses</label>
                            <div data-repeater-list="addresses">
                                <div data-repeater-item class="row g-2 mb-4 align-items-end border p-2 rounded">
                                    <div class="col-md-10">
                                        <!-- Add margin-bottom to the inputs for spacing -->
                                        <input type="text" name="label" id="addresses.*.label" class="form-control mb-1" placeholder="Enter Address Label" />
                                        <input type="text" name="line" id="addresses.*.line" class="form-control mb-1" placeholder="Enter Address Line" />

                                        <!-- Country select with margin-bottom -->
                                        <select class="form-control mb-1 country-select">
                                            <option value="">Select Country</option>
                                            @foreach($associatedData['countries'] as $country)
                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                        <div id="state-url" data-url="{{ route('states') }}"></div>

                                        <!-- State select with margin-bottom -->
                                        <select name="state_id" id="addresses.*.state_id" class="form-control mb-1 state-select">
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
                        <button type="reset" class="btn btn-outline-secondary mt-2" data-bs-dismiss="modal">Cancel</button>
                    </div>

                </form>
            </div>
        </div>
        <!-- Modal to add new user Ends-->
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
        show: function() {
            $(this).slideDown();
            feather.replace();
        },
        hide: function(deleteElement) {
            $(this).slideUp(deleteElement);
        }
    });
    feather.replace();
</script>
<script src="{{ asset('js/scripts/pages/app-user-list.js') }}?v={{ time() }}"></script>
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



@endsection
