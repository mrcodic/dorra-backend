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
    @php
        use Illuminate\Support\Facades\Cache;

        $orderData = Cache::get(getOrderStepCacheKey()) ?? [];
        $pricingSubtotal = $orderData['pricing_details']['sub_total'] ?? 0;
        $quantity = $orderData['pricing_details']['quantity'] ?? 0;
        $tax = number_format($pricingSubtotal * 0.1, 2);
        $deliveryFee = isset($orderData['delivery_fee']) ? number_format($orderData['delivery_fee'], 2) : '30.00';
        $discountAmount = isset($orderData['discount_amount']) ? number_format($orderData['discount_amount'], 2) : '0.00';
        $total = isset($orderData['total']) ? number_format($orderData['total'], 2) : number_format($pricingSubtotal + 30 + ($pricingSubtotal * 0.1), 2);
    @endphp
    <div class="container bg-white rounded-3 "
         style="min-height: 100vh;padding-top:60px;padding-left:146px;padding-right:146px">

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
        document.addEventListener("DOMContentLoaded", function () {
            feather.replace();

            // Read the step from the URL
            const urlParams = new URLSearchParams(window.location.search);
            let currentStep = parseInt(urlParams.get('step')) || 1;
            if (currentStep < 1) currentStep = 1;
            if (currentStep > 8) currentStep = 8;

            const steps = document.querySelectorAll(".step");

            // Function to show a specific step and update the URL
            function showStep(stepNumber) {
                if (stepNumber < 1 || stepNumber > 8) return;
                steps.forEach((step, index) => {
                    step.style.display = (index + 1 === stepNumber) ? 'block' : 'none';
                });
                currentStep = stepNumber;
                updateProgressBar();
                console.log(stepNumber)
                history.pushState({}, '', `?step=${stepNumber}`);
            }

            // Function to update the progress bar (if present)
            function updateProgressBar() {
                const progress = document.querySelector('.progress-bar');
                if (progress) {
                    const percentage = (currentStep / steps.length) * 100;
                    progress.style.width = `${percentage}%`;
                }
            }

            // Handle next button clicks
            document.querySelectorAll('[data-next-step]').forEach(button => {
                button.addEventListener("click", function () {
                    if (currentStep < 8) {
                        showStep(currentStep + 1);
                    }
                });
            });

            // Handle previous button clicks
            document.querySelectorAll('[data-prev-step]').forEach(button => {
                button.addEventListener("click", function () {
                    if (currentStep > 1) {
                        showStep(currentStep - 1);
                    }
                });
            });

            // Initialize with the step from the URL
            showStep(currentStep);

            // Handle browser back/forward navigation
            window.addEventListener('popstate', function (event) {
                const urlParams = new URLSearchParams(window.location.search);
                const step = parseInt(urlParams.get('step')) || 1;
                if (step >= 1 && step <= 8) {
                    showStep(step);
                }
            });

            // Rest of your code (e.g., customer and product input handling)
            const customerInput = document.querySelector('#step-1 input');
            const customerResults = document.getElementById('customer-results-wrapper');
            customerInput.addEventListener('input', function () {
                customerResults.style.display = this.value.trim() ? 'block' : 'none';
            });

            const productInput = document.querySelector('#step-2 input');
            const productFilters = document.getElementById('product-filters-wrapper');
            productInput.addEventListener('input', function () {
                productFilters.style.display = this.value.trim() ? 'block' : 'none';
            });
        });
    </script>
<script>
      $(document).ready(function () {
          handleAjaxFormSubmit(".order-form", {
            successMessage: "Order created successfully!",
            onSuccess: function (response, $form) {
                window.location.href = "{{ route('orders.index') }}";
        }});
    });



</script>
@endsection
