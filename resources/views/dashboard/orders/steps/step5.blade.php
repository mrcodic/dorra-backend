<div id="step-5" class="step" style="display: none;">
    <h5 class="mb-2 fs-3 text-black">Personal Information</h5>

    <div>
        <input type="hidden" id="edit-tag-id">

        <!-- Shipping Method Selection -->
        <div class="mb-3" id="shippingMethodSection">
            <label class="form-label fw-bold fs-5 mb-2">Shipping Method</label>
            <div class="d-flex gap-2 ">
                <div class="col-6 form-check border rounded-3 p-1 px-3 flex-fill">
                    <input class="form-check-input" type="radio" name="shipping_method" id="shipToCustomer" value="ship" checked>
                    <label class="form-check-label fs-4 text-black" for="shipToCustomer">
                        Ship to customer
                    </label>
                </div>
                <div class="col-6 form-check border rounded-3 p-1 px-3 flex-fill">
                    <input class="form-check-input" type="radio" name="shipping_method" id="pickUp" value="pickup">
                    <label class="form-check-label fs-4 text-black" for="pickUp">
                    Pick up
                    </label>
                </div>
            </div>
        </div>

        <!-- Ship to Customer Section -->
        <div id="shipSection">
            <!-- Existing Addresses -->
            <div class="d-flex gap-2 ">
                <div class="col-6 form-check border rounded-3 p-1 px-3 flex-fill">
                    <input class="form-check-input" type="radio" name="address_id" id="address1" value="1">
                    <label class="form-check-label fs-4 text-black" for="shipToCustomer">

                        <p>Home</p>
                        <p class="text-dark fs-16">15 street name, neighborhood</p>
                    </label>
                </div>
                <div class="col-6 form-check border rounded-3 p-1 px-3 flex-fill">
                    <input class="form-check-input" type="radio" name="address_id" id="address2" value="2">
                    <label class="form-check-label fs-4 text-black" for="pickUp">
                        <p>Office</p>
                        <p class="text-dark fs-16">15 street name, neighborhood</p>
                    </label>
                </div>
            </div>

            <!-- Divider -->
            <div class="text-center my-3 fw-bold">OR</div>

            <!-- Add New Address -->
            <div class="">
                <h5 class="mb-2 text-black fs-4">Add new address</h5>
                <div class="mb-2">
                    <label class="form-label ">Address Label</label>
                    <select class="form-select">
                        <option>Home</option>
                        <option>Work</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col">
                        <label class="form-label ">Country</label>
                        <input type="text" class="form-control">
                    </div>
                    <div class="col">
                        <label class="form-label ">State</label>
                        <input type="text" class="form-control">
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Address Line</label>
                    <input type="text" class="form-control">
                </div>
                <div class="mb-2">
                    <label class="form-label">Delivery Instructions</label>
                    <textarea class="form-control" rows="2"></textarea>
                </div>
            </div>
        </div>

        <!-- Pick Up Section -->
        <div id="pickupSection" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="w-50 fs-4 text-black">
            To proceed to checkout, choose a pick up location.
            This will help us deliver your order to the preferred  location.
            </div>
                <button type="button" class="btn btn-outline-secondary fs-16"  data-bs-toggle="modal" data-bs-target="#selectLocationModal"> <i data-feather="map-pin"></i> Select pick up location</button>
            </div>


            <div class="mb-2">
                <label class="form-label fw-bold fs-3">Who's picking up the package?</label>
            </div>
            <div class="row g-2 mb-2">
                <div class="col">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control">
                </div>
                <div class="col">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control">
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label">Email</label>
                <input type="email" class="form-control">
            </div>
            <div class="mb-2">
                <label class="form-label">Phone Number</label>
                <input type="tel" class="form-control">
            </div>
        </div>

        <!-- Change Location Section -->
        <div id="changeLocationSection" style="display: none;">
            <div class="d-flex align-items-center gap-1 mb-3">
                <i data-feather="chevron-left" class="cursor-pointer" id="backToPickup" style="cursor: pointer;"></i>
                <h5 class="fs-4 text-black mb-0">Change Pick up Location</h5>
            </div>

            <div class="mb-3">
                <input type="text" class="form-control" placeholder="Search for a location..." id="locationSearch">
            </div>

            <div id="mapPlaceholder" style="height: 300px; background-color: #f0f0f0; border-radius: 8px;">
                <p class="text-center text-muted pt-5">Map will display here based on search</p>
            </div>
        </div>

    </div>



    <div class="d-flex justify-content-end mt-2">
        <button class="btn btn-outline-secondary me-1" data-prev-step>Back</button>
        <button class="btn btn-primary" data-next-step>Next</button>
    </div>

    @include('modals/select-location')
</div>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        feather.replace();
        const shipRadio = document.getElementById("shipToCustomer");
        const pickupRadio = document.getElementById("pickUp");
        const shipSection = document.getElementById("shipSection");
        const pickupSection = document.getElementById("pickupSection");

        function toggleSections() {
            if (shipRadio.checked) {
                shipSection.style.display = "block";
                pickupSection.style.display = "none";
            } else {
                shipSection.style.display = "none";
                pickupSection.style.display = "block";
            }
        }

        shipRadio.addEventListener("change", toggleSections);
        pickupRadio.addEventListener("change", toggleSections);
        toggleSections();
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const shipRadio = document.getElementById("shipToCustomer");
        const pickupRadio = document.getElementById("pickUp");
        const shipSection = document.getElementById("shipSection");
        const pickupSection = document.getElementById("pickupSection");
        const changeLocationSection = document.getElementById("changeLocationSection");
        const shippingMethodSection = document.getElementById("shippingMethodSection");

        const changeLocationBtn = pickupSection.querySelector(".lined-btn");
        const backToPickupBtn = document.getElementById("backToPickup");

        function toggleSections() {
            if (shipRadio.checked) {
                shipSection.style.display = "block";
                pickupSection.style.display = "none";
                changeLocationSection.style.display = "none";
                shippingMethodSection.style.display = "block";
            } else {
                shipSection.style.display = "none";
                pickupSection.style.display = "block";
                changeLocationSection.style.display = "none";
                shippingMethodSection.style.display = "block";
            }
        }

        shipRadio.addEventListener("change", toggleSections);
        pickupRadio.addEventListener("change", toggleSections);
        toggleSections();

        changeLocationBtn.addEventListener("click", function() {
            pickupSection.style.display = "none";
            changeLocationSection.style.display = "block";
            shippingMethodSection.style.display = "none";
        });

        backToPickupBtn.addEventListener("click", function() {
            changeLocationSection.style.display = "none";
            pickupSection.style.display = "block";
            shippingMethodSection.style.display = "block";
        });

        // Initialize feather icons
        feather.replace();
    });
</script>
