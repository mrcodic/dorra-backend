<div class="modal modal-slide-in new-user-modal fade" id="showMessageModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editMessageForm" enctype="multipart/form-data" action="">
                @csrf
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Show Message</h5>
                </div>

                <div class="modal-body flex-grow-1">
                    <div class="">
                        <div class="mb-2">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" id="modalName" disabled />
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Email Address</label>
                            <input type="text" class="form-control" id="modalEmail" disabled />
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="modalPhone" disabled />
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" id="modalMessage" rows="2" disabled></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Added Date</label>
                            <input type="text" class="form-control" id="modalCreatedAt" disabled>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Reply</label>
                            <textarea name="reply" class="form-control" rows="2" placeholder="Write your reply here"></textarea>
                        </div>
                    </div>
                </div>

                <!-- ✅ Footer must be inside modal-content -->
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary fs-5" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5" id="saveChangesButton">Send Reply</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script !src="">
    handleAjaxFormSubmit("#editMessageForm",
        {
            successMessage: "Reply Send Successfully",
            onSuccess: function (){
                location.reload()
            }
        }
    )
</script>
