@extends('layouts/contentLayoutMaster')
@section('title', 'Edit Invoice ')
@section('main-page', 'Invoice')
@section('sub-page', 'Edit Invoice')

@section('vendor-style')
<!-- Vendor CSS Files -->
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')
<div class="container bg-white rounded-3 p-3">
    <div class="mb-3">
        <label for="order_number" class="form-label fw-bold text-dark">Invoice Number:</label>
        <input type="text" id="order_number" value="{{ $model->invoice_number }}" name="order_number"
               class="form-control form-control-lg fw-bold"
               value="#1234567"
               readonly>

    <div class="d-flex align-items-center status-pill justify-content-center">
        {{-- محتوى الحالة أو أي زر إضافي هنا --}}
    </div>
</div>
    <form>
      <div class="row">
    <!-- Left Column -->

    <div class="row">

        <div class="mb-2">
            <label class="form-label fw-bold">Issued Date</label>
            <input type="text" class="form-control" value="{{ $model->issued_date }}" name="issued_date" value="10/20/2025" readonly>
        </div>

    <!-- Left side: Inputs -->
    <div class="col-md-8">
        <h5 class="mb-2 fs-16 text-black">Customer Details</h5>

        <div class="mb-2">
            <label class="form-label fw-bold">Name</label>
            <input type="text" class="form-control" value="{{ $model->user->first_name }}"  name="name" value="John Doe" readonly>
        </div>
        <div class="mb-2">
            <label class="form-label fw-bold">Email</label>
            <input type="email" class="form-control" value="{{ $model->user->email }}" name="email" value="john@example.com" readonly>
        </div>
        <div class="mb-2">
            <label class="form-label fw-bold">Phone</label>
            <input type="text" class="form-control" value="{{ $model->user->phone_number }}" name="phone" value="+1 123 456 7890" readonly>
        </div>
    </div>

    <!-- Right side: Radios -->
<div class="col-md-4">
    <h5 class="mb-2 fs-16 text-black">Invoice Status</h5>
    @foreach (\App\Enums\Invoice\InvoiceStatusEnum::cases() as $status)
        <div class="form-check mb-2">
            <input class="form-check-input"
                   type="radio"
                   name="invoice_status"
                   id="status_{{ $status->value }}"
                   value="{{ $status->value }}"
                   style="transform: scale(1.5); margin-right: 10px;"
                   {{ old('invoice_status', $model->invoice_status->value ?? 2) == $status->value ? 'checked' : '' }}>
            <label class="form-check-label fw-bold fs-5" for="status_{{ $status->value }}">
                {{ $status->label() }}
            </label>
        </div>
    @endforeach
</div>



                <!-- Shipping Details -->
                <div class="d-flex justify-content-between align-items-center mb-2">
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

                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                </div>

                <div class="rounded p-1 d-flex align-items-center text-black" style="background-color: #FCF8FC;">
                    <i data-feather="truck" class="me-2"></i>
                    <div>
                        <p class="fs-4">Estimated delivery time</p>
                        <div class="fs-16">Tomorrow, 2:00 PM - 4:00 PM</div>
                    </div>
                </div> --}}
            </div>
            <!-- Right Column -->
            <div class="col-12 col-md-8">
                {{-- <div class="p-1 rounded" style="background-color: #FCF8FC;">
                    <p class="mb-1 fs-16 text-dark">Order Placed on:</p>
                    <p class="fs-16 text-black">April 30, 2025</p> --}}
                </div>
              
                <!-- Items List -->
                 @foreach ($model->designs as $design)
                    @php
                        $product = $design->product;
                    @endphp
                    <div class="mb-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="d-flex">
                                <img src="{{ asset('images/banner/banner-1.jpg') }}" class="me-3 rounded" alt="Product" style="width: 60px; height: 60px;">
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
                                    ${{ number_format($product->base_price ?? 0, 2) }}
                                </div>
                                @if ($model->designs->count() > 1 )
                                    <form class="delete-design-form d-inline" method="POST" action="{{ route('orders.orderItems.delete', ['orderId' => $model->id, 'designId' => $design->id]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger mt-1 delete-design-btn" data-design-id="{{ $design->id }}">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                
                <br>
                <br>
                <br>
                <br>
                <br>
          
                <hr>


                 <form id="discount-code-form" action="{{ route('orders.apply-discount-code') }}" method="post">
            <div class="input-group">
                @csrf
                <input type="text" class="form-control" name="code" placeholder="Enter discount code">
                <button class="btn btn-secondary" type="submit">Apply</button>
            </div>
            <div id="discount-code-error" class="invalid-feedback d-block text-danger mt-1" style="display: none;"></div>
            <div id="discount-code-success" class="valid-feedback d-block text-success mt-1" style="display: none;"></div>
        </form>

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
                        <i data-feather="info" data-bs-toggle="tooltip" title="Delivery charges may vary based on location."></i>
                    </span>
                    <span class="fs-16 text-black">${{$model->delivery_amount}}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-dark fs-16 fw-bold">
                        Tax
                        <i data-feather="info" data-bs-toggle="tooltip" title="Tax is calculated as per applicable laws."></i>
                    </span>
                    <span class="fs-16 text-black">${{ $model->tax_amount }}</span>
                </div>

                <hr class="border-dashed my-1">

                <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                    <span class="fs-4 text-black ">Total</span>
                    <span class="fs-4 text-black fw-bold">${{$model->total_price}}</span>
                </div>

                <!-- Status Display -->
              

                <!-- Action Buttons -->
                <div class="d-flex gap-2 justify-content-between ">


                    <button class="btn btn-outline-secondary">Discard Changes</button>
                    <div class="d-flex gap-1">
                        <button class="btn btn-outline-secondary">Cancel invoice</button>
                        <button class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
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