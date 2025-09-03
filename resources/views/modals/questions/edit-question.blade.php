<div class="modal modal-slide-in new-user-modal fade" id="editQuestionModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editFaqForm" enctype="multipart/form-data" action="">
                @csrf
                @method("PUT")
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Edit Question</h5>
                </div>
                <div class="modal-body flex-grow-1">


                    <div class="row">
                        <div class="col-md-6 mb-1">
                            <label class="form-label label-text">Question (EN)</label>
                            <textarea class="form-control auto-resize" id="question-en"
                                      placeholder="Write your question here(En)" name="question[en]"></textarea>
                        </div>
                        <div class="col-md-6 mb-1">
                            <label class="form-label label-text">Question (AR)</label>
                            <textarea class="form-control auto-resize" id="question-ar"
                                      placeholder="Write your question here(Ar)" name="question[ar]"></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-1">
                            <label class="form-label label-text">Answer (EN)</label>
                            <textarea class="form-control auto-resize" id="answer-en" placeholder="Enter Answer(En)"
                                      name="answer[en]"></textarea>
                        </div>
                        <div class="col-md-6 mb-1">
                            <label class="form-label label-text">Answer (AR)</label>
                            <textarea class="form-control auto-resize" id="answer-ar" placeholder="Enter Answer(Ar)"
                                      name="answer[ar]"></textarea>
                        </div>
                    </div>

                    <!-- Added date input below -->
                    {{--                    <div class="row mb-2">--}}
                    {{--                        <div class="col-12">--}}
                    {{--                            <label class="form-label">Added Date</label>--}}
                    {{--                            <input type="date" class="form-control" value="2024-05-26" di>--}}
                    {{--                        </div>--}}
                    {{--                    </div>--}}


                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary fs-5" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="saveChangesButton">
                        <span class="btn-text">Save Changes</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status"
                              aria-hidden="true"></span>
                    </button>


                </div>
            </form>
        </div>
    </div>
</div>
<script !src="">
    handleAjaxFormSubmit("#editFaqForm",{
        successMessage: "Faq Edited Successfully",
        onSuccess:function (){
            location.reload()
        }
    })
</script>
