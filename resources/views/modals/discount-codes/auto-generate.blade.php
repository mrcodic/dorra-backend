<div class="modal modal-slide-in new-user-modal fade" id="createCodeTemplateModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addDiscountForm" method="post" enctype="multipart/form-data"
                  action="{{ route('discount-codes.store') }}">
                @csrf
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

                <div class="modal-header mb-1">
                    <h5 class="modal-title">Create Discount Code</h5>
                </div>

                <div class="modal-body flex-grow-1">

                    <!-- Code Mode -->
                    <div class="form-group mb-2">
                        <label class="label-text mb-1 d-block">Code Mode</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="code_mode" id="generatedMode" value="1" checked>
                            <label class="form-check-label text-black fs-16" for="generatedMode">Generated Codes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="code_mode" id="customMode" value="2">
                            <label class="form-check-label text-black fs-16" for="customMode">Custom Code</label>
                        </div>
                    </div>

                    <!-- Type -->
                    <div class="form-group mb-2">
                        <label for="createDiscountType" class="label-text mb-1">Type</label>
                        <select id="createDiscountType" class="form-select select2" name="type">
                            <option value="" disabled selected>Select discount code type</option>
                            @foreach(\App\Enums\DiscountCode\TypeEnum::cases() as $case)
                                <option value="{{ $case->value }}">{{ $case->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Prefix -->
                    <div class="form-group mb-2" id="prefixFieldWrapper">
                        <label for="createPrefix" class="label-text mb-1">Prefix (Write 4 char)</label>
                        <input type="text" name="code" id="createPrefix" class="form-control"
                               placeholder="Add prefix here" maxlength="4">
                    </div>

                    <!-- Number of codes -->
                    <div class="form-group mb-2" id="numberOfCodesWrapper">
                        <label for="numberOfCodes" class="label-text mb-1">Number of discount code</label>
                        <input type="number" id="numberOfCodes" name="number_of_discount_codes" class="form-control"
                               placeholder="Enter number of generated codes here">
                    </div>

                    <!-- Discount Value -->
                    <div class="form-group mb-2">
                        <label for="createDiscountValue" class="label-text mb-1">Discount Value</label>
                        <input type="number" name="value" id="createDiscountValue" class="form-control"
                               placeholder="Enter discount value here">
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

                    <!-- Show for new registered users -->
                    <div class="form-group mb-2 new-registered-users d-none">
                        <input type="hidden" name="show_for_new_registered_users" value="0">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="show_for_new_registered_users"
                                   id="showForNewRegisteredUser" value="1">
                            <label class="form-check-label text-black fs-16" for="showForNewRegisteredUser">
                                Show for new registered users
                            </label>
                        </div>
                    </div>

                    <!-- Scope -->
                    <div class="form-group mb-2">
                        <label class="label-text mb-1 d-block">Scope</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="scope" id="scopeGeneral" value="3" checked>
                            <label class="form-check-label text-black fs-16" for="scopeGeneral">General</label>
                        </div>
                        <div class="form-check form-check-inline">
                            {{-- PRODUCT = 2 --}}
                            <input class="form-check-input" type="radio" name="scope" id="scopeProducts" value="2">
                            <label class="form-check-label text-black fs-16" for="scopeProducts">Products</label>
                        </div>
                        <div class="form-check form-check-inline">
                            {{-- CATEGORY = 1 --}}
                            <input class="form-check-input" type="radio" name="scope" id="scopeCategories" value="1">
                            <label class="form-check-label text-black fs-16" for="scopeCategories">Categories</label>
                        </div>
                    </div>

                    {{-- ── scope=2 PRODUCT ──────────────────────────────────────── --}}
                    <div class="d-none productsField row">

                        {{-- Filter: pick a category to narrow products --}}
                        <div class="form-group mb-1 col-6">
                            <label for="productCategoryFilter" class="label-text mb-1">Products</label>
                            <select id="productCategoryFilter" class="form-select select2">
                                @foreach($associatedData['product_with_categories'] as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Result: products --}}
                        <div class="form-group mb-2  col-6">
                            <label for="productsSelect" class="label-text mb-1">Categories</label>
                            <select id="productsSelect" name="product_ids[]"
                                    class="form-select select2 productsSelect" multiple>
                            </select>
                        </div>
                    </div>

                    {{-- ── scope=1 CATEGORY ────────────────────────────────────── --}}
                    <div class="d-none categoriesField">

                        {{-- Result: categories --}}
                        <div class="form-group mb-2">
                            <label for="categoriesSelect" class="label-text mb-1">Categories</label>
                            <select id="categoriesSelect" name="category_ids[]"
                                    class="form-select select2 categoriesSelect" multiple>
                                @foreach($associatedData['categories'] as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-top-0 d-flex flex-wrap-reverse justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <div class="d-flex flex-wrap-reverse gap-1">
                        <button type="submit" class="btn btn-outline-secondary" id="generateBtn">
                            Generate
                            <span id="generateLoader" class="spinner-border spinner-border-sm d-none"></span>
                        </button>
                        <button type="button" class="btn btn-primary fs-5" id="SaveChangesButton">
                            <span>Generate & Export</span>
                            <span id="saveLoader" class="spinner-border spinner-border-sm d-none"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(function () {

        // ── Init Select2 (مرة واحدة بس) ───────────────────────────────────────
        $('.select2').select2({ dropdownParent: $('#createCodeTemplateModal') });

        // ══════════════════════════════════════════════════════════════════════
        // Code Mode Toggle
        // ══════════════════════════════════════════════════════════════════════
        function toggleCodeModeFields() {
            const isCustom = $('input[name="code_mode"]:checked').val() === '2';

            $('#numberOfCodesWrapper').toggleClass('d-none', isCustom);
            $('#numberOfCodes').val('').prop('required', false);
            $('.new-registered-users').toggleClass('d-none', !isCustom);

            if (!isCustom) {
                // reset new-registered-users side effects
                $('#showForNewRegisteredUser').prop('checked', false);
                $('#createRestrictions').closest('.form-group').removeClass('d-none');
                $('#createRestrictions').val('').prop('disabled', false);
                $('#createExpiryDate').closest('.form-group').removeClass('d-none');
                $('#createExpiryDate').val('').prop('disabled', false);
            }

            $('#prefixFieldWrapper label').text(isCustom ? 'Custom Code' : 'Prefix (Write 4 char)');
            $('#createPrefix')
                .attr('placeholder', isCustom ? 'Enter custom code' : 'Add prefix here')
                .attr('maxlength',   isCustom ? 255              : 4);
        }

        $('input[name="code_mode"]').on('change', toggleCodeModeFields);
        toggleCodeModeFields();

        // ══════════════════════════════════════════════════════════════════════
        // Show for new registered users
        // ══════════════════════════════════════════════════════════════════════
        $('#showForNewRegisteredUser').on('change', function () {
            const hide = $(this).is(':checked');
            $('#createRestrictions').closest('.form-group').toggleClass('d-none', hide);
            $('#createRestrictions').val('').prop('disabled', hide);
            $('#createExpiryDate').closest('.form-group').toggleClass('d-none', hide);
            $('#createExpiryDate').val('').prop('disabled', hide);
        });

        // ══════════════════════════════════════════════════════════════════════
        // Discount Value cap
        // ══════════════════════════════════════════════════════════════════════
        $('#createDiscountValue').on('input', function () {
            const isPercentage = $('#createDiscountType').val() === "{{ \App\Enums\DiscountCode\TypeEnum::PERCENTAGE->value }}";
            const val = parseFloat(this.value);
            if (isPercentage && val > 100) this.value = 100;
            if (val < 1) this.value = 1;
        });

        // ══════════════════════════════════════════════════════════════════════
        // Scope Toggle  (GENERAL=3 | PRODUCT=2 | CATEGORY=1)
        // ══════════════════════════════════════════════════════════════════════
        $('input[name="scope"]').on('change', function () {
            const scope = parseInt(this.value);
            $('.productsField').toggleClass('d-none', scope !== 2);
            $('.categoriesField').toggleClass('d-none', scope !== 1);

            // clear opposite field
            if (scope !== 2) $('.productsSelect').val(null).trigger('change');
            if (scope !== 1) $('.categoriesSelect').val(null).trigger('change');
        });
        $('input[name="scope"]:checked').trigger('change');

        // ══════════════════════════════════════════════════════════════════════
        // Filter: Category → fetch Products
        // ══════════════════════════════════════════════════════════════════════
        $('#productCategoryFilter').on('change', function () {
            const categoryId = $(this).val();
            const $products  = $('#productsSelect');
            const saved      = $products.val() || [];   // حافظ على الـ selected

            // ── مفيش category مختارة → فضي الـ select ─────────────────────────────
            if (!categoryId) {
                $products.empty().trigger('change');
                return;
            }

            $.ajax({
                url:  "{{ route('products.categories') }}",
                type: 'get',
                data: {
                    _token:      "{{ csrf_token() }}",
                    category_id: categoryId,
                },
                beforeSend() {
                    $products.prop('disabled', true).empty();   // loading state
                },
                success(response) {
                    (response.data || []).forEach(product => {
                        // لو كان متاختار قبل كده خليه selected
                        const isSelected = saved.includes(String(product.id));
                        $products.append(new Option(product.name, product.id, false, isSelected));
                    });
                    $products.trigger('change');    // refresh select2
                },
                error(xhr) {
                    console.error('Error fetching products:', xhr.responseText);
                },
                complete() {
                    $products.prop('disabled', false);
                },
            });
        });
        // ══════════════════════════════════════════════════════════════════════
        // Filter: Product → fetch Categories
        // ══════════════════════════════════════════════════════════════════════
        $('#categoryProductFilter').on('change', function () {
            const productId   = $(this).val();
            const $categories = $('#categoriesSelect');
            const saved       = $categories.val() || [];

            if (!productId) {
                // restore all categories
                $categories.empty();
                @foreach($associatedData['categories'] as $category)
                $categories.append(new Option("{{ $category->name }}", "{{ $category->id }}"));
                @endforeach
                $categories.val(saved).trigger('change');
                return;
            }

            $.ajax({
                url: "{{ route('products.categories') }}",
                type: 'POST',
                data: { _token: "{{ csrf_token() }}", product_id: productId },
                beforeSend: () => $categories.prop('disabled', true),
                success(response) {
                    $categories.empty();
                    (response.data || []).forEach(c => {
                        $categories.append(new Option(c.name, c.id, false, saved.includes(String(c.id))));
                    });
                    $categories.trigger('change');
                },
                error(xhr) { console.error('Error fetching categories:', xhr.responseText); },
                complete:   () => $categories.prop('disabled', false),
            });
        });

        // ══════════════════════════════════════════════════════════════════════
        // Reset modal on close
        // ══════════════════════════════════════════════════════════════════════
        $('#createCodeTemplateModal').on('hidden.bs.modal', function () {
            $('#addDiscountForm')[0].reset();
            $('#showForNewRegisteredUser').prop('checked', false);
            $('.select2').val(null).trigger('change');
            $('#productCategoryFilter, #categoryProductFilter').val(null).trigger('change');
            toggleCodeModeFields();
            $('input[name="scope"]:checked').trigger('change');
        });

        // ══════════════════════════════════════════════════════════════════════
        // Submit — Generate
        // ══════════════════════════════════════════════════════════════════════
        $('#addDiscountForm').on('submit', function (e) {
            e.preventDefault();
            const form        = $(this);
            const generateBtn = $('#generateBtn');
            const exportBtn   = $('#SaveChangesButton');
            const loader      = $('#generateLoader');

            generateBtn.add(exportBtn).prop('disabled', true);
            loader.removeClass('d-none');

            $.ajax({
                url:         form.attr('action'),
                method:      'POST',
                data:        new FormData(this),
                processData: false,
                contentType: false,
                success() {
                    Toastify({ text: "Code added successfully!", duration: 2000,
                        gravity: "top", position: "right", backgroundColor: "#28C76F", close: true }).showToast();
                    $('#createCodeTemplateModal').modal('hide');
                    $(".code-list-table").DataTable().ajax.reload();
                },
                error(xhr) { showErrors(xhr); },
                complete() {
                    generateBtn.add(exportBtn).prop('disabled', false);
                    loader.addClass('d-none');
                }
            });
        });

        // ══════════════════════════════════════════════════════════════════════
        // Generate & Export
        // ══════════════════════════════════════════════════════════════════════
        $('#SaveChangesButton').on('click', function () {
            const btn    = $(this);
            const loader = $('#saveLoader');

            btn.prop('disabled', true);
            loader.removeClass('d-none');
            $('#generateBtn').prop('disabled', true);

            $.ajax({
                url:         "{{ route('discount-codes.generate.export') }}",
                method:      'POST',
                data:        new FormData($('#addDiscountForm')[0]),
                processData: false,
                contentType: false,
                xhrFields:   { responseType: 'blob' },
                success(response, status, xhr) {
                    const disposition = xhr.getResponseHeader('Content-Disposition');
                    let filename = 'discount_codes.xlsx';
                    const match = disposition?.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                    if (match?.[1]) filename = match[1].replace(/['"]/g, '');

                    const link = Object.assign(document.createElement('a'), {
                        href: URL.createObjectURL(new Blob([response], {
                            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        })),
                        download: filename,
                    });
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    Toastify({ text: "File exported successfully!", duration: 2000,
                        gravity: "top", position: "right", backgroundColor: "#28C76F", close: true }).showToast();
                    $('#createCodeTemplateModal').modal('hide');
                    $(".code-list-table").DataTable().ajax.reload();
                },
                error(xhr) { showErrors(xhr); },
                complete() {
                    btn.prop('disabled', false);
                    loader.addClass('d-none');
                    $('#generateBtn').prop('disabled', false);
                }
            });
        });

        // ══════════════════════════════════════════════════════════════════════
        // Helper: show validation errors
        // ══════════════════════════════════════════════════════════════════════
        function showErrors(xhr) {
            const errors = xhr.responseJSON?.errors;
            const messages = errors
                ? Object.values(errors).flat()
                : ["Something went wrong. Please try again."];

            messages.forEach(msg => Toastify({
                text: msg, duration: 4000,
                gravity: "top", position: "right", backgroundColor: "#EA5455", close: true,
            }).showToast());
        }
    });
</script>
