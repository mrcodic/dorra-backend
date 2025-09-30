@extends('layouts/contentLayoutMaster')

@section('title', 'Jobs')

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

        {{-- Page header / toolbar --}}
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="mb-0 d-flex align-items-center gap-2">
                    <span class="badge bg-dark">{{ $model->code }}</span>

                    @if($model->orderItem?->order)
                        <a href="{{ route('orders.show', $model->orderItem->order_id) }}" target="_blank" class="text-decoration-none">
                            Order #{{ $model->orderItem->order->order_number ?? $model->orderItem->order_id }}
                        </a>
                    @endif

                    @if($model->orderItem)
                        <span class="text-muted">— {{ $model->orderItem->itemable?->name ?? "Item #{$model->order_item_id}" }}</span>
                    @endif
                </h5>
            </div>

            <div class="d-flex align-items-center gap-1">
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">
                    <i data-feather="arrow-left" class="me-25"></i> Back
                </a>
                <button id="printTicketBtn" class="btn btn-sm btn-outline-primary">
                    <i data-feather="printer" class="me-25"></i> Print
                </button>
            </div>
        </div>

        @php
            $specsRaw = $model->specs ?? []; // or the JSON you have
           $specs = is_array($specsRaw) ? $specsRaw : (json_decode($specsRaw ?? '[]', true) ?? []);
           $now = now();
           $due = $model->due_at ?? null;
           $dueDiffMins = $due ? $now->diffInMinutes($due, false) : null; // negative if overdue
           $dueBadgeClass = $due
             ? ($dueDiffMins < 0 ? 'bg-danger' : ($dueDiffMins <= 180 ? 'bg-warning text-dark' : 'bg-success'))
             : 'bg-secondary';
           $dueTitle = $due
             ? ($dueDiffMins < 0
                 ? 'Overdue by '.$now->diffForHumans($due, ['parts' => 2, 'short'=>true, 'syntax'=>\Carbon\CarbonInterface::DIFF_ABSOLUTE])
                 : 'Due in '.$due->diffForHumans($now, ['parts' => 2, 'short'=>true]))
             : 'No due date';
        @endphp

        {{-- Top: Codes + Status --}}
        <div class="row g-3 align-items-stretch">
            {{-- Codes card --}}
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0">Codes</h6>
                        <button class="btn btn-sm btn-outline-secondary copy-btn" data-copy="{{ $model->code }}">
                            <i data-feather="copy" class="me-25"></i> Copy Code
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 text-center">

                            <div class="col-6">
                                {{-- Code128 --}}
                                <img
                                    src="{{ $model->barcode_svg_url }}"
                                    alt="Code128"
                                    class="img-fluid border rounded p-2 w-100"
                                >
                                <div class="small text-muted mt-50">Code128</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status card --}}
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="m-0">Current State</h6>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-5 col-sm-4 text-muted">Station</dt>
                            <dd class="col-7 col-sm-8 fw-semibold">{{ $model->station?->name ?? '-' }}</dd>

                            <dt class="col-5 col-sm-4 text-muted">Status</dt>
                            <dd class="col-7 col-sm-8">
                                <span class="badge bg-primary">{{ $model->status->label() }}</span>
                            </dd>

                            <dt class="col-5 col-sm-4 text-muted">Priority</dt>
                            <dd class="col-7 col-sm-8">
              <span class="badge {{ $model->priority === \App\Enums\JobTicket\PriorityEnum::RUSH ? 'bg-danger' : 'bg-secondary' }}">
                {{ $model->priority?->label() }}
              </span>
                            </dd>

                            <dt class="col-5 col-sm-4 text-muted">Due</dt>
                            <dd class="col-7 col-sm-8">
                                @if($due)
                                    <span class="badge {{ $dueBadgeClass }}" data-bs-toggle="tooltip" title="{{ $dueTitle }}">
                  {{ $due->format('Y-m-d H:i') }}
                </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Specifications --}}
        @php
            $specsRaw = $model->specs ?? []; // or the JSON you have
            $specs = is_array($specsRaw) ? $specsRaw : (json_decode($specsRaw ?? '[]', true) ?? []);
        @endphp

        @if(!empty($specs))
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                    <tr><th class="w-35">Specification</th><th>Option</th></tr>
                    </thead>
                    <tbody>
                    @foreach($specs as $row)
                        <tr>
                            <th>{{ \Illuminate\Support\Str::headline((string)($row['spec_name'] ?? '')) }}</th>
                            <td>{{ $row['option_name'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted">No specifications.</p>
        @endif


    </section>
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
