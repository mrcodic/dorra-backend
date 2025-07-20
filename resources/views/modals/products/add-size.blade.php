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
                            <input type="text" id="width" class="form-control" name="width"
                                placeholder="Width">
                        </div>
                        <div class="form-group mb-2">
                            <label for="height" class="label-text mb-1">Height</label>
                            <input type="text" id="height" class="form-control" name="height"
                                placeholder="Height">
                        </div>

                        <div class="form-group mb-2">
                            <label for="mockup-type" class="label-text mb-1">Unit</label>
                            <select id="mockup-type" name="type" class="form-select">
                                <option value="" disabled>select mockup type</option>
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
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status"
                            aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>





<script>
    $(document).ready(function() {

        handleAjaxFormSubmit("#addSizeForm", {
            successMessage: "Dimension Created Successfully",
            onSuccess: function(response) {
                console.log(response)
                $('#addSizeModal').modal('hide');
            }
        })
    });


</script>
