<div id="step-2" class="step" style="display: none;">
    <h5 class="mb-2 fs-3 text-black">2. Select Products</h5>

    <!-- Search Input -->
    <div class="input-group">
        <span class="input-group-text bg-white border-end-0">
            <i data-feather="search"></i>
        </span>
        <input type="text" id="product-search" class="form-control border-start-0 border-end-0"
               placeholder="Search for products">
        <span class="input-group-text bg-white border-start-0"></span>
    </div>

    <!-- Validation message -->
    <div id="product-warning" class="text-danger mt-1" style="display: none;">
        Please select a product before continuing.
    </div>

    <!-- Filters + Results -->
    <div id="product-filters-wrapper" class="border shadow rounded-2 p-1 mt-2">
        <h6 class="mt-1">Category</h6>
        <div class="mb-1" id="category-filters">
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

    <div class="d-flex justify-content-end mt-2">
        <button class="btn btn-outline-secondary me-1" id="back-step-1">Back</button>
        <button class="btn btn-primary" id="next-step-2" data-next-step>Next</button>
    </div>
</div>

<script>
    let selectedCategory = null;
    let selectedTags = [];
    let selectedProductId = null;

    $('#product-search').on('input', filterProducts);
    $('#product-search').on('click', () => $('#product-filters-wrapper').slideDown());

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

        // Hide warning when product is selected
        $('#product-warning').hide();
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

    $('#next-step-2').on('click', function () {
        if (!selectedProductId) {
            $('#product-warning').show();
            return;
        }

        $.ajax({
            url: '{{ route("orders.step2") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                product_id: selectedProductId
            },
            success: function (response) {
                console.log('Product submitted successfully:', response);
                // Hide step 2, show next step here
            },
            error: function (xhr) {
                console.error('Error:', xhr.responseJSON);
            }
        });
    });

</script>
