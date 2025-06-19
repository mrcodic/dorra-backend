@extends('layouts/contentLayoutMaster')
@section('title', 'Edit orderss')
@section('main-page', 'Orders')
@section('sub-page', 'Edit Orders')

@section('vendor-style')
<!-- Vendor CSS Files -->
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')

<div class="container bg-white rounded-3 p-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div><span class="fs-16 text-dark fw-bold">Order Number: </span><span class="fs-4 text-black fw-bold">{{$model->order_number}}</span></div>
        <div class="d-flex align-items-center status-pill justify-content-center">
            <div class="status-icon me-1">
                <i data-feather="check"></i> <!-- You can dynamically change the icon -->
            </div>
            <span class="status-text">Confirmed</span>
        </div>
    </div>
    <form id="order-form" class="form" action="{{ route('orders.update',$model->id) }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @method("PUT")
        <div class="row">
            <!-- Left Column -->
            <div class="col-12 col-md-4">
                <!-- Customer Details -->
                <h5 class="mb-2 fs-16 text-black">Customer Details</h5>

                <div class="mb-2">
                    <label class="form-label fw-bold">First Name</label>
                    <input type="text" class="form-control" name="first_name" value="{{ optional($model->OrderAddress->first())->first_name }}">
                </div>
                <div class="mb-2">
                    <label class="form-label fw-bold">Last Name</label>
                    <input type="text" class="form-control" name="last_name" value="{{ optional($model->OrderAddress->first())->last_name }}">
                </div>
                <div class="mb-2">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" class="form-control" name="email" value="{{ optional($model->OrderAddress->first())->email }}">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Phone</label>
                    <input type="text" class="form-control" name="phone" value="{{ optional($model->OrderAddress->first())->phone }}">
                </div>


                <!-- Shipping Details -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0 fs-16 text-black">Shipping Details</h5>

                    <button type="button" class="lined-btn" data-bs-toggle="modal" data-bs-target="#editOrderShippingModal">
                        Edit
                    </button>


                </div>
                <span class="text-black fs-16 fw-bold mb-1">Address:</span>
                <div class="border rounded p-2 mb-2 text-black fs-5">

                    {{ optional($model->OrderAddress->first())->address_line }}, {{ optional($model->OrderAddress->first())->address_label}},<br>{{ optional($model->OrderAddress->first())->state }}, {{ optional($model->OrderAddress->first())->country }}
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
                </div>
            </div>
            <!-- Right Column -->
            <div class="col-12 col-md-8">
               <div class="p-1 rounded" style="background-color: #FCF8FC;">
                    <p class="mb-1 fs-16 text-dark">Order Placed on:</p>
                    <p class="fs-16 text-black">{{ $model->created_at->format('F d, Y') }}</p>
                </div>
                <label class="form-label fw-bold mt-3 mb-1 fs-16 text-black">Payment Status</label>
                <select class="form-select mb-4" name="order_status">
                    <option value="confirmed">Completed</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                <h5 class="fw-bold mt-3 mb-1 fs-16 text-black">Items</h5>

                @foreach($model->designs as $design)
                    @php
                        $product = optional(optional($design->template)->product);
                    @endphp

                <!-- Items List -->
                 <div class="mb-1">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="d-flex">
                        <img src="{{ asset('images/banner/banner-1.jpg') }}" class="me-3 rounded" alt="Product" style="width: 60px; height: 60px;">
                        <div>
                            <div class="fw-bold text-black fs-16">
                                {{ $product->name ?? 'No Product Found' }}
                            </div>
                            <div class="text-dark fs-5">
                                Qty: {{ $design->pivot->quantity }}
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-black">
                            ${{ number_format($product->base_price ?? 0, 2) }}
                        </div>
                        <button class="btn btn-sm btn-outline-danger mt-1">Delete</button>
                    </div>
                </div>
            </div>
            @endforeach


                {{-- <!-- Repeat for more items -->
                <div class="mb-3 border-bottom pb-2">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="d-flex">
                            <img src="{{ asset('images/banner/banner-1.jpg') }}" class="me-3 rounded" alt="Product" style="width: 60px; height: 60px;">
                            <div>
                                <div class="fw-bold text-black fs-16">Product Name 1</div>
                                <div class="text-dark fs-5">Qty: 2</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-black">$40.00</div>
                            <button class="btn btn-sm btn-outline-danger mt-1">Delete</button>
                        </div>
                    </div>
                </div> --}}

                <!-- Pricing Summary -->
                <h5 class="mt-3 mb-1 text-black fs-16">Pricing Details</h5>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-dark fs-16 fw-bold">Subtotal</span>
                    <span class="fs-4 text-black fw-bold">$ {{ $model->subtotal }} </span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-dark fs-16 fw-bold">Discount</span>
                    <span class="fs-16 text-black">-$ {{$model->discount_amount}}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-dark fs-16 fw-bold">
                        Delivery
                        <i data-feather="info" data-bs-toggle="tooltip" title="Delivery charges may vary based on location."></i>
                    </span>
                    <span class="fs-16 text-black">$ {{$model->delivery_amount}}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-dark fs-16 fw-bold">
                        Tax
                        <i data-feather="info" data-bs-toggle="tooltip" title="Tax is calculated as per applicable laws."></i>
                    </span>
                    <span class="fs-16 text-black">$ {{$model->tax_amount}}</span>
                </div>

                <hr class="border-dashed my-1">

                <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                    <span class="fs-4 text-black ">Total</span>
                    <span class="fs-4 text-black fw-bold">$ {{$model->total_price}}</span>
                </div>

                <!-- Status Display -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Order Status</label>
                    <div class="d-flex align-items-center status-pill justify-content-center">
                        <div class="status-icon me-1">
                            <i data-feather="check"></i> <!-- You can dynamically change the icon -->
                        </div>
                        <span class="status-text">Confirmed</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                    <div class="d-flex gap-2 justify-content-between ">


                    <button class="btn btn-outline-secondary">Discard Changes</button>
                    <div class="d-flex gap-1">
                        <button class="btn btn-outline-secondary">Cancel Order</button>
                        <button  class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@include("modals.orders.edit-shipping-details")

@endsection


@section('vendor-script')
<script src="{{ asset(mix('vendors/js/forms/repeater/jquery.repeater.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
@endsection

@section('page-script')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="https://unpkg.com/feather-icons"></script>

<script>
      $('#order-form').on('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                $.ajax({
                    url: this.action,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.success) {
                            sessionStorage.setItem('order_updated', 'true');
                            window.location.href = '/orders';
                        }
                    },
                    error: function (xhr) {
                        $.each(xhr.responseJSON.errors, (k, msgArr) => {
                            Toastify({
                                text: msgArr[0],
                                duration: 4000,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#EA5455",
                                close: true
                            }).showToast();
                        });
                    }
                });
            });

</script>
@endsection