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
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
        <div class="d-flex align-items-center gap-1 flex-wrap">
            <h5 class="mb-0 d-flex align-items-center gap-2">
                <span class="badge bg-dark">{{ $model->code }}</span>

                @if($model->orderItem?->order)
                <a href="{{ route('orders.show', $model->orderItem->order_id) }}" target="_blank"
                    class="text-decoration-none">
                    Order #{{ $model->orderItem->order->order_number ?? $model->orderItem->order_id }}
                </a>
                @endif

                @if($model->orderItem)
                <span class="text-muted">— {{ $model->orderItem->itemable?->name ?? "Item #{$model->order_item_id}"
                    }}</span>
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
    ? ($dueDiffMins < 0 ? 'bg-danger' : ($dueDiffMins <=180 ? 'bg-warning text-dark' : 'bg-success' )) : 'bg-secondary'
        ; $dueTitle=$due ? ($dueDiffMins < 0 ? 'Overdue by ' .$now->diffForHumans($due, ['parts' => 2, 'short'=>true,
        'syntax'=>\Carbon\CarbonInterface::DIFF_ABSOLUTE])
        : 'Due in '.$due->diffForHumans($now, ['parts' => 2, 'short'=>true]))
        : 'No due date';
        @endphp

        {{-- Top: Details + Codes + Status --}}
        <div class="p-1 d-flex flex-column flex-md-row gap-2 rounded-3" style="background-color: white">
            <div class="d-flex flex-column">
                <img src="{{ $model->orderItem->orderable->getMainImageUrl() ?: asset('/images/item-photo.png')}}" alt="item photo" class="mb-2" width="320px"
                    height="320px">
                <div class="d-flex flex-column">
                    <div class="d-flex flex-column gap-1">
                        <p style="color: #424746; margin: 0; font-size: 16px">Operator:</p>
                        <div class="d-flex align-items-center gap-1">
                            <img src="{{asset('/images/admin-avatar.png')}}" alt="Admin avatar">
                            <div class="d-flex flex-column">
                                <h5 style="color: #121212">John Doe</h5>
                                <p style="margin: 0; color: #424746">Admin</p>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="d-flex flex-column">
                    <div class="d-flex flex-column gap-1">
                        <p style="color: #424746; margin: 0; font-size: 16px">Order ID:</p>
                        <div class="d-flex align-items-center justify-content-between">
                            <p style="margin: 0; color: #121212">{{ $model->code }}</p>
                            <a href="{{ route("orders.show",$model->orderItem->order->id) }}" style="margin: 0; color: #24B094; cursor: pointer">Go to Order</a>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="d-flex flex-column">
                    <div class="d-flex flex-column gap-1">
                        <p style="color: #424746; margin: 0; font-size: 16px">Designs:</p>
                        <div class="d-flex flex-wrap align-items-center gap-1 justify-content-between">
                            <div class="d-flex flex-column">
                                <p style="margin: 0; color: #121212">Design</p>
                                <img src="{{$model->orderItem->itemable->getImageUrl()}}" alt="item photo">
                            </div>
{{--                            <div class="d-flex flex-column">--}}
{{--                                <p style="margin: 0; color: #121212">Back Design</p>--}}
{{--                                <img src="{{asset('/images/item-photo.png')}}" alt="item photo">--}}
{{--                            </div>--}}
                        </div>
                    </div>
                </div>
            </div>
            {{-- Details --}}
            <div class="d-flex flex-column">
                <div>
                    <p style="color: #424746; margin: 0; font-size: 18px">{{ $model->code }}</p>
                    <hr>
                    <h5 style="color: #121212; font-size: 25px">{{ $model->orderItem->orderable?->name }}</h5>
                    <p style="color: #424746; font-size: 15px">{{$model->orderItem->orderable?->description}}</p>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex gap-2 gap-md-4">
                            <div class="d-flex gap-1 align-items-center">
                                <p style="color: #424746; margin:0">Station:</p>
                                <span class="rounded-3"
                                    style="color: #424746; background-color: #FAFBFC; padding: 7px">{{ $model->station?->name }}</span>
                            </div>
                            <div class="d-flex gap-1 align-items-center">
                                <p style="color: #424746; margin:0">Status:</p>
                                <span class="rounded-3"
                                    style="color: #424746; background-color: #CED5D4; padding: 7px">{{ $model->currentStatus?->name }}</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2 gap-md-4">
                            <div class="d-flex gap-1 align-items-center">
                                <p style="color: #424746; margin:0">Priority:</p>
                                <span class="rounded-3"
                                    style="color: white; background-color: {{ $model->priority == \App\Enums\JobTicket\PriorityEnum::STANDARD ? '#F8AB1B' : '#E74943' }}; padding: 7px">{{ $model->priority }}</span>
                            </div>
                            <div class="d-flex gap-1 align-items-center">
                                <p style="color: #424746; margin:0">Due Date:</p>
                                <span class="rounded-3" style="color: #424746; padding: 7px">{{ $model->due_at?->format("d/m/Y") }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Codes card --}}
                <div class="row g-1 text-center mt-2">
                    <div class="col-12">
                        {{-- Code128 --}}
                        <img src="{{ $model->barcode_svg_url }}" alt="Code128"
                            class="img-fluid border rounded p-2 w-100">
                    </div>
                </div>

                {{-- Specifications --}}
                @php
                $specsRaw = $model->specs ?? []; // or the JSON you have
                $specs = is_array($specsRaw) ? $specsRaw : (json_decode($specsRaw ?? '[]', true) ?? []);
                @endphp

                @if(!empty($specs))
                <div class="table-responsive mt-1">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th class="w-35">Specification</th>
                                <th>Option</th>
                            </tr>
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
                <p class="text-muted mt-1">No specifications.</p>
                @endif
            </div>
        </div>




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
