<div class="modal modal-slide-in new-user-modal fade" id="editQuestionModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addCategoryForm" enctype="multipart/form-data" action="">
                @csrf

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Edit Question</h5>
                </div>
                <div class="modal-body flex-grow-1">


                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label label-text">Question (EN)</label>
                            <textarea class="form-control auto-resize" id="question-en" placeholder="Write your question here(En)" name="description[en]" >Who are we?</textarea>
                        </div>
                        <div class="col-6">
                            <label class="form-label label-text">Question (AR)</label>
                            <textarea class="form-control auto-resize" id="question-ar" placeholder="Write your question here(Ar)" name="description[ar]" ></textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label label-text">Answer (EN)</label>
                            <textarea class="form-control auto-resize" id="answer-en" placeholder="Enter Answer(En)" name="description[en]" >Based in Cairo, Egypt, with a market presence since 2003. The company specializes in on-demand digital printing
and custom label production, providing fast, precise, and
reliable solutions tailored to the unique needs of businesses.</textarea>
                        </div>
                        <div class="col-6">
                            <label class="form-label label-text">Answer (AR)</label>
                            <textarea class="form-control auto-resize" id="answer-ar" placeholder="Enter Answer(Ar)" name="description[ar]" ></textarea>
                        </div>
                    </div>

                    <!-- Added date input below -->
                    <div class="row mb-2">
                        <div class="col-12">
                            <label class="form-label">Added Date</label>
                            <input type="date" class="form-control" value="2024-05-26" >
                        </div>
                    </div>


                </div>

              <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary fs-5" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="saveChangesButton">
                        <span class="btn-text">Save Changes</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status" aria-hidden="true"></span>
                    </button>

                </div>
            </form>
        </div>
    </div>
</div>
