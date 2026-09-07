<div class="modal modal-slide-in new-user-modal fade" id="showBundleModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"
            >
                ×
            </button>

            <div class="modal-header mb-1">
                <h5 class="modal-title">Bundle Details</h5>
            </div>

            <div class="modal-body flex-grow-1">
                <div class="mb-2">
                    <label class="label-text mb-1">Bundle</label>
                    <input id="showBundleName" class="form-control" readonly>
                </div>

                <div class="mb-2">
                    <label class="label-text mb-1">Trigger</label>
                    <div id="showBundleTrigger" class="border rounded p-1"></div>
                </div>

                <div class="mb-2">
                    <label class="label-text mb-1">Rewards</label>
                    <div id="showBundleRewards" class="border rounded p-1"></div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="label-text mb-1">Activation</label>
                        <input id="showBundleApplication" class="form-control" readonly>
                    </div>

                    <div class="col-md-6 mb-2">
                        <label class="label-text mb-1">Usage</label>
                        <input id="showBundleRepeat" class="form-control" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
