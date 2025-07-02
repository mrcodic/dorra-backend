<div class="modal modal-slide-in new-user-modal fade" id="editMockupModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editMockupForm" enctype="multipart/form-data" action="{{ route('mockups.store') }}">
                @csrf
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Edit Mockup</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="">
                        <div class="form-group mb-2">
                            <label for="templateName" class="label-text mb-1">Mockup Name</label>
                            <input type="text" id="templateName" class="form-control" name="name"
                                   placeholder="Mockup Name">
                        </div>

                        <div class="form-group mb-2">
                            <label for="mockup-type" class="label-text mb-1">Mockup Type</label>
                            <select id="mockup-type" name="type" class="form-select">
                                <option value="" disabled>select mockup type</option>
                                @foreach(\App\Enums\Mockup\TypeEnum::cases() as $type)
                                    <option value="{{ $type->value }}"> {{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-2">
                            <label for="productsSelect" class="label-text mb-1">Product</label>
                            <select id="productsSelect" name="product_id" class="form-select select2" multiple>
                                <option value="" disabled>Choose product</option>
                                @foreach($associatedData['products'] as $product)
                                    <option
                                        value="{{ $product->id }}">{{ $product->getTranslation('name', app()->getLocale()) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label for="tagsSelect" class="label-text mb-1">Colors</label>
                            <button id="color-picker-trigger">Pick colors</button>
                            <ul id="selected-colors"></ul>
                            <input type="hidden" name="colors" id="colorsInput">
                        </div>
                        <div class="col-md-12">
                            <div class="mb-1">
                                <label class="form-label label-text" for="product-image-main">Mockup File</label>

                                <!-- Hidden real input -->
                                <input type="file" name="image" id="product-image-main" class="form-control d-none"
                                       accept="image/*">

                                <!-- Custom Upload Card -->
                                <div id="upload-area" class="upload-card">
                                    <div id="upload-content">
                                        <i data-feather="upload" class="mb-2"></i>
                                        <p>Drag file here to upload</p>
                                    </div>


                                </div>
                                <div>
                                    <!-- Progress Bar -->
                                    <div id="upload-progress" class="progress mt-2 d-none w-50">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                                             style="width: 0%"></div>
                                    </div>


                                    <!-- Uploaded Image Preview -->
                                    <div id="uploaded-image"
                                         class="uploaded-image d-none position-relative mt-1 d-flex align-items-center gap-2">
                                        <img src="" alt="Uploaded" class="img-fluid rounded"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                        <div id="file-details" class="file-details">
                                            <div class="file-name fw-bold"></div>
                                            <div class="file-size text-muted small"></div>
                                        </div>
                                        <button type="button" id="remove-image"
                                                class="btn btn-sm position-absolute text-danger"
                                                style="top: 5px; right: 5px; background-color: #FFEEED">
                                            <i data-feather="trash"></i>
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                        <span class="btn-text">Create</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status"
                              aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



