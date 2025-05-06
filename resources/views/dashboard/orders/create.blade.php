@extends('layouts/contentLayoutMaster')

@section('title', 'Add Order')
@section('main-page', 'Orders')
@section('sub-page', 'Add New Order')

@section('vendor-style')
<!-- Vendor CSS Files -->
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')
<div class="container bg-white rounded-3 " style="min-height: 100vh;padding-top:160px;padding-left:146px;padding-right:146px">
    <!-- Step 1 -->
    <!-- Step 1 -->
    <div id="step-1">
        <h5 class="mb-2 fs-3 text-black">1. Select Customer</h5>
        <div class="input-group mb-2">
            <span class="input-group-text bg-white border-end-0">
                <i data-feather="search"></i>
            </span>
            <input type="text" class="form-control border-start-0 border-end-0" placeholder="Search for a customer">
            <span class="input-group-text bg-white border-start-0">
                <i data-feather="filter"></i>
            </span>
        </div>
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary fs-5" id="next-to-step-2">Next</button>
        </div>
    </div>


    <!-- Step 2 -->
    <div id="step-2" style="display: none;">
    <h5 class="mb-2 fs-3 text-black">2. Select Products</h5>
        <div class="input-group mb-2">
            <span class="input-group-text bg-white border-end-0">
                <i data-feather="search"></i>
            </span>
            <input type="text" class="form-control border-start-0 border-end-0" placeholder="Search for products">
            <span class="input-group-text bg-white border-start-0">
                <i data-feather="filter"></i>
            </span>
        </div>
        <div class="d-flex justify-content-end">
            <button class="btn btn-outline-secondary me-1" id="back-to-step-1">Back</button>
            <button class="btn btn-primary">Next</button>
        </div>
    </div>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();

        document.getElementById('next-to-step-2').addEventListener('click', function() {
            document.getElementById('step-1').style.display = 'none';
            document.getElementById('step-2').style.display = 'block';
        });

        document.getElementById('back-to-step-1').addEventListener('click', function() {
            document.getElementById('step-2').style.display = 'none';
            document.getElementById('step-1').style.display = 'block';
        });
    });
</script>


@endsection