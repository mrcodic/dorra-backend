@extends('layouts/contentLayoutMaster')

@section('title', 'User View - Billing & Plans')

@section('vendor-style')
    {{-- Page Css files --}}
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/animate/animate.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/extensions/sweetalert2.min.css')) }}">
@endsection

@section('page-style')
    {{-- Page Css files --}}
    <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/extensions/ext-component-sweet-alerts.css')) }}">
@endsection

@section('content')
    <section class="app-user-view-billing">
        <div class="row">
            <!-- User Sidebar -->
            <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
                <!-- User Card -->
                <div class="card">
                    <div class="card-body">
                        <div class="user-avatar-section">
                            <div class="d-flex align-items-center flex-column">
                                <img
                                    class="img-fluid rounded mt-3 mb-2"
                                    src="{{asset('images/portrait/small/avatar-s-2.jpg')}}"
                                    height="110"
                                    width="110"
                                    alt="User avatar"
                                />
                                <div class="user-info text-center">
                                    <h4>Gertrude Barton</h4>
                                    <span class="badge bg-light-secondary">Author</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-around my-2 pt-75">
                            <div class="d-flex align-items-start me-2">
              <span class="badge bg-light-primary p-75 rounded">
                <i data-feather="check" class="font-medium-2"></i>
              </span>
                                <div class="ms-75">
                                    <h4 class="mb-0">1.23k</h4>
                                    <small>Tasks Done</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
              <span class="badge bg-light-primary p-75 rounded">
                <i data-feather="briefcase" class="font-medium-2"></i>
              </span>
                                <div class="ms-75">
                                    <h4 class="mb-0">568</h4>
                                    <small>Projects Done</small>
                                </div>
                            </div>
                        </div>
                        <h4 class="fw-bolder border-bottom pb-50 mb-1">Details</h4>
                        <div class="info-container">
                            <ul class="list-unstyled">
                                <li class="mb-75">
                                    <span class="fw-bolder me-25">Username:</span>
                                    <span>violet.dev</span>
                                </li>
                                <li class="mb-75">
                                    <span class="fw-bolder me-25">Billing Email:</span>
                                    <span>vafgot@vultukir.org</span>
                                </li>
                                <li class="mb-75">
                                    <span class="fw-bolder me-25">Status:</span>
                                    <span class="badge bg-light-success">Active</span>
                                </li>
                                <li class="mb-75">
                                    <span class="fw-bolder me-25">Role:</span>
                                    <span>Author</span>
                                </li>
                                <li class="mb-75">
                                    <span class="fw-bolder me-25">Tax ID:</span>
                                    <span>Tax-8965</span>
                                </li>
                                <li class="mb-75">
                                    <span class="fw-bolder me-25">Contact:</span>
                                    <span>+1 (609) 933-44-22</span>
                                </li>
                                <li class="mb-75">
                                    <span class="fw-bolder me-25">Language:</span>
                                    <span>English</span>
                                </li>
                                <li class="mb-75">
                                    <span class="fw-bolder me-25">Country:</span>
                                    <span>Wake Island</span>
                                </li>
                            </ul>
                            <div class="d-flex justify-content-center pt-2">
                                <a href="javascript:;" class="btn btn-primary me-1" data-bs-target="#editUser" data-bs-toggle="modal">
                                    Edit
                                </a>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- /User Card -->

            </div>
            <!--/ User Sidebar -->

            <!-- User Content -->
            <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
                <!-- User Pills -->
                <ul class="nav nav-pills mb-2">
                    <li class="nav-item">
                        <a class="nav-link" href="{{asset('app/user/view/account')}}">
                            <i data-feather="user" class="font-medium-3 me-50"></i>
                            <span class="fw-bold">Account</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{asset('app/user/view/security')}}">
                            <i data-feather="lock" class="font-medium-3 me-50"></i>
                            <span class="fw-bold">Security</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{asset('app/user/view/billing')}}">
                            <i data-feather="bookmark" class="font-medium-3 me-50"></i>
                            <span class="fw-bold">Billing & Plans</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{asset('app/user/view/notifications')}}">
                            <i data-feather="bell" class="font-medium-3 me-50"></i><span class="fw-bold">Notifications</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{asset('app/user/view/connections')}}">
                            <i data-feather="link" class="font-medium-3 me-50"></i><span class="fw-bold">Connections</span>
                        </a>
                    </li>
                </ul>
                <!--/ User Pills -->



                <!-- payment methods -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-50">Payment Methods</h4>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addNewCard">
                            <i data-feather="plus"></i>
                            <span>Add Card</span>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="added-cards">
                            <div class="cardMaster rounded border p-2 mb-1">
                                <div class="d-flex justify-content-between flex-sm-row flex-column">
                                    <div class="card-information">
                                        <img
                                            class="mb-1 img-fluid"
                                            src="{{asset('images/icons/payments/mastercard.png')}}"
                                            alt="Master Card"
                                        />
                                        <div class="d-flex align-items-center mb-50">
                                            <h6 class="mb-0">Tom McBride</h6>
                                            <span class="badge badge-light-primary ms-50">Primary</span>
                                        </div>
                                        <span class="card-number">∗∗∗∗ ∗∗∗∗ 9856</span>
                                    </div>
                                    <div class="d-flex flex-column text-start text-lg-end">
                                        <div class="d-flex order-sm-0 order-1 mt-1 mt-sm-0">
                                            <button class="btn btn-outline-primary me-75" data-bs-toggle="modal" data-bs-target="#editCard">
                                                Edit
                                            </button>
                                            <button class="btn btn-outline-secondary">Delete</button>
                                        </div>
                                        <span class="mt-2">Card expires at 12/24</span>
                                    </div>
                                </div>
                            </div>
                            <div class="cardMaster border rounded p-2 mb-1">
                                <div class="d-flex justify-content-between flex-sm-row flex-column">
                                    <div class="card-information">
                                        <img
                                            class="mb-1 img-fluid"
                                            src="{{asset('images/icons/payments/visa.png')}}"
                                            alt="Visa Card"
                                        />
                                        <h6>Mildred Wagner</h6>
                                        <span class="card-number">∗∗∗∗ ∗∗∗∗ 5896</span>
                                    </div>
                                    <div class="d-flex flex-column text-start text-lg-end">
                                        <div class="d-flex order-sm-0 order-1 mt-1 mt-sm-0">
                                            <button class="btn btn-outline-primary me-75" data-bs-toggle="modal" data-bs-target="#editCard">
                                                Edit
                                            </button>
                                            <button class="btn btn-outline-secondary">Delete</button>
                                        </div>
                                        <span class="mt-2">Card expires at 02/24</span>
                                    </div>
                                </div>
                            </div>
                            <div class="cardMaster border rounded p-2">
                                <div class="d-flex justify-content-between flex-sm-row flex-column">
                                    <div class="card-information">
                                        <img
                                            class="mb-1 img-fluid"
                                            src="{{asset('images/icons/payments/american-ex.png')}}"
                                            alt="Visa Card"
                                        />
                                        <h6>Mildred Wagner</h6>
                                        <span class="card-number">∗∗∗∗ ∗∗∗∗ 5896</span>
                                    </div>
                                    <div class="d-flex flex-column text-start text-lg-end">
                                        <div class="d-flex order-sm-0 order-1 mt-1 mt-sm-0">
                                            <button class="btn btn-outline-primary me-75" data-bs-toggle="modal" data-bs-target="#editCard">
                                                Edit
                                            </button>
                                            <button class="btn btn-outline-secondary">Delete</button>
                                        </div>
                                        <span class="mt-2">Card expires at 02/24</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- / payment methods -->

                <!-- Billing Address -->
                @foreach($user->addresses as $address)

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-50">Billing Address</h4>
                        <button
                            class="btn btn-primary btn-sm edit-address"
                            type="button"
                            data-bs-toggle="modal"
                            data-bs-target="#addNewAddressModal"
                        >
                            Edit address
                        </button>
                    </div>

                    <div class="card-body">
                            <div class="row">
                                <div class="col-xl-7 col-12">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4 fw-bolder mb-1">Address Label:</dt>
                                        <dd class="col-sm-8 mb-1">{{ $address->label }}</dd>

                                        <dt class="col-sm-4 fw-bolder mb-1">Address Line:</dt>
                                        <dd class="col-sm-8 mb-1">{{ $address->line }}</dd>

                                        <dt class="col-sm-4 fw-bolder mb-1">Country:</dt>
                                        <dd class="col-sm-8 mb-1">{{ $address->state->country->name }}</dd>

                                        <dt class="col-sm-4 fw-bolder mb-1">State</dt>
                                        <dd class="col-sm-8 mb-1">{{ $address->state->name }}</dd>

                                    </dl>
                                </div>

                            </div>
                    </div>

                </div>
                    @include('modals/modal-add-new-address',['address' => $address])

                @endforeach

                <!--/ Billing Address -->
            </div>
            <!--/ User Content -->
        </div>
    </section>

    @include('modals/modal-edit-user')
    @include('modals/modal-upgrade-plan')
    @include('modals/modal-edit-cc')
    @include('modals/modal-add-new-cc')
@endsection

@section('vendor-script')
    {{-- Vendor js files --}}
    <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/cleave/cleave.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/cleave/addons/cleave-phone.us.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/validation/jquery.validate.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/extensions/sweetalert2.all.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/extensions/polyfill.min.js')) }}"></script>
@endsection

@section('page-script')
    {{-- Page js files --}}
    <script src="{{ asset('js/scripts/pages/modal-add-new-address.js') }}?v={{ time() }}"></script>

    <script src="{{ asset(mix('js/scripts/pages/modal-edit-user.js')) }}"></script>
    <script src="{{ asset(mix('js/scripts/pages/modal-add-new-cc.js')) }}"></script>
    <script src="{{ asset(mix('js/scripts/pages/modal-edit-cc.js')) }}"></script>
    <script src="{{ asset(mix('js/scripts/pages/app-user-view-billing.js')) }}"></script>
    <script src="{{ asset(mix('js/scripts/pages/app-user-view.js')) }}"></script>
    <script !src="">

    </script>
@endsection
