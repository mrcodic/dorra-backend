<div class="modal modal-slide-in new-user-modal fade" id="createCodeTemplateModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addDiscountForm" method="post" enctype="multipart/form-data" action="{{ route('discount-codes.store') }}">
                @csrf
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Create Discount Code</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="form-group mb-2">
                        <label for="discountType" class="label-text mb-1">Type</label>
                        <select id="discountType" class="form-select select2"  name="type" >
                            <option value="" disabled selected>Select discount code type</option>
                            @foreach(\App\Enums\DiscountCode\TypeEnum::cases() as $case)
                                <option value="{{ $case->value }}">{{ $case->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-2">
                        <label for="prefix" class="label-text mb-1">Prefix (Write 4 char)</label>
                        <input type="text" name="code" id="prefix" class="form-control" placeholder="Add prefix here">
                    </div>

                    <div class="form-group mb-2">
                        <label for="discountValue" class="label-text mb-1">Discount Value</label>
                        <input type="text" name="value" id="discountValue" class="form-control" placeholder="Enter discount value here">
                    </div>

                    <div class="d-flex gap-1">
                        <div class="form-group mb-2 col-6">
                            <label for="restrictions" class="label-text mb-1">Restrictions</label>
                            <input type="number" name="max_usage" id="restrictions" class="form-control" placeholder="Enter number of usage times">
                        </div>
                        <div class="form-group mb-2 col-6">
                            <label for="expiryDate" class="label-text mb-1">Expiry Date</label>
                            <input type="date" name="expired_at" id="expiryDate" class="form-control">
                        </div>
                    </div>

                    <!-- Radio switch for Products or Categories -->
                    <div class="form-group mb-2">
                        <label class="label-text mb-1 d-block">Type</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="scope" id="applyToProducts" value="1" checked>
                            <label class="form-check-label text-black fs-16" for="applyToProducts">Products</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="scope" id="applyToCategories" value="2" >
                            <label class="form-check-label text-black fs-16" for="applyToCategories">Categories</label>
                        </div>
                    </div>

                    <!-- Products dropdown -->
                    <div class="form-group mb-2 productsField" id="productsField">
                        <label for="productsSelect" class="label-text mb-1">Products</label>
                        <select id="productsSelect" name="product_ids[]" class="form-select select2 productsSelect" multiple>
                            @foreach($associatedData['products'] as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Categories dropdown -->
                    <div class="form-group mb-2 d-none categoriesField" id="categoriesField">
                        <label for="categoriesSelect" class="label-text mb-1">Categories</label>
                        <select id="categoriesSelect" name="category_ids[]" class="form-select select2 categoriesSelect" multiple>
                            @foreach($associatedData['categories'] as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>


                </div>
                <div class="modal-footer border-top-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-outline-secondary">Generate</button>
                        <button type="button" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                            <span >Generate & Export</span>
                            <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status" aria-hidden="true"></span>
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

        // Toggle between products and categories
        $('input[name="scope"]').on('change', function () {
            if (this.value == 1) {
                $('.productsField').removeClass('d-none');
                $('.categoriesField').addClass('d-none');
                $('.categoriesSelect').val(null).trigger('change');
            } else {
                $('.categoriesField').removeClass('d-none');
                $('.productsField').addClass('d-none');
                $('.productsSelect').val(null).trigger('change');
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

            $('#saveLoader').removeClass('d-none'); // Show loading spinner
            $('#SaveChangesButton span:first-child').text('Saving...');

            $.ajax({
                url: actionUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#saveLoader').addClass('d-none');
                    $('#SaveChangesButton span:first-child').text('Generate & Export');
                    Toastify({
                        text: "Code added successfully!",
                        duration: 2000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#28C76F",
                        close: true,
                    }).showToast();
                    // Optional: Close modal and reload table or show success
                    $('#createCodeTemplateModal').modal('hide');
                    form[0].reset();
                    $('.select2').val(null).trigger('change');
                    $(".code-list-table").DataTable().ajax.reload();

                },
                error: function (xhr) {
                    $('#saveLoader').addClass('d-none');
                    $('#SaveChangesButton span:first-child').text('Generate & Export');

                    let errors = xhr.responseJSON?.errors;
                    if (errors) {
                        let messages = Object.values(errors).map(msg => msg.join(', ')).join('\n');
                        alert('Validation error:\n' + messages);
                    } else {
                        alert('Something went wrong. Please try again.');
                    }
                }
            });
        });
    });
</script>





