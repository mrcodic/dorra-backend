<div class="modal modal-slide-in new-user-modal fade" id="addQuestionModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addFaqForm" enctype="multipart/form-data" action="{{ route("faqs.store") }}">
                @csrf

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Add New Question</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <!-- Question in Arabic and English -->
                    <div class="row">
                        <div class="col-md-6 mb-1">
                            <label class="form-label label-text">Question (EN)</label>
                            <textarea class="form-control" id="add-category-description-en"
                                placeholder="Write your question here(En)" name="question[en]" rows="2"></textarea>
                        </div>
                        <div class="col-md-6 mb-1">
                            <label class="form-label label-text">Question (AR)</label>
                            <textarea class="form-control" id="add-category-description-ar"
                                placeholder="Write your question here(Ar)" name="question[ar]" rows="2"></textarea>
                        </div>
                    </div>
                    <!-- Answer in Arabic and English -->
                    <div class="row">
                        <div class="col-md-6 mb-1">
                            <label class="form-label label-text">Answer (EN)</label>
                            <textarea class="form-control" id="add-category-description-en"
                                placeholder="Enter Answer(En)" name="answer[en]" rows="2"></textarea>
                        </div>
                        <div class="col-md-6 mb-1">
                            <label class="form-label label-text">Answer (AR)</label>
                            <textarea class="form-control" id="add-category-description-ar"
                                placeholder="Enter Answer(Ar)" name="answer[ar]" rows="2"></textarea>
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary fs-5" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="saveChangesButton">
                        <span class="btn-text">Save</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status"
                            aria-hidden="true"></span>
                    </button>

                </div>
            </form>
        </div>
    </div>
</div>
<script !src="">
    handleAjaxFormSubmit("#addFaqForm",{
        successMessage: "Question Added Successfully",
        onSuccess: function () {
            location.reload()
        }
    })
</script>
