<div id="step-2" class="step" style="display: none;">
    <h5 class="mb-2 fs-3 text-black">2. Select Category</h5>

    <!-- Search Input -->
    <div class="input-group">
        <span class="input-group-text bg-white border-end-0">
            <i data-feather="search"></i>
        </span>
        <input type="text" id="product-search" class="form-control border-start-0 border-end-0"
               placeholder="Search for categories">
        <span class="input-group-text bg-white border-start-0"></span>
    </div>

    <!-- Validation message -->
    <div id="product-warning" class="text-danger mt-1" style="display: none;">
        Please select a product before continuing.
    </div>

    <!-- Filters + Results - Hidden by default -->
    <div id="product-filters-wrapper" class="border shadow rounded-2 p-1 mt-2" style="display:none;">

        <h6 class="mt-1">Product</h6>
        <div class="mb-1" id="product-filters">
            @forelse($associatedData['categories'] as $category)
                <span class="badge rounded-pill bg-light text-dark me-1 category-pill"
                      data-category="{{ $category->id }}">
        {{ $category->name }}
      </span>
            @empty
                <span class="badge rounded-pill bg-light text-dark me-1">No categories found</span>
            @endforelse
        </div>

        <hr>

        <h6>Tags</h6>
        <div class="mb-1" id="tag-filters">
            @forelse($associatedData['tags'] as $tag)
                <span class="badge rounded-pill bg-light text-dark me-1 tag-pill"
                      data-tag="{{ $tag->id }}">
        {{ $tag->name }}
      </span>
            @empty
                <span class="badge rounded-pill bg-light text-dark me-1">No tags found</span>
            @endforelse
        </div>

        <hr>
        <div id="product-results"></div>
    </div>
    <h5 class="mb-2 fs-3 text-black mt-2">Or Select Product</h5>
    <select name="category_id" id="categorySelect" class="form-select">
        <option value="" selected disabled>select Product</option>
        @forelse($associatedData['products_without'] as $product)
        <option value="{{ $product->id }}">{{ $product->name }}</option>
        @empty
            <span class="badge  bg-light text-dark me-1">No products found</span>

        @endforelse
    </select>
    <div class="d-flex justify-content-end mt-2">
        <button class="btn btn-outline-secondary me-1" id="back-step-1">Back</button>
        <button class="btn btn-primary fs-5" id="next-step-2" data-next-step disabled>Next</button>
    </div>
</div>

<script>
    let selectedCategory = null;
    let selectedTags = [];
    let selectedProductId = null;

    function updateProductNextButtonState() {
        $('#next-step-2').prop('disabled', !selectedProductId);
    }

    // Initialize with filters hidden
    $(document).ready(function() {
        $('#product-filters-wrapper').hide();
    });

    // Toggle filters when clicking search input
    $('#product-search').on('click', function(e) {
        e.stopPropagation();
        $('#product-filters-wrapper').slideToggle();
    });

    // Close filters when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#product-filters-wrapper').length &&
            !$(e.target).is('#product-search') &&
            !$(e.target).closest('.input-group').length) {
            $('#product-filters-wrapper').slideUp();
        }
    });

    $('#product-search').on('input', function () {
        selectedProductId = null;
        updateProductNextButtonState();
        filterProducts();
    });

    $(document).on('click', '.category-pill', function () {
        const id = $(this).data('category');
        if (selectedCategory === id) {
            selectedCategory = null;
            $(this).removeClass('pill-selected bg-primary text-white').addClass('bg-light text-dark');
        } else {
            selectedCategory = id;
            $('.category-pill').removeClass('pill-selected bg-primary text-white').addClass('bg-light text-dark');
            $(this).removeClass('bg-light text-dark').addClass('pill-selected bg-primary text-white');
        }
        filterProducts();
    });

    $(document).on('click', '.tag-pill', function () {
        const id = $(this).data('tag');
        if ($(this).hasClass('pill-selected')) {
            selectedTags = selectedTags.filter(tag => tag !== id);
            $(this).removeClass('pill-selected bg-primary text-white').addClass('bg-light text-dark');
        } else {
            selectedTags.push(id);
            $(this).removeClass('bg-light text-dark').addClass('pill-selected bg-primary text-white');
        }
        filterProducts();
    });

    $(document).on('click', '.product-item', function () {
        selectedProductId = $(this).data('id');
        $('#product-search').val($(this).data('name'));
        $('#product-filters-wrapper').slideUp();
        $('.product-item').removeClass('border-primary');
        $(this).addClass('border border-primary');
        $('#product-warning').hide();
        updateProductNextButtonState();
    });

    function filterProducts() {
        const query = $('#product-search').val();
        $.ajax({
            url: "{{ route('products.search') }}",
            method: 'GET',
            data: {
                search: query,
                category_id: selectedCategory,
                tag_ids: selectedTags
            },
            traditional: true,
            success: function (html) {
                $('#product-results').html(html);
            }
        });
    }

    $('#back-step-1').on('click', function () {
        $('#step-2').hide();
        $('#step-1').show();
    });

    $('#next-step-2').on('click', function() {
        if (!selectedProductId) {
            $('#product-warning').show();
            return;
        }

        // Show loading state
        $('#step-3').html('<div class="d-flex justify-content-center align-items-center" style="min-height: 300px;"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        $('#step-2').hide();
        $('#step-3').show();


    });

    $(document).on('click', 'back-step-1', function() {
        $('#step-3').hide();
        $('#step-2').show();
    });

    $(document).on('click', 'next-step-2', function() {
        $('#step-2').hide();
        $('#step-3').show();
    });
    const selectedCategoryId = $('#categorySelect').val();

    $.ajax({
        url: '{{ route("orders.step2") }}',
        method: 'POST',
        data: {
            product_id: selectedProductId,
            category_id: selectedCategoryId,
            _token: '{{ csrf_token() }}'
        },
        success: function (response) {
            // Proceed to next step (handle as needed)
        },
        error: function (xhr) {
            console.error(xhr.responseJSON);
        }
    });
</script>
