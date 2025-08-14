<div class="modal fade modal-slide-in new-user-modal" id="editSubCategoryModal">
    <div class="modal-dialog">
        <div class="modal-content pt-0">
            <form id="editSubCategoryForm" enctype="multipart/form-data" action="">
                @csrf
                @method('PUT')
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

                <div class="modal-header">
                    <h5 class="modal-title" id="editSubCategoryModalLabel">Edit Subcategory</h5>

                </div>

                <div class="modal-body">
                    <input type="hidden" id="edit-sub-category-id">

                    <div class="mb-3">
                        <label for="edit-sub-category-name-en" class="form-label label-text">Name (EN)</label>
                        <input type="text" class="form-control" id="edit-sub-category-name-en" name="name[en]">
                    </div>

                    <div class="mb-3">
                        <label for="edit-sub-category-name-ar" class="form-label label-text">Name (AR)</label>
                        <input type="text" class="form-control" id="edit-sub-category-name-ar" name="name[ar]">
                    </div>

                    <div class="mb-3">
                        <label for="edit-sub-category-parent-id" class="form-label label-text">Main Product</label>
                        <select name="parent_id"  class="form-select">
                            <option value="" disabled selected>Choose Main Product</option>
                            @foreach($associatedData['categories'] as $category)
                                <option value="{{ $category->id }}">{{ $category->getTranslation('name', app()->getLocale()) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary " data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary  saveChangesButton" id="SaveChangesButton">
                        <span class="btn-text">Save Changes</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status" aria-hidden="true"></span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
