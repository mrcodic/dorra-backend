<div class="modal new-user-modal fade" id="templateEditorModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="add-new-user modal-content pt-0 px-1">

            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

            <div class="modal-header mb-1 border-0 p-0">
                <h5 class="modal-title fs-4">Select Products to add template</h5>
            </div>

            <form id="templateEditorForm" action="{{ config('services.editor_url') }}" method="get">
                <input type="hidden" name="product_ids" id="product_ids_hidden">

                <div class="modal-body flex-grow-1 d-flex flex-column gap-2">
                    @foreach($associatedData['products'] as $product)
                        <div class="form-check option-box rounded border py-1 px-3 d-flex align-items-center">
                            <input
                                class="form-check-input me-2"
                                type="checkbox"
                                name="product_ids[]"
                                id="product_{{ $product->id }}"
                                value="{{ $product->id }}"
                            />
                            <label class="form-check-label mb-0 flex-grow-1" for="product_{{ $product->id }}">
                                {{ $product->name }}
                            </label>
                        </div>
                    @endforeach
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
    document.getElementById('templateEditorForm').addEventListener('submit', function (e) {
        const form = this;
        const selected = Array.from(form.querySelectorAll('input[name="product_ids[]"]:checked'))
            .map(cb => cb.value);

        if (selected.length === 0) {
            e.preventDefault();
            alert('Please select at least one product.');
            return;
        }

        // Set hidden input value
        form.querySelector('#product_ids_hidden').value = selected.join(',');

        // Remove names from checkboxes to prevent them being sent as array
        form.querySelectorAll('input[name="product_ids[]"]').forEach(cb => cb.removeAttribute('name'));
    });
</script>
