@extends('layouts/contentLayoutMaster')
@section('title', 'Show Invoice ')
@section('main-page', 'Invoices')
@section('sub-page', 'Show Invoice')
@section('main-page-url', route("invoices.index"))
@section('sub-page-url', route("invoices.show",$model->id))
@section('vendor-style')
    <!-- Vendor CSS Files -->
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')
    <div class="bg-white rounded-3 p-2">
        <div class="mb-1">
            <label for="order_number" class="form-label fw-bold text-dark">Invoice Number:</label>
            <input type="text" id="order_number" value="{{ $model->invoice_number }}" name="order_number"
                   class="form-control form-control-lg fw-bold" value="#1234567" readonly>

            <div class="d-flex align-items-center status-pill justify-content-center">
                {{-- محتوى الحالة أو أي زر إضافي هنا --}}
            </div>
        </div>
        <form>
            <div class="row">
                <!-- Left Column -->

                <div class="mb-1">
                    <label class="form-label fw-bold">Issued Date</label>
                    <input type="text" class="form-control" value="{{ $model->issued_date }}" name="issued_date"
                           value="10/20/2025" readonly>
                </div>

                <!-- Left side: Inputs -->
                <div class="col-md-8">
                    <h5 class="mb-1 fs-16 text-black">Customer Details</h5>

                    <div class="mb-1">
                        <label class="form-label fw-bold">Name</label>
                        <input type="text" class="form-control" value="{{ $model->order->user?->first_name ?? $model->order?->guest?->first_name }}" name="name"
                               value="John Doe" readonly>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" class="form-control" value="{{ $model->order->user?->email ??  $model->order?->guest?->email }}" name="email"
                               value="john@example.com" readonly>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-bold">Phone</label>
                        <input type="text" class="form-control" value="{{ $model->order->user?->phone_number ?? $model->order?->guest?->email }}" name="phone"
                               value="+1 123 456 7890" readonly>
                    </div>
                </div>

                {{--            <!-- Right side: Radios -->--}}
                {{--            <div class="col-md-4">--}}
                {{--                <h5 class="mb-1 fs-16 text-black">Invoice Status</h5>--}}
                {{--                @foreach (\App\Enums\Invoice\InvoiceStatusEnum::cases() as $status)--}}
                {{--                <div class="form-check mb-1">--}}
                {{--                    <input class="form-check-input" type="radio" name="invoice_status" id="status_{{ $status->value }}"--}}
                {{--                        value="{{ $status->value }}" style="transform: scale(1.5); margin-right: 10px;" {{--}}
                {{--                        old('invoice_status', $model->invoice_status->value ?? 2) == $status->value ? 'checked' : '' }}>--}}
                {{--                    <label class="form-check-label fw-bold fs-5" for="status_{{ $status->value }}">--}}
                {{--                        {{ $status->label() }}--}}
                {{--                    </label>--}}
                {{--                </div>--}}
                {{--                @endforeach--}}
                {{--            </div>--}}



                <!-- Shipping Details -->
                <div class="d-flex justify-content-between align-items-center mb-1">
                    {{-- <h5 class="mb-0 fs-16 text-black">Shipping Details</h5>

                    <button type="button" class="lined-btn" data-bs-toggle="modal" data-bs-target="#editOrderShippingModal">
                        Edit
                    </button> --}}


                </div>
                {{-- <span class="text-black fs-16 fw-bold mb-1">Address:</span>
                <div class="border rounded p-2 mb-2 text-black fs-5">

                    1234 Example St, Suite 100<br>City, Country
                </div>
                <span class="text-black fs-16 fw-bold mb-1">Delivery Instructions:</span>
                <div class="border rounded p-1 mb-2 text-black fs-5">

                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore
                    et dolore magna aliqua.
                </div>

                <div class="rounded p-1 d-flex align-items-center text-black" style="background-color: #FCF8FC;">
                    <i data-feather="truck" class="me-2"></i>
                    <div>
                        <p class="fs-4">Estimated delivery time</p>
                        <div class="fs-16">Tomorrow, 2:00 PM - 4:00 PM</div>
                    </div>
                </div> --}}
                <!-- Right Column -->
                <div class="col-12 col-md-8">
                    {{-- <div class="p-1 rounded" style="background-color: #FCF8FC;">
                        <p class="mb-1 fs-16 text-dark">Order Placed on:</p>
                        <p class="fs-16 text-black">April 30, 2025</p> --}}
                </div>
                <!-- Items List -->
                @foreach ($model->order?->orderItems ??[] as $design)
                    @php
                        $product = $design->product;
                    @endphp
                    <div class="mb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="d-flex">
                                <img src="{{ $design->itemable?->getFirstMediaUrl(Str::plural(Str::lower(class_basename($design->itemable)))) }}" class="me-3 rounded" alt="Product"
                                     style="width: 60px; height: 60px;">
                                <div>
                                    <div class="fw-bold text-black fs-16">
                                        {{ $product->name ?? 'No Product Found' }}
                                    </div>
                                    <div class="text-dark fs-5">
                                        Qty: {{ $design->quantity }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-black">
                                    ${{ number_format($design->sub_total ?? 0, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <hr>


                <!-- Pricing Summary -->
                <h5 class="mt-3 mb-1 text-black fs-16">Pricing Details</h5>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-dark fs-16 fw-bold">Subtotal</span>
                    <span class="fs-4 text-black fw-bold">{{$model->subtotal}}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-dark fs-16 fw-bold">Discount</span>
                    <span class="fs-16 text-black">-{{$model->discount_amount}}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-dark fs-16 fw-bold">
                        Delivery
                        <i data-feather="info" data-bs-toggle="tooltip"
                           title="Delivery charges may vary based on location."></i>
                    </span>
                    <span class="fs-16 text-black">${{$model->delivery_amount}}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-dark fs-16 fw-bold">
                        Tax
                        <i data-feather="info" data-bs-toggle="tooltip"
                           title="Tax is calculated as per applicable laws."></i>
                    </span>
                    <span class="fs-16 text-black">${{ $model->tax_amount }}</span>
                </div>

                <hr class="border-dashed my-1">

                <div class="d-flex justify-content-between fw-bold fs-5 mb-2">
                    <span class="fs-4 text-black ">Total</span>
                    <span class="fs-4 text-black fw-bold">${{$model->total_price}}</span>
                </div>

                <!-- Status Display -->
                <a class="btn btn-primary" href="{{ route("invoices.download") }}">Download</a>

            </div>
        </form>
    </div>

@endsection


@section('vendor-script')
    <script src="{{ asset(mix('vendors/js/forms/repeater/jquery.repeater.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
@endsection

@section('page-script')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://unpkg.com/feather-icons"></script>

@endsection
