<div id="step-2" class="step" style="display: none;">
        <h5 class="mb-2 fs-3 text-black">2. Select Products</h5>
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
                <i data-feather="search"></i>
            </span>
            <input type="text" class="form-control border-start-0 border-end-0" placeholder="Search for products">
            <span class="input-group-text bg-white border-start-0">
            </span>
        </div>
        <div id="product-filters-wrapper" class="border shadow rounded-2 p-1" style="display: none;">
            <h6 class="mt-1">Category</h6>
            <div class="mb-1" id="category-filters">
                <span class="badge rounded-pill bg-light text-dark me-1 category-pill">Electronics</span>
                <span class="badge rounded-pill bg-primary text-white me-1 category-pill">Shoes</span>
            </div>

            <hr>

            <h6>Tags</h6>
            <div class="mb-1" id="tag-filters">
                <span class="badge rounded-pill bg-light text-dark me-1 tag-pill">New</span>
                <span class="badge rounded-pill bg-primary text-white me-1 tag-pill">Sale</span>
            </div>

            <hr>

            <div id="product-results">
                <div class="d-flex align-items-center mb-1">
                    <img src="{{ asset('images/banner/banner-1.jpg') }}" class="mx-1 rounded" width="48" height="48" alt="Product Image">
                    <div>
                        <div class="fw-bold">Nike Air Max</div>
                        <div class="text-muted small">High-performance running shoes for all surfaces.</div>
                    </div>
                </div>
            </div>
        </div>


        <div class="d-flex justify-content-end mt-2">
            <button class="btn btn-outline-secondary me-1" data-prev-step>Back</button>
            <button class="btn btn-primary" data-next-step>Next</button>
        </div>
    </div>