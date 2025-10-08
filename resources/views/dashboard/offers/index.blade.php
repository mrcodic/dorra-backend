@extends('layouts/contentLayoutMaster')

@section('title', 'Offers')
@section('main-page', 'Offers')

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
            .offer-list-table th:nth-child(4),
            .offer-list-table th:nth-child(5),
            .offer-list-table th:nth-child(6),
            .offer-list-table th:nth-child(7) {
                display: none !important;
            }

            .offer-list-table tbody tr:not(.details-row) td:nth-child(4),
            .offer-list-table tbody tr:not(.details-row) td:nth-child(5),
            .offer-list-table tbody tr:not(.details-row) td:nth-child(6),
            .offer-list-table tbody tr:not(.details-row) td:nth-child(7) {
                display: none !important;
            }

            /* Style for clickable rows */
            .offer-list-table tbody tr:not(.details-row) {
                cursor: pointer;
                transition: background-color 0.2s ease;
            }

            /* Add expand indicator to the first column */
            .offer-list-table tbody tr:not(.details-row) td:nth-child(1) {
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
                from { opacity: 0; }
                to   { opacity: 1; }
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
            .details-row { display: none !important; }
            .expand-icon { display: none !important; }
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
            </div>
            <div class="card-datatable table-responsive pt-0">
                <div class="row gx-2 gy-2 align-items-center px-1">

                    {{-- Search Input --}}
                    <div class="col-12 col-md-6">
                        <form action="" method="get" class="position-relative search-form">
                            <i data-feather="search"
                               class="position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                            <input type="text" class="form-control ps-5 border rounded-3" name="search_value"
                                   id="search-offer-form" placeholder="Search offer..." style="height: 38px;">
                            <button type="button" id="clear-search"
                                    class="position-absolute top-50 translate-middle-y text-muted"
                                    style="margin-right: 5px; right: 0; background: transparent; border: none; font-weight: bold; color: #aaa; cursor: pointer; font-size: 18px; line-height: 1;"
                                    title="Clear search">
                                &times;
                            </button>
                        </form>
                    </div>

                    {{-- Filter Select --}}
                    <div class="col-12 col-md-3">
                        <select class="form-select filter-type" name="type">
                            <option value="" selected disabled>Type</option>
                            <option value="">All</option>
                            @foreach(\App\Enums\Offer\TypeEnum::cases() as $type)
                                <option value="{{ $type }}">{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Add Button --}}
                    <div class="col-12 col-md-3 text-md-end">
                        <a class="btn btn-outline-primary w-100 w-md-auto" data-bs-toggle="modal"
                           data-bs-target="#addOfferModal">
                            <i data-feather="plus"></i>
                            Add New Offer
                        </a>
                    </div>

                </div>

                <table class="offer-list-table table">
                    <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" id="select-all-checkbox" class="form-check-input">
                        </th>
                        <th>Offer Name</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                </table>

                <div id="bulk-delete-container" class="my-2 bulk-delete-container" style="display:none;">
                    <div class="delete-container d-flex align-items-center gap-2">
                        <p id="selected-count-text" class="m-0">0 Offers are selected</p>

                        <!-- Open modal -->
                        <button type="button" id="open-bulk-delete-modal"
                                class="btn btn-outline-danger d-flex align-items-center gap-1">
                            <i data-feather="trash-2"></i> Delete Selected
                        </button>

                        <!-- Hidden form actually posted on confirm -->
                        <form id="bulk-delete-form" method="POST" action="{{ route('offers.bulk-delete') }}"
                              style="display:none;">
                            @csrf
                            <!-- We’ll submit via AJAX; no visible button here -->
                        </form>
                    </div>
                </div>
            </div>

            {{-- Modals --}}
            @include('modals.offers.show-offer')
            @include('modals.offers.add-offer')
            @include('modals.offers.edit-offer')

            @include('modals.delete',[
                'id' => 'deleteOfferModal',
                'formId' => 'deleteOfferForm',
                'title' => 'Delete Offer',
            ])

            @include('modals.delete', [
                'id' => 'deleteOffersModal',
                'formId' => 'bulk-delete-form',
                'buttonId' => 'confirm-bulk-delete',
                'title' => 'Delete Offers',
                'confirmText' => 'Are you sure you want to delete these items?',
            ])

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
    <script>
        const offersDataUrl   = "{{ route('offers.data') }}";
        const offersCreateUrl = "{{ route('offers.create') }}";
        const locale          = "{{ app()->getLocale() }}";
    </script>

    <script>
        $(document).ready(function () {
            // Clear search input
            $('#clear-search').on('click', function () {
                $('#search-offer-form').val('').trigger('input');
            });

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

            // Simple accordion toggle function (mobile only)
            function toggleAccordion($row) {
                if ($(window).width() > 768) return;
                const $detailsRow = $row.next('.details-row');
                const $icon = $row.find('.expand-icon');

                // Close all others
                $('.details-row.show').removeClass('show');
                $('.expand-icon.expanded').removeClass('expanded');

                if ($detailsRow.length && !$detailsRow.hasClass('show')) {
                    $detailsRow.addClass('show');
                    $icon.addClass('expanded');
                }
            }

            // Accordion click handler (single namespace)
            $(document).off('click.accordion')
                .on('click.accordion', '.offer-list-table tbody tr:not(.details-row)', function(e) {
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
                    $('.offer-list-table tbody tr:not(.details-row)').each(function() {
                        const $row = $(this);

                        // Remove existing details and icons first
                        $row.find('.expand-icon').remove();
                        $row.next('.details-row').remove();

                        // Add expand icon to first column
                        $row.find('td:nth-child(1)').append('<span class="expand-icon"><i class="fa-solid fa-angle-down"></i></span>');

                        // Get data for details
                        const value     = $row.find('td:nth-child(4)').html() || '';
                        const startDate = $row.find('td:nth-child(5)').html() || '';
                        const endDate   = $row.find('td:nth-child(6)').html() || '';
                        const actions   = $row.find('td:nth-child(7)').html() || '';

                        // Create details row
                        const detailsHtml = `
                            <tr class="details-row">
                                <td colspan="4">
                                    <div class="details-content">
                                        <div class="detail-row">
                                            <span class="detail-label">Value:</span>
                                            <span class="detail-value">${value}</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Start Date:</span>
                                            <span class="detail-value">${startDate}</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">End Date:</span>
                                            <span class="detail-value">${endDate}</span>
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
            $(document).on('draw.dt', '.offer-list-table', function () {
                $('#bulk-delete-container').hide();
                $('#select-all-checkbox').prop('checked', false);
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
                    $('#selected-count-text').text(`${count} Offer${count > 1 ? 's' : ''} selected`);
                    $('#bulk-delete-container').show();
                } else {
                    $('#bulk-delete-container').hide();
                }
            }

            // Initialize on page load
            setTimeout(initAccordion, 500);
        });
    </script>

    <script>
        // Clamp percent inputs
        $(document).on('input', '#createDiscountValue, #editOfferValue', function () {
            let v = parseInt($(this).val(), 10);
            if (isNaN(v)) v = '';
            else v = Math.max(1, Math.min(100, v));
            $(this).val(v);
        });

        /** ========== Select2 + Modal Type Toggle (with silent init) ========== */

        // Helper: init select2 inside modal once
        function initModalSelect2($m) {
            $m.find('.select2').each(function () {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({ dropdownParent: $m });
                }
            });
        }

        // Generic handler that accepts {silent:true} to avoid clearing values on init
        function bindTypeToggle($m, optsSelects) {
            // optsSelects = { productsFieldSel, categoriesFieldSel, productsSelectSel, categoriesSelectSel }
            const {
                productsFieldSel, categoriesFieldSel,
                productsSelectSel, categoriesSelectSel
            } = optsSelects;

            // change handler (accepts e, [opts])
            $m.on('change', 'input[name="type"]', function (e, opts = {}) {
                const silent = !!opts.silent;
                const v = parseInt(this.value, 10);

                if (v === 2) {
                    // Products
                    $m.find(productsFieldSel).removeClass('d-none');
                    $m.find(categoriesFieldSel).addClass('d-none');
                    if (!silent) {
                        $m.find(categoriesSelectSel).val(null).trigger('change'); // clear only when user changes
                    }
                } else if (v === 1) {
                    // Categories
                    $m.find(categoriesFieldSel).removeClass('d-none');
                    $m.find(productsFieldSel).addClass('d-none');
                    if (!silent) {
                        $m.find(productsSelectSel).val(null).trigger('change'); // clear only when user changes
                    }
                }
            });
        }

        // ---- EDIT MODAL ----
        $('#editOfferModal')
            .on('shown.bs.modal', function () {
                const $m = $(this);
                initModalSelect2($m);

                // Ensure correct section visibility on open without clearing values
                const $checked = $m.find('input[name="type"]:checked');
                if ($checked.length) {
                    $checked.trigger('change', [{ silent: true }]);
                }
            });

        bindTypeToggle($('#editOfferModal'), {
            productsFieldSel: '.productsField',
            categoriesFieldSel: '.categoriesField',
            productsSelectSel:  '#editProductsSelect',
            categoriesSelectSel:'#editCategoriesSelect'
        });

        // ---- ADD MODAL ----
        $('#addOfferModal')
            .on('shown.bs.modal', function () {
                const $m = $(this);
                initModalSelect2($m);

                // Ensure correct section visibility on open without clearing values
                const $checked = $m.find('input[name="type"]:checked');
                if ($checked.length) {
                    $checked.trigger('change', [{ silent: true }]);
                }
            });

        bindTypeToggle($('#addOfferModal'), {
            productsFieldSel: '.addProductsField',
            categoriesFieldSel: '.addCategoriesField',
            productsSelectSel:  '.add-products-select',
            categoriesSelectSel:'.add-categories-select'
        });

        // Keep your form handlers
        handleAjaxFormSubmit("#addOfferForm", {
            successMessage: "Offer Created Successfully",
            onSuccess: function () { location.reload(); }
        });

        handleAjaxFormSubmit("#editOfferForm", {
            successMessage: "Offer Updated Successfully",
            onSuccess: function () { location.reload(); }
        });
    </script>

    <script>
        $(document).ready(function () {
            // Select all toggle (secondary block for safety if DT redraws)
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
                    $('#selected-count-text').text(`${count} Offer${count > 1  ? 's' : ''} selected`);
                    $('#bulk-delete-container').show();
                } else {
                    $('#bulk-delete-container').hide();
                }
            }
        });
    </script>

    {{-- Page js files --}}
    <script src="{{ asset('js/scripts/pages/app-offer-list.js') }}?v={{ time() }}"></script>
@endsection
