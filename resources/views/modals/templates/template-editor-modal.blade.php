<div class="modal new-user-modal fade" id="templateEditorModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="add-new-user modal-content pt-0 px-1">

            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
            <div class="modal-header mb-1 border-0 p-0">
                <h5 class="modal-title fs-4">Select Product to add template</h5>

            </div>
            <form id="templateProductForm" action="{{ config('services.editor_url') }}" method="get"  target="_blank">
                <div class="modal-body flex-grow-1 d-flex flex-column gap-2">
                    <div class="form-check option-box rounded border py-1 px-3 d-flex align-items-center">
                        <input
                            class="form-check-input me-2"
                            type="radio"
                            name="product_type"
                            id="tshirt"
                            value="T-shirt"
                            required
                        />
                        <label class="form-check-label mb-0 flex-grow-1" for="tshirt">T-shirt</label>
                    </div>
                    <div class="form-check option-box rounded border py-1 px-3 d-flex align-items-center">
                        <input
                            class="form-check-input me-2"
                            type="radio"
                            name="product_type"
                            id="other"
                            value="other"
                            required
                        />
                        <label class="form-check-label mb-0 flex-grow-1" for="other">Other</label>
                    </div>
                </div>
                <div class="modal-footer border-top-0 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Next</button>
                </div>
            </form>



        </div>

    </div>
</div>
<script>
    document.getElementById('templateProductForm').addEventListener('submit', function(event) {
        event.preventDefault();
        const selected = document.querySelector('input[name="product_type"]:checked');
        if (selected) {
            const url = this.action + '?product_type=' + encodeURIComponent(selected.value);
            window.open(url, '_blank'); 
        }
    });

</script>
