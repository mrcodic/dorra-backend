<div class="modal modal-slide-in new-user-modal fade" id="addOfferModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="addDiscountForm" method="post" enctype="multipart/form-data" action="{{ route('discount-codes.store') }}">
                @csrf
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Add New Offer</h5>
                </div>
                <div class="modal-body flex-grow-1">


                    <div class="form-group mb-2">
                        <label for="createPrefix" class="label-text mb-1">Offer Name</label>
                        <input type="text" name="code" id="createPrefix" class="form-control" placeholder="Enter offer’s name">
                    </div>

                    <div class="form-group mb-2">
                        <label for="createDiscountValue" class="label-text mb-1">Offer Value (%)</label>
                        <input type="text" name="value" id="createDiscountValue" class="form-control" placeholder="Enter offer’s value ">
                    </div>



                    <!-- Radio switch for Products or Categories -->
                    <div class="form-group mb-2">
                        <label class="label-text mb-1 d-block">Type</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="scope" id="applyToProducts" value="2" checked>
                            <label class="form-check-label text-black fs-16" for="applyToProducts">Products</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="scope" id="applyToCategories" value="1">
                            <label class="form-check-label text-black fs-16" for="applyToCategories">Categories</label>
                        </div>
                    </div>
                    <div class="row">
                    <div class="col  mb-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" value="2024-05-26" >
                    </div>
                    <div class="col mb-2">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" value="2024-05-26" >
                    </div>

</div>

                </div>
                <div class="modal-footer border-top-0 d-flex justify-content-end">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                   <button type="button" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                            <span>Add</span>
                            <span id="saveLoader" class="spinner-border spinner-border-sm d-none saveLoader" role="status" aria-hidden="true"></span>
                        </button>

                </div>
            </form>
        </div>
    </div>
</div>