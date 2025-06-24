<div class="modal fade" id="selectLocationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-edit-user">


        <div class="add-new-user modal-content ">
            <div class="modal-header mb-1 mt-0 pt-0">
                <h5 class="modal-title fs-3" id="exampleModalLabel">Select Pick up Location</h5>
            </div>

            <div class="modal-body flex-grow-1">
            <div class="mb-3">
                <input type="text" class="form-control" placeholder="Search for a location..." id="locationSearch">
            </div>


            <div id="locationList" class="mb-3"></div> 


                <div id="mapPlaceholder" style="height: 300px; background-color: #f0f0f0; border-radius: 8px;">
                    <p class="text-center text-muted pt-5">Map will display here based on search</p>
                </div>
            </div>
            
            <div class="d-flex justify-content-end m-2">
    <button class="btn btn-outline-secondary me-1" data-bs-dismiss="modal">cancel</button>
    <button class="btn btn-primary">confirm</button>
  </div>
        </div>
    </div>
</div>



