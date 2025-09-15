<div class="modal modal-slide-in new-user-modal fade" id="createCodeTemplateModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addDiscountForm" method="post" enctype="multipart/form-data"
                action="{{ route('discount-codes.store') }}">
                @csrf
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Create Discount Code</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="form-group mb-2">
                        <label for="createDiscountType" class="label-text mb-1">Type</label>
                        <select id="createDiscountType" class="form-select select2" name="type">
                            <option value="" disabled selected>Select discount code type</option>
                            @foreach(\App\Enums\DiscountCode\TypeEnum::cases() as $case)
                            <option value="{{ $case->value }}">{{ $case->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-2">
                        <label for="createPrefix" class="label-text mb-1">Prefix (Write 4 char)</label>
                        <input type="text" name="code" id="createPrefix" class="form-control"
                            placeholder="Add prefix here" maxlength="4">
                    </div>

                    <div class="form-group mb-2">
                        <label for="createDiscountValue" class="label-text mb-1">Discount Value</label>
                        <input type="number" name="value" id="createDiscountValue" class="form-control"
                            placeholder="Enter discount value here">
                    </div>

                    <div class="form-group mb-2">
                        <label for="numberOfCodes" class="label-text mb-1">Number of discount code</label>
                        <input type="number" id="numberOfCodes" name="number_of_discount_codes" class="form-control"
                            placeholder="Enter number of generated codes here">
                    </div>


                    <div class="d-flex flex-column flex-md-row gap-1">
                        <div class="form-group col-12 col-md-6">
                            <label for="createRestrictions" class="label-text mb-1">Maximum Usage Limits</label>
                            <input type="number" name="max_usage" id="createRestrictions" class="form-control"
                                placeholder="Enter number of usage times">
                        </div>
                        <div class="form-group mb-2 col-12 col-md-6">
                            <label for="createExpiryDate" class="label-text mb-1">Expiry Date</label>
                            <input type="date" name="expired_at" id="createExpiryDate" class="form-control">
                        </div>
                    </div>

                    <!-- Radio switch for Products or Categories -->
                    <div class="form-group mb-2">
                        <label class="label-text mb-1 d-block">Type</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="scope" id="general" value="3">
                            <label class="form-check-label text-black fs-16" for="general">General</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="scope" id="applyToProducts" value="2"
                                checked>
                            <label class="form-check-label text-black fs-16" for="applyToProducts">Categories</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="scope" id="applyToCategories" value="1">
                            <label class="form-check-label text-black fs-16" for="applyToCategories">Products</label>
                        </div>
                    </div>

                    <!-- Products dropdown -->
                    <div class="form-group mb-2 productsField" id="productsField">
                        <label for="productsSelect" class="label-text mb-1">Categories</label>
                        <select id="productsSelect" name="product_ids[]" class="form-select select2 productsSelect"
                            multiple>
                            @foreach($associatedData['products'] as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Categories dropdown -->
                    <div class="form-group mb-2 d-none categoriesField" id="categoriesField">
                        <label for="categoriesSelect" class="label-text mb-1">Products</label>
                        <select id="categoriesSelect" name="category_ids[]" class="form-select select2 categoriesSelect"
                            multiple>
                            @foreach($associatedData['categories'] as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>


                </div>
                <div class="modal-footer border-top-0 d-flex flex-wrap-reverse justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <div class="d-flex flex-wrap-reverse gap-1">
                        <button type="submit" class="btn btn-outline-secondary" id="generateBtn">
                            Generate
                            <span id="generateLoader" class="spinner-border spinner-border-sm d-none" role="status"
                                aria-hidden="true"></span>
                        </button>

                        <button type="button" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                            <span>Generate & Export</span>
                            <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader"
                                role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(function () {
        // Initialize Select2 in modal context
        $('.select2').select2({
            dropdownParent: $('#createCodeTemplateModal')
        });
        $('#createDiscountValue').on('input', function () {
            const type = $('#createDiscountType').val();
            const value = parseFloat(this.value);

            if (type === "{{ \App\Enums\DiscountCode\TypeEnum::PERCENTAGE->value }}" && value > 100) {
                this.value = 100;
            }

            if (value < 1) {
                this.value = 1;
            }
        });


        // Toggle between products and categories
        $('input[name="scope"]').on('change', function () {
            const scope = parseInt(this.value);

            if (scope === 2) {
                // Products
                $('.productsField').removeClass('d-none');
                $('.categoriesField').addClass('d-none');
                $('.categoriesSelect').val(null).trigger('change');
            } else if (scope === 1) {
                // Categories
                $('.categoriesField').removeClass('d-none');
                $('.productsField').addClass('d-none');
                $('.productsSelect').val(null).trigger('change');
            } else {
                // General
                $('.productsField, .categoriesField').addClass('d-none');
                $('.productsSelect, .categoriesSelect').val(null).trigger('change');
            }
        });


        $('input[name="scope"]:checked').trigger('change');

        // Discount value placeholder switch
        $('#discountType').on('change', function () {
            const type = $(this).val();
            const input = $('#discountValue');
            input.attr('placeholder',
                type === 'fixed' ? 'Enter fixed discount amount' :
                    type === 'percentage' ? 'Enter discount percentage' :
                        'Enter discount value');
        });


        // AJAX form submission
        $('#addDiscountForm').on('submit', function (e) {
            e.preventDefault();

            let form = $(this);
            let formData = new FormData(this);
            let actionUrl = form.attr('action');

            // Disable buttons and show loader
            const generateBtn = $('#generateBtn');
            const generateLoader = $('#generateLoader');
            const exportBtn = $('#SaveChangesButton');

            generateBtn.attr('disabled', true);
            exportBtn.attr('disabled', true);
            generateLoader.removeClass('d-none');

            $.ajax({
                url: actionUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    Toastify({
                        text: "Code added successfully!",
                        duration: 2000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28C76F",
                        close: true,
                    }).showToast();

                    $('#createCodeTemplateModal').modal('hide');
                    form[0].reset();
                    $('.select2').val(null).trigger('change');
                    $(".code-list-table").DataTable().ajax.reload();
                },
                error: function (xhr) {
                    let errors = xhr.responseJSON?.errors;

                    if (errors) {
                        Object.values(errors).forEach(errorArray => {
                            errorArray.forEach(message => {
                                Toastify({
                                    text: message,
                                    duration: 4000,
                                    gravity: "top",
                                    position: "right",
                                    backgroundColor: "#EA5455",
                                    close: true,
                                }).showToast();
                            });
                        });
                    } else {
                        Toastify({
                            text: "Something went wrong. Please try again.",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#EA5455",
                            close: true,
                        }).showToast();
                    }
                },
                complete: function () {
                    // Re-enable buttons and hide loader
                    generateBtn.attr('disabled', false);
                    exportBtn.attr('disabled', false);
                    generateLoader.addClass('d-none');
                }
            });
        });

        // Handle "Generate & Export" button click
        $('#SaveChangesButton').on('click', function () {
            const button = $(this);
            const loader = $('#saveLoader');
            const form = $('#addDiscountForm');
            const formData = new FormData(form[0]);
            const exportUrl = "{{ route('discount-codes.generate.export') }}";

            // Disable both buttons
            button.attr('disabled', true);
            loader.removeClass('d-none');
            $('button[type="submit"]').attr('disabled', true); // Disable Generate

            $.ajax({
                url: exportUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhrFields: {
                    responseType: 'blob'
                },
                success: function (response, status, xhr) {
                    const disposition = xhr.getResponseHeader('Content-Disposition');
                    let filename = "discount_codes.xlsx";

                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                        if (match && match[1]) {
                            filename = match[1].replace(/['"]/g, '');
                        }
                    }

                    const blob = new Blob([response], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    });
                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    $('#createCodeTemplateModal').modal('hide');

                    Toastify({
                        text: "File exported successfully!",
                        duration: 2000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28C76F",
                        close: true,
                    }).showToast();

                    form[0].reset();
                    $(".code-list-table").DataTable().ajax.reload();
                },
                error: function (xhr) {
                    let errors = xhr.responseJSON?.errors;

                    if (errors) {
                        Object.values(errors).forEach(errorArray => {
                            errorArray.forEach(message => {
                                Toastify({
                                    text: message,
                                    duration: 4000,
                                    gravity: "top",
                                    position: "right",
                                    backgroundColor: "#EA5455",
                                    close: true,
                                }).showToast();
                            });
                        });
                    } else {
                        Toastify({
                            text: "Something went wrong. Please try again.",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#EA5455",
                            close: true,
                        }).showToast();
                    }
                },
                complete: function () {
                    // Re-enable both buttons after request
                    button.attr('disabled', false);
                    loader.addClass('d-none');
                    $('button[type="submit"]').attr('disabled', false);
                }
            });
        });

// Handle "Generate" button click
        $('button[type="submit"]').on('click', function () {
            $('#SaveChangesButton').attr('disabled', true);
        });


    });
</script>
