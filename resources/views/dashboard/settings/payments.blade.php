@extends('layouts/contentLayoutMaster')

@section('title', 'Settings-Payment')
@section('main-page', 'Payment')

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

@section('content')
<div class="card p-3">
    @php
    // Define label + icons for each row
    $paymentRows = [
    ['label' => 'Aman', 'icons' => ['aman.png']],
    ['label' => 'Credit/Debit Card', 'icons' => ['Visa.png', 'mastercard.png']],
    ['label' => 'Paypal', 'icons' => ['Paypal.png']],
    ['label' => 'Cash on delivery (COD)', 'icons' => ['cash.png']],
    ];
    @endphp

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
            <button class="btn btn-outline-secondary me-1">Discard Changes</button>
            <button class="btn btn-primary">Save</button>
        </div>
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
    const productsDataUrl = "{{ route('products.data') }}";
    const productsCreateUrl = "{{ route('products.create') }}";
</script>









{{-- Page js files --}}
<script src="{{ asset('js/scripts/pages/app-product-list.js') }}?v={{ time() }}"></script>
@endsection
