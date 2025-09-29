<div class="modal modal-slide-in new-user-modal fade" id="editJobModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editJobForm" method="POST" enctype="multipart/form-data" action="">
                @csrf
                @method("PUT")
                <input type="hidden" id="edit-job-id" name="id">

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3">Edit Job</h5>
                </div>

                <div class="modal-body flex-grow-1">

                    <!-- Image Upload (unchanged) -->
                    <div class="mb-1">
                        <label class="form-label label-text">Image*</label>
                        <div id="edit-category-dropzone"
                             class="d-flex align-items-center justify-content-center dropzone rounded p-3 text-center"
                             style="border: 2px dashed rgba(0,0,0,.3);">
                            <div class="dz-message" data-dz-message>
                                <span>Drop photo here or click to upload</span>
                            </div>
                        </div>
                        <input type="hidden" name="image_id" id="editUploadedImage">
                        <span class="image-hint small d-block mt-25">Max size: 1MB | 512×512 px</span>
                    </div>

                    <!-- Uploaded Image Preview -->
                    <div id="edit-uploaded-image" class="uploaded-image d-none position-relative mt-1 d-flex align-items-center gap-2">
                        <img src="" id="edit-preview-image" alt="Uploaded" class="img-fluid rounded"
                             style="width:50px;height:50px;object-fit:cover;">
                        <div id="edit-file-details" class="file-details">
                            <div class="file-name fw-bold"></div>
                            <div class="file-size text-muted small"></div>
                        </div>
                    </div>

                    <!-- Name / Description -->
                    <div class="row my-1">
                        <div class="col-md-6">
                            <label class="form-label label-text">Name (EN)</label>
                            <input type="text" class="form-control" id="edit-category-name-en" name="name[en]" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label label-text">Name (AR)</label>
                            <input type="text" class="form-control" id="edit-category-name-ar" name="name[ar]" />
                        </div>
                    </div>

                    <div class="row mb-1">
                        <div class="col-lg-6">
                            <label class="form-label label-text">Description (EN)</label>
                            <textarea class="form-control" id="edit-category-description-en" name="description[en]" rows="2"></textarea>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label label-text">Description (AR)</label>
                            <textarea class="form-control" id="edit-category-description-ar" name="description[ar]" rows="2"></textarea>
                        </div>
                    </div>

                    <!-- NEW: Station / Status / Priority -->
                    <div class="row mb-1">
                        <div class="col-md-4">
                            <label class="form-label label-text">Station</label>
                            <select class="form-select" id="edit-station-id" name="station_id">
                                <!-- Prefer server-rendered options; fallback is JS (see below) -->
                                @isset($stations)
                                    @foreach($stations as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label label-text">Status</label>
                            <select class="form-select" id="edit-status" name="status">
                                @isset($statuses)
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label label-text">Priority</label>
                            <select class="form-select" id="edit-priority" name="priority">
                                @php
                                    $priorities = \App\Enums\JobTicket\PriorityEnum::toArray();
                                @endphp
                                @foreach($priorities as $p)
                                    <option value="{{ $p['value'] }}">{{ $p['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary fs-5" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5" id="editSaveChangesButton">
                        <span class="btn-text">Save Changes</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
