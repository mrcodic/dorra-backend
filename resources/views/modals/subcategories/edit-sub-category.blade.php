<div class="modal fade modal-slide-in new-user-modal" id="editSubCategoryModal">
    <div class="modal-dialog">
        <div class="modal-content pt-0">
            <form id="editSubCategoryForm" enctype="multipart/form-data" action="">
                @csrf
                @method('PUT')
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

                <div class="modal-header">
                    <h5 class="modal-title" id="editSubCategoryModalLabel">Edit Sub Product</h5>

                </div>

                <div class="modal-body">
                    <input type="hidden" id="edit-sub-category-id">
                    <!-- Image Upload -->
                    <div class="mb-1">
                        <label class="form-label label-text">Image*</label>
                        <div id="edit-category-dropzone"
                             class="d-flex align-items-center justify-content-center dropzone rounded p-3 text-center"
                             style="border: 2px dashed rgba(0, 0, 0, 0.3);">
                            <div class="dz-message" data-dz-message>
                                <span>Drop photo here or click to upload</span>
                            </div>
                        </div>
                        <input type="hidden" name="sub_image_id" id="editUploadedImage">
                    </div>
                    <span class="image-hint small">
                        Max size: 1MB | Dimensions: 512x512 px
                    </span>

                    <!-- Upload Progress -->
                    <div id="edit-upload-progress" class="progress mt-2 d-none w-50">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                    </div>

                    <!-- Uploaded Image Preview -->
                    <div id="edit-uploaded-image"
                         class="uploaded-image d-none position-relative mt-1 d-flex align-items-center gap-2">
                        <img src="" id="edit-preview-image" alt="Uploaded" class="img-fluid rounded"
                             style="width: 50px; height: 50px; object-fit: cover;">
                        <div id="edit-file-details" class="file-details">
                            <div class="file-name fw-bold"></div>
                            <div class="file-size text-muted small"></div>
                            </div>
                    <div class="mb-1">
                        <label for="edit-sub-category-name-en" class="form-label label-text">Name (EN)</label>
                        <input type="text" class="form-control" id="edit-sub-category-name-en" name="name[en]">
                    </div>

                    <div class="mb-1">
                        <label for="edit-sub-category-name-ar" class="form-label label-text">Name (AR)</label>
                        <input type="text" class="form-control" id="edit-sub-category-name-ar" name="name[ar]">
                    </div>

                    <div class="mb-1">
                        <label for="edit-sub-category-parent-id" class="form-label label-text">Main Product</label>
                        <select name="parent_id" class="form-select">
                            <option value="" disabled selected>Choose Main Product</option>
                            @foreach($associatedData['categories'] as $category)
                            <option value="{{ $category->id }}">{{ $category->getTranslation('name', app()->getLocale())
                                }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary " data-bs-dismiss="modal">Cancel</button>
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
<script !src="">
    Dropzone.autoDiscover = false;

    const editDropzone = new Dropzone("#edit-category-dropzone", {
        url: "{{ route('media.store') }}", // adjust to your media upload route
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
                    $("#editUploadedImage").val(response.data.id); // store sub_image_id
                    $("#edit-preview-image").attr("src", response.data.url);
                }

            });
            this.on("removedfile", function (file) {
                $("#editUploadedImage").val("");
                if (file._hiddenInputId) {
                    fetch("{{ url('api/v1/media') }}/" + file._hiddenInputId, {
                        method: "DELETE",
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    $("#edit-preview-image").attr("src", "").addClass("d-none");

                }
            });
            // On remove -> clear hidden input

        },
    });

</script>
