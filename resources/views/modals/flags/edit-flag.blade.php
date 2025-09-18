<div class="modal modal-slide-in new-user-modal fade" id="editFlagModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editFlagForm" enctype="multipart/form-data" action="">
                @csrf
                @method("PUT")
                <button type="button" class="btn-close " data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Edit Flag</h5>
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
                    <div class="form-group mb-2">
                        <label for="editTemplatesSelect" class="label-text mb-1">Templates</label>
                        <select id="editTemplatesSelect" class="form-select select2" name="templates[]" multiple>
                            @foreach($associatedData['templates'] as $template)
                                <option value="{{ $template->id }}">
                                    {{ $template->getTranslation('name', app()->getLocale()) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label for="editProductsSelect" class="label-text mb-1">Categories</label>
                        <select id="editProductsSelect" class="form-select select2" name="products[]" multiple>
                            @foreach($associatedData['products'] as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->getTranslation('name', app()->getLocale()) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

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
<script !src="">
    $(document).ready(function () {
        $('#editTemplatesSelect').select2({
            placeholder: "Choose Templates",
            allowClear: true
        });
        $('#editProductsSelect').select2({
            placeholder: "Choose Products",
            allowClear: true
        });
    });

</script>
