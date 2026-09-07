<div class="modal modal-slide-in new-user-modal fade" id="addBundleModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">

            <form
                id="addBundleForm"
                method="post"
                enctype="multipart/form-data"
                action="{{ route('bundles.store') }}"
            >
                @csrf

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                >
                    ×
                </button>

                <div class="modal-header mb-1">
                    <h5 class="modal-title">Add New Bundle</h5>
                </div>

                <div class="modal-body flex-grow-1">
                    @include('modals.bundles._form', [
                        'prefix' => 'add',
                    ])
                </div>

                <div class="modal-footer border-top-0 d-flex justify-content-end">
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary fs-5 saveChangesButton"
                    >
                        <span class="btn-text">Add</span>

                        <span
                            class="spinner-border spinner-border-sm d-none saveLoader"
                            role="status"
                            aria-hidden="true"
                        ></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
