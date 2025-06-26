<div id="step-7" class="step">
    <h5 class="mb-2 fs-3 text-black">Order Confirmation</h5>

    <!-- Wrapper for confirmation sections -->
    <div class="d-flex flex-column">

        <!-- Section: Customer Info -->
        <div class="mb-2 p-1 rounded" style="background-color: #F9FDFC;">
            <h4 class="fw-bold mb-2 text-black">Personal Information</h4>
            <p class="my-1"><span class="fs-4 text-dark">Name:</span>
                <span id="confirm-customer-name" class="fs-16 text-black">
                    @isset($orderData['personal_info']['first_name'])
                        {{ $orderData['personal_info']['first_name'] }}
                        {{ $orderData['personal_info']['last_name'] ?? '' }}
                    @else
                        Not specified
                    @endisset
                </span>
            </p>
            <p class="my-1"><span class="fs-4 text-dark">Email:</span>
                <span id="confirm-customer-email" class="fs-16 text-black">
                    {{ $orderData['personal_info']['email'] ?? 'Not specified' }}
                </span>
            </p>
            <p class="my-1"><span class="fs-4 text-dark">Phone Number:</span>
                <span id="confirm-customer-phone" class="fs-16 text-black">
                    {{ $orderData['personal_info']['phone_number'] ?? 'Not specified' }}
                </span>
            </p>
        </div>

        <!-- Section: Shipping Information -->
        @if(!empty($orderData['shipping_info']))
            <div class="mb-2 p-1 rounded" style="background-color: #F9FDFC;">
                <h4 class="fw-bold mb-2 text-black">Shipping Information</h4>

                <p class="my-1">
                    <span class="fs-4 text-dark">Estimated Delivery Time:</span>
                    <span class="fs-16 text-black">
                {{ $orderData['shipping_info']['estimated_delivery'] ?? 'Not specified' }}
            </span>
                </p>

                <p class="my-1">
                    <span class="fs-4 text-dark">Shipping Address:</span>
                    <span class="fs-16 text-black">
                {{ $orderData['shipping_info']['line'] ?? '' }}
                        {{ isset($orderData['shipping_info']['label']) ? ', ' . $orderData['shipping_info']['label'] : '' }}
                        {{ isset($orderData['shipping_info']['state']) ? ', ' . $orderData['shipping_info']['state'] : '' }}
                        {{ isset($orderData['shipping_info']['country']) ? ', ' . $orderData['shipping_info']['country'] : '' }}
            </span>
                </p>

                <div class="my-1 d-flex align-items-start">
                    <p class="fs-4 text-dark" style="width:30%;">Delivery Instructions:</p>
                    <p class="fs-16 text-black">
                        {{ $orderData['shipping_info']['instructions'] ?? 'No special instructions' }}
                    </p>
                </div>
            </div>

        @elseif(!empty($orderData['pickup_info']))
            <div class="mb-2 p-1 rounded" style="background-color: #F9FDFC;">
                <h4 class="fw-bold mb-2 text-black">Pickup Information</h4>

                <p class="my-1">
                    <span class="fs-4 text-dark">Pickup Location:</span>
                    <span class="fs-16 text-black">
                        {{ $orderData['pickup_info']['location_name'] ?? '' }}
                    </span>
                </p>

                <p class="my-1">
                    <span class="fs-4 text-dark">Address:</span>
                    <span class="fs-16 text-black">
                        {{ $orderData['pickup_info']['line'] ?? '' }}
                        {{ isset($orderData['pickup_info']['state']) ? ', ' . $orderData['pickup_info']['state'] : '' }}
                        {{ isset($orderData['pickup_info']['country']) ? ', ' . $orderData['pickup_info']['country'] : '' }}
                    </span>
                </p>
            </div>
        @else
            <p class="text-muted">No shipping or pickup information available.</p>
        @endif


        <!-- Section: Selected Items -->
        <div class="mb-2 p-1 rounded" style="background-color: #F9FDFC;">
            <h4 class="fw-bold mb-2 text-black">Items</h4>
            <div class="">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="d-flex">
                        <img src="{{ $orderData['design_info']['design_image'] ?? asset('images/default-photo.png') }}"
                             class="me-2 rounded"
                             alt="Product" style="width: 60px; height: 60px;">
                        <div>
                            <div class="fw-bold text-black fs-16">
                                {{ $orderData['product_name'] ?? 'Unknown product' }}
                            </div>
                            <div class="text-dark fs-5">
                                Qty: {{ $orderData['pricing_details']['quantity'] ?? 1 }}
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-black">
                            ${{ number_format($orderData['pricing_details']['total'] ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Pricing Details -->
        <div class="mb-0 p-1 rounded" style="background-color: #F9FDFC;">
            <h4 class="fw-bold mb-2 text-black">Pricing Details</h4>
            <div class="d-flex justify-content-between mb-1">
                <span class="text-dark fs-16 fw-bold">Subtotal</span>
                <span class="fs-4 text-black fw-bold">
                    ${{ number_format($orderData['pricing_details']['sub_total'] ?? 0, 2) }}
                </span>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span class="text-dark fs-16 fw-bold">Discount</span>
                <span class="fs-16 text-black">
                    ${{ number_format($orderData['pricing_details']['discount'] ?? 0, 2) }}
                </span>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span class="text-dark fs-16 fw-bold">
                    Delivery
                    <i data-feather="info" data-bs-toggle="tooltip"
                       title="Delivery charges may vary based on location."></i>
                </span>
                <span class="fs-16 text-black">
                    ${{ number_format($orderData['pricing_details']['delivery'] ?? 0, 2) }}
                </span>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span class="text-dark fs-16 fw-bold">
                    Tax
                    <i data-feather="info" data-bs-toggle="tooltip"
                       title="Tax is calculated as per applicable laws."></i>
                </span>
                <span class="fs-16 text-black">
                    ${{ number_format($orderData['pricing_details']['tax'] ?? 0, 2) }}
                </span>
            </div>

            <hr class="border-dashed my-1">

            <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                <span class="fs-4 text-black">Total</span>
                <span class="fs-4 text-black fw-bold">
                    ${{ number_format($orderData['pricing_details']['total'] ?? 0, 2) }}
                </span>
            </div>
        </div>


    </div>


</div>
