<div class="modal modal-slide-in new-user-modal fade" id="showOfferModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addDiscountForm" method="post" enctype="multipart/form-data" action="">
                @csrf
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">View Offer</h5>
                </div>
                <div class="modal-body flex-grow-1">


                    <div class="form-group mb-2">
                        <label for="createPrefix" class="label-text mb-1">Offer Name</label>
                        <input type="text" name="code" id="showOfferName" class="form-control" placeholder="Enter offer’s name" disabled />
                    </div>

                    <div class="form-group mb-2">
                        <label for="createDiscountValue" class="label-text mb-1">Offer Value (%)</label>
                        <input type="text" name="value" id="showOfferValue" class="form-control" placeholder="Enter offer’s value " disabled />
                    </div>



                    <!-- Radio switch for Products or Categories -->
                    <div class="form-group mb-2">
                        <label class="label-text mb-1 d-block">Type</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="scope" id="showApplyToProducts" value="1" checked disabled />
                            <label class="form-check-label text-black fs-16" for="showApplyToProducts">Products</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="scope" id="showApplyToCategories" value="2" disabled />
                            <label class="form-check-label text-black fs-16" for="showApplyToCategories">Categories</label>
                        </div>
                    </div>
                    {{-- Applied to --}}
                    <div class="form-group mb-2">
                        <label class="label-text mb-1 d-block">Applied To</label>

                        {{-- Products --}}
                        <div id="showProductsWrap" class="d-none">
                            <div class="small text-muted mb-1"></div>
                            <div id="showProducts" class="d-flex flex-wrap gap-1">Products</div>
                        </div>

                        {{-- Categories --}}
                        <div id="showCategoriesWrap" class="d-none">
                            <div class="small text-muted mb-1">Categories</div>
                            <div id="showCategories" class="d-flex flex-wrap gap-1"></div>
                        </div>
                    </div>
                    <div class="row">
                            <div class="col  mb-2">
                                <label class="form-label">Start Date</label>
                                <input id="showStartDate" type="date" class="form-control"  disabled />
                            </div>
                        <div class="col mb-2">
                            <label class="form-label">End Date</label>
                            <input  id="showEndDate" type="date" class="form-control"  disabled />
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
