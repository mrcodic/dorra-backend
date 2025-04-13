@extends('layouts/contentLayoutMaster')

@section('title', 'User List')

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
        <div class="row">
            <div class="col-lg-3 col-sm-6">
                <div class="card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="fw-bolder mb-75">21,459</h3>
                            <span>Total Users</span>
                        </div>
                        <div class="avatar bg-light-primary p-50">
            <span class="avatar-content">
              <i data-feather="user" class="font-medium-4"></i>
            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="fw-bolder mb-75">4,567</h3>
                            <span>Paid Users</span>
                        </div>
                        <div class="avatar bg-light-danger p-50">
            <span class="avatar-content">
              <i data-feather="user-plus" class="font-medium-4"></i>
            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="fw-bolder mb-75">19,860</h3>
                            <span>Active Users</span>
                        </div>
                        <div class="avatar bg-light-success p-50">
            <span class="avatar-content">
              <i data-feather="user-check" class="font-medium-4"></i>
            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="fw-bolder mb-75">237</h3>
                            <span>Pending Users</span>
                        </div>
                        <div class="avatar bg-light-warning p-50">
            <span class="avatar-content">
              <i data-feather="user-x" class="font-medium-4"></i>
            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- list and filter start -->
        <div class="card">
            <div class="card-body border-bottom">
                <h4 class="card-title">Search & Filter</h4>
                <div class="row">
                    <div class="col-md-4 user_role"></div>
                    <div class="col-md-4 user_plan"></div>
                    <div class="col-md-4 user_status"></div>
                </div>
            </div>
            <div class="card-datatable table-responsive pt-0">
                <table class="user-list-table table">
                    <thead class="table-light">
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Joined Date</th>
                        <th>Orders Count</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                </table>
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
                                    name="first_name"
                                />
                            </div>
                            <div class="mb-1">
                                <label class="form-label" for="last_name">Last Name</label>
                                <input
                                    type="text"
                                    class="form-control dt-full-name"
                                    id="last_name"
                                    placeholder="Doe"
                                    name="last_name"
                                />
                            </div>
                            <div class="mb-1">
                                <label class="form-label" for="email">Email</label>
                                <input
                                    type="text"
                                    id="email"
                                    class="form-control dt-email"
                                    placeholder="john.doe@example.com"
                                    name="email"
                                />
                            </div>
                            <div class="mb-1">
                                <label class="form-label" for="phone_number">Phone Number</label>
                                <div class="input-group">
                                    <!-- Phone Code Select -->
                                    <select class="form-select" id="phone-code" name="country_code_id">
                                        @foreach($associatedData['country_codes'] as $countryCode)
                                            <option value="{{ $countryCode->id }}">{{ $countryCode->phone_code }} ({{ $countryCode->iso_code }})</option>
                                        @endforeach

                                        <!-- Add more countries as needed -->
                                    </select>

                                    <!-- Phone Number Input -->
                                    <input
                                        type="text"
                                        id="phone_number"
                                        class="form-control dt-contact"
                                        placeholder="(609) 933-44-22"
                                        name="phone_number"
                                    />
                                    <input type="hidden" name="full_phone_number"  id="full_phone_number" />
                                </div>
                            </div>
                            <div class="mb-1">
                                <label class="form-label" for="image">Image</label>
                                <input
                                    type="file"
                                    id="image"
                                    class="form-control"
                                    name="image"
                                />
                            </div>

                            <div class="mb-1">
                                <label class="form-label" for="password">Password</label>
                                <input
                                    type="password"
                                    id="password"
                                    class="form-control dt-contact"
                                    placeholder="**********"
                                    name="password"
                                />
                            </div>
                            <div class="mb-1">
                                <label class="form-label" for="password_confirmation">Confirm Password</label>
                                <input
                                    type="password"
                                    id="password_confirmation"
                                    class="form-control dt-contact"
                                    placeholder="**********"
                                    name="password_confirmation"
                                />
                            </div>
                            <div class="mb-1 form-check form-switch">
                                <label class="form-label form-check-label" for="status">Account Status</label>
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    id="status"
                                    name="status"
                                    checked
                                />

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
    <script src="{{ asset('js/scripts/pages/app-user-list.js') }}"></script>



@endsection
