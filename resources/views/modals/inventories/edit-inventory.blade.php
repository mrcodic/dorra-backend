<div class="modal modal-slide-in new-user-modal fade" id="editInventoryModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editInventoryForm" method="post" enctype="multipart/form-data"
                  action="{{ route("inventories.store") }}" class="landing-category-form">
                @csrf
                @method("PUT")
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Edit Inventory</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="row my-3">
                        <div class="form-group mb-2">
                            <label for="editInventoryName" class="label-text mb-1">Name</label>
                            <input
                                id="editInventoryName"
                                type="text"
                                name="name"
                                class="form-control"
                                placeholder="Enter Name"
                                required
                            >

                        </div>
                        <div class="row my-3">
                            <div class="form-group mb-2">
                                <label for="editInventoryNumber" class="label-text mb-1">Number</label>
                                <input

                                    type="number"
                                    name="number"
                                    id="editInventoryNumber"
                                    class="form-control"
                                    placeholder="Enter Number"
                                    required
                                >

                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-outline-secondary fs-5" data-bs-dismiss="modal">Cancel
                        </button>
                        <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="saveChangesButton">
                            <span class="btn-text">Save</span>
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
    handleAjaxFormSubmit("#editInventoryForm", {
        successMessage: "Inventory updated Successfully",
        onSuccess: function () {
            location.reload();
        }
    })
</script>
