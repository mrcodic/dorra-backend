@extends('layouts/contentLayoutMaster')

@section('title', 'User View - Account')

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
                    <img src="" alt="Code128" class="img-fluid border rounded p-2">
                    <div class="small text-muted mt-1">BarCode</div>
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
                            <span class="badge {{ $model->priority === \App\Enums\JobTicket\PriorityEnum::RUSH ? 'bg-danger' : 'bg-secondary' }}">
            {{ $model->priority?->label() }}
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
