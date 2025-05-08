@extends('layouts/contentLayoutMaster')

@section('title', 'Orders')
@section('main-page', 'Orders')

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
    <div class="container card p-1">

        <!-- Newest Orders -->
        <div class=" mb-2">
            <div class=" d-flex  align-items-center collapsed-toggle" data-bs-toggle="collapse"
                 data-bs-target="#newestOrders" aria-expanded="true" style="cursor: pointer;">
                <i data-feather="chevron-up" class="toggle-icon" data-target="#newestOrders"></i>
                <h4 class="mb-0 text-black">Newest Orders</h4>
            </div>
            <div id="newestOrders" class="collapse show">
                <div class="row m-1">
                    <div class=" col-md-6  shadow rounded  p-1">
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="d-flex justify-content-start align-items-center">
                                <!-- Images section -->
                                <div class="d-flex flex-column align-items-start me-1">
                                    <div class="d-flex">
                                        <img src="{{asset('images/portrait/small/avatar-s-2.jpg') }}"
                                             class="order-img me-1 mb-1" alt="Product 1">
                                        <img src="{{ asset('images/portrait/small/avatar-s-2.jpg') }}"
                                             class="order-img me-1 mb-1" alt="Product 2">
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('images/portrait/small/avatar-s-2.jpg') }}"
                                             class="order-img me-1" alt="Product 3">
                                        <div class="more-images-box">+2</div>
                                    </div>
                                </div>

                                <!-- Order details -->
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1">Order #1234</h6>
                                    <p class="mb-1 text-muted">Items: Shampoo, Conditioner, Towel</p>
                                    <p class="mb-1 fw-semibold">Total: $45.00</p>
                                    <p class="text-muted small">Placed on: 2025-05-03</p>
                                </div>
                            </div>

                            <!-- Status -->

                            <div class="d-flex align-items-center status-pill justify-content-center">
                                <div class="status-icon me-1">
                                    <i data-feather="box"></i> <!-- Change icon based on status -->
                                </div>
                                <span class="status-text">Placed</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Delivered Orders -->
        <div class=" mb-2">
            <div class=" d-flex align-items-center collapsed-toggle" data-bs-toggle="collapse"
                 data-bs-target="#deliveredOrders" aria-expanded="false" style="cursor: pointer;">
                <i data-feather="chevron-down" class="toggle-icon" data-target="#deliveredOrders"></i>
                <h4 class="mb-0 text-black"> Delivered Orders</h4>
            </div>
            <div id="deliveredOrders" class="collapse">
                <div class="row m-1">
                    <div class=" col-md-6  shadow rounded  p-1">
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="d-flex justify-content-start align-items-center">
                                <!-- Images section -->
                                <div class="d-flex flex-column align-items-start me-1">
                                    <div class="d-flex">
                                        <img src="{{asset('images/portrait/small/avatar-s-2.jpg') }}"
                                             class="order-img me-1 mb-1" alt="Product 1">
                                        <img src="{{ asset('images/portrait/small/avatar-s-2.jpg') }}"
                                             class="order-img me-1 mb-1" alt="Product 2">
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('images/portrait/small/avatar-s-2.jpg') }}"
                                             class="order-img me-1" alt="Product 3">
                                        <div class="more-images-box">+2</div>
                                    </div>
                                </div>

                                <!-- Order details -->
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1">Order #1234</h6>
                                    <p class="mb-1 text-muted">Items: Shampoo, Conditioner, Towel</p>
                                    <p class="mb-1 fw-semibold">Total: $45.00</p>
                                    <p class="text-muted small">Placed on: 2025-05-03</p>
                                </div>
                            </div>

                            <!-- Status -->

                            <div class="d-flex align-items-center status-pill justify-content-center">
                                <div class="status-icon me-1">
                                    <i data-feather="truck"></i> <!-- Change icon based on status -->
                                </div>
                                <span class="status-text">Delivered</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>


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
                    <table class="order-list-table table">
                        <thead class="table-light">
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all-checkbox">
                            </th>
                            <th>Order Number</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Price</th>
                            <th>Order Status</th>
                            <th>Added Date</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                    </table>
                    <div id="bulk-delete-container" class="my-2 bulk-delete-container" style="display: none;">
                        <div class="delete-container">
                            <p id="selected-count-text">0 Products are selected</p>
                            <button id="delete-selected-btn"
                                    class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1">
                                <i data-feather="trash-2"></i> Delete Selected
                            </button>
                        </div>
                    </div>


                </div>
                @include('modals.categories.show-category')
                @include('modals.categories.edit-category')
                @include('modals.categories.add-category')

            </div>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        const ordersDataUrl = "{{ route('products.data') }}";
        const ordersCreateUrl = "{{ route('orders.create') }}";
    </script>

    {{-- Page js files --}}
    <script src="{{ asset('js/scripts/pages/app-order-list.js') }}?v={{ time() }}"></script>
    <script src="https://unpkg.com/feather-icons"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            feather.replace();

            // Set icon based on initial state
            document.querySelectorAll('.toggle-icon').forEach(icon => {
                const targetId = icon.getAttribute('data-target');
                const collapseEl = document.querySelector(targetId);

                if (collapseEl.classList.contains('show')) {
                    icon.setAttribute('data-feather', 'chevron-up');
                } else {
                    icon.setAttribute('data-feather', 'chevron-down');
                }
            });

            feather.replace(); // re-render after initial update

            // Handle dynamic toggle icon on collapse
            document.querySelectorAll('.collapsed-toggle').forEach(header => {
                const icon = header.querySelector('.toggle-icon');
                const targetId = icon.getAttribute('data-target');
                const collapseEl = document.querySelector(targetId);

                collapseEl.addEventListener('show.bs.collapse', () => {
                    icon.setAttribute('data-feather', 'chevron-up');
                    feather.replace();
                });

                collapseEl.addEventListener('hide.bs.collapse', () => {
                    icon.setAttribute('data-feather', 'chevron-down');
                    feather.replace();
                });
            });
        });
    </script>

@endsection
