<div class="modal modal-slide-in new-user-modal fade" id="showInventoryModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addInventoryForm" method="post" enctype="multipart/form-data" action="{{ route("inventories.store") }}" class="landing-category-form">
                @csrf

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Show Inventory</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="row my-3">
                        <div class="form-group mb-2">
                            <label for="show-name" class="label-text mb-1">Name</label>
                            <input
                                id="show-name"
                                type="text"
                                class="form-control"
                                readonly
                            >

                        </div>
                        <div class="row my-3">
                        <div class="form-group mb-2">
                            <label for="show-number" class="label-text mb-1">Number</label>
                            <input
                                id="show-number"
                                type="number"
                                class="form-control"
                                readonly
                            >

                        </div>
                        </div>
                        <div class="row my-3">
                        <div class="form-group mb-2">
                            <label for="show-children-count" class="label-text mb-1">Available Places</label>
                            <input
                                id="show-children-count"
                                type="number"
                                class="form-control"
                                readonly
                            >

                        </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary fs-5" data-bs-dismiss="modal">Cancel</button>

                </div>
            </form>
        </div>
    </div>
</div>

