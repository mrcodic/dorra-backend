<div class="modal modal-slide-in new-user-modal fade" id="addSubCategoryModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addSubCategoryForm" enctype="multipart/form-data" action="{{ route('sub-categories.store') }}">
                @csrf

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Add New Subproduct</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                        <label class="form-label label-text">Image*</label>

                        <!-- Dropzone upload area -->
                        <div id="category-dropzone"
                             class="d-flex align-items-center justify-content-center dropzone rounded p-3 text-center"
                             style="border: 2px dashed rgba(0, 0, 0, 0.3);">
                            <div class="dz-message" data-dz-message>
                                <span>Drop image here or click to upload</span>
                            </div>
                        </div>

                        <!-- hidden input to store uploaded sub_image_id -->
                        <input type="hidden" name="sub_image_id" id="uploadedImage">
                    </div>
                    <!-- Name in Arabic and English -->
                    <div class="row mb-1">
                        <div class="col-md-6">
                            <label class="form-label label-text">Name (EN)</label>
                            <input type="text" class="form-control" placeholder="Enter Category Name(En)"
                                id="add-category-name-en" name="name[en]" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label label-text">Name (AR)</label>
                            <input type="text" class="form-control" placeholder="Enter Category Name(Ar)"
                                id="add-category-name-ar" name="name[ar]" />
                        </div>
                    </div>
                    <!-- Description in Arabic and English -->
                    <div class="row mb-1">
                        <div class="col-lg-6">
                            <label class="form-label label-text">Description (EN)</label>
                            <textarea class="form-control" id="add-category-description-en"
                                      placeholder="Enter Description Name(En)" name="description[en]" rows="2"></textarea>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label label-text">Description (AR)</label>
                            <textarea class="form-control" id="add-category-description-ar"
                                      placeholder="Enter Description Name(Ar)" name="description[ar]" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="mb-1">
                            <label class="form-label label-text">Product</label>
                            <select name="parent_id" class="form-select" id="">
                                <option value="" disabled selected>Choose Main Product</option>
                                @foreach($associatedData['categories'] as $category)
                                <option value="{{ $category->id }}"> {{
                                    $category->getTranslation('name',app()->getLocale()) }}</option>
                                @endforeach

                            </select>
                   
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                        <span class="btn-text">Save</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status"
                            aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    Dropzone.autoDiscover = false;

    const categoryDropzone = new Dropzone("#category-dropzone", {
        url: "{{ route('media.store') }}",   // backend route for image upload
        paramName: "file",
        maxFiles: 1,
        maxFilesize: 1, // MB

        acceptedFiles: "image/*",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        addRemoveLinks: true,
        dictDefaultMessage: "Drop image here or click to upload",
        init: function () {
            this.on("success", function (file, response) {
                if (response.success && response.data) {
                    file._hiddenInputId = response.data.id;

                    $("#uploadedImage").val(response.data.id); // store sub_image_id
                }
            });

            this.on("removedfile", function (file) {
                $("#uploadedImage").val("");
                if (file._hiddenInputId) {
                    fetch("{{ url('api/v1/media') }}/" + file._hiddenInputId, {
                        method: "DELETE",
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    $("#preview-image").attr("src", "").addClass("d-none");

                }
            });
        }
    });
</script>
