<div class="modal modal-slide-in new-user-modal fade" id="showMessage">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editTagForm" enctype="multipart/form-data" action="">
                @csrf
                @method("PUT")
                <button type="button" class="btn-close " data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3" id="exampleModalLabel">Show Question</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="">
                        <div class="mb-2">
                            <label class="form-label ">Name</label>
                            <input type="text" class="form-control" disabled />
                        </div>
                        <div class="mb-2">
                            <label class="form-label ">Email Address</label>
                            <input type="text" class="form-control" disabled />
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" disabled />
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" rows="2" disabled></textarea>
                        </div>
                        <div class=" mb-2">
                            <label class="form-label">Added Date</label>
                            <input type="date" class="form-control" value="2024-05-26" disabled>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Reply</label>
                            <textarea class="form-control" rows="2" placeholder="Write your question here"></textarea>
                        </div>
                    </div>
                </div>
        </div>
        <div class="modal-footer border-top-0">
            <button type="button" class="btn btn-outline-danger fs-5" data-bs-dismiss="modal">Delete</button>
            <button type="submit" class="btn btn-primary fs-5" id="saveChangesButton">Send Reply</button>
        </div>
        </form>
    </div>
</div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        feather.replace();
        const shipRadio = document.getElementById("shipToCustomer");
        const pickupRadio = document.getElementById("pickUp");
        const shipSection = document.getElementById("shipSection");
        const pickupSection = document.getElementById("pickupSection");

        function toggleSections() {
            if (shipRadio.checked) {
                shipSection.style.display = "block";
                pickupSection.style.display = "none";
            } else {
                shipSection.style.display = "none";
                pickupSection.style.display = "block";
            }
        }

        shipRadio.addEventListener("change", toggleSections);
        pickupRadio.addEventListener("change", toggleSections);
        toggleSections();
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const shipRadio = document.getElementById("shipToCustomer");
        const pickupRadio = document.getElementById("pickUp");
        const shipSection = document.getElementById("shipSection");
        const pickupSection = document.getElementById("pickupSection");
        const changeLocationSection = document.getElementById("changeLocationSection");
        const shippingMethodSection = document.getElementById("shippingMethodSection");

        const changeLocationBtn = pickupSection.querySelector(".lined-btn");
        const backToPickupBtn = document.getElementById("backToPickup");

        function toggleSections() {
            if (shipRadio.checked) {
                shipSection.style.display = "block";
                pickupSection.style.display = "none";
                changeLocationSection.style.display = "none";
                shippingMethodSection.style.display = "block";
            } else {
                shipSection.style.display = "none";
                pickupSection.style.display = "block";
                changeLocationSection.style.display = "none";
                shippingMethodSection.style.display = "block";
            }
        }

        shipRadio.addEventListener("change", toggleSections);
        pickupRadio.addEventListener("change", toggleSections);
        toggleSections();

        changeLocationBtn.addEventListener("click", function() {
            pickupSection.style.display = "none";
            changeLocationSection.style.display = "block";
            shippingMethodSection.style.display = "none";
        });

        backToPickupBtn.addEventListener("click", function() {
            changeLocationSection.style.display = "none";
            pickupSection.style.display = "block";
            shippingMethodSection.style.display = "block";
        });

        // Initialize feather icons
        feather.replace();
    });
</script>