@extends('layouts/contentLayoutMaster')

@section('title', 'Jobs')
@section('main-page', 'Jobs')

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
    /* Statistics Cards */
    .card-specs {
        border: 1.5px solid #CED5D4;
        border-radius: 20px;
        width: 166px;
        flex: 1;
    }

    .card-specs p {
        color: #424746;
        font-size: 14px;
        margin: 0;
        padding: 0;
    }

    .card-specs .number {
        color: #121212;
        font-size: 20px;
        font-weight: bold;
        padding-right: 5px
    }

    .card-specs .text {
        color: #424746;
        font-size: 16px;
    }

    /* Responsive table accordion styles */
    @media (max-width: 768px) {

        /* Hide the last 4 columns on mobile */
        .job-list-table th:nth-child(4),
        .job-list-table th:nth-child(5),
        .job-list-table th:nth-child(6) {
            display: none !important;
        }

        .job-list-table tbody tr:not(.details-row) td:nth-child(4),
        .job-list-table tbody tr:not(.details-row) td:nth-child(5),
        .job-list-table tbody tr:not(.details-row) td:nth-child(6) {
            display: none !important;
        }

        /* Style for clickable rows */
        .job-list-table tbody tr:not(.details-row) {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        /* Add expand indicator to the role column */
        .job-list-table tbody tr:not(.details-row) td:nth-child(1) {
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

<section class="app-user-list">

    <div class="modal-header mb-1">
        <h5 class="modal-title">
            <span class="badge bg-dark me-2">{{ $model->code }}</span>
            @if($model->orderItem?->order)
                <a href="{{ route('orders.show', $model->orderItem->order_id) }}" target="_blank">
                    Order #{{ $model->orderItem->order->number ?? $model->orderItem->order_id }}
                </a>
            @endif
            @if($model->orderItem)
                <span class="text-muted ms-2">— {{ $model->orderItem->name ?? "Item #{$model->order_item_id}" }}</span>
            @endif
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
    </div>

    <div class="modal-body">
        {{-- Top row: QR + Code128 + Current State --}}
        <div class="row g-3 align-items-center mb-2">
            <div class="col-md-4 text-center">
                <img src="{{ route('jobtickets.qr', $model) }}" alt="QR" class="img-fluid border rounded p-2">
                <div class="small text-muted mt-1">QR</div>
            </div>
            <div class="col-md-4 text-center">
                <img src="{{ route('jobtickets.code128', $model) }}" alt="Code128" class="img-fluid border rounded p-2">
                <div class="small text-muted mt-1">Code128</div>
            </div>
            <div class="col-md-4">
                <div class="d-flex flex-column gap-2">
                    <div>
                        <span class="text-muted me-1">Station:</span>
                        <span class="fw-bold">{{ $model->station?->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-muted me-1">Status:</span>
                        <span class="badge bg-primary">{{ $model->status->label() }}</span>
                    </div>
                    <div>
                        <span class="text-muted me-1">Priority:</span>
                        <span class="badge {{ $model->priority === 2 ? 'bg-danger' : 'bg-secondary' }}">
            {{ \App\Enums\JobTicket\PriorityEnum::from($model->priority)->label() }}
          </span>
                    </div>
                    @if($model->due_at)
                        <div>
                            <span class="text-muted me-1">Due:</span>
                            <span class="fw-semibold">{{ $model->due_at->format('Y-m-d H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Specs table --}}
        @php $specs = is_array($model->specs) ? $model->specs : (json_decode($model->specs ?? '[]', true) ?? []); @endphp
        @if(!empty($specs))
            <h6 class="mt-2">Specifications</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <tbody>
                    @foreach($specs as $k => $v)
                        <tr>
                            <th class="w-25">{{ Str::headline($k) }}</th>
                            <td>
                                @if(is_array($v) || is_object($v))
                                    <code class="small">{{ json_encode($v, JSON_UNESCAPED_UNICODE) }}</code>
                                @else
                                    {{ $v }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
    @endif



</section>

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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script>
    const jobsDataUrl = "{{ route('job-tickets.data') }}";

</script>

{{-- Page js files --}}
<script src="{{ asset('js/scripts/pages/app-jobs-list.js') }}?v={{ time() }}"></script>
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
            $(document).on('click.accordion', '.job-list-table tbody tr:not(.details-row)', function (e) {
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
                    $('.job-list-table tbody tr:not(.details-row)').each(function () {
                        const $row = $(this);

                        // Remove existing details and icons first
                        $row.find('.expand-icon').remove();
                        $row.next('.details-row').remove();

                        // Add expand icon to role column
                        $row.find('td:nth-child(1)').append('<span class="expand-icon"><i class="fa-solid fa-angle-down"></i></span>');

                        // Get data for details
                        const price = $row.find('td:nth-child(4)').html() || '';
                        const issuedAt = $row.find('td:nth-child(5)').html() || '';
                        const actions = $row.find('td:nth-child(6)').html() || '';

                        // Create details row
                        const detailsHtml = `
                    <tr class="details-row">
                        <td colspan="3">
                            <div class="details-content">
                                <div class="detail-row">
                                    <span class="detail-label">Price:</span>
                                    <span class="detail-value">${price}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Issued Ate:</span>
                                    <span class="detail-value">${issuedAt}</span>
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
            $(document).on('draw.dt', '.code-list-table', function () {
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
                    $('#selected-count-text').text(`${count} Invoice${count > 1 ? 's are' : ' is'} selected`);
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

<script>
    // Backup accordion handler in case the main one doesn't work
        $(document).ready(function () {
            // Alternative click handler
            $(document).off('click.accordion').on('click.accordion', '.job-list-table tbody tr:not(.details-row)', function (e) {
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
