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
                            <p id="selected-count-text">0 Orders are selected</p>
                            <button type="submit" id="delete-selected-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteOrdersModal"
                                    class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1 delete-selected-btns">
                                <i data-feather="trash-2"></i> Delete Selected
                            </button>
                            <form style="display: none;" id="bulk-delete-form" method="POST"
                                  action="{{ route('orders.bulk-delete') }}">
                                @csrf
                                <button type="submit" id="delete-selected-btn"
                                        class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1 delete-selected-btns">
                                    <i data-feather="trash-2"></i> Delete Selected
                                </button>
                            </form>


                        </div>
                    </div>
                </div>

                @include('modals.categories.show-category')
                @include('modals.categories.edit-category')
                @include('modals.categories.add-category')
                @include('modals.delete', [
                    'id' => 'deleteOrderModal',
                    'formId' => 'deleteOrderForm',
                    'title' => 'Delete Order',
                    'message' => 'Are you sure you want to delete this order? This action cannot be undone.',
                    'confirmText' => 'Yes, Delete Order'
                ])

                @include('modals.delete',[
                            'id' => 'deleteOrdersModal',
                            'formId' => 'bulk-delete-form',
                            'title' => 'Delete Orders',
                            'confirmText' => 'Are you sure you want to delete this items?',
                            ])
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
                    <script>
                        const ordersDataUrl = "{{ route('orders.data') }}";
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


                        // bulk delete function for show number of orders selected
                        $(document).ready(function () {
                            // Select all toggle
                            $('#select-all-checkbox').on('change', function () {
                                $('.category-checkbox').prop('checked', this.checked);
                                updateBulkDeleteVisibility();
                            });

                            // When individual checkbox changes
                            $(document).on('change', '.category-checkbox', function () {
                                // If any is unchecked, uncheck "Select All"
                                if (!this.checked) {
                                    $('#select-all-checkbox').prop('checked', false);
                                } else if ($('.category-checkbox:checked').length === $('.category-checkbox').length) {
                                    $('#select-all-checkbox').prop('checked', true);
                                }
                                updateBulkDeleteVisibility();
                            });


                            // On table redraw (e.g. pagination, search)
                            $(document).on('draw.dt', function () {
                                $('#bulk-delete-container').hide();
                                $('#select-all-checkbox').prop('checked', false);
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
                                    $('#selected-count-text').text(`${count} Categor${count > 1 ? 'ies' : 'y'} are selected`);
                                    $('#bulk-delete-container').show();
                                } else {
                                    $('#bulk-delete-container').hide();
                                }
                            }
                        });

                    </script>

{{--                    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAP_API_KEY"></script>--}}
                    <script>
                        // let map;
                        // let marker;
                        //
                        // function initMap(lat = 30.0444, lng = 31.2357) {
                        //     const defaultLocation = {lat: parseFloat(lat), lng: parseFloat(lng)};
                        //     map = new google.maps.Map(document.getElementById('mapPlaceholder'), {
                        //         zoom: 10,
                        //         center: defaultLocation,
                        //     });
                        //
                        //     marker = new google.maps.Marker({
                        //         position: defaultLocation,
                        //         map: map,
                        //     });
                        // }

                        $(document).ready(function () {
                            $('#selectLocationModal').on('shown.bs.modal', function () {
                                initMap();
                            });

                            $('#location-search').on('input', function () {
                                console.log('Search query:', $(this).val());
                                const query = $(this).val();
                                if (query.length >= 2) {
                                    $.ajax({
                                        url: "{{ route('locations.search') }}",
                                        method: 'GET',
                                        data: {search: query},
                                        success: function (response) {
                                            $('#locationList').html(response);
                                        }
                                    });
                                } else {
                                    $('#locationList').html('');
                                }
                            });

                            // عند الضغط على نتيجة البحث
                            $(document).on('click', '.location-item', function () {
                                const lat = parseFloat($(this).data('lat'));
                                const lng = parseFloat($(this).data('lng'));
                                const name = $(this).data('name');

                                const newLocation = {lat: lat, lng: lng};
                                map.setCenter(newLocation);
                                marker.setPosition(newLocation);

                                $('#mapPlaceholder').find('p').hide();
                                $('#locationSearch').val(name);
                                $('#locationList').html('');
                            });
                        });
                    </script>

@endsection
