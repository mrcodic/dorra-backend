<div id="step-7" class="step" style="display: none;">
    <h5 class="mb-2 fs-3 text-black">Order Confirmation</h5>

    <!-- Wrapper for confirmation sections -->
    <div class=" d-flex flex-column">

        <!-- Section: Customer Info -->
        <div class="mb-2 p-1 rounded" style="background-color: #F9FDFC;">
            <h4 class="fw-bold mb-2 text-black">Personal Information</h4>
            <p class="my-1"><span class="fs-4 text-dark">Name:</span> <span id="confirm-customer-name"
                    class="fs-16 text-black">John Doe</span></p>
            <p class="my-1"><span class="fs-4 text-dark">Email:</span> <span id="confirm-customer-email"
                    class="fs-16 text-black">john@example.com</span></p>
            <p class="my-1"><span class="fs-4 text-dark">Phone Number:</span> <span id="confirm-customer-email"
                    class="fs-16 text-black">+20 0123 1234 5678</span></p>
        </div>

        <!-- Section: Shipping Information -->
        <div class="mb-2 p-1 rounded" style="background-color: #F9FDFC;">
            <h4 class="fw-bold mb-2 text-black">Shipping Information</h4>
            <p class="my-1"><span class="fs-4 text-dark">Estimated Delivery Time:</span> <span
                    id="confirm-customer-email" class="fs-16 text-black">Monday, 23 Sep 2024</span></p>
            <p class="my-1"><span class="fs-4 text-dark">Shipping Address:</span> <span id="confirm-customer-email"
                    class="fs-16 text-black">15 street name, neighborhood, Cairo, Egypt</span></p>
            <div class="my-1 d-flex align-items-start">
                <p class="fs-4 text-dark" style="width:30%;">Delivery Instructions:</p>
                <p id="confirm-customer-email" class="fs-16 text-black">Lorem ipsum dolor sit amet, consectetur
                    adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
            </div>
        </div>

        <!-- Section: Selected Items -->
        <div class="mb-2 p-1 rounded" style="background-color: #F9FDFC;">
            <h4 class="fw-bold mb-2 text-black">Items</h4>
            <div class="">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="d-flex">
                        <img src="{{ asset('images/banner/banner-1.jpg') }}" class="me-2 rounded" alt="Product"
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
            </div>

        </div>

        <!-- Section: Pricing Details -->
        <div class="mb-0 p-1 rounded" style="background-color: #F9FDFC;">
            <h4 class="fw-bold mb-2 text-black">Pricing Details</h4>
            <div class="d-flex justify-content-between mb-1">
                <span class="text-dark fs-16 fw-bold">Subtotal</span>
                <span class="fs-4 text-black fw-bold">$65.00</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span class="text-dark fs-16 fw-bold">Discount</span>
                <span class="fs-16 text-black">-$5.00</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span class="text-dark fs-16 fw-bold">
                    Delivery
                    <i data-feather="info" data-bs-toggle="tooltip"
                        title="Delivery charges may vary based on location."></i>
                </span>
                <span class="fs-16 text-black">$5.00</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span class="text-dark fs-16 fw-bold">
                    Tax
                    <i data-feather="info" data-bs-toggle="tooltip"
                        title="Tax is calculated as per applicable laws."></i>
                </span>
                <span class="fs-16 text-black">$3.00</span>
            </div>

            <hr class="border-dashed my-1">

            <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                <span class="fs-4 text-black ">Total</span>
                <span class="fs-4 text-black fw-bold">$68.00</span>
            </div>

        </div>

        <div class="d-flex justify-content-between mt-4">
            <p class="text-black">Download order summary, this might be helpful for offline records</p>
            <button class="btn btn-primary">Download</button>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <button class="btn btn-outline-secondary me-1" data-prev-step>Back</button>
        <button class="btn btn-primary" data-next-step>Next</button>
    </div>
</div>
