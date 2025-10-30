<div class="modal modal-slide-in new-user-modal fade" id="editSubIndustryModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editSubIndustryForm" enctype="multipart/form-data" action="">
                @csrf
                @method("PUT")
                <button type="button" class="btn-close " data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Edit Sub Industry</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <input type="hidden" id="edit-tag-id">



                    <!-- Name in Arabic and English -->
                    <div class="row mb-1">
                        <div class="col-md-6">
                            <label class="form-label label-text">Name (EN)</label>
                            <input type="text" class="form-control" id="edit-tag-name-en" name="name[en]" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label label-text">Name (AR)</label>
                            <input type="text" class="form-control" id="edit-tag-name-ar" name="name[ar]" />
                        </div>
                    </div>
                </div>
                <div class="mb-1">
                    <label class="form-label label-text">Industry</label>
                    <select name="parent_id" class="form-select" id="parent-id">
                        <option value="" disabled selected>Choose Main Industry</option>
                        @foreach($associatedData['industries'] as $industry)
                            <option value="{{ $industry->id }}"> {{
                                    $industry->getTranslation('name',app()->getLocale()) }}</option>
                        @endforeach

                    </select>

                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary fs-5" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary  saveChangesButton" id="SaveChangesButton">
                        <span class="btn-text">Save Changes</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status"
                              aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    handleAjaxFormSubmit("#editSubIndustryForm",{
        successMessage:"Sub Industry updated successfully",
        onSuccess:function () {
            location.reload()
        }
    })
</script>
