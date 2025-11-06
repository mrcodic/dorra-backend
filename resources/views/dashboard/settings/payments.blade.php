@extends('layouts/contentLayoutMaster')

@section('title', 'Settings-Payment')
@section('main-page', 'Payment & Shipping')

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
@endsection
@php
    // Define label + icons for each row
    $paymentRows = [
    ['label' => 'Aman', 'icons' => ['aman.png']],
    ['label' => 'Credit/Debit Card', 'icons' => ['Visa.png', 'mastercard.png']],
    ['label' => 'Paypal', 'icons' => ['Paypal.png']],
    ['label' => 'Cash on delivery (COD)', 'icons' => ['cash.png']],
    ];
@endphp

@section('content')
    <div class="card p-2">

        {{-- Payment header --}}
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h4 class="mb-0">Payment</h4>
        </div>

        @foreach ($paymentMethods as $paymentMethod)
            <form action="{{ route('toggle-payment-methods', $paymentMethod->id) }}"
                  method="POST"
                  class="toggle-payment-form"
                  id="toggle-payment-form-{{ $paymentMethod->id }}">
                @csrf

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ asset('images/' . strtolower($paymentMethod->code)) . '.png' }}"
                             alt="Payment Icon" style="height:40px;width:auto;">
                        <span class="fw-bold text-black">{{ $paymentMethod->name }}</span>
                    </div>

                    <div class="form-check form-switch">
                        {{-- ensure 0 is sent when unchecked --}}
                        <input type="hidden" name="active" value="0">
                        <input name="active"
                               class="form-check-input payment-toggle"
                               type="checkbox"
                               value="1"
                               @checked($paymentMethod->active)
                               data-form="#toggle-payment-form-{{ $paymentMethod->id }}">
                        <label class="form-check-label"></label>
                    </div>
                </div>
            </form>
        @endforeach
        {{-- remove the shared Save/Discard for payments; each row submits itself --}}

        {{-- Divider between Payment and Shipping --}}
        <hr class="my-3"/>
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h4 class="mb-0">Shipping</h4>
        </div>

        <form action="{{ route('landing-sections.update') }}"
              method="POST"
              id="shippingForm"
              class="shipping-toggle-form">
            @csrf
            @method('PUT')

            {{-- What setting to toggle --}}
            <input type="hidden" name="key" value="shipping_visibility">
            {{-- Single source of truth submitted to backend --}}
            <input type="hidden" name="value" id="shipping-value"
                   value="{{ setting('shipping_visibility') ? 1 : 0 }}">

            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('images/shipping.png') }}" alt="Shipping" style="height:40px;width:auto;">
                    <span class="fw-bold text-black">Enable Shipping at Checkout</span>
                </div>
                <div class="form-check form-switch">
                    {{-- keep checkbox UNNAMED; JS writes hidden value before submit --}}
                    <input
                        class="form-check-input shipping-toggle"
                        type="checkbox"
                        id="shipping-enabled"
                        @checked(setting('shipping_visibility'))
                    >
                    <label class="form-check-label" for="shipping-enabled"></label>
                </div>
            </div>
        </form>

    </div>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        // AJAX handler for ALL payment forms
        handleAjaxFormSubmit(".toggle-payment-form", {
            successMessage: "Payment method updated",
            onSuccess: function () {
                // optional: reload to reflect server truth
                location.reload();
            },
            resetForm : false,
        });

        // Auto-submit the specific form when its switch changes
        document.querySelectorAll(".payment-toggle").forEach((el) => {
            el.addEventListener("change", function () {
                const form = this.closest("form");
                if (form) form.requestSubmit(); // native submit (keeps CSRF etc.)
            });
        });
    </script>

    <script>
        // AJAX for the shipping form
        handleAjaxFormSubmit(".shipping-toggle-form", {
            successMessage: "Shipping setting updated",
            resetForm: false,
            onSuccess: function () {
                location.reload();
            },

        });

        // When user toggles, write the value and submit
        (function () {
            const form   = document.getElementById('shippingForm');
            const toggle = document.getElementById('shipping-enabled');
            const hidden = document.getElementById('shipping-value');

            if (!form || !toggle || !hidden) return;

            // prevent double submits
            let inFlight = false;

            toggle.addEventListener('change', function () {
                if (inFlight) return;
                hidden.value = this.checked ? 1 : 0;
                inFlight = true;
                form.requestSubmit();
                // small cooldown; your handleAjaxFormSubmit will clear quickly
                setTimeout(() => { inFlight = false; }, 800);
            });
        })();
    </script>









    {{-- Page js files --}}
    <script src="{{ asset('js/scripts/pages/app-product-list.js') }}?v={{ time() }}"></script>
@endsection
