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

        @php
            // Define label + icons for each row
            $paymentRows = [
              ['label' => 'Aman', 'icons' => ['aman.png']],
              ['label' => 'Credit/Debit Card', 'icons' => ['Visa.png', 'mastercard.png']],
              ['label' => 'Paypal', 'icons' => ['Paypal.png']],
              ['label' => 'Cash on delivery (COD)', 'icons' => ['cash.png']],
            ];
        @endphp

        {{-- Payment toggles --}}
        @foreach ($paymentRows as $index => $row)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-3">
                    @foreach ($row['icons'] as $icon)
                        <img src="{{ asset('images/' . $icon) }}" alt="Payment Icon" style="height: 40px; width: auto;">
                    @endforeach
                    <span class="fw-bold text-black">{{ $row['label'] }}</span>
                </div>
                <div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="switch-{{ $index }}">
                        <label class="form-check-label" for="switch-{{ $index }}"></label>
                    </div>
                </div>
            </div>

        @endforeach
        <div class="d-flex justify-content-end mt-2">
            <button class="btn btn-outline-secondary me-1" type="reset">Discard Changes</button>
            <button class="btn btn-primary">Save</button>
        </div>
        {{-- Divider between Payment and Shipping --}}
        <hr class="my-3" />
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h4 class="mb-0">Shipping</h4>
        </div>
        <form action="{{ route('landing-sections.update') }}" method="post" id="shippingForm">
            @csrf
            @method('PUT')

            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('images/shipping.png') }}" alt="Shipping" style="height: 40px; width: auto;">
                    <span class="fw-bold text-black">Enable Shipping at Checkout</span>
                </div>
                <div class="form-check form-switch">
                    {{-- NOTE: checkbox has NO name; controller ignores its value --}}
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="shipping-enabled"
                        data-initial="{{ setting('shipping_visibility') ? '1' : '0' }}"
                        @checked(setting('shipping_visibility'))
                    >
                    <label class="form-check-label" for="shipping-enabled"></label>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-2">
                <button type="reset" class="btn btn-outline-secondary me-1"  id="discardBtn">Discard Changes</button>
                <button class="btn btn-primary" type="submit" id="saveBtn" disabled>Save</button>
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
 handleAjaxFormSubmit("#shippingForm",{
     successMessage:"Request completed successfully",
     onSuccess: function () {
         location.reload()
     }
 })
</script>
<script>
    (function () {
        const form = document.getElementById('shippingForm');
        const toggle = document.getElementById('shipping-enabled');
        const saveBtn = document.getElementById('saveBtn');
        const discardBtn = document.getElementById('discardBtn');
        const initial = toggle.dataset.initial === '1';
        const KEY_NAME = 'key';
        const KEY_VALUE = 'shipping_visibility';

        function ensureKeyInput(present) {
            let hidden = form.querySelector(`input[name="${KEY_NAME}"]`);
            if (present) {
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = KEY_NAME;
                    hidden.value = KEY_VALUE;
                    form.appendChild(hidden);
                }
            } else if (hidden) {
                hidden.remove();
            }
        }

        function syncUI() {
            const changed = (toggle.checked !== initial);
            saveBtn.disabled = !changed;
            ensureKeyInput(changed); // only send `key` when the user actually changed the toggle
        }

        // init
        syncUI();

        toggle.addEventListener('change', syncUI);

        // prevent accidental submit with no changes (would 404 in your controller)
        form.addEventListener('submit', function (e) {
            if (saveBtn.disabled) {
                e.preventDefault();
                if (window.Swal) {
                    Swal.fire({ icon: 'info', title: 'No changes to save' });
                }
            }
        });

        // discard -> reset to initial state
        discardBtn?.addEventListener('click', function () {
            toggle.checked = initial;
            syncUI();
        });
    })();
</script>








{{-- Page js files --}}
<script src="{{ asset('js/scripts/pages/app-product-list.js') }}?v={{ time() }}"></script>
@endsection
