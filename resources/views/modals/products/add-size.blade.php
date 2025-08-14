<div class="modal modal-slide-in new-user-modal fade" id="addSizeModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addSizeForm" enctype="multipart/form-data" action="{{ route("dimensions.store") }}">
                @csrf
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Add Custom Size</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="">
                        <div class="form-group mb-2">
                            <label for="width" class="label-text mb-1">Width</label>
                            <input type="number" id="width" class="form-control" name="width"
                                   placeholder="Width">
                        </div>
                        <div class="form-group mb-2">
                            <label for="height" class="label-text mb-1">Height</label>
                            <input type="number" id="height" class="form-control" name="height"
                                   placeholder="Height">
                        </div>

                        <div class="form-group mb-2">
                            <label for="mockup-type" class="label-text mb-1">Unit</label>
                            <select id="mockup-type" name="unit" class="form-select">
                                <option value="" disabled selected>select unit type</option>
                                @foreach(\App\Enums\Product\UnitEnum::cases() as $type)
                                    <option value="{{ $type->value }}"> {{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                            <span class="btn-text">Confirm</span>
                            <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader"
                                  role="status"
                                  aria-hidden="true"></span>
                        </button>
                    </div>
            </form>
        </div>
    </div>
</div>


<script>
    $(document).ready(function () {
        handleAjaxFormSubmit("#addSizeForm", {
            successMessage: "Dimension Created Successfully",
            onSuccess: function (response) {
                $('#addSizeModal').modal('hide');

                const width = $('#addSizeForm input[name="width"]').val();
                const height = $('#addSizeForm input[name="height"]').val();
                const unit = $('#addSizeForm select[name="unit"]').val();
                const id = response.data.id;
                const name = `${width}*${height}`;

                const newDimension = {id, width, height, unit, name, is_custom: 1};

                // Get existing dimensions from sessionStorage
                let stored = sessionStorage.getItem('custom_dimensions');
                let dimensions = stored ? JSON.parse(stored) : [];

                // Add new one
                dimensions.push(newDimension);
                sessionStorage.setItem('custom_dimensions', JSON.stringify(dimensions));

                // Add to DOM
                const checkboxHtml = `
<label class="form-check option-box rounded border py-1 px-3 d-flex align-items-center" for="dimension-${id}">
    <input
        class="form-check-input me-2"
        type="checkbox"
        style="pointer-events: none"
        id="dimension-${id}"
        value="${id}"
        checked
    />
    <span class="form-check-label mb-0 flex-grow-1" >${name}</span>
</label>`;


                $('#custom-dimensions-container').append(checkboxHtml);

                $('#addSizeForm')[0].reset();
            }
        });

        // On page load: re-render dimensions stored in sessionStorage
        let stored = sessionStorage.getItem('custom_dimensions');
        if (stored) {
            const dimensions = JSON.parse(stored);
            dimensions.forEach(d => {
                if (!$(`#dimension-${d.id}`).length) {
                    const checkboxHtml = `
<div class="form-check option-box rounded border py-1 px-3 d-flex align-items-center">
    <input
        class="form-check-input me-2"
        type="checkbox"

        id="dimension-${id}"
        value="${id}"
        checked
    />
    <label class="form-check-label mb-0 flex-grow-1" for="dimension-${id}">${name}</label>
</div>`;

                    $('#custom-dimensions-container').append(checkboxHtml);

                }
            });
        }
    });

</script>

