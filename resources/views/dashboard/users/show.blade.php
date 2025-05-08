@extends('layouts/contentLayoutMaster')

@section('title', 'User View - Account')
@section('main-page', 'Users')
@section('sub-page', 'Show User')

@section('vendor-style')
    {{-- Page Css files --}}
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/animate/animate.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/extensions/sweetalert2.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/rowGroup.bootstrap5.min.css')) }}">
@endsection

@section('page-style')
    {{-- Page Css files --}}
    <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/extensions/ext-component-sweet-alerts.css')) }}">
@endsection

@section('content')
    <section class="app-user-view-account">
        <div class="row">
            <!-- User Sidebar -->
            <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
                <!-- User Card -->
                <div class="card">
                    <div class="card-body">
                        <div class="user-avatar-section">
                            <div class="d-flex align-items-center flex-column">
                                <img
                                    class="img-fluid rounded-circle mt-3 mb-2"
                                    src="{{$model->image?->getUrl() ?? asset('images/portrait/small/avatar-s-2.jpg')}}"
                                    height="110"
                                    width="110"
                                    alt="User avatar"/>
                                <div class="user-info text-center">
                                    <h4>{{ $model->name }}</h4>
                                    <span class=" text-secondary">userid123</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-1 my-1">
                            <div class="d-flex align-items-center gap-1">

                                <i data-feather="calendar" class="font-medium-2"></i>
                                <h4 class="mb-0">Joined 13 Oct 2024</h4>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <i data-feather="briefcase" class="font-medium-2"></i>
                                <h4 class="mb-0">Basic Plan</h4>
                            </div>
                        </div>
                        <div class="w-100 d-flex flex-column gap-1">
                            <div class="fw-semibold disabled-field w-100 p-1 text-center">Last Login:13 Oct 2024</div>
                            <div
                                class="w-100 rounded-3 border p-1 d-flex justify-content-center align-items-center gap-1">
                                <i data-feather="at-sign" class="font-medium-2"></i><span>user1@gmail.com</span></div>
                            <div
                                class="w-100 rounded-3 border p-1 d-flex justify-content-center align-items-center gap-1">
                                <i data-feather="smartphone" class="font-medium-2"></i><span>+20 123 456 7895</span>
                            </div>
                        </div>

                        <div class="info-container">
                            <h2 class="text-black my-1">Teams</h2>
                            <div class="card border rounded p-1 my-2">
                                <div class="d-flex justify-content-between align-items-start">


                                    <!-- left: Icon and Info -->
                                    <div class="d-flex gap-2 align-items-center">
                                        <div class="">
                                            <i data-feather="users" class="text-primary"></i> <!-- User icon -->
                                        </div>
                                        <div class="text-center flex-grow-1">

                                            <h5>John Doe’s Team</h5>
                                            <div class="d-flex align-items-center justify-content-center">
                                                <i data-feather="calendar"> </i> Joined 13 Oct 2024
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Right: Actions Dropdown -->
                                    <div class="dropdown">
                                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown">
                                            <i data-feather="more-horizontal" class=""></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="#">Show all team members</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#">Remove from team</a>
                                            </li>
                                        </ul>
                                    </div>

                                </div>
                            </div>


                            <div class="d-flex flex-column gap-1">
                                <a href="javascript:;" class="btn btn-primary me-1 w-100" data-bs-target="#editUser"
                                   data-bs-toggle="modal">
                                    Edit User
                                </a>
                                <button class="btn btn-outline-danger me-1 w-100">
                                    Delete User
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /User Card -->

            </div>
            <!--/ User Sidebar -->

            <!-- User Content -->
            <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs border-bottom-0">
                            <li class="nav-item">
                                <a class="nav-link active custom-tab" data-bs-toggle="tab" href="#tab1">Orders</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link custom-tab" data-bs-toggle="tab" href="#tab2">Reviews</a>
                            </li>
                        </ul>
                        <div class="tab-content mt-3">
                            <!-- tab 1 content -->
                            <div class="tab-pane fade show active" id="tab1">
                                <!-- Pills -->
                                <ul class="nav nav-pills mb-3" id="order-status-pills">
                                    <li class="nav-item">
                                        <button class="tab-button btn active text-white" data-status="all">All</button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="tab-button btn" data-status="placed">Placed</button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="tab-button btn" data-status="canceled">Canceled</button>
                                    </li>
                                </ul>

                                <!-- Order Card -->
                                <div class="card border rounded-3 p-1 mb-2">
                                    <!-- Order Header -->
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 fs-16"><span class="fs-5">Order Number:</span> #12345</h6>
                                        <span class=" fs-16 rounded-pill px-1 py-50"
                                              style="background-color: #FCF8FC;color:#4E2775">Cancelled</span>
                                    </div>

                                    <!-- Total Price -->
                                    <p class="mb-2 fw-bold fs-16"><span class="fs-5">Total Price:</span> $150.00</p>

                                    <!-- Order Items -->
                                    <div class="mb-2">
                                        <h3 class="fs-16 text-black">Items</h3>
                                        <!-- Item 1 -->
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="d-flex align-items-start gap-2">
                                                <img
                                                    src="{{$model->image?->getUrl() ?? asset('images/portrait/small/avatar-s-2.jpg')}}"
                                                    class="rounded" alt="Item" style="max-width: 55px;"/>
                                                <div>
                                                    <div class="text-black fss-16">Car Wash</div>
                                                    <small class="fs-5">Qty: 2</small>
                                                </div>
                                            </div>
                                            <div class="fw-bold text-black">$50.00</div>
                                        </div>

                                        <!-- Item 2 -->
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="d-flex align-items-start gap-2">
                                                <img
                                                    src="{{$model->image?->getUrl() ?? asset('images/portrait/small/avatar-s-2.jpg')}}"
                                                    class="rounded" alt="Item" style="max-width: 55px;"/>
                                                <div>
                                                    <div class="text-black fss-16">Car Wash</div>
                                                    <small class="fs-5">Qty: 2</small>
                                                </div>
                                            </div>
                                            <div class="fw-bold text-black">$50.00</div>
                                        </div>
                                    </div>

                                    <!-- Show Order Button -->
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-primary mb-1">Show Order</button>
                                    </div>


                                    <!-- Divider -->
                                    <hr/>
                                </div>
                            </div>
                            <!-- tab 2 content -->
                            <div class="tab-pane fade" id="tab2"> <!-- Total Reviews Section -->
                                <div class="">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-small">Total Reviews:</span>
                                        <span class="label-text">421 Reviews</span>
                                    </div>

                                    <!-- Single Review -->
                                    <div class="">
                                        <div class="d-flex align-items-center gap-1 mb-2">
                                            <img src="{{ asset('images/banner/banner-1.jpg') }}" alt="Avatar"
                                                 class="rounded-circle" width="50" height="50">
                                            <div>
                                                <div class="fw-bold text-dark fs-4">John Doe</div>
                                                <div class="text-small">2024-04-22</div>
                                            </div>
                                        </div>
                                        <div class="mb-2 label-text">
                                            This product is really great, highly recommend!
                                        </div>
                                        <div class="mb-2">
                                            <img src="{{ asset('images/banner/banner-1.jpg') }}" alt="Review Image"
                                                 class="img-fluid rounded">
                                        </div>
                                        <div class="mb-2 d-flex align-items-center gap-2">
                                            <div class="rating-stars text-warning" data-rating="4.1"></div>
                                            <span class="fs-6">Placed 27/09/2024</span>
                                        </div>

                                        <div class="d-flex gap-2 justify-content-end w-100">
                                            <button class="btn btn-outline-danger"><i data-feather="trash-2"></i> Delete
                                            </button>
                                            <button class="btn btn-primary" data-bs-toggle="modal"
                                                    data-bs-target="#modals-slide-in">
                                                Reply
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Divider -->
                                    <hr class="my-2">

                                    <!-- Comment Reply -->
                                    <div class="">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <div class="d-flex align-items-center gap-1 ">
                                                <img src="{{ asset('images/logo-reply.png') }}" alt="Avatar"
                                                     class="rounded-circle" width="48" height="48">

                                                <div class="fw-bold text-primary">Reply from Dorra Team</div>


                                            </div>
                                            <div class="text-small">2024-04-23</div>
                                        </div>

                                        <div class="mb-2 label-text mx-5">
                                            Thank you for your kind words! We're happy you loved the product.
                                        </div>
                                        <div class="d-flex gap-2 justify-content-end">
                                            <button class="btn btn-outline-danger"><i data-feather="trash-2"></i> Delete
                                                Comment
                                            </button>
                                            <button class="btn btn-outline-secondary">Delete Reply</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--/ User Content -->
            </div>
    </section>

    @include('modals.users.edit-user',['user'=>$model , 'countryCodes' => $associatedData['country_codes']])
@endsection

@section('vendor-script')
    {{-- Vendor js files --}}
    <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/cleave/cleave.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/cleave/addons/cleave-phone.us.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/validation/jquery.validate.min.js')) }}"></script>
    {{-- data table --}}
    <script src="{{ asset(mix('vendors/js/extensions/moment.min.js')) }}"></script>
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
    <script src="{{ asset(mix('vendors/js/extensions/sweetalert2.all.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/extensions/polyfill.min.js')) }}"></script>
@endsection

@section('page-script')

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    {{-- Page js files --}}
    <script src="{{ asset('js/scripts/pages/modal-edit-user.js') }}?v={{ time() }}"></script>
    <script src="{{ asset(mix('js/scripts/pages/app-user-view-account.js')) }}"></script>
    <script src="{{ asset(mix('js/scripts/pages/app-user-view.js')) }}"></script>
@endsection
