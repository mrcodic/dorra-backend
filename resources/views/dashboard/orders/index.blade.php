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
<style>
    .status-card.selected {
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
        font-size: 16px;
        padding-top: 5px;
    }

    .card-specs .number {
        color: #121212;
        font-size: 20px;
        font-weight: bold;
        padding-right: 5px
    }

    .card-specs .order {
        color: #424746;
        font-size: 16px;
    }

    /* Responsive table accordion styles */
    @media (max-width: 768px) {

        /* Hide the last 4 columns on mobile */
        .order-list-table th:nth-child(2),
        .order-list-table th:nth-child(6),
        .order-list-table th:nth-child(7),
        .order-list-table th:nth-child(8) {
            display: none !important;
        }

        .order-list-table tbody tr:not(.details-row) td:nth-child(2),
        .order-list-table tbody tr:not(.details-row) td:nth-child(6),
        .order-list-table tbody tr:not(.details-row) td:nth-child(7),
        .order-list-table tbody tr:not(.details-row) td:nth-child(8) {
            display: none !important;
        }

        /* Style for clickable rows */
        .order-list-table tbody tr:not(.details-row) {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        /* Add expand indicator to the role column */
        .order-list-table tbody tr:not(.details-row) td:nth-child(1) {
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
<div class=" card p-1">


    <!-- users list start -->
    <section class="app-user-list">

        <!-- list and filter start -->
        <div class="card">
            <div class="card-body">

                <div class="row">
                    <div class="col-md-4 user_role"></div>
                    <div class="col-md-4 user_plan"></div>
                    <div class="col-md-4 user_status"></div>
                </div>
            </div>
            <div class="card-datatable table-responsive pt-0">
                {{-- Statistics Cards --}}
                <div class="mb-3 d-flex flex-wrap gap-1">
                    <div class="d-flex flex-column justify-content-between p-1 card-specs status-card"
                        data-status="{{ \App\Enums\Order\StatusEnum::PENDING->value }}">
                        <img src="{{asset('/images/pendingIcon.svg')}}" alt="pendingIcon" style="width: 32px">
                        <p>Pending Orders</p>
                        <div class="d-flex align-items-center">
                            <span class="number">{{
                                \App\Models\Order::status(\App\Enums\Order\StatusEnum::PENDING)->count()}}</span>
                            <span class="order">Orders</span>
                        </div>
                    </div>

                    <div class="d-flex flex-column justify-content-between p-1 card-specs status-card"
                        data-status="{{ \App\Enums\Order\StatusEnum::CONFIRMED->value }}">
                        <img src="{{asset('/images/confirmedIcon.svg')}}" alt="confirmedIcon" style="width: 32px">
                        <p>Confirmd Orders</p>
                        <div class="d-flex align-items-center">
                            <span class="number">{{
                                \App\Models\Order::status(\App\Enums\Order\StatusEnum::CONFIRMED)->count()}}</span>
                            <span class="order">Orders</span>
                        </div>
                    </div>

                    <div class="d-flex flex-column justify-content-between p-1 card-specs status-card"
                        data-status="{{ \App\Enums\Order\StatusEnum::PREPARED->value }}">
                        <img src="{{asset('/images/preparingIcon.svg')}}" alt="preparingIcon" style="width: 32px">
                        <p>Preparing Orders</p>
                        <div class="d-flex align-items-center">
                            <span class="number">{{
                                \App\Models\Order::status(\App\Enums\Order\StatusEnum::PREPARED)->count()}}</span>
                            <span class="order">Orders</span>
                        </div>
                    </div>

                    <div class="d-flex flex-column justify-content-between p-1 card-specs status-card"
                        data-status="{{ \App\Enums\Order\StatusEnum::SHIPPED->value }}">
                        <img src="{{asset('/images/deliveryIcon.svg')}}" alt="deliveryIcon" style="width: 32px">
                        <p>Out for delivery</p>
                        <div class="d-flex align-items-center">
                            <span class="number">{{
                                \App\Models\Order::status(\App\Enums\Order\StatusEnum::SHIPPED)->count()}}</span>
                            <span class="order">Orders</span>
                        </div>
                    </div>

                    <div class="d-flex flex-column justify-content-between p-1 card-specs status-card"
                        data-status="{{ \App\Enums\Order\StatusEnum::DELIVERED->value }}">
                        <img src="{{asset('/images/deliveryIcon.svg')}}" alt="deliveryIcon" style="width: 32px">
                        <p>Delivered Orders</p>
                        <div class="d-flex align-items-center">
                            <span class="number">{{
                                \App\Models\Order::status(\App\Enums\Order\StatusEnum::DELIVERED)->count() }}</span>
                            <span class="order">Orders</span>
                        </div>
                    </div>

                    <div class="d-flex flex-column justify-content-between p-1 card-specs status-card"
                        data-status="{{ \App\Enums\Order\StatusEnum::REFUNDED->value }}">
                        <img src="{{asset('/images/refundedIcon.svg')}}" alt="refundedIcon" style="width: 32px">
                        <p>Refunded Orders</p>
                        <div class="d-flex align-items-center">
                            <span class="number">{{
                                \App\Models\Order::status(\App\Enums\Order\StatusEnum::REFUNDED)->count() }}</span>
                            <span class="order">Orders</span>
                        </div>
                    </div>
                </div>
                {{-- Search and Select Options --}}
                <div class="px-1 d-flex flex-wrap justify-content-between align-items-center gap-1">
                    <form action="" method="get" class="position-relative flex-grow-1 me-1 col-12 col-md-4 search-form">
                        <i data-feather="search"
                            class="position-absolute top-50 translate-middle-y ms-2 text-muted"></i>

                        <input type="text" class="form-control ps-5 border rounded-3" name="search_value"
                            id="search-order-form" placeholder="Search order..." style="height: 38px;">

                        <!-- Clear button -->
                        <button type="button" id="clear-search" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
                   background: transparent; border: none; font-weight: bold;
                   color: #aaa; cursor: pointer; font-size: 18px; line-height: 1;" title="Clear filter">
                            &times;
                        </button>
                    </form>


                    {{-- Filter Select - 10% on md+, half width on sm --}}
                    <div class="col-12 col-md-2">
                        <select name="created_at" class="form-select filter-status">
                            <option value="" disabled selected>Status</option>
                            <option value="">All</option>

                            @foreach (\App\Enums\Order\StatusEnum::cases() as $status)
                            <option value="{{$status}}">
                                {{ $status->label() }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-2">
                        <select name="created_at" class="form-select filter-date">
                            <option value="" disabled selected>Date</option>
                            <option value="asc">Oldest</option>
                            <option value="desc">Newest</option>
                        </select>
                    </div>

                    {{-- Add Button - 20% on md+, full width on xs --}}
                    <div class="col-12 col-md-2">
                        <button type="button" id="print-confirmed-orders" class="btn btn-outline-primary w-100 w-md-auto">
                            <i data-feather="printer"></i>
                            Printing New orders
                        </button>
                    </div>
                    @can('orders_create')
                    <div class="col-12 col-md-2">
                        <a class="btn btn-outline-primary w-100 w-md-auto" href="{{ route('orders.create') }}">
                            <i data-feather="plus"></i>
                            Add New Order
                        </a>
                    </div>
                    @endcan

                </div>

                <table class="order-list-table table">
                    <thead class="table-light">
                        <tr>

                            <th>
                                <input type="checkbox" id="select-all-checkbox" class="form-check-input" @disabled(!auth()->user()->hasPermissionTo('orders_delete'))>
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
                    <div class="delete-container d-flex flex-wrap align-items-center justify-content-center justify-content-md-between"
                        style="z-index: 10;">
                        <p id="selected-count-text">0 Orders are selected</p>
                        <button type="submit" id="delete-selected-btn" data-bs-toggle="modal"
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
                @if($model->status == \App\Enums\Order\StatusEnum::PREPARED)
                    <button type="button" id="bulk-request-pickup"
                            class="btn btn-outline-primary d-flex align-items-center gap-1">
                        <i data-feather="truck"></i> Request Pickup
                    </button>


                @endif
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
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" />
            <script>
                const ordersDataUrl = "{{ route('orders.data') }}";
                const ordersCreateUrl = "{{ route('orders.create') }}";
            </script>
                <script>
                    $(document).on('click', '#print-confirmed-orders', function () {
                        $.ajax({
                            url: "{{ route('orders.print') }}",
                            method: "GET",
                            success: function (response) {
                                // Create a hidden iframe for printing
                                const iframe = document.createElement('iframe');
                                iframe.style.position = 'fixed';
                                iframe.style.right = '0';
                                iframe.style.bottom = '0';
                                iframe.style.width = '0';
                                iframe.style.height = '0';
                                iframe.style.border = '0';
                                document.body.appendChild(iframe);

                                const doc = iframe.contentDocument || iframe.contentWindow.document;
                                doc.open();
                                doc.write(`
    <!DOCTYPE html>
    <html>
      <head>
        <title>Confirmed Orders</title>
        <meta charset="utf-8">
        <style>
          body { font-family: Arial, sans-serif; }
          table { border-collapse: collapse; width: 100%; }
          th, td { border: 1px solid #ccc; padding: 5px; }
          h3 { margin-bottom: 5px; }
        </style>
      </head>
      <body>
        ${response.html}
      </body>
    </html>
  `);
                                doc.close();

                                const cleanup = () => {
                                    try { document.body.removeChild(iframe); } catch (e) {}
                                };

                                const cw = iframe.contentWindow;
                                // Close/cleanup when printing is done
                                if ('onafterprint' in cw) {
                                    cw.onafterprint = cleanup;
                                } else {
                                    try {
                                        const mql = cw.matchMedia('print');
                                        if (mql && mql.addEventListener) {
                                            mql.addEventListener('change', e => { if (!e.matches) cleanup(); });
                                        } else if (mql && mql.addListener) {
                                            mql.addListener(e => { if (!e.matches) cleanup(); });
                                        } else {
                                            // Last-ditch: cleanup when parent regains focus
                                            window.addEventListener('focus', function onFocus() {
                                                cleanup();
                                                window.removeEventListener('focus', onFocus);
                                            }, { once: true });
                                        }
                                    } catch (e) {
                                        // Fallback cleanup when parent regains focus
                                        window.addEventListener('focus', function onFocus() {
                                            cleanup();
                                            window.removeEventListener('focus', onFocus);
                                        }, { once: true });
                                    }
                                }

                                // Trigger print after iframe paints
                                iframe.onload = function () {
                                    cw.focus();
                                    cw.print();
                                };
                            },

                            error: function () {
                                alert('Failed to load confirmed orders for printing.');
                            }
                        });
                    });
                </script>

            {{-- Page js files --}}
            <script src="{{ asset('js/scripts/pages/app-order-list.js') }}?v={{ time() }}"></script>
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
                $(document).on('click.accordion', '.order-list-table tbody tr:not(.details-row)', function(e) {
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
                        $('.order-list-table tbody tr:not(.details-row)').each(function() {
                            const $row = $(this);

                            // Remove existing details and icons first
                            $row.find('.expand-icon').remove();
                            $row.next('.details-row').remove();

                            // Add expand icon to role column
                            $row.find('td:nth-child(1)').append('<span class="expand-icon"><i class="fa-solid fa-angle-down"></i></span>');

                            // Get data for details
                            const orderNumber = $row.find('td:nth-child(2)').html() || '';
                            const orderStatus = $row.find('td:nth-child(6)').html() || '';
                            const addedDate = $row.find('td:nth-child(7)').html() || '';
                            const actions = $row.find('td:nth-child(8)').html() || '';

                            // Create details row
                            const detailsHtml = `
                                <tr class="details-row">
                                    <td colspan="4">
                                        <div class="details-content">
                                            <div class="detail-row">
                                                <span class="detail-label">Order Number:</span>
                                                <span class="detail-value">${orderNumber}</span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">Order Status:</span>
                                                <span class="detail-value">${orderStatus}</span>
                                            </div>
                                            <div class="detail-row">
                                                <span class="detail-label">Added Date:</span>
                                                <span class="detail-value">${addedDate}</span>
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
                $(document).on('draw.dt', '.order-list-table', function () {
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
    $(document).off('click.accordion').on('click.accordion', '.order-list-table tbody tr:not(.details-row)', function(e) {
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
