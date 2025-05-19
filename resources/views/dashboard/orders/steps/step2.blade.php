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
            @forelse($associatedData['categories'] as $category)
                <span class="badge rounded-pill bg-light text-dark me-1 category-pill"
                      data-category="{{ $category->id }}">
                {{ $category->name }}
                </span>
            @empty
                <span class="badge rounded-pill bg-light text-dark me-1 category-pill">no categories found</span>

            @endforelse

        </div>

        <hr>

        <h6>Tags</h6>
        <div class="mb-1" id="tag-filters">
            @forelse($associatedData['tags'] as $tag)
                <span class="badge rounded-pill bg-light text-dark me-1 tag-pill" data-tag="{{ $tag->id }}">
                   {{ $tag->name }}
                </span>
            @empty
                <span class="badge rounded-pill bg-light text-dark me-1 tag-pill">no tags found</span>

            @endforelse

        </div>

        <hr>

        <div id="product-results">

        </div>
    </div>


    <div class="d-flex justify-content-end mt-2">
        <button class="btn btn-outline-secondary me-1" data-prev-step>Back</button>
        <button class="btn btn-primary" data-next-step>Next</button>
    </div>
</div>

<script>
    $(document).ready(function () {

        // When clicking on a product in the results
        $(document).on('click', '.product-item', function() {
            // Get product name from data attribute
            const productName = $(this).data('name');

            // Set it as the input value
            $('input[placeholder="Search for products"]').val(productName);

            // Optionally hide the filters/results after selection
            $('#product-filters-wrapper').slideUp();
        });
        // Show filters on input click
        $('input[placeholder="Search for products"]').on('click', function () {
            $('#product-filters-wrapper').slideDown();
        });

        let selectedCategory = null;
        let selectedTags = [];

        // Category - single select with toggle
        $(document).on('click', '.category-pill', function () {
            const categoryId = $(this).data('category');

            if (selectedCategory === categoryId) {
                // Deselect if already selected
                $(this).removeClass('pill-selected bg-primary text-white').addClass('bg-light text-dark');
                selectedCategory = null;
            } else {
                // Select new category
                $('.category-pill').removeClass('pill-selected bg-primary text-white').addClass('bg-light text-dark');
                $(this).removeClass('bg-light text-dark').addClass('pill-selected bg-primary text-white');
                selectedCategory = categoryId;
            }

            filterProducts();
        });



        // Tag - multi-select with toggle
        $(document).on('click', '.tag-pill', function () {
            const tagId = $(this).data('tag');

            if ($(this).hasClass('pill-selected')) {
                // Unselect
                $(this).removeClass('pill-selected bg-primary text-white').addClass('bg-light text-dark');
                selectedTags = selectedTags.filter(id => id !== tagId);
            } else {
                // Select
                $(this).removeClass('bg-light text-dark').addClass('pill-selected bg-primary text-white');
                if (!selectedTags.includes(tagId)) {
                    selectedTags.push(tagId);
                }
            }

            filterProducts(); // Call the filter function after updating selectedTags
        });


        // Search input
        $('input[placeholder="Search for products"]').on('input', function () {
            filterProducts();
        });

        // Filter AJAX call
        function filterProducts() {
            let search = $('input[placeholder="Search for products"]').val();
            $.ajax({
                url: "{{ route('products.search') }}",
                type: 'GET',
                data: {
                    search: search,
                    category_id: selectedCategory,
                    tag_ids: selectedTags
                },
                traditional: true,
                success: function (response) {
                    $('#product-results').html(response);
                }
            });
        }

        // Ensure pointer cursor for pills
        $('.category-pill, .tag-pill').css('cursor', 'pointer');
    });
</script>
