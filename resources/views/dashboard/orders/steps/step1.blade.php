<div id="step-1" class="step">
        <h5 class="mb-2 fs-3 text-black">1. Select Customer</h5>
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
                <i data-feather="search"></i>
            </span>
            <input type="text" class="form-control border-start-0 border-end-0" placeholder="Search for a customer">

            <span class="input-group-text bg-white border-start-0">
            </span>
        </div>
        <div id="customer-results-wrapper" class="border shadow rounded-2 p-1" style="display: none;">
            <div class="mt-2" id="customer-results">
                <!-- Example Result -->
                <div class="d-flex align-items-center mb-1">
                    <img src="{{ asset('images/banner/banner-1.jpg') }}" class="rounded-circle mx-1" width="40" height="40" alt="Customer Avatar">
                    <span class="fw-bold">John Doe</span>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-2">
            <button class="btn btn-primary fs-5" data-next-step>Next</button>
        </div>
    </div>