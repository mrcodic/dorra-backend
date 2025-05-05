@extends('layouts/contentLayoutMaster')
@section('title', 'Edit orderss')
@section('main-page', 'Orders')
@section('sub-page', 'Edit Orders')

@section('vendor-style')
<!-- Vendor CSS Files -->
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between">
    <div><span>Order Number:</span><span>#1234567</span></div>
    <div class="d-flex align-items-center status-pill justify-content-center">
        <div class="status-icon me-1">
            <i data-feather="box"></i> <!-- Change icon based on status -->
        </div>
        <span class="status-text">Placed</span>
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


@endsection