<div class="modal modal-slide-in new-user-modal fade" id="addSubCategoryModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addSubCategoryForm" enctype="multipart/form-data" action="{{ route("sub-categories.store") }}">
                @csrf

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Add New Subcategory</h5>
                </div>
                <div class="modal-body flex-grow-1">

                    <!-- Name in Arabic and English -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label label-text">Name (EN)</label>
                            <input type="text" class="form-control" placeholder="Enter Category Name(En)"
                                   id="add-category-name-en" name="name[en]"/>
                        </div>
                        <div class="col-6">
                            <label class="form-label label-text">Name (AR)</label>
                            <input type="text" class="form-control" placeholder="Enter Category Name(Ar)"
                                   id="add-category-name-ar" name="name[ar]"/>
                        </div>
                    </div>


                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label label-text">Product</label>
                            <select name="parent_id" class="form-select" id="">
                                <option value="" disabled selected>Choose Main Product</option>
                                @foreach($associatedData['categories'] as $category)
                                    <option
                                        value="{{ $category->id }}"> {{ $category->getTranslation('name',app()->getLocale()) }}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                        <span class="btn-text">Save</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

