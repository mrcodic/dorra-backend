@extends('layouts/contentLayoutMaster')
@section('title', 'Edit Order')
@section('main-page', 'Orders')
@section('sub-page', 'Edit Orders')
@section('main-page-url', route("orders.index"))
@section('sub-page-url', route("orders.edit", $model->id))
@section('vendor-style')
<!-- Vendor CSS Files -->
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')

<div class="bg-white rounded-3 p-2">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div><span class="fs-16 text-dark fw-bold">Order Number: </span><span
                class="fs-4 text-black fw-bold">{{$model->order_number}}</span></div>

    </div>
    <form id="order-form" class="form" action="{{ route('orders.update',$model->id) }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        @method("PUT")
        <div class="row">
            <!-- Left Column -->
            <div class="col-12 col-md-4">
                <!-- Customer Details -->
                <h5 class="mb-2 fs-16 text-black">Customer Details</h5>

                <div class="mb-2">
                    <label class="form-label fw-bold">First Name</label>
                    <input type="text" class="form-control" name="first_name"
                        value="{{ $model->orderAddress->first_name }}">
                </div>
                <div class="mb-2">
                    <label class="form-label fw-bold">Last Name</label>
                    <input type="text" class="form-control" name="last_name"
                        value="{{ $model->orderAddress->last_name }}">
                </div>
                <div class="mb-2">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" class="form-control" name="email"
                        value="{{ $model->orderAddress->email }}">
                </div>
                <div class="mb-2">
                    <label class="form-label fw-bold">Phone</label>
                    <input type="text" class="form-control" name="phone"
                        value="{{ $model->orderAddress->phone }}">
                </div>


                <!-- Shipping Details -->
                @php
                $address = $model->orderAddress;
                @endphp

                @if($address)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0 fs-16 text-black">
                        {{ $address->type === 'pickup' ? 'Pickup Details' : 'Shipping Details' }}
                    </h5>

                    <button type="button" class="lined-btn" data-bs-toggle="modal"
                        data-bs-target="#editOrderShippingModal">
                        Edit
                    </button>
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


                @php
                use App\Enums\Order\StatusEnum;
                @endphp

                <label class="form-label fw-bold mt-3 mb-1 fs-16 text-black">Payment Status</label>
                <select class="form-select mb-4" name="status">
                    @foreach (StatusEnum::cases() as $status)
                    <option value="{{ $status->value }}" {{ (old('status', $model->status?->value) == $status->value) ?
                        'selected' : '' }}>
                        {{ $status->label() }}
                    </option>
                    @endforeach
                </select>

                <h5 class="fw-bold mt-3 mb-1 fs-16 text-black">Items</h5>
                @foreach ($model->orderItems as $orderItem)
                @php
                $product = $orderItem->product;
                @endphp
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
                                    Qty: {{ $orderItem->quantity }}
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-black">
                                {{ number_format($orderItem->sub_total ?? 0, 2) }}
                            </div>
                            @if ($model->orderItems->count() > 1 && $model->status == StatusEnum::PENDING)
                            <form class="delete-design-form d-inline" method="POST"
                                action="{{ route('orders.designs.delete', ['orderId' => $model->id, 'designId' => $orderItem->id]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger mt-1 delete-design-btn"
                                    data-design-id="{{ $orderItem->id }}">
                                    Delete
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach



                <!-- Pricing Summary -->
                <h5 class="mt-3 mb-1 text-black fs-16">Pricing Details</h5>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-dark fs-16 fw-bold">Subtotal</span>
                    <span class="fs-4 text-black fw-bold"> {{ $model->subtotal }} </span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-dark fs-16 fw-bold">Discount</span>
                    <span class="fs-16 text-black">- {{$model->discount_amount}}</span>
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
                    <span class="fs-16 text-black"> {{$model->tax_amount}}</span>
                </div>

                <hr class="border-dashed my-1">

                <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                    <span class="fs-4 text-black ">Total</span>
                    <span class="fs-4 text-black fw-bold"> {{$model->total_price}}</span>
                </div>

                <!-- Status Display -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Order Status</label>
                    <div class="d-flex align-items-center status-pill justify-content-center">
                        <div class="status-icon me-1">
                            <i data-feather="check"></i> <!-- You can dynamically change the icon -->
                        </div>
                        <span class="status-text">{{ $model->status->label() }}</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex flex-wrap-reverse gap-2 justify-content-between ">


                    <button class="btn btn-outline-secondary">Discard Changes</button>
                    <div class="d-flex gap-1">
                        <button class="btn btn-outline-secondary">Cancel Order</button>
                        <button class="btn btn-primary">Save changes</button>
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
    $(document).ready(function () {
        $(document).on('submit', '.delete-design-form', function (e) {
            e.preventDefault();

            const $form = $(this);
            const actionUrl = $form.attr('action');

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: $('meta[name="csrf-token"]').attr('content'),
                },
                success: function (res) {
                    Toastify({
                        text: "Design deleted successfully!",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28a745",
                        close: true,
                    }).showToast();

                    // Optionally remove the whole item block
                    $form.closest('.mb-1').remove();
                },
                error: function (xhr) {
                    Toastify({
                        text: "Failed to delete design.",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#EA5455",
                        close: true,
                    }).showToast();
                }
            });
        });

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
    });



// handle new address
    $(document).ready(function () {

        // Country-State dropdown handling
        $(document).on("change", ".address-country-select", function () {
            const countryId = $(this).val();
            const stateSelect = $(".address-state-select");

            if (countryId) {
                $.ajax({
                    url: "{{ route('states') }}",
                    method: "GET",
                    data: { "filter[country_id]": countryId },
                    success: function (response) {
                        stateSelect.empty().append('<option value="">Select State</option>');
                        $.each(response.data, function (index, state) {
                            stateSelect.append(`<option value="${state.id}">${state.name}</option>`);
                        });
                    },
                    error: function () {
                        stateSelect.empty().append('<option value="">Error loading states</option>');
                    }
                });
            } else {
                stateSelect.empty().append('<option value="">Select State</option>');
            }
        });

        // Form submission with validation
        $('#addAddressForm').on('submit', function (e) {
            e.preventDefault();

            const $form = $(this);
            const formData = $form.serialize();
            const actionUrl = $form.attr('action');

            // Reset validation
            $('.invalid-feedback').text('').hide();
            $('.form-control, .form-select').removeClass('is-invalid');

            // Show loading state
            const saveButton = $('#saveChangesButton');
            const saveLoader = $('#saveLoader');
            const saveButtonText = $('.btn-text');

            saveButton.prop('disabled', true);
            saveLoader.removeClass('d-none');
            saveButtonText.addClass('d-none');

            $.ajax({
                url: actionUrl,
                method: 'POST',
                data: formData,
                success: function (response) {
                    // Reset loading state
                    saveButton.prop('disabled', false);
                    saveLoader.addClass('d-none');
                    saveButtonText.removeClass('d-none');

                    // Show success toast
                    Toastify({
                        text: "Address added successfully!",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28a745",
                        close: true,
                        callback: function() {
                            $('#addNewAddressModal').modal('hide');
                            $form[0].reset();
                            window.location.hash = '#tab3';
                            location.reload();
                        }
                    }).showToast();
                },
                error: function (xhr) {
                    // Reset loading state
                    saveButton.prop('disabled', false);
                    saveLoader.addClass('d-none');
                    saveButtonText.removeClass('d-none');

                    if (xhr.status === 422) {
                        // Handle validation errors
                        const errors = xhr.responseJSON.errors;

                        // Show individual field errors
                        $.each(errors, function (field, messages) {
                            const errorField = $(`#${field}-error`);
                            const inputField = $(`[name="${field}"]`);

                            if (errorField.length && inputField.length) {
                                inputField.addClass('is-invalid');
                                errorField.text(messages[0]).show();
                            }
                        });

                        // Show general error toast
                        Toastify({
                            text: "Please fix the validation errors",
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#dc3545",
                            close: true
                        }).showToast();
                    } else {
                        // Show general error toast
                        Toastify({
                            text: xhr.responseJSON?.message || "An error occurred while saving the address",
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#dc3545",
                            close: true
                        }).showToast();
                    }
                }
            });
        });

        // Clear validation when modal is closed
        $('#addNewAddressModal').on('hidden.bs.modal', function () {
            $('.invalid-feedback').text('').hide();
            $('.form-control, .form-select').removeClass('is-invalid');
            $('#addAddressForm')[0].reset();
        });
    });



    handleAjaxFormSubmit('.delete-design-form', {
        successMessage: 'design deleted successfully!',
    });







</script>
@endsection
