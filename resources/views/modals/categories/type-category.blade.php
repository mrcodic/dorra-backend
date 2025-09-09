<div class="modal new-user-modal fade" id="categoryModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="add-new-user modal-content pt-0 px-1">

            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
            <div class="modal-header mb-1 border-0 p-0">
                <h5 class="modal-title fs-4">Select Product Type</h5>
            </div>

            <div class="modal-body flex-grow-1 d-flex flex-column gap-2">

                <div class="form-check option-box rounded border py-1 px-3 d-flex align-items-center">
                    <input
                        class="form-check-input me-2"
                        type="radio"
                        name="product_type"
                        id="productWithCategory"
                        value="with"
                    />
                    <label class="form-check-label mb-0 flex-grow-1" for="productWithCategory">
                         With Categories
                    </label>
                </div>

                <div class="form-check option-box rounded border py-1 px-3 d-flex align-items-center">
                    <input
                        class="form-check-input me-2"
                        type="radio"
                        name="product_type"
                        id="productWithoutCategory"
                        value="without"
                    />
                    <label class="form-check-label mb-0 flex-grow-1" for="productWithoutCategory">
                        Without Categories
                    </label>
                </div>

                <!-- مكان التنبيه -->
                <small id="productTypeError" class="text-danger d-none">
                    Please select a product type before continuing.
                </small>

            </div>

            <div class="modal-footer border-top-0 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="nextBtn" class="btn btn-primary">Next</button>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const nextBtn = document.getElementById("nextBtn");
        const withCategory = document.getElementById("productWithCategory");
        const withoutCategory = document.getElementById("productWithoutCategory");
        const errorMsg = document.getElementById("productTypeError");

        nextBtn.addEventListener("click", function () {
            if (withCategory.checked) {
                const nextModal = new bootstrap.Modal(document.getElementById("addCategoryModal"));
                nextModal.show();

                const currentModal = bootstrap.Modal.getInstance(document.getElementById("categoryModal"));
                currentModal.hide();
            } else if (withoutCategory.checked) {
                window.location.href = "/categories/create";
            } else {

                errorMsg.classList.remove("d-none");
            }
        });

        [withCategory, withoutCategory].forEach(input => {
            input.addEventListener("change", () => {
                errorMsg.classList.add("d-none");
            });
        });
    });
</script>
