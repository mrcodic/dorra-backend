@extends('layouts/contentLayoutMaster')

@section('title', 'Add Order')
@section('main-page', 'Orders')
@section('sub-page', 'Add New Order')

@section('vendor-style')
<!-- Vendor CSS Files -->
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">

<style>
    .pill-selected {
        background-color: #24B094 !important;
        color: white !important;
    }

    .category-pill.pill-selected {
        background-color: #24B094 !important;
        color: white !important;
    }

    .category-pill,
    .tag-pill {
        cursor: pointer;
    }
</style>

@endsection

@section('content')
<div class="container bg-white rounded-3 " style="min-height: 100vh;padding-top:60px;padding-left:146px;padding-right:146px">

    <!-- Step 1 -->

    @include('dashboard.orders.steps.step1')

    <!-- Step 2 -->
    @include('dashboard.orders.steps.step2')

    <!-- Step 3 -->
    @include('dashboard.orders.steps.step3')
    <!-- Step 4 -->
    @include('dashboard.orders.steps.step4')
    <!-- Step 5 -->
    @include('dashboard.orders.steps.step5')

    <!-- Step 6 -->
    @include('dashboard.orders.steps.step6')
    <!-- Step 7 -->
    @include('dashboard.orders.steps.step7')
    <!-- Step 8 -->
    @include('dashboard.orders.steps.step8')

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
    document.addEventListener("DOMContentLoaded", function() {
        feather.replace();

        let currentStep = 1;
        const steps = document.querySelectorAll(".step");

        function showStep(stepNumber) {
            steps.forEach((step, index) => {
                step.style.display = (index + 1 === stepNumber) ? 'block' : 'none';
            });
            currentStep = stepNumber;
            updateProgressBar(); // optional
        }

        function updateProgressBar() {
            const progress = document.querySelector('.progress-bar');
            if (progress) {
                const percentage = (currentStep / steps.length) * 100;
                progress.style.width = `${percentage}%`;
            }
        }

        document.querySelectorAll('[data-next-step]').forEach(button => {
            button.addEventListener("click", function() {

                showStep(currentStep + 1);

            });
        });

        document.querySelectorAll('[data-prev-step]').forEach(button => {
            button.addEventListener("click", function() {
                showStep(currentStep - 1);
            });
        });

        showStep(currentStep); // initialize

        // Show customer results when typing
        const customerInput = document.querySelector('#step-1 input');
        const customerResults = document.getElementById('customer-results-wrapper');
        customerInput.addEventListener('input', function() {
            customerResults.style.display = this.value.trim() ? 'block' : 'none';
        });

        // Show product filters/results when typing
        const productInput = document.querySelector('#step-2 input');
        const productFilters = document.getElementById('product-filters-wrapper');
        productInput.addEventListener('input', function() {
            productFilters.style.display = this.value.trim() ? 'block' : 'none';
        });


    });
</script>




@endsection
