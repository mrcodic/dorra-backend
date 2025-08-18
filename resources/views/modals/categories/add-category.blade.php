<div class="modal modal-slide-in new-user-modal fade" id="addCategoryModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addCategoryForm" enctype="multipart/form-data" action="{{ route('categories.store') }}">
                @csrf

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Add New Product</h5>
                </div>
                <div class="modal-body flex-grow-1">

                    <div class="mb-1">
                        <label class="form-label label-text">Image*</label>

                        <!-- Dropzone upload area -->
                        <div id="category-dropzone" class="dropzone border rounded p-3" style="cursor:pointer; min-height:150px;">
                            <div class="dz-message" data-dz-message>
                                <span>Drop image here or click to upload</span>

                            </div>
                        </div>

                        <!-- hidden input to store uploaded image_id -->
                        <input type="hidden" name="image_id" id="uploadedImage">
                    </div>
                    <span class="image-hint small text-end" >
                        Max size: 1MB | Dimensions: 512x512 px
                    </span>

                    <!-- Name in Arabic and English -->
                    <div class="row my-3">
                        <div class="col-6">
                            <label class="form-label label-text">Name (EN)</label>
                            <input type="text" class="form-control" placeholder="Enter Category Name(En)" id="add-category-name-en" name="name[en]" />
                        </div>
                        <div class="col-6">
                            <label class="form-label label-text">Name (AR)</label>
                            <input type="text" class="form-control" placeholder="Enter Category Name(Ar)" id="add-category-name-ar" name="name[ar]" />
                        </div>
                    </div>

                    <!-- Description in Arabic and English -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label label-text">Description (EN)</label>
                            <textarea class="form-control" id="add-category-description-en" placeholder="Enter Description Name(En)" name="description[en]" rows="2"></textarea>
                        </div>
                        <div class="col-6">
                            <label class="form-label label-text">Description (AR)</label>
                            <textarea class="form-control" id="add-category-description-ar" placeholder="Enter Description Name(Ar)" name="description[ar]" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary fs-5" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="saveChangesButton">
                        <span class="btn-text">Save</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status" aria-hidden="true"></span>
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

                    $("#uploadedImage").val(response.data.id); // store image_id
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
