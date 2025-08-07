<div class="modal modal-slide-in new-user-modal fade" id="addLandingCategoryModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addCategoryForm" method="post" enctype="multipart/form-data" action="{{ route("categories.landing") }}" class="landing-category-form">
                @csrf

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Add Product</h5>
                </div>
                <div class="modal-body flex-grow-1">

                    <!-- Name in Arabic and English -->
                    <div class="row my-3">
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="productsSelect" class="label-text mb-1">Products</label>
                                <select id="productsSelect" class="form-select category-select" name="category_id">
                                    <option value="" disabled selected>Choose product</option>
                                    @foreach($allCategories as $category)
                                        <option value="{{ $category->id }}">
                                            {{ $category->getTranslation('name', app()->getLocale()) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="subCategorySelect" class="label-text mb-1">SubCategories</label>
                                <select id="subCategorySelect" class="form-select select2 category-sub-category-select" data-sub-category-url="{{ route('sub-categories')}}" name="sub_categories[]" multiple>

                                </select>
                            </div>

                        </div>
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label for="tagsSelect" class="label-text mb-1">Category</label>
                                <select id="tagsSelect" class="form-select select2 sub-category-select" name="products[]" multiple>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">
                                            {{ $product->getTranslation('name', app()->getLocale()) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary fs-5" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="saveChangesButton">
                        <span class="btn-text">Save</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status" aria-hidden="true"></span>
                    </button>

                </div>
            </form>
        </div>
    </div>
</div>
<script !src="">
    $(document).ready(function () {
        $('#subCategorySelect').select2();
        $('#tagsSelect').select2();

    });
</script>
<script>
    $(document).ready(function () {
        const $subProducts = $('#subCategorySelect');
        const $categories = $('#tagsSelect');
        const limit = 12;

        function showError($target) {
            const errorMsg = `
            <div class="text-danger mt-1 select-limit-error" style="font-size: 0.875em;">
                You can select a maximum of ${limit} items total (SubProducts + Category).
            </div>`;

            // Remove existing error messages
            $('.select-limit-error').remove();

            // Insert message after the select2 container
            $target.next('.select2-container').after(errorMsg);

            // Auto-hide after 5 seconds
            setTimeout(() => $('.select-limit-error').fadeOut(300, function () { $(this).remove(); }), 5000);
        }

        function enforceLimit(e) {
            const totalSelected = $subProducts.select2('data').length + $categories.select2('data').length;

            if (totalSelected > limit) {
                const $targetSelect = $(e.target);
                const selectedId = e.params.data.id;

                // Revert the last selected option
                const newSelection = $targetSelect.val().filter(val => val !== selectedId);
                $targetSelect.val(newSelection).trigger('change');

                showError($targetSelect);
            }
        }

        $subProducts.on('select2:select', enforceLimit);
        $categories.on('select2:select', enforceLimit);
    });
</script>

<script !src="">
    $('.category-select').on('change', function() {
        const categoryId = $(this).val();

        const $subCategorySelect = $('.category-sub-category-select');

        $.ajax({
            url: `${$subCategorySelect.data('sub-category-url')}?filter[parent_id]=${categoryId}`,
            method: "GET",
            success: function(res) {
                $subCategorySelect.empty().append('<option value="" disabled>Select subProduct</option>');
                $.each(res.data, (i, s) => $subCategorySelect.append(`<option value="${s.id}">${s.name}</option>`));
            },
            error: function() {
                $subCategorySelect.empty().append('<option value="">Error loading Subcategories</option>');
            }
        });
    });

</script>
<script>
    $(document).ready(function () {
        const input = $('#add-category-image');
        const uploadArea = $('#add-upload-area');
        const progressBar = $('#add-upload-progress .progress-bar');
        const progressContainer = $('#add-upload-progress');
        const uploadedImage = $('#add-uploaded-image');
        const imgPreview = $('#add-uploaded-image img');
        const fileNameDisplay = $('#add-file-details .file-name');
        const fileSizeDisplay = $('#add-file-details .file-size');
        const removeBtn = $('#add-remove-image');

        // Click upload area to open file input
        uploadArea.on('click', function () {
            input.click();
        });

        // Drag over style
        uploadArea.on('dragover', function (e) {
            e.preventDefault();
            uploadArea.addClass('dragover');
        });

        uploadArea.on('dragleave', function (e) {
            e.preventDefault();
            uploadArea.removeClass('dragover');
        });

        uploadArea.on('drop', function (e) {
            e.preventDefault();
            uploadArea.removeClass('dragover');
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) handleFile(files[0]);
        });

        // File input change
        input.on('change', function (e) {
            if (e.target.files.length > 0) handleFile(e.target.files[0]);
        });

        function handleFile(file) {
            if (!file.type.startsWith('image/')) return;

            const fileSizeKB = (file.size / 1024).toFixed(2) + ' KB';
            progressContainer.removeClass('d-none');
            progressBar.css('width', '0%');

            let progress = 0;
            const interval = setInterval(function () {
                progress += 10;
                progressBar.css('width', progress + '%');
                if (progress >= 100) {
                    clearInterval(interval);

                    const reader = new FileReader();
                    reader.onload = function (e) {
                        imgPreview.attr('src', e.target.result);
                        fileNameDisplay.text(file.name);
                        fileSizeDisplay.text(fileSizeKB);
                        uploadedImage.removeClass('d-none');
                        progressContainer.addClass('d-none');
                    };
                    reader.readAsDataURL(file);
                }
            }, 100);
        }

        // Remove image
        removeBtn.on('click', function () {
            imgPreview.attr('src', '');
            fileNameDisplay.text('');
            fileSizeDisplay.text('');
            uploadedImage.addClass('d-none');
            input.val('');
        });

        // Replace icons if needed
        feather.replace();
    });
</script>
