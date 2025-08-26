@extends('layouts/contentLayoutMaster')
@section('title', 'Show Order')
@section('main-page', 'Orders')
@section('sub-page', 'Show Order')
@section('main-page-url', route("orders.index"))
@section('sub-page-url', route("orders.show",$model->id))
@section('vendor-style')
<!-- Vendor CSS Files -->
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')
<div class="bg-white rounded-3 p-2">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div><span class="fs-16 text-dark fw-bold">Order Number: </span><span class="fs-4 text-black fw-bold">{{
                $model->order_number }}</span></div>
        <div class="d-flex align-items-center status-pill justify-content-center">
            <div class="status-icon me-1">
                <i data-feather="check"></i>
            </div>
            <span class="status-text">Confirmed</span>
        </div>
    </div>
    <form>
        <div class="row">
            <!-- Left Column -->
            <div class="col-12 col-md-4">
                <h5 class="mb-2 fs-16 text-black">Customer Details</h5>
                <div class="mb-2">
                    <label class="form-label fw-bold">First Name</label>
                    <input type="text" class="form-control" name="first_name"
                        value="{{ $model->orderAddress->first_name }}" readonly>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-bold">Last Name</label>
                    <input type="text" class="form-control" name="last_name"
                        value="{{ $model->orderAddress->last_name }}" readonly>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" class="form-control" name="email"
                        value="{{ $model->orderAddress->email }}" readonly>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-bold">Phone</label>
                    <input type="text" class="form-control" name="phone"
                        value="{{ $model->orderAddress->phone }}" readonly>
                </div>


                @php
                $address = $model->orderAddress;
                @endphp

                @if($address)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0 fs-16 text-black">
                        {{ $address->type === 'pickup' ? 'Pickup Details' : 'Shipping Details' }}
                    </h5>
                </div>

                <span class="text-black fs-16 fw-bold mb-1">
                    {{ $address->type === 'pickup' ? 'Location:' : 'Address:' }}
                </span>

                <div class="border rounded p-2 mb-2 text-black fs-5">
                    @if($address->type === 'pickup')
                    {{ $address->location_name }}<br>
                    {{ $address->state }}, {{ $address->country }}
                    @else
                    {{ $address->address_line }}, {{ $address->address_label }}<br>
                    {{ $address->state }}, {{ $address->country }}
                    @endif
                </div>
                @endif
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
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-12 col-md-8">
                <div class="p-1 rounded" style="background-color: #FCF8FC;">
                    <p class="mb-1 fs-16 text-dark">Order Placed on:</p>
                    <p class="fs-16 text-black">{{ $model->created_at->format('F d, Y') }}</p>
                </div>
                <label class="form-label fw-bold mt-3 mb-1 fs-16 text-black">Payment Status</label>
                <p>Completed</p>


                @foreach($model->orderItems as $orderItem)
                @php
                $product = $orderItem->product;
                @endphp

                <!-- Items List -->
                <div class="mb-1">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="d-flex">
                            <img src="{{ $orderItem->itemable?->getFirstMediaUrl(Str::plural(Str::lower(class_basename($orderItem->itemable)))) }}"
                                class="me-3 rounded" alt="Product" style="width: 60px; height: 60px;">
                            <div>
                                <div class="fw-bold text-black fs-16">
                                    {{ $product->name ?? 'No Product Found' }}
                                </div>
                                <div class="text-dark fs-5">
                                    Qty: {{$orderItem->quantity }}
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-black">
                                ${{ number_format($orderItem->sub_total ?? 0, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                {{-- <div class="mb-3 border-bottom pb-2">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="d-flex">
                            <img src="{{ asset('images/banner/banner-1.jpg') }}" class="mx-1 rounded" alt="Product"
                                style="width: 60px; height: 60px;">
                            <div>
                                <div class="fw-bold text-black fs-16">Product Name 1</div>
                                <div class="text-dark fs-5">Qty: 2</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-black">$40.00</div>
                        </div>
                    </div>
                </div> --}}

                <h5 class="mt-3 mb-1 text-black fs-16">Pricing Details</h5>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-dark fs-16 fw-bold">Subtotal</span>
                    <span class="fs-4 text-black fw-bold"> {{ $model->subtotal }} </span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-dark fs-16 fw-bold">Discount</span>
                    <span class="fs-16 text-black">-{{ $model->discount_amount }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-dark fs-16 fw-bold">
                        Delivery
                        <i data-feather="info" data-bs-toggle="tooltip"
                            title="Delivery charges may vary based on location."></i>
                    </span>
                    <span class="fs-16 text-black"> {{$model->delivery_amount}}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-dark fs-16 fw-bold">
                        Tax
                        <i data-feather="info" data-bs-toggle="tooltip"
                            title="Tax is calculated as per applicable laws."></i>
                    </span>
                    <span class="fs-16 text-black">{{ $model->tax_amount }}</span>
                </div>

                <hr class="border-dashed my-1">

                <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                    <span class="fs-4 text-black ">Total</span>
                    <span class="fs-4 text-black fw-bold"> {{$model->total_price}}</span>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Order Status</label>
                    <div class="d-flex align-items-center status-pill justify-content-center">
                        <div class="status-icon me-1">
                            <i data-feather="check"></i>
                        </div>
                        <span class="status-text">Confirmed</span>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end ">
                    <a class="btn btn-primary" href="{{ route('orders.edit',$model->id) }}">Edit Order</a>
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
